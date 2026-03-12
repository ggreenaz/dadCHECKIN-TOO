#!/usr/bin/env php
<?php
/**
 * Demo Data Seeder — dadCHECKIN-TOO
 *
 * Creates realistic demo data so new users can explore the application
 * before going live. All IDs are tracked in org settings under 'demo_data'
 * so every record can be cleanly expunged later.
 *
 * Usage:
 *   php scripts/seed_demo.php [--org-id=1]
 *
 * Expunge via: Admin → Settings → Expunge Demo Data
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/bootstrap.php';

use App\Core\Database;

$db    = Database::getInstance();
$orgId = null;

foreach ($argv ?? [] as $arg) {
    if (preg_match('/--org-id=(\d+)/', $arg, $m)) $orgId = (int)$m[1];
}
if (!$orgId) {
    $row   = $db->query("SELECT organization_id FROM organizations LIMIT 1")->fetch();
    $orgId = $row ? (int)$row['organization_id'] : null;
}
if (!$orgId) {
    echo "ERROR: No organization found. Run the installer first.\n";
    exit(1);
}

// Guard against double-seeding
$orgRow   = $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
$orgRow->execute([$orgId]);
$settings = json_decode($orgRow->fetchColumn() ?? '{}', true) ?: [];

if (!empty($settings['demo_data'])) {
    echo "Demo data already loaded for org #{$orgId}. Skipping.\n";
    exit(0);
}

echo "Seeding demo data for org #{$orgId}...\n";

$demoIds = [
    'department_ids' => [],
    'host_ids'       => [],
    'reason_ids'     => [],
    'visitor_ids'    => [],
    'visit_ids'      => [],
];

// ── 1. Departments ────────────────────────────────────────────────
$departments = [
    'Administration',
    'Human Resources',
    'Information Technology',
    'Facilities & Operations',
    'Finance & Accounting',
];

$insDept = $db->prepare("INSERT INTO departments (organization_id, name) VALUES (?, ?)");
$deptIds = [];
foreach ($departments as $dname) {
    $chk = $db->prepare("SELECT department_id FROM departments WHERE organization_id = ? AND name = ?");
    $chk->execute([$orgId, $dname]);
    $row = $chk->fetch();
    if ($row) {
        $deptIds[$dname] = (int)$row['department_id'];
    } else {
        $insDept->execute([$orgId, $dname]);
        $id = (int)$db->lastInsertId();
        $deptIds[$dname]         = $id;
        $demoIds['department_ids'][] = $id;
    }
}
echo "  ✓ " . count($departments) . " departments\n";

// ── 2. Hosts ──────────────────────────────────────────────────────
$hosts = [
    ['name'=>'Sarah Mitchell',   'email'=>'s.mitchell@demo.local',  'dept'=>'Administration'],
    ['name'=>'James Thornton',   'email'=>'j.thornton@demo.local',  'dept'=>'Administration'],
    ['name'=>'Michael Chen',     'email'=>'m.chen@demo.local',      'dept'=>'Administration'],
    ['name'=>'Patricia Nguyen',  'email'=>'p.nguyen@demo.local',    'dept'=>'Human Resources'],
    ['name'=>'David Okafor',     'email'=>'d.okafor@demo.local',    'dept'=>'Human Resources'],
    ['name'=>'Michelle Torres',  'email'=>'m.torres@demo.local',    'dept'=>'Human Resources'],
    ['name'=>'Angela Foster',    'email'=>'a.foster@demo.local',    'dept'=>'Human Resources'],
    ['name'=>'Kevin Patel',      'email'=>'k.patel@demo.local',     'dept'=>'Information Technology'],
    ['name'=>'Amanda Reyes',     'email'=>'a.reyes@demo.local',     'dept'=>'Information Technology'],
    ['name'=>'Christopher Lee',  'email'=>'c.lee@demo.local',       'dept'=>'Information Technology'],
    ['name'=>'Daniel Castillo',  'email'=>'d.castillo@demo.local',  'dept'=>'Information Technology'],
    ['name'=>'Robert Simmons',   'email'=>'r.simmons@demo.local',   'dept'=>'Facilities & Operations'],
    ['name'=>'Lisa Kowalski',    'email'=>'l.kowalski@demo.local',  'dept'=>'Facilities & Operations'],
    ['name'=>'Thomas Bryant',    'email'=>'t.bryant@demo.local',    'dept'=>'Finance & Accounting'],
    ['name'=>'Jennifer Walsh',   'email'=>'j.walsh@demo.local',     'dept'=>'Finance & Accounting'],
];

$insHost = $db->prepare(
    "INSERT INTO hosts (organization_id, department_id, name, email, active) VALUES (?, ?, ?, ?, 1)"
);
foreach ($hosts as $h) {
    $insHost->execute([$orgId, $deptIds[$h['dept']] ?? null, $h['name'], $h['email']]);
    $demoIds['host_ids'][] = (int)$db->lastInsertId();
}
echo "  ✓ " . count($hosts) . " hosts\n";
$hostIdList = $demoIds['host_ids'];

// ── 3. Visit Reasons ──────────────────────────────────────────────
$reasons = [
    'Job Interview',
    'Vendor / Supplier Meeting',
    'IT Support',
    'Delivery / Package Pickup',
    'Employee Guest',
    'Training Session',
    'Contractor',
    'General Inquiry',
];

$insReason = $db->prepare(
    "INSERT INTO visit_reasons (organization_id, label, active) VALUES (?, ?, 1)"
);
foreach ($reasons as $label) {
    $chk = $db->prepare("SELECT reason_id FROM visit_reasons WHERE organization_id = ? AND label = ?");
    $chk->execute([$orgId, $label]);
    if (!$chk->fetch()) {
        $insReason->execute([$orgId, $label]);
        $demoIds['reason_ids'][] = (int)$db->lastInsertId();
    }
}

$reasonStmt = $db->prepare("SELECT reason_id FROM visit_reasons WHERE organization_id = ? AND active = 1");
$reasonStmt->execute([$orgId]);
$reasonIdList = $reasonStmt->fetchAll(\PDO::FETCH_COLUMN);
echo "  ✓ " . count($reasons) . " visit reasons\n";

// ── 4. Visitors ───────────────────────────────────────────────────
$firstNames = [
    'Emma','Liam','Olivia','Noah','Ava','William','Sophia','James','Isabella','Oliver',
    'Mia','Benjamin','Charlotte','Elijah','Amelia','Lucas','Harper','Mason','Evelyn','Logan',
    'Abigail','Alexander','Emily','Ethan','Elizabeth','Daniel','Sofia','Matthew','Avery','Aiden',
    'Ella','Henry','Scarlett','Michael','Grace','Jackson','Victoria','Sebastian','Riley','Carter',
    'Aria','Owen','Lily','Wyatt','Eleanor','John','Hannah','Jack','Lillian','Luke',
    'Natalie','Jayden','Addison','Dylan','Aubrey','Grayson','Ellie','Levi','Stella','Isaac',
    'Nora','Ryan','Zoey','Nathan','Penelope','Aaron','Leah','Isaiah','Hazel','Connor',
    'Violet','Adrian','Aurora','Charles','Savannah','Eli','Audrey','Josiah','Brooklyn','Hunter',
];
$lastNames = [
    'Smith','Johnson','Williams','Brown','Jones','Garcia','Miller','Davis','Martinez','Wilson',
    'Anderson','Taylor','Thomas','Hernandez','Moore','Martin','Jackson','Thompson','White','Lopez',
    'Lee','Gonzalez','Harris','Clark','Lewis','Robinson','Walker','Perez','Hall','Young',
    'Allen','Sanchez','Wright','King','Scott','Green','Baker','Adams','Nelson','Carter',
    'Mitchell','Roberts','Turner','Phillips','Campbell','Parker','Evans','Edwards','Collins',
    'Stewart','Flores','Morris','Nguyen','Murphy','Rivera','Cook','Rogers','Morgan','Peterson',
    'Cooper','Reed','Bailey','Bell','Gomez','Kelly','Howard','Ward','Cox','Diaz',
    'Richardson','Wood','Watson','Brooks','Bennett','Gray','James','Reyes','Cruz','Hughes',
];
$domains = ['gmail.com','yahoo.com','outlook.com','hotmail.com','icloud.com'];

$insVisitor = $db->prepare(
    "INSERT INTO visitors (first_name, last_name, email, phone) VALUES (?, ?, ?, ?)"
);

$used = [];
$visitorIdList = [];
for ($i = 0; $i < 80; $i++) {
    do {
        $fn  = $firstNames[array_rand($firstNames)];
        $ln  = $lastNames[array_rand($lastNames)];
        $key = "$fn $ln";
    } while (isset($used[$key]));
    $used[$key] = true;

    $email = strtolower($fn[0] . $ln) . rand(10, 99) . '@' . $domains[array_rand($domains)];
    $area  = [212,310,415,512,617,720,808,312,404,214][array_rand([0,1,2,3,4,5,6,7,8,9])];
    $phone = "($area) " . rand(200,999) . '-' . str_pad(rand(0,9999),4,'0',STR_PAD_LEFT);

    $insVisitor->execute([$fn, $ln, $email, $phone]);
    $vid = (int)$db->lastInsertId();
    $visitorIdList[]         = $vid;
    $demoIds['visitor_ids'][] = $vid;
}
echo "  ✓ 80 visitors\n";

// ── 5. Visits — 90 days of history ───────────────────────────────
$insVisit = $db->prepare(
    "INSERT INTO visits
     (organization_id, visitor_id, host_id, reason_id,
      check_in_time, check_out_time, status, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$now        = time();
$visitCount = 0;
$target     = 420;
$attempts   = 0;

// Hour weight pool — business hours, heavier mid-morning and after lunch
$hourPool = [];
$hourWeights = [7=>2, 8=>9, 9=>11, 10=>10, 11=>8, 12=>4, 13=>8, 14=>10, 15=>9, 16=>6, 17=>2];
foreach ($hourWeights as $h => $w) {
    for ($j = 0; $j < $w; $j++) $hourPool[] = $h;
}

// Duration pool — minutes
$durPool = [];
$durRanges = [[15,25,10],[25,45,25],[45,75,30],[75,120,20],[120,180,10],[5,15,5]];
foreach ($durRanges as [$lo,$hi,$w]) {
    for ($j = 0; $j < $w; $j++) $durPool[] = rand($lo, $hi);
}

while ($visitCount < $target && $attempts < 3000) {
    $attempts++;
    $daysAgo = rand(1, 90);
    $base    = strtotime("-{$daysAgo} days", $now);
    $dow     = (int)date('N', $base); // 1=Mon … 7=Sun

    // Skip weekends ~75% of the time
    if ($dow >= 6 && rand(1,4) <= 3) continue;

    $hour   = $hourPool[array_rand($hourPool)];
    $min    = rand(0,59);
    $checkInTs = mktime($hour, $min, rand(0,59),
        (int)date('n',$base), (int)date('j',$base), (int)date('Y',$base));

    $dur = $durPool[array_rand($durPool)];

    $roll = rand(1,100);
    if ($roll <= 78) {
        $status   = 'completed';
        $checkOut = date('Y-m-d H:i:s', $checkInTs + $dur * 60);
    } elseif ($roll <= 87) {
        $status   = 'no_show';
        $checkOut = null;
    } else {
        $status   = 'auto_completed';
        $checkOut = date('Y-m-d H:i:s', $checkInTs); // zero-duration
    }

    $checkIn   = date('Y-m-d H:i:s', $checkInTs);
    $visitorId = $visitorIdList[array_rand($visitorIdList)];
    $hostId    = $hostIdList[array_rand($hostIdList)];
    $reasonId  = $reasonIdList ? $reasonIdList[array_rand($reasonIdList)] : null;

    $insVisit->execute([
        $orgId, $visitorId, $hostId, $reasonId,
        $checkIn, $checkOut, $status, $checkIn, $checkIn,
    ]);
    $demoIds['visit_ids'][] = (int)$db->lastInsertId();
    $visitCount++;
}

// Add 8 active check-ins right now so Live Logs has something to show
$staggerMins = [175, 142, 118, 93, 76, 54, 38, 17];
foreach ($staggerMins as $minsAgo) {
    $checkInTs = $now - ($minsAgo * 60);
    $checkIn   = date('Y-m-d H:i:s', $checkInTs);
    $visitorId = $visitorIdList[array_rand($visitorIdList)];
    $hostId    = $hostIdList[array_rand($hostIdList)];
    $reasonId  = $reasonIdList ? $reasonIdList[array_rand($reasonIdList)] : null;

    $insVisit->execute([
        $orgId, $visitorId, $hostId, $reasonId,
        $checkIn, null, 'checked_in', $checkIn, $checkIn,
    ]);
    $demoIds['visit_ids'][] = (int)$db->lastInsertId();
    $visitCount++;
}

echo "  ✓ {$visitCount} visits (90 days + 8 active now)\n";

// ── 6. Save demo registry ─────────────────────────────────────────
$latestSettings = json_decode(
    $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?")
       ->execute([$orgId]) ? $db->query("SELECT settings FROM organizations WHERE organization_id = {$orgId}")->fetchColumn() : '{}',
    true
) ?: [];
$latestSettings['demo_data'] = array_merge($demoIds, ['seeded_at' => date('Y-m-d H:i:s')]);

$db->prepare("UPDATE organizations SET settings = ? WHERE organization_id = ?")
   ->execute([json_encode($latestSettings), $orgId]);

echo "\n✓ Demo data loaded. To remove it: Admin → Settings → Expunge Demo Data\n";
