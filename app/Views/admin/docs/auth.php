<?php
$docMeta = $docMeta ?? ['title' => 'Auth & LDAP Setup', 'back' => '/admin/setup/auth', 'icon' => '🔑'];
include __DIR__ . '/_header.php';
?>

<div class="doc-section">
    <div class="doc-section-title">Overview</div>
    <div class="doc-section-body">
        <p>The Authentication setup page controls <em>how staff log in</em> to the dadCHECKIN-TOO admin panel. This is a <strong>Super Admin only</strong> page — only the system administrator can change authentication providers, as a misconfiguration could lock everyone out.</p>
        <p>dadCHECKIN-TOO separates two concerns:</p>
        <div class="doc-item">
            <div class="doc-item-label">Authentication</div>
            <div class="doc-item-desc">Who are you? Verified by your login provider (local password, LDAP/AD, Google, or Microsoft).</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Authorization</div>
            <div class="doc-item-desc">What can you do? Controlled by dadCHECKIN-TOO roles and permissions regardless of how you logged in.</div>
        </div>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">Authentication Providers</div>
    <div class="doc-section-body">
        <div class="doc-item">
            <div class="doc-item-label">Local Accounts</div>
            <div class="doc-item-desc">Always enabled and cannot be turned off. Staff log in with an email address and password stored in dadCHECKIN-TOO. Ideal for the Super Admin break-glass account and small organizations without a directory service.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">LDAP / Active Directory</div>
            <div class="doc-item-desc">Staff log in with their existing Windows/domain credentials. dadCHECKIN-TOO queries your LDAP or AD server to verify the password — no need to manage separate passwords. Requires your server address, base DN, and optionally a bind account for searching.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Google SSO</div>
            <div class="doc-item-desc">Staff click "Sign in with Google" and authenticate via their Google Workspace account. Requires a Google OAuth Client ID and Secret from the Google Cloud Console. The authorized redirect URI must be set to <code>/auth/callback/google</code>.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Microsoft / Azure AD</div>
            <div class="doc-item-desc">Staff sign in with their Microsoft 365 or Entra (Azure AD) account. Requires an Application (Client) ID, Client Secret, and Tenant ID from the Azure portal. Redirect URI must be set to <code>/auth/callback/microsoft</code>.</div>
        </div>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">LDAP Configuration Fields</div>
    <div class="doc-section-body">
        <div class="doc-item">
            <div class="doc-item-label">Server Host</div>
            <div class="doc-item-desc">The IP address or hostname of your LDAP/AD server (e.g., <code>ldap.yourdomain.com</code> or <code>192.168.1.10</code>).</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Port</div>
            <div class="doc-item-desc">Default is <strong>389</strong> for standard LDAP or <strong>636</strong> for LDAPS (encrypted). Use LDAPS whenever possible.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Use LDAPS (SSL/TLS)</div>
            <div class="doc-item-desc">Enable to use an encrypted connection. Requires a valid certificate on your LDAP server. Strongly recommended for production use.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Base DN</div>
            <div class="doc-item-desc">The starting point in your directory tree for searches (e.g., <code>dc=yourdomain,dc=com</code>). All user lookups are scoped to this location.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Search Contexts</div>
            <div class="doc-item-desc">Optional. Semicolon-separated OUs to search for users (e.g., <code>ou=Staff,dc=domain,dc=com;ou=Admin,dc=domain,dc=com</code>). Leave blank to use the Base DN.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Bind Account DN &amp; Password</div>
            <div class="doc-item-desc">A service account used to search the directory. Many AD environments require this. Use a read-only account with minimal permissions. Leave blank to attempt anonymous bind.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Username Attribute</div>
            <div class="doc-item-desc">The LDAP attribute that contains the user's login name. Use <code>sAMAccountName</code> for Active Directory or <code>uid</code> for OpenLDAP.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Email Attribute</div>
            <div class="doc-item-desc">The attribute containing the user's email address. Typically <code>mail</code>.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Test Connection</div>
            <div class="doc-item-desc">Click <strong>Test Connection</strong> to verify dadCHECKIN-TOO can reach your LDAP server and authenticate before saving. Always test before saving to avoid locking out users.</div>
        </div>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">LDAP Access Mode</div>
    <div class="doc-section-body">
        <p>When LDAP is enabled, you control who can sign in from the <a href="/admin/users">System Users</a> page:</p>
        <div class="doc-item">
            <div class="doc-item-label">Open Mode</div>
            <div class="doc-item-desc">Any user who exists in your LDAP/AD directory can sign in automatically. They are created with the <strong>staff</strong> role. You can promote them to higher roles after their first login. Best for large organizations where you want low friction.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Closed Mode</div>
            <div class="doc-item-desc">Only LDAP users who have been pre-added to the System Users list are allowed to sign in. Others are denied even if their AD credentials are valid. Best for organizations that need tighter access control.</div>
        </div>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">Important Notes</div>
    <div class="doc-section-body">
        <div class="doc-item">
            <div class="doc-item-label">Break-Glass Account</div>
            <div class="doc-item-desc">Always keep at least one local Super Admin account. If your LDAP/SSO provider goes down, this account lets you log in and fix the configuration.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Multiple Providers</div>
            <div class="doc-item-desc">You can enable more than one provider at the same time. The login page will show all enabled options.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Password Changes</div>
            <div class="doc-item-desc">For LDAP/SSO users, passwords are managed entirely by the external provider. dadCHECKIN-TOO never stores their password.</div>
        </div>
    </div>
</div>
