<?php use App\Core\View; ?>

<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <div style="font-size:2rem;">🔍</div>
        <div>
            <div style="font-weight:700;font-size:1.1rem;">Existing dadtoo Installation Detected</div>
            <div style="color:var(--text-muted);font-size:0.875rem;margin-top:2px;">
                Connected to <strong><?= View::e($dbCfg['database']) ?></strong> and found your legacy data.
                Your old database will <strong>not</strong> be modified.
            </div>
        </div>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
        <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:8px 12px;color:var(--text-muted);width:140px;">Legacy database</td>
            <td style="padding:8px 12px;font-family:monospace;font-weight:600;"><?= View::e($dbCfg['database']) ?></td>
        </tr>
        <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:8px 12px;color:var(--text-muted);">Host</td>
            <td style="padding:8px 12px;font-family:monospace;"><?= View::e($dbCfg['host']) ?>:<?= View::e((string)$dbCfg['port']) ?></td>
        </tr>
        <tr>
            <td style="padding:8px 12px;color:var(--text-muted);">Status</td>
            <td style="padding:8px 12px;"><span style="color:var(--success);font-weight:600;">✓ Connected</span></td>
        </tr>
    </table>
</div>

<!-- Step 1: Before you continue -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-title">Before You Continue — A New Database Is Required</div>

    <p style="margin-bottom:16px;">
        CheckIn uses a new database structure that cannot be installed into your existing
        <code><?= View::e($dbCfg['database']) ?></code> database. Your historical data will be
        <strong>migrated across automatically</strong> — but first, an empty destination database
        must exist and your database user must have full access to it.
    </p>

    <div style="background:var(--warning-bg,#fffbeb);border:1px solid var(--warning-border,#fcd34d);
                border-radius:6px;padding:14px 16px;margin-bottom:20px;font-size:0.875rem;color:#78350f;">
        <strong>⚠ Do you need help from your IT department or hosting provider?</strong><br>
        If you do not have permission to create databases yourself, stop here and send
        them the SQL commands below before continuing.
    </div>

    <div style="font-weight:600;margin-bottom:8px;">
        Give your database administrator these commands:
    </div>

    <div style="position:relative;">
        <pre id="sql-block" style="background:var(--surface-2);border:1px solid var(--border);border-radius:6px;
            padding:14px 16px;font-size:0.82rem;overflow-x:auto;margin-bottom:4px;line-height:1.6;">CREATE DATABASE <span id="sql-dbname">checkin</span> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON <span id="sql-dbname2">checkin</span>.* TO '<?= View::e($dbCfg['username']) ?>'@'localhost';
FLUSH PRIVILEGES;</pre>
        <button type="button" onclick="copySql()" style="position:absolute;top:10px;right:10px;
            background:var(--surface);border:1px solid var(--border);border-radius:4px;
            padding:4px 10px;font-size:0.75rem;cursor:pointer;" id="copy-btn">Copy</button>
    </div>
    <small style="color:var(--text-muted);">
        These commands must be run by a MySQL user with <code>GRANT</code> privileges — typically the root account or your hosting control panel.
    </small>
</div>

<!-- MySQL script generator -->
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
        <div class="card-title" style="margin:0;">Generate MySQL Setup Script</div>
        <label style="display:flex;align-items:center;gap:8px;font-size:0.85rem;
                      color:var(--text-muted);cursor:pointer;font-weight:normal;">
            <input type="checkbox" id="root-toggle"
                   onchange="document.getElementById('root-form').style.display=this.checked?'block':'none';">
            Show
        </label>
    </div>
    <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:0;">
        Fill in the details below and we will generate the exact MySQL commands to give
        to your database administrator or root user.
    </p>

    <div id="root-form" style="display:none;margin-top:20px;">

        <div class="form-grid" style="margin-bottom:20px;">
            <div class="form-group">
                <label for="gen_db_name">Database Name</label>
                <input type="text" id="gen_db_name" placeholder="e.g. checkin" value="checkin">
                <small style="color:var(--text-muted);">
                    Must be different from <code><?= View::e($dbCfg['database']) ?></code>.
                </small>
            </div>
            <div class="form-group">
                <label for="gen_db_user">Username</label>
                <input type="text" id="gen_db_user" placeholder="e.g. checkinuser"
                       value="<?= View::e($dbCfg['username']) ?>">
                <small style="color:var(--text-muted);">The user your application will connect with.</small>
            </div>
            <div class="form-group form-group-full">
                <label for="gen_db_pass">Password</label>
                <input type="text" id="gen_db_pass" placeholder="e.g. Str0ngP@ssword"
                       autocomplete="off">
                <small style="color:var(--text-muted);">
                    Choose a strong password for this database user.
                </small>
            </div>
        </div>

        <button type="button" class="button" onclick="generateScript()">
            Generate MySQL Script
        </button>

        <div id="gen-script-wrap" style="display:none;margin-top:20px;">
            <div style="font-weight:600;margin-bottom:8px;font-size:0.875rem;">
                Give these commands to your database administrator or root user:
            </div>
            <div style="position:relative;">
                <pre id="gen-script" style="background:var(--surface-2);border:1px solid var(--border);
                    border-radius:6px;padding:14px 16px 14px 16px;font-size:0.82rem;
                    line-height:1.8;white-space:pre;overflow-x:auto;margin-bottom:4px;"></pre>
                <button type="button" onclick="copyGenScript()" id="gen-copy-btn"
                        style="position:absolute;top:10px;right:10px;background:var(--surface);
                               border:1px solid var(--border);border-radius:4px;
                               padding:4px 10px;font-size:0.75rem;cursor:pointer;">Copy</button>
            </div>
            <small style="color:var(--text-muted);display:block;margin-top:6px;margin-bottom:20px;">
                These commands must be run by a MySQL user with <code>GRANT</code> privileges —
                typically the root account or your hosting control panel.
            </small>

            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <button type="button" class="button button-outline" onclick="testTerminalDb()">
                    Test Database Connection
                </button>
                <div id="term-test-result" style="display:none;padding:8px 14px;border-radius:6px;
                     font-size:0.875rem;flex:1;min-width:200px;"></div>
            </div>
        </div>

    </div>
</div>

<!-- Data Protection Notice -->
<div style="display:flex;align-items:flex-start;gap:12px;background:var(--surface-2);
            border:1px solid var(--border);border-radius:8px;padding:14px 16px;margin-bottom:20px;">
    <div style="font-size:1.3rem;flex-shrink:0;margin-top:1px;">🔒</div>
    <div style="font-size:0.8rem;color:var(--text-muted);line-height:1.6;">
        <strong style="color:var(--text);display:block;margin-bottom:3px;">Data Protection Notice</strong>
        All credentials and data entered on this page are processed exclusively on
        <strong>your own server</strong> and written only to your local database.
        No information — including database credentials, visitor records, or configuration —
        is transmitted to dadCHECKIN, its developers, or any third party.
        This software runs entirely within your infrastructure.
    </div>
</div>

<!-- Step 2: Enter new DB details -->
<div class="card">
    <div class="card-title">New Database Details</div>
    <p style="color:var(--text-muted);margin-bottom:4px;font-size:0.9rem;">
        Once the database has been created and access granted, enter the details below.
    </p>
    <p style="color:var(--text-muted);margin-bottom:20px;font-size:0.85rem;">
        If your database user already has <code>CREATE DATABASE</code> permission, CheckIn will
        create it automatically when you click the upgrade button.
    </p>

    <form method="POST" action="/install/upgrade-prepare" id="prep-form">
        <input type="hidden" name="source_db" value="<?= View::e($dbCfg['database']) ?>">

        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="new_db_name">New Database Name</label>
                <input type="text" name="new_db_name" id="new_db_name"
                       value="checkin" required placeholder="e.g. checkin"
                       oninput="document.getElementById('sql-dbname').textContent=this.value||'checkin';
                                document.getElementById('sql-dbname2').textContent=this.value||'checkin';">
                <small style="color:var(--text-muted);font-size:0.8rem;">
                    Must be a different database from <code><?= View::e($dbCfg['database']) ?></code>.
                </small>
            </div>
            <div class="form-group">
                <label for="new_db_user">Username</label>
                <input type="text" name="new_db_user" id="new_db_user"
                       value="<?= View::e($dbCfg['username']) ?>" required>
            </div>
            <div class="form-group">
                <label for="new_db_pass">Password</label>
                <input type="password" name="new_db_pass" id="new_db_pass"
                       value="<?= View::e($dbCfg['password']) ?>">
            </div>
        </div>

        <div style="margin-top:16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <button type="button" class="button button-outline" onclick="checkNewDatabase()">
                Check Database
            </button>
            <div id="db-check-result" style="display:none;padding:8px 14px;border-radius:6px;
                 font-size:0.875rem;flex:1;min-width:200px;"></div>
        </div>

        <div id="upgrade-buttons" style="display:none;margin-top:20px;padding-top:20px;
             border-top:1px solid var(--border);">
            <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:16px;">
                Choose your upgrade method:
            </p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <button type="submit" name="upgrade_type" value="quick" class="button">
                    Quick Upgrade &rarr;
                </button>
                <button type="submit" name="upgrade_type" value="guided"
                        class="button" style="background:var(--primary);">
                    Guided Upgrade (Recommended) &rarr;
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function checkNewDatabase() {
    var btn    = event.target;
    var res    = document.getElementById('db-check-result');
    var dbName = document.getElementById('new_db_name').value.trim();
    var dbUser = document.getElementById('new_db_user').value.trim();
    var dbPass = document.getElementById('new_db_pass').value;

    if (!dbName || !dbUser) {
        res.style.display    = 'block';
        res.style.background = 'var(--danger-bg,#fef2f2)';
        res.style.border     = '1px solid var(--danger-border,#fecaca)';
        res.style.color      = '#991b1b';
        res.textContent      = '✗ Database name and username are required.';
        return;
    }

    btn.textContent  = 'Checking…';
    btn.disabled     = true;
    res.style.display = 'none';
    document.getElementById('upgrade-buttons').style.display = 'none';

    var data = new FormData();
    data.append('db_host', 'localhost');
    data.append('db_port', '3306');
    data.append('db_name', dbName);
    data.append('db_user', dbUser);
    data.append('db_pass', dbPass);

    fetch('/install/test-db', { method: 'POST', body: data })
        .then(function(r) { return r.json(); })
        .then(function(j) {
            res.style.display = 'block';

            if (j.success) {
                // Database exists and is accessible
                res.style.background = 'var(--success-bg,#f0fdf4)';
                res.style.border     = '1px solid var(--success-border,#bbf7d0)';
                res.style.color      = '#166534';
                res.textContent      = '✓ Database found and accessible — ready to upgrade.';
                document.getElementById('upgrade-buttons').style.display = 'block';
            } else if (j.message && j.message.indexOf('does not exist') !== -1) {
                // Server reachable but DB doesn't exist — will be created
                res.style.background = 'var(--warning-bg,#fffbeb)';
                res.style.border     = '1px solid var(--warning-border,#fcd34d)';
                res.style.color      = '#78350f';
                res.textContent      = '⚠ Database "' + dbName + '" does not exist yet — it will be created automatically when you proceed.';
                document.getElementById('upgrade-buttons').style.display = 'block';
            } else {
                // Real connection failure — keep upgrade buttons hidden
                res.style.background = 'var(--danger-bg,#fef2f2)';
                res.style.border     = '1px solid var(--danger-border,#fecaca)';
                res.style.color      = '#991b1b';
                res.textContent      = '✗ ' + j.message;
            }
        })
        .catch(function() {
            res.style.display    = 'block';
            res.style.background = 'var(--danger-bg,#fef2f2)';
            res.style.border     = '1px solid var(--danger-border,#fecaca)';
            res.style.color      = '#991b1b';
            res.textContent      = '✗ Request failed. Check server logs.';
        })
        .finally(function() {
            btn.textContent = 'Check Database';
            btn.disabled    = false;
        });
}

function generateScript() {
    var dbName = document.getElementById('gen_db_name').value.trim();
    var dbUser = document.getElementById('gen_db_user').value.trim();
    var dbPass = document.getElementById('gen_db_pass').value;

    if (!dbName || !dbUser || !dbPass) {
        alert('Please fill in the database name, username, and password.');
        return;
    }

    var script =
        "CREATE DATABASE IF NOT EXISTS `" + dbName + "`\n" +
        "    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n\n" +
        "CREATE USER IF NOT EXISTS '" + dbUser + "'@'localhost'\n" +
        "    IDENTIFIED BY '" + dbPass + "';\n\n" +
        "GRANT ALL PRIVILEGES ON `" + dbName + "`.* TO '" + dbUser + "'@'localhost';\n\n" +
        "FLUSH PRIVILEGES;";

    document.getElementById('gen-script').textContent = script;
    document.getElementById('gen-script-wrap').style.display = 'block';

    // Sync the lower form
    document.getElementById('new_db_name').value       = dbName;
    document.getElementById('new_db_user').value       = dbUser;
    document.getElementById('new_db_pass').value       = dbPass;
    document.getElementById('sql-dbname').textContent  = dbName;
    document.getElementById('sql-dbname2').textContent = dbName;
}

function testTerminalDb() {
    var btn    = event.target;
    var dbName = document.getElementById('term_db_name').value.trim();
    var res    = document.getElementById('term-test-result');

    btn.textContent   = 'Testing…';
    btn.disabled      = true;
    res.style.display = 'none';

    var data = new FormData();
    data.append('db_host', 'localhost');
    data.append('db_port', '3306');
    data.append('db_name', dbName);
    data.append('db_user', document.getElementById('new_db_user').value.trim());
    data.append('db_pass', document.getElementById('new_db_pass').value);

    fetch('/install/test-db', { method: 'POST', body: data })
        .then(function(r) { return r.json(); })
        .then(function(j) {
            res.style.display = 'block';
            if (j.success) {
                res.style.background = 'var(--success-bg,#f0fdf4)';
                res.style.border     = '1px solid var(--success-border,#bbf7d0)';
                res.style.color      = '#166534';
                res.textContent      = '✓ Connected! Database is ready.';
                document.getElementById('new_db_name').value       = dbName;
                document.getElementById('upgrade-buttons').style.display = 'block';
                var cr = document.getElementById('db-check-result');
                cr.style.display = 'block';
                cr.style.background = 'var(--success-bg,#f0fdf4)';
                cr.style.border     = '1px solid var(--success-border,#bbf7d0)';
                cr.style.color      = '#166534';
                cr.textContent      = '✓ Database found and accessible — ready to upgrade.';
                document.getElementById('prep-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                res.style.background = 'var(--danger-bg,#fef2f2)';
                res.style.border     = '1px solid var(--danger-border,#fecaca)';
                res.style.color      = '#991b1b';
                res.textContent      = '✗ ' + (j.message || 'Could not connect. Did the command run successfully?');
            }
        })
        .catch(function() {
            res.style.display    = 'block';
            res.style.background = 'var(--danger-bg,#fef2f2)';
            res.style.border     = '1px solid var(--danger-border,#fecaca)';
            res.style.color      = '#991b1b';
            res.textContent      = '✗ Request failed. Check server logs.';
        })
        .finally(function() {
            btn.textContent = 'Test Database Connection';
            btn.disabled    = false;
        });
}

function copyText(text, btnId) {
    var btn = document.getElementById(btnId);
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            btn.textContent = 'Copied!';
            setTimeout(function(){ btn.textContent = 'Copy'; }, 2000);
        });
    } else {
        // Fallback for HTTP (non-secure) contexts
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity  = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try {
            document.execCommand('copy');
            btn.textContent = 'Copied!';
            setTimeout(function(){ btn.textContent = 'Copy'; }, 2000);
        } catch(e) {
            btn.textContent = 'Select manually';
            setTimeout(function(){ btn.textContent = 'Copy'; }, 3000);
        }
        document.body.removeChild(ta);
    }
}

function copyGenScript() {
    copyText(document.getElementById('gen-script').textContent, 'gen-copy-btn');
}

function copySql() {
    copyText(document.getElementById('sql-block').innerText, 'copy-btn');
}
</script>
