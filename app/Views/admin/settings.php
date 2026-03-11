<?php use App\Core\View; ?>

<div class="card">
    <div class="card-title">Organization Settings</div>
    <form method="POST" action="/admin/settings">
        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="org_name">Organization Name</label>
                <input type="text" name="org_name" id="org_name" required
                       value="<?= View::e($org['name'] ?? '') ?>">
            </div>
            <div class="form-group form-group-full">
                <label for="timezone">Timezone</label>
                <select name="timezone" id="timezone">
                    <?php
                    $zones = [
                        'America/New_York'    => 'Eastern (New York)',
                        'America/Chicago'     => 'Central (Chicago)',
                        'America/Denver'      => 'Mountain (Denver)',
                        'America/Phoenix'     => 'Mountain no DST (Phoenix)',
                        'America/Los_Angeles' => 'Pacific (Los Angeles)',
                        'America/Anchorage'   => 'Alaska',
                        'Pacific/Honolulu'    => 'Hawaii',
                        'UTC'                 => 'UTC',
                        'Europe/London'       => 'London',
                        'Europe/Paris'        => 'Paris / Berlin',
                        'Asia/Tokyo'          => 'Tokyo',
                        'Australia/Sydney'    => 'Sydney',
                    ];
                    $current = $org['timezone'] ?? 'America/Chicago';
                    foreach ($zones as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $current === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Save Settings</button>
            </div>
        </div>
    </form>
</div>

<?php
$s = json_decode($org['settings'] ?? '{}', true) ?: [];
$ac = $s['auto_checkout'] ?? [];
$acEnabled    = $ac['enabled']        ?? false;
$acTime       = $ac['checkout_time']  ?? '17:00';
$acMaxHours   = $ac['max_open_hours'] ?? 10;
$acStatus     = $ac['status']         ?? 'auto_completed';
?>
<div class="card" style="margin-top:1.5rem;">
    <div class="card-title">Auto-Checkout</div>
    <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:1.25rem;">
        Automatically close visits that are still open at end of day, or that have been open too long.
        Runs via a cron job — see your server setup for the schedule.
    </p>
    <form method="POST" action="/admin/settings/auto-checkout">
        <div class="form-grid">

            <div class="form-group form-group-full">
                <label class="toggle-label">
                    <input type="checkbox" name="ac_enabled" value="1" <?= $acEnabled ? 'checked' : '' ?>>
                    <span class="toggle-track"></span>
                    Enable auto-checkout
                </label>
            </div>

            <div class="form-group">
                <label for="ac_time">End-of-day checkout time</label>
                <input type="time" name="ac_time" id="ac_time" value="<?= View::e($acTime) ?>">
                <small class="form-hint">Any visit still open at this time will be closed.</small>
            </div>

            <div class="form-group">
                <label for="ac_max_hours">Max open hours (stale threshold)</label>
                <input type="number" name="ac_max_hours" id="ac_max_hours" min="1" max="24"
                       value="<?= (int)$acMaxHours ?>">
                <small class="form-hint">Also close any visit open longer than this — regardless of time of day.</small>
            </div>

            <div class="form-group">
                <label for="ac_status">Mark closed visits as</label>
                <select name="ac_status" id="ac_status">
                    <option value="auto_completed" <?= $acStatus === 'auto_completed' ? 'selected' : '' ?>>Auto-Completed (distinguishable from manual)</option>
                    <option value="completed"      <?= $acStatus === 'completed'      ? 'selected' : '' ?>>Completed (same as manual checkout)</option>
                </select>
            </div>

            <div class="form-actions form-group-full">
                <button type="submit" class="button">Save Auto-Checkout Settings</button>
                <span style="font-size:0.78rem;color:var(--text-muted);margin-left:12px;">
                    Cron command: <code>php <?= BASE_PATH ?>/scripts/auto_checkout.php</code>
                </span>
            </div>

        </div>
    </form>
</div>

<div class="card" style="margin-top:1.5rem;">
    <div class="card-title">Kiosk Fields</div>
    <p style="color:var(--text-muted);margin-bottom:1rem;">Choose which fields visitors see on the check-in form — show or hide Last Name, Phone, Email, and Notes.</p>
    <a href="/admin/setup/kiosk" class="button">Manage Kiosk Fields</a>
</div>

<div class="card" style="margin-top:1.5rem;">
    <div class="card-title">Authentication</div>
    <p style="color:var(--text-muted);margin-bottom:1rem;">Configure login providers — Local accounts, LDAP/Active Directory, Google SSO, or Microsoft Azure.</p>
    <a href="/admin/setup/auth" class="button">Manage Authentication Settings</a>
</div>
