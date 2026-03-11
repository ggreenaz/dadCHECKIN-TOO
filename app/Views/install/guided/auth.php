<?php
$s            = $settings ?? [];
$hasLdap      = in_array('ldap',      $s['auth_providers'] ?? []);
$hasGoogle    = in_array('google',    $s['auth_providers'] ?? []);
$hasMicrosoft = in_array('microsoft', $s['auth_providers'] ?? []);

$ldapUserTypes = [
    'ms_ad'    => ['label' => 'MS Active Directory',         'attr' => 'sAMAccountName', 'filter' => '(&(objectClass=user)(sAMAccountName=%s))'],
    'novell'   => ['label' => 'Novell Directory Services',   'attr' => 'cn',             'filter' => '(&(objectClass=inetOrgPerson)(cn=%s))'],
    'posix'    => ['label' => 'posixAccount (RFC 2307)',      'attr' => 'uid',            'filter' => '(&(objectClass=posixAccount)(uid=%s))'],
    'samba'    => ['label' => 'sambaSamAccount',              'attr' => 'sAMAccountName', 'filter' => '(&(objectClass=sambaSamAccount)(sAMAccountName=%s))'],
    'inet_org' => ['label' => 'inetOrgPerson (generic LDAP)','attr' => 'uid',            'filter' => '(&(objectClass=inetOrgPerson)(uid=%s))'],
    'custom'   => ['label' => 'Custom / Manual',              'attr' => '',               'filter' => ''],
];
$currentType = $s['ldap_user_type'] ?? 'ms_ad';

$labelDefaults = [
    'ms_ad'    => ['label' => 'Username', 'hint' => 'Enter your network username (e.g. jsmith — not jsmith@domain.com)'],
    'novell'   => ['label' => 'Username', 'hint' => 'Enter your Novell directory username'],
    'posix'    => ['label' => 'Username', 'hint' => 'Enter your system username'],
    'samba'    => ['label' => 'Username', 'hint' => 'Enter your Samba network username'],
    'inet_org' => ['label' => 'Username', 'hint' => 'Enter your directory username'],
    'custom'   => ['label' => 'Username', 'hint' => ''],
];
$isCustomized   = !empty($s['ldap_customize_labels']);
$effectiveLabel = $isCustomized ? ($s['ldap_login_label'] ?? 'Username') : ($labelDefaults[$currentType]['label'] ?? 'Username');
$effectiveHint  = $isCustomized ? ($s['ldap_login_hint']  ?? '')          : ($labelDefaults[$currentType]['hint']  ?? '');
?>

<div class="card">
    <div class="card-title">
        Step 4 of 7 — Authentication
        <span class="optional-tag">Optional</span>
    </div>

    <div class="feature-callout">
        <strong>New feature: LDAP / Active Directory &amp; OAuth</strong>
        Visitors and staff can now sign in at the kiosk using their network credentials.
        No more typing names manually — CheckIn looks them up in your directory automatically.
    </div>

    <form method="POST" action="/install/guided-upgrade/auth/save">

        <!-- Provider toggles -->
        <div style="margin-bottom:20px;">
            <div style="font-weight:600;font-size:0.82rem;color:var(--text-muted);text-transform:uppercase;
                        letter-spacing:.04em;margin-bottom:12px;">Enable Providers</div>
            <div style="display:flex;flex-wrap:wrap;gap:12px;">

                <label style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:14px 18px;
                              border:2px solid var(--border);border-radius:8px;opacity:.6;min-width:120px;text-align:center;">
                    <input type="checkbox" name="auth_providers[]" value="local" checked disabled style="display:none;">
                    <span style="font-size:1.3rem;">🔒</span>
                    <span style="font-weight:600;font-size:0.875rem;">Local Accounts</span>
                    <small style="color:var(--text-muted);font-size:0.75rem;">Always on</small>
                </label>

                <label id="lbl_ldap" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:14px 18px;
                              border:2px solid <?= $hasLdap ? 'var(--primary)' : 'var(--border)' ?>;
                              border-radius:8px;cursor:pointer;min-width:120px;text-align:center;
                              <?= $hasLdap ? 'background:var(--primary-subtle,#eff6ff)' : '' ?>">
                    <input type="checkbox" name="auth_providers[]" value="ldap" id="toggle_ldap"
                           <?= $hasLdap ? 'checked' : '' ?> style="display:none;">
                    <span style="font-size:1.3rem;">🗄️</span>
                    <span style="font-weight:600;font-size:0.875rem;">LDAP / AD</span>
                    <small style="color:var(--text-muted);font-size:0.75rem;">Domain credentials</small>
                </label>

                <label id="lbl_google" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:14px 18px;
                              border:2px solid <?= $hasGoogle ? 'var(--primary)' : 'var(--border)' ?>;
                              border-radius:8px;cursor:pointer;min-width:120px;text-align:center;
                              <?= $hasGoogle ? 'background:var(--primary-subtle,#eff6ff)' : '' ?>">
                    <input type="checkbox" name="auth_providers[]" value="google" id="toggle_google"
                           <?= $hasGoogle ? 'checked' : '' ?> style="display:none;">
                    <span style="font-size:1.3rem;">G</span>
                    <span style="font-weight:600;font-size:0.875rem;">Google SSO</span>
                    <small style="color:var(--text-muted);font-size:0.75rem;">Workspace</small>
                </label>

            </div>
        </div>

        <!-- LDAP config panel -->
        <div id="config_ldap" style="<?= $hasLdap ? '' : 'display:none;' ?>border:1px solid var(--border);
             border-radius:8px;padding:20px;margin-bottom:20px;">
            <div style="font-weight:700;margin-bottom:16px;font-size:0.95rem;">
                LDAP / Active Directory Settings
            </div>

            <div style="font-weight:600;font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;
                        letter-spacing:.05em;margin-bottom:12px;">Server</div>
            <div class="form-grid" style="margin-bottom:16px;">
                <div class="form-group form-group-full">
                    <label>Host URL</label>
                    <input type="text" name="ldap_host_url" placeholder="ldap://10.1.55.33"
                           value="<?= htmlspecialchars($s['ldap_host_url'] ?? '') ?>">
                    <small style="color:var(--text-muted);">Use <code>ldap://</code> (port 389) or <code>ldaps://</code> (port 636).</small>
                </div>
                <div class="form-group">
                    <label>Bind User (DN)</label>
                    <input type="text" name="ldap_bind_user" placeholder="ldapsearch@domain.com"
                           value="<?= htmlspecialchars($s['ldap_bind_user'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Bind Password</label>
                    <input type="password" name="ldap_bind_password"
                           placeholder="<?= empty($s['ldap_bind_password']) ? 'Enter password' : 'Leave blank to keep existing' ?>"
                           autocomplete="new-password">
                </div>
            </div>

            <div style="margin-bottom:16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <button type="button" class="button button-outline" style="font-size:0.8rem;padding:6px 14px;"
                        onclick="guTestLdap()">Test Connection</button>
                <div id="gu_ldap_result" style="display:none;padding:7px 12px;border-radius:6px;font-size:0.82rem;flex:1;"></div>
            </div>

            <hr style="border:none;border-top:1px solid var(--border);margin:16px 0;">

            <div style="font-weight:600;font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;
                        letter-spacing:.05em;margin-bottom:12px;">Directory Schema</div>
            <div class="form-grid" style="margin-bottom:8px;">
                <div class="form-group form-group-full">
                    <label>User Type</label>
                    <select name="ldap_user_type" id="ldap_user_type" onchange="guApplyPreset(this.value)">
                        <?php foreach ($ldapUserTypes as $key => $preset): ?>
                        <option value="<?= $key ?>" <?= $currentType === $key ? 'selected' : '' ?>><?= htmlspecialchars($preset['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group-full">
                    <label>Contexts</label>
                    <input type="text" name="ldap_contexts" placeholder="ou=users,dc=domain,dc=com"
                           value="<?= htmlspecialchars($s['ldap_contexts'] ?? '') ?>">
                    <small style="color:var(--text-muted);">Where users live in the directory. Separate multiple with <code>;</code>.</small>
                </div>
                <div class="form-group form-group-full">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="ldap_search_sub" value="1"
                               <?= !empty($s['ldap_search_sub']) ? 'checked' : '' ?>>
                        Search sub-OUs
                    </label>
                    <small style="color:var(--text-muted);display:block;margin-top:4px;">Enable if users are in sub-OUs beneath the listed contexts.</small>
                </div>
                <div class="form-group">
                    <label>User Attribute</label>
                    <input type="text" name="ldap_user_attribute" id="ldap_user_attribute"
                           placeholder="sAMAccountName"
                           value="<?= htmlspecialchars($s['ldap_user_attribute'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Base DN</label>
                    <input type="text" name="ldap_base_dn" placeholder="dc=domain,dc=com"
                           value="<?= htmlspecialchars($s['ldap_base_dn'] ?? '') ?>">
                </div>
            </div>

            <hr style="border:none;border-top:1px solid var(--border);margin:16px 0;">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div style="font-weight:600;font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                    Login Page Appearance
                </div>
                <label style="display:flex;align-items:center;gap:7px;font-size:0.82rem;color:var(--text-muted);cursor:pointer;">
                    <input type="checkbox" name="ldap_customize_labels" id="ldap_customize_labels"
                           value="1" <?= $isCustomized ? 'checked' : '' ?>
                           onchange="document.getElementById('gu_ldap_custom').style.display=this.checked?'':'none';
                                     document.getElementById('gu_ldap_preview').style.display=this.checked?'none':'';">
                    Customize labels
                </label>
            </div>

            <div id="gu_ldap_preview" <?= $isCustomized ? 'style="display:none"' : '' ?>>
                <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:14px 18px;">
                    <div style="font-size:0.8rem;font-weight:600;margin-bottom:4px;" id="gu_preview_label"><?= htmlspecialchars($effectiveLabel) ?></div>
                    <div style="border:1px solid var(--border);border-radius:5px;padding:7px 10px;font-size:0.875rem;
                                background:var(--surface);color:var(--text-muted);font-style:italic;">username field</div>
                    <p id="gu_preview_hint" style="font-size:0.8rem;color:var(--text-muted);font-style:italic;margin:6px 0 0;"><?= htmlspecialchars($effectiveHint) ?></p>
                </div>
            </div>

            <div id="gu_ldap_custom" <?= $isCustomized ? '' : 'style="display:none"' ?>>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Login Field Label</label>
                        <input type="text" name="ldap_login_label" id="ldap_login_label"
                               placeholder="Username" value="<?= htmlspecialchars($s['ldap_login_label'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Login Hint</label>
                        <input type="text" name="ldap_login_hint" id="ldap_login_hint"
                               placeholder="e.g. Use your short username, not your full email"
                               value="<?= htmlspecialchars($s['ldap_login_hint'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Google config panel -->
        <div id="config_google" style="<?= $hasGoogle ? '' : 'display:none;' ?>border:1px solid var(--border);
             border-radius:8px;padding:20px;margin-bottom:20px;">
            <div style="font-weight:700;margin-bottom:16px;font-size:0.95rem;">Google SSO Settings</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Client ID</label>
                    <input type="text" name="google_client_id" placeholder="....apps.googleusercontent.com"
                           value="<?= htmlspecialchars($s['google_client_id'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Client Secret</label>
                    <input type="password" name="google_client_secret" placeholder="Leave blank to keep existing" autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="step-actions">
            <button type="submit" class="button">Save &amp; Continue &rarr;</button>
            <button type="submit" name="configure_later" value="1" class="btn-later">Configure Later</button>
        </div>
    </form>
</div>

<script>
// Provider toggle click handler
['ldap', 'google'].forEach(function(p) {
    var lbl = document.getElementById('lbl_' + p);
    var cb  = document.getElementById('toggle_' + p);
    var cfg = document.getElementById('config_' + p);
    if (!lbl || !cb || !cfg) return;
    lbl.addEventListener('click', function() {
        setTimeout(function() {
            cfg.style.display = cb.checked ? '' : 'none';
            lbl.style.borderColor  = cb.checked ? 'var(--primary)' : 'var(--border)';
            lbl.style.background   = cb.checked ? 'var(--primary-subtle,#eff6ff)' : '';
        }, 0);
    });
});

// LDAP presets
var guPresets = <?= json_encode(array_map(fn($p) => ['attr' => $p['attr'], 'filter' => $p['filter']], $ldapUserTypes)) ?>;
var guAppearance = <?= json_encode($labelDefaults) ?>;

function guApplyPreset(type) {
    var preset = guPresets[type] || {};
    var appear = guAppearance[type] || {label:'Username', hint:''};
    var attrField = document.getElementById('ldap_user_attribute');
    if (type !== 'custom') {
        attrField.value = preset.attr || '';
    }
    document.getElementById('gu_preview_label').textContent = appear.label;
    var hint = document.getElementById('gu_preview_hint');
    hint.textContent    = appear.hint;
    hint.style.display  = appear.hint ? '' : 'none';
}

// Test LDAP connection
function guTestLdap() {
    var btn = event.target;
    var res = document.getElementById('gu_ldap_result');
    btn.textContent = 'Testing…'; btn.disabled = true;
    var form = btn.closest('form');
    var data = new FormData(form);
    fetch('/admin/setup/auth/test-ldap', {method:'POST', body: data})
        .then(function(r){ return r.json(); })
        .then(function(j){
            res.style.display     = 'block';
            res.style.background  = j.success ? 'var(--success-bg,#f0fdf4)'  : 'var(--danger-bg,#fef2f2)';
            res.style.border      = '1px solid ' + (j.success ? 'var(--success-border,#bbf7d0)' : 'var(--danger-border,#fecaca)');
            res.style.color       = j.success ? '#166534' : '#991b1b';
            res.textContent       = (j.success ? '✓ ' : '✗ ') + j.message;
        })
        .catch(function(){ res.style.display='block'; res.textContent='Request failed.'; })
        .finally(function(){ btn.textContent='Test Connection'; btn.disabled=false; });
}
</script>
