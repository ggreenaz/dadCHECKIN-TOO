<?php use App\Core\View; ?>

<div class="setup-stage-header">
    <a href="/admin/setup" class="back-link">&larr; Back to Setup</a>
    <h2>Authentication</h2>
    <p>Choose how staff sign in. Local accounts are always available. Enable additional providers as needed.</p>
</div>

<?php
$enabled  = $settings['auth_providers'] ?? ['local'];
$hasLocal = in_array('local', $enabled);
$hasLdap  = in_array('ldap', $enabled);
$hasGoogle    = in_array('google', $enabled);
$hasMicrosoft = in_array('microsoft', $enabled);
?>

<form method="POST" action="/admin/setup/auth/save">

    <!-- Provider toggles -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-title">Enabled Providers</div>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:16px;">
            Local accounts are always available. Toggle additional providers below and fill in their credentials.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:16px;">
            <label class="provider-toggle <?= $hasLocal ? 'active' : '' ?>">
                <input type="checkbox" name="auth_providers[]" value="local" checked disabled>
                <span class="provider-icon">🔒</span>
                <span>Local Accounts</span>
                <small>Always on</small>
            </label>
            <label class="provider-toggle <?= $hasLdap ? 'active' : '' ?>">
                <input type="checkbox" name="auth_providers[]" value="ldap" id="toggle_ldap"
                       <?= $hasLdap ? 'checked' : '' ?>>
                <span class="provider-icon">🗄️</span>
                <span>LDAP / Active Directory</span>
                <small>Domain credentials</small>
            </label>
            <label class="provider-toggle <?= $hasGoogle ? 'active' : '' ?>">
                <input type="checkbox" name="auth_providers[]" value="google" id="toggle_google"
                       <?= $hasGoogle ? 'checked' : '' ?>>
                <span class="provider-icon">G</span>
                <span>Google SSO</span>
                <small>Google Workspace</small>
            </label>
            <label class="provider-toggle <?= $hasMicrosoft ? 'active' : '' ?>">
                <input type="checkbox" name="auth_providers[]" value="microsoft" id="toggle_microsoft"
                       <?= $hasMicrosoft ? 'checked' : '' ?>>
                <span class="provider-icon">⊞</span>
                <span>Microsoft / Azure AD</span>
                <small>Microsoft 365 / Entra</small>
            </label>
        </div>
    </div>

    <!-- LDAP config -->
    <div class="card provider-config" id="config_ldap" style="margin-bottom:20px;<?= $hasLdap ? '' : 'display:none' ?>">
        <div class="card-title">LDAP / Active Directory Settings</div>

        <?php
        // User type presets — drives JS auto-fill of attribute & filter
        $ldapUserTypes = [
            'ms_ad'    => ['label' => 'MS Active Directory',           'attr' => 'sAMAccountName', 'filter' => '(&(objectClass=user)(sAMAccountName=%s))'],
            'novell'   => ['label' => 'Novell Directory Services',      'attr' => 'cn',             'filter' => '(&(objectClass=inetOrgPerson)(cn=%s))'],
            'posix'    => ['label' => 'posixAccount (RFC 2307)',         'attr' => 'uid',            'filter' => '(&(objectClass=posixAccount)(uid=%s))'],
            'samba'    => ['label' => 'sambaSamAccount',                 'attr' => 'sAMAccountName', 'filter' => '(&(objectClass=sambaSamAccount)(sAMAccountName=%s))'],
            'inet_org' => ['label' => 'inetOrgPerson (generic LDAP)',   'attr' => 'uid',            'filter' => '(&(objectClass=inetOrgPerson)(uid=%s))'],
            'custom'   => ['label' => 'Custom / Manual',                 'attr' => '',               'filter' => ''],
        ];
        $currentType = $settings['ldap_user_type'] ?? 'ms_ad';
        ?>

        <!-- Section 1: Server -->
        <p class="ldap-section-label">Server</p>
        <div class="form-grid" style="margin-bottom:8px;">
            <div class="form-group form-group-full">
                <label>Host URL <span style="font-weight:400;color:var(--text-muted)">(auth_ldap | host_url)</span></label>
                <input type="text" name="ldap_host_url" id="ldap_host_url"
                       placeholder="ldap://ldap.yourdomain.com/"
                       value="<?= View::e($settings['ldap_host_url'] ?? '') ?>">
                <small style="color:var(--text-muted)">
                    Use <code>ldap://</code> for standard (port 389) or <code>ldaps://</code> for TLS (port 636).
                    Separate multiple servers with <code>;</code> for failover — e.g.
                    <code>ldap://dc1.domain.com/; ldap://dc2.domain.com/</code>
                </small>
            </div>
            <div class="form-group">
                <label>Distinguished Name <span style="font-weight:400;color:var(--text-muted)">(bind_dn)</span></label>
                <input type="text" name="ldap_bind_user"
                       placeholder="cn=ldapuser,ou=public,o=org"
                       value="<?= View::e($settings['ldap_bind_user'] ?? '') ?>">
                <small style="color:var(--text-muted)">Leave blank for anonymous bind.</small>
            </div>
            <div class="form-group">
                <label>Password <span style="font-weight:400;color:var(--text-muted)">(bind_pw)</span></label>
                <input type="password" name="ldap_bind_password"
                       placeholder="Leave blank to keep existing" autocomplete="new-password">
            </div>
        </div>

        <div class="ldap-notice">
            <strong>Note:</strong> If you want CheckIn to sync or update user data from your directory,
            the bind account must have <em>write</em> privileges on user records.
            Be aware that syncing does not preserve multi-valued attributes — extra values will be removed on update.
        </div>

        <!-- Test connection -->
        <div style="margin-top:16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <button type="button" id="ldap_test_btn" class="button button-outline"
                    onclick="testLdapConnection()">
                Test Connection
            </button>
            <div id="ldap_test_result" style="display:none;padding:8px 14px;border-radius:6px;
                 font-size:0.875rem;flex:1;min-width:200px;"></div>
        </div>

        <hr style="border:none;border-top:1px solid var(--border);margin:20px 0;">

        <!-- Section 2: Directory schema -->
        <p class="ldap-section-label">Directory Schema</p>
        <div class="form-grid" style="margin-bottom:8px;">
            <div class="form-group form-group-full">
                <label>User Type</label>
                <select name="ldap_user_type" id="ldap_user_type">
                    <?php foreach ($ldapUserTypes as $key => $preset): ?>
                    <option value="<?= View::e($key) ?>" <?= $currentType === $key ? 'selected' : '' ?>>
                        <?= View::e($preset['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small style="color:var(--text-muted)">
                    Selecting a type auto-fills the attribute and filter below with sensible defaults.
                    Choose <em>Custom</em> to set them manually.
                </small>
            </div>
            <div class="form-group form-group-full">
                <label>Contexts <span style="font-weight:400;color:var(--text-muted)">(auth_ldap | contexts)</span></label>
                <input type="text" name="ldap_contexts"
                       placeholder="ou=users,o=org; ou=others,o=org"
                       value="<?= View::e($settings['ldap_contexts'] ?? '') ?>">
                <small style="color:var(--text-muted)">
                    List of contexts where users are located. Separate different contexts with <code>;</code>.
                    For example: <code>ou=users,o=org; ou=others,o=org</code>
                </small>
            </div>
            <div class="form-group form-group-full">
                <label class="checkbox-label">
                    <input type="checkbox" name="ldap_search_sub" value="1"
                           <?= !empty($settings['ldap_search_sub']) ? 'checked' : '' ?>>
                    Search subcontexts <span style="font-weight:400;color:var(--text-muted)">(auth_ldap | search_sub)</span>
                </label>
                <small style="color:var(--text-muted);display:block;margin-top:4px;">
                    Search users from subcontexts. Enable this if your users are in sub-OUs
                    beneath the listed contexts rather than directly inside them.
                </small>
            </div>
            <div class="form-group">
                <label>User Attribute</label>
                <input type="text" name="ldap_user_attribute" id="ldap_user_attribute"
                       placeholder="sAMAccountName"
                       value="<?= View::e($settings['ldap_user_attribute'] ?? '') ?>">
                <small style="color:var(--text-muted)">
                    The attribute matched against the entered username.
                    AD default: <code>sAMAccountName</code> &nbsp;|&nbsp; POSIX default: <code>uid</code> &nbsp;|&nbsp; Generic: <code>cn</code>
                </small>
            </div>
            <div class="form-group">
                <label>Base DN</label>
                <input type="text" name="ldap_base_dn" placeholder="dc=yourdomain,dc=com"
                       value="<?= View::e($settings['ldap_base_dn'] ?? '') ?>">
                <small style="color:var(--text-muted)">Fallback search root when no contexts are set.</small>
            </div>
            <div class="form-group form-group-full">
                <label>Search Filter <span style="font-weight:400;color:var(--text-muted)">(advanced — overrides auto-generated filter)</span></label>
                <input type="text" name="ldap_user_filter" id="ldap_user_filter"
                       placeholder="auto-generated from User Type"
                       value="<?= View::e($settings['ldap_user_filter'] ?? '') ?>">
                <small style="color:var(--text-muted)">
                    Use <code>%s</code> as the username placeholder.
                    Leave blank to auto-generate from User Type and User Attribute.
                    Example: <code>(&amp;(objectClass=user)(sAMAccountName=%s))</code>
                </small>
            </div>
        </div>

        <hr style="border:none;border-top:1px solid var(--border);margin:20px 0;">

        <!-- Section 3: Login page appearance -->
        <?php
        $labelDefaults = [
            'ms_ad'    => ['label' => 'Username', 'hint' => 'Enter your network username (e.g. jsmith — not jsmith@domain.com)'],
            'novell'   => ['label' => 'Username', 'hint' => 'Enter your Novell directory username'],
            'posix'    => ['label' => 'Username', 'hint' => 'Enter your system username'],
            'samba'    => ['label' => 'Username', 'hint' => 'Enter your Samba network username'],
            'inet_org' => ['label' => 'Username', 'hint' => 'Enter your directory username'],
            'custom'   => ['label' => 'Username', 'hint' => ''],
        ];
        $isCustomized   = !empty($settings['ldap_customize_labels']);
        $effectiveLabel = $isCustomized
            ? ($settings['ldap_login_label'] ?? 'Username')
            : ($labelDefaults[$currentType]['label'] ?? 'Username');
        $effectiveHint  = $isCustomized
            ? ($settings['ldap_login_hint'] ?? '')
            : ($labelDefaults[$currentType]['hint'] ?? '');
        ?>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <p class="ldap-section-label" style="margin:0;">Login Page Appearance</p>
            <label class="ldap-customize-toggle">
                <input type="checkbox" name="ldap_customize_labels" id="ldap_customize_labels"
                       value="1" <?= $isCustomized ? 'checked' : '' ?>>
                <span>Customize for this organization</span>
            </label>
        </div>

        <!-- Preview (shown when NOT customizing) -->
        <div id="ldap_appearance_preview" <?= $isCustomized ? 'style="display:none"' : '' ?>>
            <div class="ldap-appearance-preview">
                <div class="ldap-preview-field">
                    <span class="ldap-preview-label" id="preview_label"><?= View::e($effectiveLabel) ?></span>
                    <span class="ldap-preview-input">
                        <span style="color:var(--text-muted);font-style:italic;">username field</span>
                    </span>
                </div>
                <?php if ($effectiveHint): ?>
                <p class="ldap-preview-hint" id="preview_hint"><?= View::e($effectiveHint) ?></p>
                <?php else: ?>
                <p class="ldap-preview-hint" id="preview_hint" style="display:none;"></p>
                <?php endif; ?>
            </div>
            <p style="color:var(--text-muted);font-size:0.8rem;margin-top:8px;">
                This is what users will see on the login page. Select <em>Customize</em> to override.
            </p>
        </div>

        <!-- Custom inputs (shown when customizing) -->
        <div id="ldap_appearance_custom" <?= $isCustomized ? '' : 'style="display:none"' ?>>
            <div class="form-grid">
                <div class="form-group">
                    <label>Login Field Label</label>
                    <input type="text" name="ldap_login_label" id="ldap_login_label"
                           placeholder="Username"
                           value="<?= View::e($settings['ldap_login_label'] ?? '') ?>">
                    <small style="color:var(--text-muted)">
                        e.g. <em>Username</em>, <em>Network Login</em>, <em>Email Address</em>
                    </small>
                </div>
                <div class="form-group">
                    <label>Login Hint</label>
                    <input type="text" name="ldap_login_hint" id="ldap_login_hint"
                           placeholder="e.g. Use your short username, not your full email"
                           value="<?= View::e($settings['ldap_login_hint'] ?? '') ?>">
                    <small style="color:var(--text-muted)">
                        Short helper line shown below the login field.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Google config -->
    <div class="card provider-config" id="config_google" style="margin-bottom:20px;<?= $hasGoogle ? '' : 'display:none' ?>">
        <div class="card-title">Google SSO Settings</div>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:16px;">
            Create an OAuth 2.0 client in the
            <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Google Cloud Console</a>.
            Set the authorized redirect URI to: <code><?= View::e((require BASE_PATH . '/config/app.php')['url'] ?? '') ?>/auth/callback/google</code>
        </p>
        <div class="form-grid">
            <div class="form-group">
                <label>Client ID</label>
                <input type="text" name="google_client_id" placeholder="....apps.googleusercontent.com"
                       value="<?= View::e($settings['google_client_id'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Client Secret</label>
                <input type="password" name="google_client_secret" placeholder="Leave blank to keep existing"
                       autocomplete="new-password">
            </div>
        </div>
    </div>

    <!-- Microsoft config -->
    <div class="card provider-config" id="config_microsoft" style="margin-bottom:20px;<?= $hasMicrosoft ? '' : 'display:none' ?>">
        <div class="card-title">Microsoft / Azure AD Settings</div>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:16px;">
            Register an app in the
            <a href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade" target="_blank" rel="noopener">Azure Portal</a>.
            Set the redirect URI to: <code><?= View::e((require BASE_PATH . '/config/app.php')['url'] ?? '') ?>/auth/callback/microsoft</code>
        </p>
        <div class="form-grid">
            <div class="form-group">
                <label>Application (Client) ID</label>
                <input type="text" name="microsoft_client_id" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                       value="<?= View::e($settings['microsoft_client_id'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Client Secret</label>
                <input type="password" name="microsoft_client_secret" placeholder="Leave blank to keep existing"
                       autocomplete="new-password">
            </div>
            <div class="form-group form-group-full">
                <label>Tenant ID</label>
                <input type="text" name="microsoft_tenant_id"
                       placeholder="common  (or your specific tenant GUID)"
                       value="<?= View::e($settings['microsoft_tenant_id'] ?? 'common') ?>">
                <small style="color:var(--text-muted)">
                    Use <code>common</code> to allow any Microsoft account, or enter your tenant ID to restrict to your organization.
                </small>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="button">Save Authentication Settings</button>
    </div>
</form>

<div class="setup-stage-nav">
    <a href="/admin/setup/fields" class="button button-outline">&larr; Custom Fields</a>
    <a href="/admin/setup/users" class="button">Next: Users &rarr;</a>
</div>

<style>
.ldap-section-label {
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--text-muted);
    margin: 0 0 14px;
}
.ldap-customize-toggle {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 0.85rem;
    color: var(--text-muted);
    cursor: pointer;
    user-select: none;
}
.ldap-customize-toggle input { cursor: pointer; }
.ldap-appearance-preview {
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.ldap-preview-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.ldap-preview-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text);
}
.ldap-preview-input {
    display: block;
    border: 1px solid var(--border);
    border-radius: 5px;
    padding: 7px 10px;
    font-size: 0.875rem;
    background: var(--surface);
}
.ldap-preview-hint {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin: 0;
    font-style: italic;
}
.ldap-notice {
    background: var(--warning-bg, #fffbeb);
    border: 1px solid var(--warning-border, #fcd34d);
    border-radius: 6px;
    padding: 10px 14px;
    font-size: 0.825rem;
    color: #78350f;
    margin-top: 16px;
    line-height: 1.5;
}
.provider-toggle {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 16px 20px;
    border: 2px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    min-width: 140px;
    text-align: center;
    transition: border-color .15s, background .15s;
}
.provider-toggle:hover    { border-color: var(--primary); }
.provider-toggle.active   { border-color: var(--primary); background: var(--primary-subtle, #eff6ff); }
.provider-toggle input    { display:none; }
.provider-icon { font-size: 1.5rem; }
.provider-toggle small { color: var(--text-muted); font-size: 0.75rem; }
</style>

<script>
// Toggle provider config panels and active state
['ldap', 'google', 'microsoft'].forEach(function(p) {
    var cb  = document.getElementById('toggle_' + p);
    var cfg = document.getElementById('config_' + p);
    if (!cb || !cfg) return;
    cb.closest('label').addEventListener('click', function() {
        setTimeout(function() {
            cfg.style.display = cb.checked ? '' : 'none';
            cb.closest('label').classList.toggle('active', cb.checked);
        }, 0);
    });
});

// LDAP presets: schema defaults + login appearance defaults
var ldapPresets = <?= json_encode(array_map(fn($p) => ['attr' => $p['attr'], 'filter' => $p['filter']], $ldapUserTypes)) ?>;
var ldapAppearance = <?= json_encode($labelDefaults) ?>;

function applyLdapPreset(type) {
    var preset     = ldapPresets[type]     || {};
    var appearance = ldapAppearance[type]  || {label:'Username', hint:''};

    // Schema fields
    var attrField   = document.getElementById('ldap_user_attribute');
    var filterField = document.getElementById('ldap_user_filter');
    if (type !== 'custom') {
        attrField.value         = preset.attr   || '';
        filterField.value       = '';
        filterField.placeholder = preset.filter || '(auto-generated)';
    } else {
        attrField.placeholder   = 'e.g. uid, cn, sAMAccountName';
        filterField.placeholder = 'e.g. (&(objectClass=posixAccount)(uid=%s))';
    }

    // Appearance preview
    var previewLabel = document.getElementById('preview_label');
    var previewHint  = document.getElementById('preview_hint');
    if (previewLabel) previewLabel.textContent = appearance.label || 'Username';
    if (previewHint) {
        previewHint.textContent    = appearance.hint || '';
        previewHint.style.display  = appearance.hint ? '' : 'none';
    }
}

document.getElementById('ldap_user_type').addEventListener('change', function() {
    applyLdapPreset(this.value);
});

// Customize toggle
document.getElementById('ldap_customize_labels').addEventListener('change', function() {
    document.getElementById('ldap_appearance_preview').style.display = this.checked ? 'none' : '';
    document.getElementById('ldap_appearance_custom').style.display  = this.checked ? '' : 'none';
});

// Set initial state on page load
(function() {
    var sel = document.getElementById('ldap_user_type');
    if (!sel) return;
    var preset = ldapPresets[sel.value];
    if (preset) {
        document.getElementById('ldap_user_filter').placeholder = preset.filter || '(auto-generated)';
    }
})();

// LDAP test connection
async function testLdapConnection() {
    var btn    = document.getElementById('ldap_test_btn');
    var result = document.getElementById('ldap_test_result');

    // Collect current field values from the form
    var form = btn.closest('form');
    var data = new URLSearchParams({
        ldap_host_url:       form.querySelector('[name="ldap_host_url"]').value,
        ldap_bind_user:      form.querySelector('[name="ldap_bind_user"]').value,
        ldap_bind_password:  form.querySelector('[name="ldap_bind_password"]').value,
        ldap_base_dn:        form.querySelector('[name="ldap_base_dn"]').value,
        ldap_contexts:       form.querySelector('[name="ldap_contexts"]').value,
    });

    btn.disabled    = true;
    btn.textContent = 'Testing…';
    result.style.display = 'none';

    try {
        var res  = await fetch('/admin/setup/auth/test-ldap', { method: 'POST', body: data });
        var json = await res.json();

        result.style.display    = '';
        result.style.background = json.success ? 'var(--success-bg)' : 'var(--danger-bg)';
        result.style.border     = '1px solid ' + (json.success ? 'var(--success-border)' : 'var(--danger-border)');
        result.style.color      = json.success ? '#166534' : '#991b1b';
        result.textContent      = (json.success ? '✓ ' : '✗ ') + json.message;
    } catch (e) {
        result.style.display    = '';
        result.style.background = 'var(--danger-bg)';
        result.style.border     = '1px solid var(--danger-border)';
        result.style.color      = '#991b1b';
        result.textContent      = '✗ Request failed — check server logs.';
    }

    btn.disabled    = false;
    btn.textContent = 'Test Connection';
}
</script>
