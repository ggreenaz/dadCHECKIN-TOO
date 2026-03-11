#!/usr/bin/env php
<?php
/**
 * Auto-Checkout Cron Script
 *
 * Closes visits that are still open at end of day OR have exceeded the
 * max-open-hours threshold. Settings are configured per organization in
 * Admin → Settings → Auto-Checkout.
 *
 * Recommended cron schedule (runs every 15 minutes):
 *   *\/15 * * * * php /var/www/checkin/scripts/auto_checkout.php >> /var/log/checkin-auto-checkout.log 2>&1
 *
 * Or once daily at a fixed time (e.g. 6 PM):
 *   0 18 * * * php /var/www/checkin/scripts/auto_checkout.php >> /var/log/checkin-auto-checkout.log 2>&1
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/bootstrap.php';

use App\Core\Database;

$db  = Database::getInstance();
$now = new DateTimeImmutable('now');

echo "[" . $now->format('Y-m-d H:i:s') . "] Auto-checkout cron started.\n";

// Load all active organizations that have auto-checkout enabled
$orgs = $db->query(
    "SELECT organization_id, name, timezone, settings
     FROM organizations
     WHERE active = 1 AND settings IS NOT NULL"
)->fetchAll();

$totalClosed = 0;

foreach ($orgs as $org) {
    $orgId    = (int)$org['organization_id'];
    $orgName  = $org['name'];
    $settings = json_decode($org['settings'] ?? '{}', true) ?: [];
    $ac       = $settings['auto_checkout'] ?? [];

    if (empty($ac['enabled'])) {
        continue; // auto-checkout disabled for this org
    }

    $checkoutTime = $ac['checkout_time']   ?? '17:00';   // e.g. "17:00"
    $maxHours     = (int)($ac['max_open_hours'] ?? 10);
    $closeStatus  = in_array($ac['status'] ?? '', ['completed', 'auto_completed'])
                        ? $ac['status']
                        : 'auto_completed';

    // Use org's timezone for end-of-day comparison
    $tz      = new DateTimeZone($org['timezone'] ?: 'UTC');
    $nowLocal = new DateTimeImmutable('now', $tz);

    // Parse today's end-of-day threshold in the org's local time
    [$eodHour, $eodMin] = array_map('intval', explode(':', $checkoutTime));
    $eodToday = $nowLocal->setTime($eodHour, $eodMin, 0);

    // Build list of visit IDs to close for this org
    // Condition A: visit is open AND current local time >= end-of-day time
    // Condition B: visit has been open longer than max_open_hours
    $stmt = $db->prepare(
        "SELECT visit_id, check_in_time
         FROM visits
         WHERE organization_id = ?
           AND status = 'checked_in'
           AND check_out_time IS NULL"
    );
    $stmt->execute([$orgId]);
    $openVisits = $stmt->fetchAll();

    $toClose  = [];
    $reasons  = [];

    foreach ($openVisits as $v) {
        $checkIn    = new DateTimeImmutable($v['check_in_time'], new DateTimeZone('UTC'));
        $checkInLocal = $checkIn->setTimezone($tz);
        $elapsedHours = ($nowLocal->getTimestamp() - $checkInLocal->getTimestamp()) / 3600;

        $closeReason = null;

        // Condition A: past end-of-day and check-in was today or earlier
        if ($nowLocal >= $eodToday && $checkInLocal < $eodToday) {
            $closeReason = "end-of-day ({$checkoutTime})";
        }

        // Condition B: open too long
        if ($elapsedHours >= $maxHours) {
            $closeReason = "exceeded {$maxHours}h limit (" . round($elapsedHours, 1) . "h open)";
        }

        if ($closeReason) {
            $toClose[]  = (int)$v['visit_id'];
            $reasons[(int)$v['visit_id']] = $closeReason;
        }
    }

    if (empty($toClose)) {
        echo "  [{$orgName}] No visits to close.\n";
        continue;
    }

    // Close them all in one query
    $placeholders = implode(',', array_fill(0, count($toClose), '?'));
    $bindings     = array_merge([$closeStatus], $toClose);
    $db->prepare(
        "UPDATE visits
         SET check_out_time = NOW(),
             status         = ?,
             updated_at     = NOW()
         WHERE visit_id IN ({$placeholders})
           AND check_out_time IS NULL"
    )->execute($bindings);

    $closed = count($toClose);
    $totalClosed += $closed;

    foreach ($toClose as $vid) {
        echo "  [{$orgName}] Closed visit #{$vid} → {$closeStatus} ({$reasons[$vid]})\n";
    }

    echo "  [{$orgName}] Closed {$closed} visit(s).\n";
}

echo "[" . (new DateTimeImmutable('now'))->format('Y-m-d H:i:s') . "] Done. Total closed: {$totalClosed}.\n\n";
