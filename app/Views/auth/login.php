<?php use App\Core\View; ?>

<div class="card" style="max-width:440px;margin:60px auto;">
    <div style="text-align:center;margin-bottom:24px;">
        <div style="font-size:2rem;margin-bottom:8px;">✓</div>
        <h1 style="font-size:1.4rem;margin:0;"><?= View::e($org['name'] ?? 'dadCHECKIN-TOO') ?></h1>
        <p style="color:var(--text-muted);font-size:0.875rem;margin:4px 0 0;">Staff Sign In</p>
    </div>

    <?php
    $hasLocal    = isset($providers['local']);
    $hasLdap     = isset($providers['ldap']);
    $hasGoogle   = isset($providers['google']);
    $hasMicrosoft = isset($providers['microsoft']);
    $hasOAuth    = $hasGoogle || $hasMicrosoft;
    $hasForm     = $hasLocal || $hasLdap;
    ?>

    <!-- OAuth buttons -->
    <?php if ($hasOAuth): ?>
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:<?= $hasForm ? '20px' : '0' ?>;">
        <?php if ($hasGoogle): ?>
        <a href="/auth/redirect/google" class="button button-oauth">
            <svg width="18" height="18" viewBox="0 0 48 48" style="vertical-align:middle;margin-right:10px;">
                <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9 3.2l6.7-6.7C35.8 2.4 30.3 0 24 0 14.7 0 6.8 5.4 2.9 13.3l7.8 6C12.4 13 17.8 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4 6.9-10 6.9-17z"/>
                <path fill="#FBBC05" d="M10.7 28.7A14.6 14.6 0 0 1 9.5 24c0-1.6.3-3.2.8-4.7l-7.8-6A24 24 0 0 0 0 24c0 3.9.9 7.5 2.5 10.8l8.2-6.1z"/>
                <path fill="#34A853" d="M24 48c6.5 0 11.9-2.1 15.9-5.8l-7.5-5.8c-2.1 1.4-4.8 2.2-8.4 2.2-6.2 0-11.5-4.2-13.4-9.9l-8.2 6.1C6.7 42.5 14.8 48 24 48z"/>
            </svg>
            Sign in with Google
        </a>
        <?php endif; ?>
        <?php if ($hasMicrosoft): ?>
        <a href="/auth/redirect/microsoft" class="button button-oauth">
            <svg width="18" height="18" viewBox="0 0 21 21" style="vertical-align:middle;margin-right:10px;">
                <rect x="1"  y="1"  width="9" height="9" fill="#F25022"/>
                <rect x="11" y="1"  width="9" height="9" fill="#7FBA00"/>
                <rect x="1"  y="11" width="9" height="9" fill="#00A4EF"/>
                <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
            </svg>
            Sign in with Microsoft
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Divider between OAuth and form -->
    <?php if ($hasOAuth && $hasForm): ?>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <hr style="flex:1;border:none;border-top:1px solid var(--border);">
        <span style="color:var(--text-muted);font-size:0.8rem;">or</span>
        <hr style="flex:1;border:none;border-top:1px solid var(--border);">
    </div>
    <?php endif; ?>

    <!-- Local / LDAP form -->
    <?php if ($hasForm): ?>
    <?php
    // Pull admin-configured LDAP label/hint from org settings
    $ldapLabel = $settings['ldap_login_label'] ?? '';
    $ldapHint  = $settings['ldap_login_hint']  ?? '';
    if (!$ldapLabel) $ldapLabel = 'Username';

    // Initial state depends on which providers exist
    $ldapOnly  = $hasLdap && !$hasLocal;
    $localOnly = $hasLocal && !$hasLdap;
    // Default to LDAP when available so directory users don't have to switch
    $defaultProvider = $hasLdap ? 'ldap' : 'local';
    $startWithLdap   = $hasLdap; // true when LDAP is available (ldap-only or both)
    ?>
    <form method="POST" action="/auth/login">
        <input type="hidden" name="provider" id="hidden_provider"
               value="<?= $defaultProvider ?>">

        <?php if ($hasLdap && $hasLocal): ?>
        <div class="form-group" style="margin-bottom:12px;">
            <label>Sign-in Method</label>
            <select name="provider" id="login_provider" onchange="updateLoginForm(this.value)">
                <option value="ldap"><?= View::e($ldapLabel) ?> (Directory)</option>
                <option value="local">Local Account (email &amp; password)</option>
            </select>
        </div>
        <?php endif; ?>

        <div class="form-group" style="margin-bottom:<?= ($startWithLdap && $ldapHint) ? '4px' : '12px' ?>;" id="login_field_group">
            <label id="login_field_label"><?= $startWithLdap ? View::e($ldapLabel) : 'Email' ?></label>
            <input type="<?= $startWithLdap ? 'text' : 'email' ?>"
                   name="<?= $startWithLdap ? 'username' : 'email' ?>"
                   id="login_field"
                   placeholder="<?= $startWithLdap ? '' : 'your@email.com' ?>"
                   required autocomplete="username">
        </div>
        <?php if ($startWithLdap && $ldapHint): ?>
        <p id="login_field_hint" style="color:var(--text-muted);font-size:0.8rem;margin:0 0 12px;">
            <?= View::e($ldapHint) ?>
        </p>
        <?php elseif ($hasLocal && !$hasLdap): ?>
        <?php else: ?>
        <p id="login_field_hint" style="color:var(--text-muted);font-size:0.8rem;margin:0 0 12px;display:none;">
            <?= View::e($ldapHint) ?>
        </p>
        <?php endif; ?>

        <div class="form-group" style="margin-bottom:20px;">
            <label>Password</label>
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
        </div>
        <button type="submit" class="button" style="width:100%;">Sign In</button>
    </form>
    <?php endif; ?>
</div>

<style>
.button-oauth {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface);
    color: var(--text);
    border: 1px solid var(--border);
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: background .15s, border-color .15s;
}
.button-oauth:hover {
    background: var(--surface-2);
    border-color: var(--primary);
}
</style>

<?php if (isset($providers['local'], $providers['ldap'])): ?>
<script>
var ldapLabel = <?= json_encode($ldapLabel) ?>;
var ldapHint  = <?= json_encode($ldapHint) ?>;

function updateLoginForm(provider) {
    var label = document.getElementById('login_field_label');
    var input = document.getElementById('login_field');
    var hint  = document.getElementById('login_field_hint');

    if (provider === 'ldap') {
        label.textContent  = ldapLabel;
        input.type         = 'text';
        input.name         = 'username';
        input.placeholder  = '';
        if (hint) {
            hint.style.display = ldapHint ? '' : 'none';
            hint.textContent   = ldapHint;
        }
    } else {
        label.textContent  = 'Email';
        input.type         = 'email';
        input.name         = 'email';
        input.placeholder  = 'your@email.com';
        if (hint) hint.style.display = 'none';
    }
}
</script>
<?php endif; ?>
