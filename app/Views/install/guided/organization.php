<div class="card" style="margin-bottom:16px;">
    <div class="card-title">Step 1 of 7 — Organization &amp; Admin Account</div>
    <p style="color:var(--text-muted);margin-bottom:20px;">
        Create your organization profile and the first administrator account.
        These steps are required and cannot be skipped.
    </p>

    <form method="POST" action="/install/guided-upgrade/organization/save">

        <p style="font-weight:600;font-size:0.82rem;color:var(--text-muted);text-transform:uppercase;
                  letter-spacing:.04em;margin-bottom:12px;">Organization</p>
        <div class="form-grid" style="margin-bottom:28px;">
            <div class="form-group form-group-full">
                <label for="org_name">Organization Name</label>
                <input type="text" name="org_name" id="org_name"
                       placeholder="e.g. Lincoln High School, City Hall"
                       value="<?= \App\Core\View::e($_POST['org_name'] ?? '') ?>">
                <small style="color:var(--text-muted);">Leave blank for "My Organization" — changeable later.</small>
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
                    $sel = $_POST['timezone'] ?? 'America/Chicago';
                    foreach ($zones as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $sel === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <p style="font-weight:600;font-size:0.82rem;color:var(--text-muted);text-transform:uppercase;
                  letter-spacing:.04em;margin-bottom:12px;">Administrator Account</p>
        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="name">Your Name</label>
                <input type="text" name="name" id="name" placeholder="Full Name"
                       value="<?= \App\Core\View::e($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group form-group-full">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email"
                       placeholder="admin@yourorg.com" required
                       value="<?= \App\Core\View::e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password"
                       placeholder="At least 8 characters" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" name="password_confirm" id="password_confirm"
                       placeholder="Repeat password" required>
            </div>
        </div>

        <div class="step-actions">
            <button type="submit" class="button">Continue &rarr;</button>
        </div>
    </form>
</div>
