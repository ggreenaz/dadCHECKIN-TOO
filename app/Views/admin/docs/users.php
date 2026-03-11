<?php $docSub = 'Manage who can log into the admin panel, their roles, and individual permissions.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>The System Users page lets org admins control who can sign into the admin panel. You can add users, assign them roles that determine what parts of the admin they can see, and grant fine-grained permissions for specific actions like exporting data or managing hosts. Users can authenticate via a local password, LDAP/Active Directory, or an OAuth provider (Google, Microsoft).</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you see on this page</h2>
        <div class="doc-item">
            <div class="doc-item-label">LDAP Access Mode card</div>
            <div class="doc-item-desc">Only visible when LDAP authentication is configured. Lets you toggle between Open mode (any LDAP user can sign in) and Closed mode (only users explicitly listed in the table may sign in).</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Users table</div>
            <div class="doc-item-desc">Every admin user for your organisation with their avatar initials, name, email, role badge, active permissions, auth provider, last login date, status (active / inactive), and action buttons.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Role badges</div>
            <div class="doc-item-desc">Colour-coded: amber = super_admin, blue = org_admin, green = location_admin, grey = staff.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Inactive users</div>
            <div class="doc-item-desc">Shown with reduced opacity so they remain visible but clearly inactive. They cannot sign in while deactivated.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <h3 class="doc-section-sub">Adding a user</h3>
        <ol class="doc-ol">
            <li>Click <strong>Add User</strong> at the top right.</li>
            <li>Enter the user's full name and email address.</li>
            <li>Choose a role (see Permission Model below).</li>
            <li>Select an authentication provider. If your organisation uses LDAP, the default is LDAP — no password is needed. For local auth, enter a password.</li>
            <li>Tick any additional permissions for location_admin or staff users.</li>
            <li>Click <strong>Create User</strong>.</li>
        </ol>

        <h3 class="doc-section-sub">LDAP vs local authentication</h3>
        <ul class="doc-ul">
            <li><strong>Local</strong> — the user signs in with an email address and password stored in dadCHECKIN-TOO. A password must be set when the account is created. You can change it at any time by editing the user and entering a new password.</li>
            <li><strong>LDAP</strong> — the user signs in with their existing directory credentials. dadCHECKIN-TOO verifies the login against your LDAP/AD server. No password is stored in dadCHECKIN-TOO.</li>
            <li><strong>Google / Microsoft</strong> — the user clicks the OAuth button on the login screen and is authenticated via the provider. The email address must match what is stored here.</li>
        </ul>

        <h3 class="doc-section-sub">Editing or deactivating a user</h3>
        <ul class="doc-ul">
            <li>Click <strong>Edit</strong> next to any user to change their name, email, role, provider, or permissions.</li>
            <li>Click <strong>Deactivate</strong> to prevent the user from signing in without deleting their record. You can reactivate them at any time.</li>
            <li>You cannot deactivate your own account.</li>
        </ul>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration</h2>

        <h3 class="doc-section-sub">LDAP access modes</h3>
        <ul class="doc-ul">
            <li><strong>Open mode</strong> — any user whose credentials are valid in your LDAP/AD directory can sign in and will be provisioned automatically. Useful when you want to grant broad access without pre-registering each person.</li>
            <li><strong>Closed mode</strong> — only LDAP users whose email address is already in the users table are allowed to sign in. Use this when you want strict control over who has admin panel access.</li>
        </ul>

        <h3 class="doc-section-sub">Permission model</h3>
        <div class="doc-item">
            <div class="doc-item-label">super_admin</div>
            <div class="doc-item-desc">Full access to everything including auth setup and system configuration. Only one local super_admin account is permitted per organisation.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">org_admin</div>
            <div class="doc-item-desc">Full access to all admin features including user management, settings, and data. All permissions are granted automatically.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">location_admin</div>
            <div class="doc-item-desc">Can view the dashboard, live logs, and history. Additional permissions (reports, analytics, hosts, reasons, export) must be explicitly granted.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">staff</div>
            <div class="doc-item-desc">Minimal access by default — can view the dashboard and live logs only. All other access requires explicit permissions to be granted.</div>
        </div>

        <h3 class="doc-section-sub" style="margin-top:12px;">Individual permissions</h3>
        <ul class="doc-ul">
            <li><strong>View Reports</strong> — access the Reports Hub to view generated reports.</li>
            <li><strong>Configure Reports</strong> — set up report schedules and email recipients.</li>
            <li><strong>Manage Hosts</strong> — add, edit, and deactivate hosts.</li>
            <li><strong>Manage Reasons</strong> — add, edit, and deactivate visit reasons.</li>
            <li><strong>View Analytics</strong> — access the analytics charts and heatmaps.</li>
            <li><strong>Export Data</strong> — download visit history as CSV.</li>
        </ul>
    </div>

</div>
