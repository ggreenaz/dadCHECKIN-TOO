<div class="card" style="margin-bottom:16px;">
    <div class="card-title">Database Status</div>
    <div id="step3-db-result" style="padding:10px 14px;border-radius:6px;font-size:0.875rem;
         background:var(--surface-2);border:1px solid var(--border);color:var(--text-muted);">
        Checking connection…
    </div>
</div>

<div class="card">
    <div class="card-title">Organization &amp; Admin Account</div>
    <p style="color:var(--text-muted);margin-bottom:24px;font-size:0.9rem;">
        Everything else — hosts, visit reasons, custom fields, branding, and CSV imports —
        can be configured from the admin panel after setup.
    </p>
    <form method="POST" action="/install/3/save">

        <p style="font-weight:600;font-size:0.85rem;margin-bottom:12px;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:.04em;">Organization</p>
        <div class="form-grid" style="margin-bottom:28px;">
            <div class="form-group form-group-full">
                <label for="org_name">Organization Name</label>
                <input type="text" name="org_name" id="org_name"
                       placeholder="e.g. Acme Corp, City Hall, Lincoln Elementary"
                       value="<?= \App\Core\View::e($_POST['org_name'] ?? '') ?>">
                <small style="color:var(--text-muted)">Leave blank to use "My Organization" — changeable later.</small>
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

        <p style="font-weight:600;font-size:0.85rem;margin-bottom:12px;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:.04em;">Admin Account</p>
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
            <div class="form-actions">
                <button type="submit" class="button">Finish Setup &rarr;</button>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-test the saved DB connection on page load
(async function () {
    const el = document.getElementById('step3-db-result');
    try {
        const res  = await fetch('/install/test-db', {
            method: 'POST',
            body: new URLSearchParams({ db_host: '', db_port: '', db_name: '', db_user: '', db_pass: '' })
        });
        // We just ping with empty fields to get a server-side check using the saved local config
        const json = await res.json();
        el.style.background = json.success ? 'var(--success-bg)'  : 'var(--danger-bg)';
        el.style.border     = '1px solid ' + (json.success ? 'var(--success-border)' : 'var(--danger-border)');
        el.style.color      = json.success ? '#166534' : '#991b1b';
        el.textContent      = (json.success ? '✓ ' : '✗ ') + json.message;
    } catch (e) {
        el.textContent = '⚠ Could not verify connection.';
    }
})();
</script>
