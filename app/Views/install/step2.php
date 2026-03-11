<div class="card">
    <div class="card-title">Database Connection</div>
    <p style="color:var(--text-muted);margin-bottom:20px;font-size:0.9rem;">
        The database must already exist and the user must have full access to it.
        Tables will be created automatically.
    </p>
    <form method="POST" action="/install/2/save" id="db-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="db_host">Host</label>
                <input type="text" name="db_host" id="db_host"
                       value="<?= \App\Core\View::e($_POST['db_host'] ?? 'localhost') ?>" required>
            </div>
            <div class="form-group">
                <label for="db_port">Port</label>
                <input type="number" name="db_port" id="db_port"
                       value="<?= \App\Core\View::e($_POST['db_port'] ?? '3306') ?>" required>
            </div>
            <div class="form-group form-group-full">
                <label for="db_name">Database Name</label>
                <input type="text" name="db_name" id="db_name"
                       placeholder="e.g. checkin" required
                       value="<?= \App\Core\View::e($_POST['db_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="db_user">Username</label>
                <input type="text" name="db_user" id="db_user" required
                       value="<?= \App\Core\View::e($_POST['db_user'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="db_pass">Password</label>
                <input type="password" name="db_pass" id="db_pass">
            </div>
        </div>

        <div id="test-result" style="display:none;margin:16px 0;padding:10px 14px;border-radius:6px;font-size:0.875rem;"></div>

        <div class="form-actions" style="margin-top:16px;">
            <button type="button" class="button button-outline" onclick="testConnection()">Test Connection</button>
            <button type="submit" class="button" id="connect-btn">Connect &amp; Continue &rarr;</button>
        </div>
    </form>
</div>

<script>
async function testConnection() {
    const form    = document.getElementById('db-form');
    const result  = document.getElementById('test-result');
    const data    = new FormData(form);
    const btn     = document.querySelector('[onclick="testConnection()"]');

    btn.textContent = 'Testing…';
    btn.disabled    = true;
    result.style.display = 'none';

    try {
        const res  = await fetch('/install/test-db', { method: 'POST', body: data });
        const json = await res.json();
        result.style.display  = 'block';
        result.style.background    = json.success ? 'var(--success-bg)'  : 'var(--danger-bg)';
        result.style.border        = '1px solid ' + (json.success ? 'var(--success-border)' : 'var(--danger-border)');
        result.style.color         = json.success ? '#166534' : '#991b1b';
        result.textContent         = (json.success ? '✓ ' : '✗ ') + json.message;
    } catch (e) {
        result.style.display  = 'block';
        result.style.background = 'var(--danger-bg)';
        result.style.border     = '1px solid var(--danger-border)';
        result.style.color      = '#991b1b';
        result.textContent      = '✗ Request failed. Check server logs.';
    }

    btn.textContent = 'Test Connection';
    btn.disabled    = false;
}
</script>
