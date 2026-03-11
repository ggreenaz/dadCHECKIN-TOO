<?php
/**
 * Refresh Live Demo Visits
 *
 * Resets the check_in_time of all currently active (checked_in) visits
 * to staggered times within the last 3 hours, so Live Logs always
 * looks fresh whenever you need to demonstrate it.
 *
 * Run: php scripts/refresh_live.php
 */

define('ROOT', dirname(__DIR__));
$dbCfg = require ROOT . '/config/database.local.php';

$pdo = new PDO(
    "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$dbCfg['database']};charset=utf8mb4",
    $dbCfg['username'],
    $dbCfg['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$orgId = 1;
$now   = time();

// Get all currently active visit IDs
$ids = $pdo->query(
    "SELECT visit_id FROM visits
     WHERE organization_id = $orgId AND status = 'checked_in'"
)->fetchAll(PDO::FETCH_COLUMN);

if (empty($ids)) {
    echo "No active visits found. Run seed_demo.php first.\n";
    exit;
}

// Spread check-in times evenly across the last 0–3 hours
// First visitor = 3h ago (red), last = ~5 min ago (green)
$count    = count($ids);
$maxMins  = 180; // 3 hours spread

$update = $pdo->prepare(
    "UPDATE visits SET check_in_time = ? WHERE visit_id = ?"
);

foreach ($ids as $i => $visitId) {
    // Distribute: last person is newest, first person is oldest
    $fraction  = ($count > 1) ? ($i / ($count - 1)) : 0;
    $minsAgo   = (int)round($maxMins * $fraction);
    $minsAgo   = max(5, $minsAgo); // at least 5 minutes ago
    $checkInTs = $now - ($minsAgo * 60);
    $checkIn   = date('Y-m-d H:i:s', $checkInTs);

    $update->execute([$checkIn, $visitId]);
    echo "Visit $visitId → checked in {$minsAgo}m ago ($checkIn)\n";
}

echo "\nDone — {$count} active visits refreshed. Reload /admin/live to see fresh bars.\n";
