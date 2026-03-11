#!/usr/bin/env php
<?php
/**
 * migrate_from_dadtoodb.php
 *
 * One-time migration from the legacy dadtoodb schema into the new checkin schema.
 *
 * Usage:
 *   php scripts/migrate_from_dadtoodb.php [--dry-run] [--source=dadtoodb_import]
 *
 * What it migrates:
 *   1. visiting_persons  → hosts        (6 counselors)
 *   2. visit_reasons     → visit_reasons (5 reasons)
 *   3. users (only those with visits) → visitors (~520 students)
 *   4. checkin_checkout  → visits       (6,430 records)
 *
 * Safe to run multiple times — skips already-migrated records.
 */

define('BASE_PATH', dirname(__DIR__));

$opts   = getopt('', ['dry-run', 'source:']);
$dryRun = isset($opts['dry-run']);
$srcDb  = $opts['source'] ?? 'dadtoodb_import';

$dbCfg  = require BASE_PATH . '/config/database.local.php';
$dstDsn = "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$dbCfg['database']};charset=utf8mb4";

$srcDsn = "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$srcDb};charset=utf8mb4";

try {
    $src = new PDO($srcDsn, $dbCfg['username'], $dbCfg['password'], [
        PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $dst = new PDO($dstDsn, $dbCfg['username'], $dbCfg['password'], [
        PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$org = $dst->query("SELECT organization_id FROM organizations LIMIT 1")->fetch();
if (!$org) die("No organization found. Run the install wizard first.\n");
$orgId = (int)$org['organization_id'];

out("Source database : {$srcDb}");
out("Destination     : {$dbCfg['database']}");
out("Organization ID : {$orgId}");
out($dryRun ? "MODE            : DRY RUN (no data will be written)" : "MODE            : LIVE");
out(str_repeat('─', 60));

// ── 1. visiting_persons → hosts ───────────────────────────────────
out("\n[1/4] Migrating hosts (visiting_persons → hosts)");

$persons = $src->query("SELECT * FROM visiting_persons ORDER BY person_id")->fetchAll();
$hostMap = [];

$chkHost = $dst->prepare("SELECT host_id FROM hosts WHERE organization_id = ? AND name = ?");
$insHost = $dst->prepare(
    "INSERT INTO hosts (organization_id, name, active, created_at) VALUES (?, ?, 1, NOW())"
);

foreach ($persons as $p) {
    $chkHost->execute([$orgId, $p['person_name']]);
    $ex = $chkHost->fetch();
    if ($ex) {
        $hostMap[$p['person_id']] = (int)$ex['host_id'];
        out("  SKIP  {$p['person_name']} (host_id={$ex['host_id']})");
    } elseif ($dryRun) {
        out("  WOULD ADD  {$p['person_name']}");
    } else {
        $insHost->execute([$orgId, $p['person_name']]);
        $id = (int)$dst->lastInsertId();
        $hostMap[$p['person_id']] = $id;
        out("  ADDED  {$p['person_name']} → host_id={$id}");
    }
}
out("  Total: " . count($persons) . " hosts");

// ── 2. visit_reasons → visit_reasons ─────────────────────────────
out("\n[2/4] Migrating visit reasons");

$reasons   = $src->query("SELECT * FROM visit_reasons ORDER BY reason_id")->fetchAll();
$reasonMap = [];

$chkReason = $dst->prepare("SELECT reason_id FROM visit_reasons WHERE organization_id = ? AND label = ?");
$insReason = $dst->prepare(
    "INSERT INTO visit_reasons (organization_id, label, active, sort_order) VALUES (?, ?, 1, ?)"
);

foreach ($reasons as $i => $r) {
    $chkReason->execute([$orgId, $r['reason_description']]);
    $ex = $chkReason->fetch();
    if ($ex) {
        $reasonMap[$r['reason_id']] = (int)$ex['reason_id'];
        out("  SKIP  {$r['reason_description']}");
    } elseif ($dryRun) {
        out("  WOULD ADD  {$r['reason_description']}");
    } else {
        $insReason->execute([$orgId, $r['reason_description'], $i + 1]);
        $id = (int)$dst->lastInsertId();
        $reasonMap[$r['reason_id']] = $id;
        out("  ADDED  {$r['reason_description']} → reason_id={$id}");
    }
}
out("  Total: " . count($reasons) . " reasons");

// ── 3. users → visitors (only those with actual visits) ───────────
out("\n[3/4] Migrating visitors (users referenced in checkin_checkout only)");

// Only fetch users who appear in checkin_checkout — avoids loading ~10M rows
$activeVisitors = $src->query(
    "SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email
     FROM users u
     INNER JOIN checkin_checkout cc ON cc.user_id = u.user_id
     WHERE u.first_name IS NOT NULL AND u.first_name != ''"
)->fetchAll();

$visitorMap = [];
$added = 0; $skipped = 0;

$chkEmail  = $dst->prepare("SELECT visitor_id FROM visitors WHERE email = ?");
$chkName   = $dst->prepare("SELECT visitor_id FROM visitors WHERE first_name = ? AND last_name = ?");
$insVis    = $dst->prepare(
    "INSERT INTO visitors (first_name, last_name, email, phone, created_at) VALUES (?, ?, ?, NULL, NOW())"
);

foreach ($activeVisitors as $v) {
    $email = trim($v['email'] ?? '') ?: null;

    // Match by email
    if ($email) {
        $chkEmail->execute([$email]);
        $ex = $chkEmail->fetch();
        if ($ex) { $visitorMap[$v['user_id']] = (int)$ex['visitor_id']; $skipped++; continue; }
    }

    // Match by name
    $chkName->execute([$v['first_name'], $v['last_name']]);
    $ex = $chkName->fetch();
    if ($ex) { $visitorMap[$v['user_id']] = (int)$ex['visitor_id']; $skipped++; continue; }

    // Insert
    if (!$dryRun) {
        $insVis->execute([$v['first_name'], $v['last_name'], $email]);
        $id = (int)$dst->lastInsertId();
        $visitorMap[$v['user_id']] = $id;
    }
    $added++;
}
out("  Active visitors: " . count($activeVisitors) . " | Added: {$added} | Already existed: {$skipped}");

// ── 4. checkin_checkout → visits ─────────────────────────────────
out("\n[4/4] Migrating visits (checkin_checkout → visits)");

// Build set of already-migrated legacy IDs (for idempotency)
$done = [];
if (!$dryRun) {
    $done = array_flip(
        $dst->query("SELECT legacy_id FROM visits WHERE legacy_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN)
    );
}

$visits = $src->query(
    "SELECT * FROM checkin_checkout ORDER BY record_id"
)->fetchAll();

$insVisit = $dst->prepare(
    "INSERT INTO visits
        (organization_id, visitor_id, host_id, reason_id,
         check_in_time, check_out_time, status, legacy_id, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$inserted = 0; $skippedV = 0; $unmapped = 0;

foreach ($visits as $cc) {
    $lid = (int)$cc['record_id'];
    if (isset($done[$lid])) { $skippedV++; continue; }

    $visitorId = $visitorMap[$cc['user_id']] ?? null;
    if (!$visitorId) { $unmapped++; continue; }

    $hostId   = $hostMap[$cc['visiting_person_id']] ?? null;
    $reasonId = $reasonMap[$cc['visit_reason_id']]  ?? null;
    $checkOut = $cc['checkout_time'] ?: null;
    $status   = $checkOut ? 'completed' : 'checked_in';

    if (!$dryRun) {
        $insVisit->execute([
            $orgId, $visitorId, $hostId, $reasonId,
            $cc['checkin_time'], $checkOut, $status, $lid,
            $cc['created_at'] ?? $cc['checkin_time'],
            $cc['updated_at'] ?? $cc['checkin_time'],
        ]);
    }
    $inserted++;
}

out("  Total: " . count($visits) . " | Migrated: {$inserted} | Already done: {$skippedV} | Unmapped: {$unmapped}");

out("\n" . str_repeat('─', 60));
out($dryRun
    ? "DRY RUN complete — no data written. Remove --dry-run to apply."
    : "✓ Migration complete."
);

function out(string $s): void { echo $s . "\n"; }
