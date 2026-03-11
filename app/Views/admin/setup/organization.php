<?php use App\Core\View; ?>

<div class="setup-stage-header">
    <a href="/admin/setup" class="back-link">&larr; Back to Setup</a>
    <h2>Organization Details</h2>
    <p>Set your organization name and timezone. These appear on the check-in screen and are used for all date/time display.</p>
</div>

<div class="card">
    <div class="card-title">Organization Details</div>
    <form method="POST" action="/admin/setup/organization/save">
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
                <button type="submit" class="button">Save</button>
                <a href="/admin/setup" class="button button-outline">Back to Timeline</a>
            </div>
        </div>
    </form>
</div>
