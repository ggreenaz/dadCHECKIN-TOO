<?php
$docMeta = $docMeta ?? ['title' => 'Configuration Guide', 'back' => '/admin/settings', 'icon' => '📋'];
$docSub  = 'Complete reference for every setting in dadCHECKIN-TOO — organization, auto-checkout, kiosk, authentication, users, and notifications.';
include __DIR__ . '/_header.php';
?>

<div class="doc-body">

<!-- ── OVERVIEW ─────────────────────────────────────────────── -->
<div class="doc-section">
    <h2 class="doc-section-title">Overview</h2>
    <p>dadCHECKIN-TOO is configured through several pages in the admin panel. This guide covers every configurable setting in the system, what it does, and what value to enter. Settings are divided into the following areas:</p>
    <ul style="margin:12px 0 0 20px;line-height:2;">
        <li><a href="#org">Organization Settings</a> — name and timezone</li>
        <li><a href="#autocheckout">Auto-Checkout</a> — automatic end-of-day visit closing</li>
        <li><a href="#kiosk">Kiosk Fields</a> — what visitors see on the check-in form</li>
        <li><a href="#auth">Authentication</a> — how staff log in (local, LDAP, Google, Microsoft)</li>
        <li><a href="#ldap">LDAP / Active Directory</a> — complete field reference</li>
        <li><a href="#google">Google SSO</a> — OAuth setup</li>
        <li><a href="#microsoft">Microsoft / Azure AD</a> — OAuth setup</li>
        <li><a href="#ldap-access">LDAP Access Mode</a> — open vs. closed enrollment</li>
        <li><a href="#users">System Users</a> — roles and permissions</li>
        <li><a href="#notifications">Notifications</a> — email alerts and SMTP</li>
        <li><a href="#live">Live Logs &amp; Bulk Checkout</a> — managing active visitors</li>
    </ul>
</div>

<!-- ── ORGANIZATION ──────────────────────────────────────────── -->
<div class="doc-section" id="org">
    <h2 class="doc-section-title">Organization Settings</h2>
    <p>Found at <strong>Admin → Settings</strong>. These settings affect the entire installation. Only Organization Administrators can change them.</p>

    <div class="doc-item">
        <div class="doc-item-label">Organization Name</div>
        <div class="doc-item-desc">
            The name displayed in the header bar on every admin page and on the visitor kiosk check-in screen.
            Set this to your school, district office, or facility name exactly as you want visitors to see it.
            <br><br>
            <strong>Example:</strong> <code>South Toms River Elementary School</code>
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Timezone</div>
        <div class="doc-item-desc">
            All visit timestamps are stored internally in UTC. The timezone setting converts them for display
            and for scheduling auto-checkout. <strong>This must be set correctly before going live.</strong>
            If the timezone is wrong, all check-in/check-out times will display incorrectly, and
            the auto-checkout end-of-day trigger will fire at the wrong clock time.
            <br><br>
            <strong>For New Jersey:</strong> Select <code>Eastern (New York)</code> — this handles both
            Eastern Standard Time (EST, UTC-5) and Eastern Daylight Time (EDT, UTC-4) automatically.
            <br><br>
            <strong>Available options:</strong> Eastern, Central, Mountain, Mountain (no DST), Pacific,
            Alaska, Hawaii, UTC, London, Paris/Berlin, Tokyo, Sydney.
        </div>
    </div>
</div>

<!-- ── AUTO-CHECKOUT ─────────────────────────────────────────── -->
<div class="doc-section" id="autocheckout">
    <h2 class="doc-section-title">Auto-Checkout</h2>
    <p>
        Auto-checkout automatically closes visits that are still open at end of day or that have been
        open longer than a set limit. This prevents "ghost" visitors from accumulating overnight when
        staff forget to check someone out. It runs via a server cron job — it does not run on its own.
    </p>

    <div class="doc-item">
        <div class="doc-item-label">Enable auto-checkout</div>
        <div class="doc-item-desc">
            Toggle this on to activate automatic closing. The feature has no effect unless the cron job
            is also configured on the server (see below). Default: off.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">End-of-day checkout time</div>
        <div class="doc-item-desc">
            The time of day — in your configured timezone — when any visit that is still open gets
            automatically closed. The cron job must run at or after this time for it to trigger.
            <br><br>
            <strong>Recommendation for schools:</strong> Set to <code>17:00</code> (5:00 PM) or
            <code>18:00</code> (6:00 PM) to catch any visitors who left without checking out.
            <br><br>
            The system only closes visits whose check-in time is <em>before</em> this end-of-day threshold,
            so a visitor who checks in at 4:45 PM will not be immediately closed at 5:00 PM — the system
            waits until the next cron run after both conditions are met.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Max open hours (stale threshold)</div>
        <div class="doc-item-desc">
            A safety net. Any visit open longer than this many hours is closed regardless of time of day.
            This catches cases where the end-of-day cron was missed (server downtime, holiday, etc.).
            <br><br>
            <strong>Recommendation:</strong> Set to <code>10</code>–<code>12</code> hours for a school day.
            This means a 7:30 AM check-in would auto-close by 5:30–7:30 PM even if the end-of-day trigger
            did not fire.
            <br><br>
            <strong>Maximum allowed:</strong> 24 hours (enforced by the system).
            <br><br>
            This threshold also sets the default flag level on the <strong>Live Logs</strong> page bulk
            checkout tool — visits older than this many hours are highlighted in red and pre-selected
            when you click "Select All Flagged."
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Mark closed visits as</div>
        <div class="doc-item-desc">
            Controls the status label applied to auto-closed visits.
            <br><br>
            <strong>Auto-Completed</strong> (recommended) — auto-closed visits are labeled differently
            from manually checked-out visits. You can filter by this status in the Visit History and
            Log Hub to see how many visits are being auto-closed daily. A high number indicates that
            staff are not properly checking visitors out.
            <br><br>
            <strong>Completed</strong> — auto-closed visits are indistinguishable from manual checkouts.
            Choose this only if you do not need to audit auto-closures.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Cron job setup</div>
        <div class="doc-item-desc">
            Auto-checkout requires a Linux cron job to trigger the script. Log in to the server as root
            or with sudo and run <code>crontab -e</code>, then add one of the following lines:
            <br><br>
            <strong>Run every 15 minutes</strong> (recommended — catches end-of-day and stale visits promptly):
            <br>
            <code>*/15 * * * * php /var/www/checkin/scripts/auto_checkout.php &gt;&gt; /var/log/checkin-auto-checkout.log 2&gt;&amp;1</code>
            <br><br>
            <strong>Run once at a fixed time</strong> (e.g., 5:30 PM every weekday):
            <br>
            <code>30 17 * * 1-5 php /var/www/checkin/scripts/auto_checkout.php &gt;&gt; /var/log/checkin-auto-checkout.log 2&gt;&amp;1</code>
            <br><br>
            The script logs every visit it closes. Review <code>/var/log/checkin-auto-checkout.log</code>
            periodically to confirm it is running.
        </div>
    </div>
</div>

<!-- ── KIOSK FIELDS ──────────────────────────────────────────── -->
<div class="doc-section" id="kiosk">
    <h2 class="doc-section-title">Kiosk Fields</h2>
    <p>
        Found at <strong>Admin → Settings → Manage Kiosk Fields</strong> or
        <strong>Admin → Setup → Kiosk</strong>. Controls which fields appear on the
        public visitor check-in form. Each field can be independently shown or hidden,
        and shown fields can optionally be made required.
    </p>

    <div class="doc-item">
        <div class="doc-item-label">First Name</div>
        <div class="doc-item-desc">
            Always shown and always required. Cannot be turned off. This is the minimum
            information needed to create a visitor record.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Last Name</div>
        <div class="doc-item-desc">
            Show: Adds a Last Name field to the check-in form.<br>
            Required: Visitor cannot submit without entering a last name.<br><br>
            <strong>Recommendation:</strong> Show and require for schools so visitor records are complete.
            If you only need a first name (low-friction kiosk), you can hide it.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Phone Number</div>
        <div class="doc-item-desc">
            Show: Adds a Phone Number field.<br>
            Required: Visitor must enter a phone number.<br><br>
            Phone number is also used as a lookup method on the check-out (/depart) page.
            If visitors will use phone number to check themselves out, this field must be shown
            (but does not need to be required — they can still check out by name if left blank).
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Email</div>
        <div class="doc-item-desc">
            Show: Adds an Email field.<br>
            Required: Visitor must enter an email address.<br><br>
            Email is useful if you send notification emails to visitors or if email notification
            rules are configured. If your organization does not use email-based visitor notifications,
            this field can be hidden to keep the kiosk form minimal.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Notes</div>
        <div class="doc-item-desc">
            Show: Adds a free-text Notes field where the visitor can enter additional comments
            (e.g., "Dropping off lunch for Johnny Smith — Room 14").<br>
            Required: Visitor must enter a note.<br><br>
            <strong>Recommendation:</strong> Show but do not require. Notes are helpful for context
            but requiring them adds friction to the check-in process.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Required vs. optional rule</div>
        <div class="doc-item-desc">
            A field cannot be required if it is not shown. If you uncheck Show, the Required
            checkbox is automatically disabled and cleared. You must show a field before
            you can make it required.
        </div>
    </div>
</div>

<!-- ── AUTHENTICATION OVERVIEW ──────────────────────────────── -->
<div class="doc-section" id="auth">
    <h2 class="doc-section-title">Authentication — Overview</h2>
    <p>
        Found at <strong>Admin → Setup → Authentication</strong>. Controls how staff and
        administrators log in to the dadCHECKIN-TOO admin panel.
        <strong>This page is restricted to Super Admins only</strong> — a misconfiguration
        could lock all users out, so access is intentionally limited.
    </p>
    <p>
        dadCHECKIN-TOO separates <em>authentication</em> (proving who you are)
        from <em>authorization</em> (what you are allowed to do). Authentication is handled
        by the configured providers below. Authorization is handled by the role assigned
        to each user in the System Users list.
    </p>
    <p>
        Multiple providers can be active at the same time. The login page will display all
        enabled options. <strong>Local accounts are always available</strong> and cannot be
        disabled — they serve as the break-glass fallback if an external provider fails.
    </p>

    <div class="doc-item">
        <div class="doc-item-label">Local Accounts</div>
        <div class="doc-item-desc">
            Always on. Staff log in with an email address and a password stored in
            dadCHECKIN-TOO's own database. Passwords are bcrypt-hashed and never stored in plain text.
            Ideal for the Super Admin account and for small organizations that do not have a
            directory service. Manage local accounts at <strong>Admin → Users</strong>.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">LDAP / Active Directory</div>
        <div class="doc-item-desc">
            Staff log in with their existing Windows domain or LDAP credentials.
            dadCHECKIN-TOO queries your directory server to verify the password —
            no passwords are stored. On first successful login, users are automatically
            created in the system with the <em>staff</em> role (in Open Mode) or must
            be pre-provisioned (in Closed Mode). See the <a href="#ldap">LDAP section</a> below.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Google SSO</div>
        <div class="doc-item-desc">
            Staff click "Sign in with Google" and complete authentication via Google.
            Requires a Google OAuth 2.0 application. See the <a href="#google">Google SSO section</a> below.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Microsoft / Azure AD</div>
        <div class="doc-item-desc">
            Staff sign in with their Microsoft 365 or Entra ID (formerly Azure Active Directory) account.
            Requires an app registration in the Azure Portal.
            See the <a href="#microsoft">Microsoft section</a> below.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Break-Glass Rule</div>
        <div class="doc-item-desc">
            <strong>Always keep at least one active local Super Admin account.</strong>
            If your LDAP server goes offline, your Google OAuth app is misconfigured, or
            your Azure tenant changes, you need a way to log in and fix the problem.
            The local Super Admin account is that fallback. Do not delete it.
        </div>
    </div>
</div>

<!-- ── LDAP ──────────────────────────────────────────────────── -->
<div class="doc-section" id="ldap">
    <h2 class="doc-section-title">LDAP / Active Directory — Complete Field Reference</h2>
    <p>
        Enable the LDAP / Active Directory toggle, then fill in the fields below.
        Click <strong>Test Connection</strong> before saving to verify the settings work.
    </p>

    <h3 style="font-size:0.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin:20px 0 12px;">Server</h3>

    <div class="doc-item">
        <div class="doc-item-label">Host URL</div>
        <div class="doc-item-desc">
            The address of your LDAP or Active Directory server, including the protocol prefix.
            <br><br>
            Use <code>ldap://</code> for standard unencrypted LDAP (port 389):
            <br><code>ldap://10.1.55.33/</code> or <code>ldap://dc.yourdomain.org/</code>
            <br><br>
            Use <code>ldaps://</code> for SSL/TLS encrypted LDAP (port 636):
            <br><code>ldaps://dc.yourdomain.org/</code>
            <br><br>
            For failover, separate multiple servers with a semicolon:
            <br><code>ldap://dc1.domain.com/; ldap://dc2.domain.com/</code>
            <br><br>
            <strong>Tip:</strong> Using an IP address (e.g. <code>ldap://10.1.55.33/</code>) avoids
            DNS resolution failures if internal DNS is not configured on the web server.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Distinguished Name (Bind DN)</div>
        <div class="doc-item-desc">
            The full Distinguished Name of a service account used to search the directory.
            Most Active Directory environments require a bind account — anonymous binds are
            typically disabled.
            <br><br>
            <strong>AD format:</strong> <code>cn=ServiceAccount,ou=ServiceAccounts,dc=stg,dc=stgrsd,dc=org</code>
            <br>
            <strong>UPN format (also accepted by AD):</strong> <code>serviceaccount@stg.stgrsd.org</code>
            <br><br>
            Use a dedicated read-only service account with the minimum permissions needed
            to search the directory — it does not need write access unless you enable
            directory sync. Leave this field blank only if your LDAP server allows anonymous binds.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Password (Bind Password)</div>
        <div class="doc-item-desc">
            The password for the bind account above. Stored encrypted in the database.
            Leave this field blank when editing to keep the existing saved password.
            You only need to enter a password when setting it for the first time or changing it.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Test Connection</div>
        <div class="doc-item-desc">
            Clicking <strong>Test Connection</strong> verifies three things without saving:
            (1) the server is reachable at the Host URL, (2) the bind account credentials
            are accepted, and (3) the directory is searchable. Always test before saving,
            especially after changing the Host URL or password.
            <br><br>
            A successful test result does not permanently save your settings — you must still
            click <strong>Save Authentication Settings</strong>.
        </div>
    </div>

    <h3 style="font-size:0.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin:20px 0 12px;">Directory Schema</h3>

    <div class="doc-item">
        <div class="doc-item-label">User Type</div>
        <div class="doc-item-desc">
            Selects a preset that auto-fills the User Attribute and Search Filter fields with
            sensible defaults for your directory type.
            <br><br>
            <strong>MS Active Directory</strong> — Use this for Windows Server / Active Directory.
            Sets attribute to <code>sAMAccountName</code>, filter to
            <code>(&amp;(objectClass=user)(sAMAccountName=%s))</code>.
            <br><br>
            <strong>Novell Directory Services</strong> — Use for Novell eDirectory.
            Attribute: <code>cn</code>.
            <br><br>
            <strong>posixAccount (RFC 2307)</strong> — Use for Linux/Unix LDAP directories.
            Attribute: <code>uid</code>.
            <br><br>
            <strong>sambaSamAccount</strong> — Use for Samba-based Windows-compatible directories.
            Attribute: <code>sAMAccountName</code>.
            <br><br>
            <strong>inetOrgPerson (generic LDAP)</strong> — Use for generic LDAP servers
            (OpenLDAP, 389 Directory, etc.). Attribute: <code>uid</code>.
            <br><br>
            <strong>Custom / Manual</strong> — Override all values manually using the
            User Attribute and Search Filter fields.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Contexts</div>
        <div class="doc-item-desc">
            The Organizational Units (OUs) where user accounts are located. dadCHECKIN-TOO
            searches each context in order and stops at the first one where the user is found.
            Separate multiple contexts with a semicolon.
            <br><br>
            <strong>Example (single OU):</strong>
            <br><code>ou=stg,dc=stg,dc=stgrsd,dc=org</code>
            <br><br>
            <strong>Example (multiple OUs — staff and administrators):</strong>
            <br><code>ou=Staff,dc=domain,dc=com;ou=Administrators,dc=domain,dc=com</code>
            <br><br>
            If left blank, the Base DN field is used as the search root. Providing specific
            contexts is faster and more secure than searching the entire directory.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Search Subcontexts</div>
        <div class="doc-item-desc">
            When checked, dadCHECKIN-TOO performs a deep subtree search within each context,
            finding users in sub-OUs nested beneath the listed contexts.
            <br><br>
            When unchecked, only the immediate level of each context is searched (one-level scope).
            <br><br>
            <strong>For Active Directory:</strong> This is automatically set to subtree search
            regardless of this setting, because AD restricts one-level searches on most OUs.
            For other directory types, enable this if your users are organized in sub-OUs
            beneath the listed contexts.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">User Attribute</div>
        <div class="doc-item-desc">
            The LDAP attribute compared against the username typed at login.
            Auto-filled when you select a User Type preset.
            <br><br>
            <strong>Active Directory:</strong> <code>sAMAccountName</code>
            — this is the short Windows login name (e.g. <code>jsmith</code>, not <code>jsmith@domain.com</code>).
            <br>
            <strong>OpenLDAP / posixAccount:</strong> <code>uid</code>
            <br>
            <strong>Novell / generic:</strong> <code>cn</code>
            <br><br>
            If staff log in with their email address instead of their short username,
            set this to <code>mail</code> or <code>userPrincipalName</code>.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Base DN</div>
        <div class="doc-item-desc">
            The root of the directory tree, used as a fallback when no Contexts are specified.
            This should be the top-level DN of your domain.
            <br><br>
            <strong>Example:</strong> <code>dc=stg,dc=stgrsd,dc=org</code>
            for the domain <code>stg.stgrsd.org</code>.
            <br><br>
            If you have specified Contexts above, the Base DN is not used for user searches.
            It may still be used by the Test Connection tool.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Search Filter (advanced)</div>
        <div class="doc-item-desc">
            An LDAP search filter that selects the user record. Use <code>%s</code> as the
            placeholder for the entered username. Leave blank to auto-generate from the
            selected User Type and User Attribute.
            <br><br>
            Most installations should leave this blank. Only set it manually if your directory
            has a non-standard schema or you need to add extra conditions (e.g., restrict login
            to members of a specific group).
            <br><br>
            <strong>Example (AD with group membership check):</strong>
            <br><code>(&amp;(objectClass=user)(sAMAccountName=%s)(memberOf=cn=CheckInUsers,ou=Groups,dc=domain,dc=com))</code>
            <br><br>
            <strong>Standard AD auto-generated filter (shown for reference):</strong>
            <br><code>(&amp;(objectClass=user)(sAMAccountName=%s))</code>
        </div>
    </div>

    <h3 style="font-size:0.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin:20px 0 12px;">Login Page Appearance</h3>

    <div class="doc-item">
        <div class="doc-item-label">Login Field Label</div>
        <div class="doc-item-desc">
            The label shown above the username input on the staff login page.
            Auto-set from the User Type preset (typically <em>Username</em>).
            Enable <strong>Customize for this organization</strong> to override it.
            <br><br>
            <strong>Examples:</strong> <code>Username</code>, <code>Network Login</code>,
            <code>Windows Username</code>, <code>Email Address</code>
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Login Hint</div>
        <div class="doc-item-desc">
            A short helper message shown below the login field to guide staff.
            Enable <strong>Customize for this organization</strong> to set it.
            <br><br>
            <strong>Example:</strong> <em>Enter your network username — not your full email address
            (e.g., jsmith, not jsmith@stgrsd.org)</em>
            <br><br>
            Particularly useful if staff are confused about whether to enter their short username
            or their full email.
        </div>
    </div>
</div>

<!-- ── GOOGLE SSO ────────────────────────────────────────────── -->
<div class="doc-section" id="google">
    <h2 class="doc-section-title">Google SSO</h2>
    <p>
        Staff click "Sign in with Google" and are redirected to Google's login page.
        After authenticating, they are returned to dadCHECKIN-TOO. Their Google account
        email must match a user record in the system (or auto-provisioning must be enabled).
    </p>
    <p><strong>Setup steps in Google Cloud Console:</strong></p>
    <ol style="margin:8px 0 16px 20px;line-height:2.2;">
        <li>Go to <strong>APIs &amp; Services → Credentials</strong></li>
        <li>Click <strong>Create Credentials → OAuth 2.0 Client ID</strong></li>
        <li>Application type: <strong>Web application</strong></li>
        <li>Add an <strong>Authorized redirect URI</strong>:
            <code>https://yourdomain.com/auth/callback/google</code></li>
        <li>Copy the <strong>Client ID</strong> and <strong>Client Secret</strong></li>
    </ol>

    <div class="doc-item">
        <div class="doc-item-label">Client ID</div>
        <div class="doc-item-desc">
            The OAuth 2.0 Client ID from the Google Cloud Console.
            It ends in <code>.apps.googleusercontent.com</code>.
            <br><strong>Example:</strong> <code>123456789-abcdefg.apps.googleusercontent.com</code>
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Client Secret</div>
        <div class="doc-item-desc">
            The OAuth 2.0 Client Secret from the Google Cloud Console.
            Stored encrypted. Leave blank when editing to keep the existing value.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Authorized redirect URI</div>
        <div class="doc-item-desc">
            This must be set in the Google Cloud Console exactly as:
            <br><code>https://yourdomain.com/auth/callback/google</code>
            <br><br>
            Replace <code>yourdomain.com</code> with your actual server address.
            Google will reject the OAuth flow if the redirect URI in the console does not exactly
            match what dadCHECKIN-TOO sends. The URI is shown on the Auth Setup page next to
            the credential fields.
        </div>
    </div>
</div>

<!-- ── MICROSOFT ─────────────────────────────────────────────── -->
<div class="doc-section" id="microsoft">
    <h2 class="doc-section-title">Microsoft / Azure AD (Entra ID)</h2>
    <p>
        Staff click "Sign in with Microsoft" and authenticate via their Microsoft 365
        or Entra ID account. Requires an app registration in the Azure Portal.
    </p>
    <p><strong>Setup steps in the Azure Portal:</strong></p>
    <ol style="margin:8px 0 16px 20px;line-height:2.2;">
        <li>Go to <strong>Azure Active Directory → App registrations → New registration</strong></li>
        <li>Name: <em>dadCHECKIN-TOO</em> (or any name)</li>
        <li>Supported account types: <em>Accounts in this organizational directory only</em>
            (or "Any Azure AD directory" for multi-tenant)</li>
        <li>Redirect URI: <strong>Web</strong> →
            <code>https://yourdomain.com/auth/callback/microsoft</code></li>
        <li>After creation, copy the <strong>Application (Client) ID</strong> and <strong>Directory (Tenant) ID</strong></li>
        <li>Go to <strong>Certificates &amp; secrets → New client secret</strong>, copy the <strong>Value</strong>
            (not the Secret ID — the Value is the actual secret)</li>
    </ol>

    <div class="doc-item">
        <div class="doc-item-label">Application (Client) ID</div>
        <div class="doc-item-desc">
            The GUID shown on the app registration Overview page.
            <br><strong>Format:</strong> <code>xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</code>
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Client Secret</div>
        <div class="doc-item-desc">
            The secret <strong>Value</strong> (not the ID) created under Certificates &amp; secrets.
            Stored encrypted. Leave blank when editing to keep the existing value.
            <br><br>
            <strong>Important:</strong> Client secrets expire. Set a reminder to rotate this
            secret before its expiration date, or users will be unable to log in via Microsoft.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Tenant ID</div>
        <div class="doc-item-desc">
            Controls which Microsoft accounts are allowed to sign in.
            <br><br>
            <strong><code>common</code></strong> — Any Microsoft account (personal or organizational).
            Use this only if you intentionally want open access.
            <br><br>
            <strong>Your Tenant ID GUID</strong> (recommended for schools) — Restricts login
            to accounts within your specific Azure AD / Entra ID tenant.
            Found on the app registration Overview page as "Directory (tenant) ID."
            <br><strong>Format:</strong> <code>xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</code>
            <br><br>
            <strong>Recommendation for school districts:</strong> Always use your specific
            Tenant ID so only district Microsoft 365 accounts can log in.
        </div>
    </div>
</div>

<!-- ── LDAP ACCESS MODE ──────────────────────────────────────── -->
<div class="doc-section" id="ldap-access">
    <h2 class="doc-section-title">LDAP Access Mode</h2>
    <p>
        Found at <strong>Admin → Users → LDAP Access Mode</strong>. When LDAP is enabled,
        this setting controls whether any valid directory user can sign in, or only
        pre-approved users.
    </p>

    <div class="doc-item">
        <div class="doc-item-label">Open Mode</div>
        <div class="doc-item-desc">
            Any user who exists in your LDAP / Active Directory and provides valid credentials
            can sign in. On their first login, a dadCHECKIN-TOO account is automatically
            created with the <strong>staff</strong> role.
            <br><br>
            Administrators can then promote these users to higher roles from the System Users page.
            <br><br>
            <strong>Best for:</strong> Large organizations where all or most directory users
            should have some level of access, and low-friction onboarding is preferred.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Closed Mode</div>
        <div class="doc-item-desc">
            Only LDAP users who have been explicitly added to the System Users list are allowed
            to log in. A user whose AD credentials are valid but who is not on the approved list
            will be denied access with an "Invalid credentials" message.
            <br><br>
            <strong>Best for:</strong> Organizations that need strict control over who accesses
            the admin panel — e.g., only the front office secretary and principal should have access,
            not all district staff.
            <br><br>
            To pre-add a user in Closed Mode: go to <strong>Admin → Users → Add User</strong>,
            set the Auth Provider to <em>LDAP</em>, and enter their directory username.
            They will be matched by username on login and their DN will be updated automatically.
        </div>
    </div>
</div>

<!-- ── SYSTEM USERS ──────────────────────────────────────────── -->
<div class="doc-section" id="users">
    <h2 class="doc-section-title">System Users</h2>
    <p>
        Found at <strong>Admin → Users</strong>. Manages who has access to the dadCHECKIN-TOO
        admin panel and what they can do. Only Organization Admins and Super Admins can manage users.
    </p>

    <div class="doc-item">
        <div class="doc-item-label">Super Admin</div>
        <div class="doc-item-desc">
            Full system access. Can change authentication providers, manage all organizations,
            access the System Setup wizard, and manage all users. There should be exactly one
            Super Admin — the system administrator — with a local account as a break-glass fallback.
            <br><br>
            <strong>Capabilities:</strong> Everything in the system including Auth Setup,
            System Setup, User Management, and all admin functions.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Org Admin</div>
        <div class="doc-item-desc">
            Full access to their organization's settings, users, hosts, reasons, fields,
            and reports. Cannot access Auth Setup or manage other organizations.
            <br><br>
            <strong>Typical assignment:</strong> Principal, front office manager, or IT coordinator
            responsible for the check-in system.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Location Admin</div>
        <div class="doc-item-desc">
            Can view and manage visits, hosts, and reasons. Cannot change system settings or manage users.
            Suitable for a building secretary who needs to run reports and manage check-ins but should
            not change global configuration.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Staff</div>
        <div class="doc-item-desc">
            Read-only access to Live Logs, Visit History, and the Log Hub. Can view but not edit
            hosts, reasons, or settings. Automatically assigned to LDAP users on first login
            in Open Mode.
            <br><br>
            <strong>Typical assignment:</strong> Teachers or office assistants who need to see
            who is currently checked in.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Deactivating a user</div>
        <div class="doc-item-desc">
            Deactivated users cannot log in, but their account and associated audit trail are preserved.
            Use deactivation instead of deletion when a staff member leaves — you may need to reference
            their account in historical records. Deactivated accounts can be reactivated at any time.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Auth Provider field</div>
        <div class="doc-item-desc">
            When adding a user manually, set Auth Provider to match how they will log in:
            <br>
            — <strong>local</strong>: they use email + password
            <br>
            — <strong>ldap</strong>: they use their AD/LDAP username
            <br>
            — <strong>google</strong> / <strong>microsoft</strong>: they use SSO
            <br><br>
            For LDAP users in Closed Mode, their directory username must match what they type
            at the login screen. The system fills in their LDAP Distinguished Name automatically
            on first login.
        </div>
    </div>
</div>

<!-- ── NOTIFICATIONS ─────────────────────────────────────────── -->
<div class="doc-section" id="notifications">
    <h2 class="doc-section-title">Notifications</h2>
    <p>
        Found at <strong>Admin → Setup → Notifications</strong>. Configures email alerts
        sent automatically when visitors check in. Requires a working SMTP connection.
    </p>

    <div class="doc-item">
        <div class="doc-item-label">SMTP Host</div>
        <div class="doc-item-desc">
            The hostname or IP address of your outgoing mail server.
            <br><br>
            <strong>Examples:</strong>
            <br>Gmail / Google Workspace: <code>smtp.gmail.com</code>
            <br>Microsoft 365: <code>smtp.office365.com</code>
            <br>On-premise Exchange: <code>mail.yourdomain.com</code> or an IP address
            <br>Local relay (no auth): <code>127.0.0.1</code> or <code>localhost</code>
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">SMTP Port</div>
        <div class="doc-item-desc">
            The TCP port your mail server accepts connections on.
            <br><br>
            <strong>465</strong> — SMTPS (SSL, encrypted from the start). Recommended when available.
            <br>
            <strong>587</strong> — Submission port with STARTTLS (starts unencrypted, upgrades to TLS).
            Used by most modern mail providers including Gmail and Microsoft 365.
            <br>
            <strong>25</strong> — Standard SMTP. Rarely used for authenticated sending; may be blocked
            by your ISP. Used for unauthenticated server-to-server relays.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Encryption</div>
        <div class="doc-item-desc">
            <strong>SSL</strong> — Use with port 465. Full encryption from connection start.
            <br>
            <strong>TLS</strong> — Use with port 587. Upgrades connection to encrypted after initial handshake.
            <br>
            <strong>None</strong> — Unencrypted. Only for internal relays on a trusted network.
            Never send email containing personal visitor data over an unencrypted connection.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">SMTP Username &amp; Password</div>
        <div class="doc-item-desc">
            Credentials for authenticating with the mail server.
            <br><br>
            For Gmail: use your full Google account email and an <strong>App Password</strong>
            (not your regular Gmail password — Google requires App Passwords when 2FA is enabled).
            Generate one at <em>Google Account → Security → App Passwords</em>.
            <br><br>
            For Microsoft 365: use the full email address and the account password,
            or configure an App Password if multi-factor authentication is enabled.
            <br><br>
            For on-premise Exchange or a relay that does not require authentication,
            leave these fields blank.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">From Name &amp; From Email</div>
        <div class="doc-item-desc">
            The sender name and address that appear in the recipient's email client.
            <br><br>
            <strong>From Name:</strong> What recipients see as the sender, e.g. <em>South Toms River Elementary</em>
            or <em>Visitor Check-In</em>.
            <br>
            <strong>From Email:</strong> The email address the message appears to come from.
            Must be a valid address that your mail server is authorized to send from.
            Using an address not authorized by your mail server (or not matching SPF/DKIM records)
            may cause messages to be marked as spam or rejected.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Notification Rules</div>
        <div class="doc-item-desc">
            Each notification rule sends an email when a visitor checks in. You can create
            multiple rules — for example, one that emails the front office for all check-ins
            and another that emails the principal only when the visit reason is "Administrative."
            <br><br>
            <strong>Recipient Email:</strong> Where the notification is sent.
            Can be an individual address or a distribution list.
            <br><br>
            <strong>Trigger:</strong> Which check-in events trigger this rule — all check-ins,
            or only check-ins matching a specific visit reason.
            <br><br>
            <strong>Email Subject &amp; Body:</strong> Customize the message.
            Placeholders like <code>{visitor_name}</code>, <code>{host}</code>,
            <code>{reason}</code>, and <code>{time}</code> are substituted with actual visit data.
        </div>
    </div>
</div>

<!-- ── LIVE LOGS & BULK CHECKOUT ─────────────────────────────── -->
<div class="doc-section" id="live">
    <h2 class="doc-section-title">Live Logs &amp; Bulk Checkout</h2>
    <p>
        Found at <strong>Admin → Live Logs</strong>. Shows all currently checked-in visitors
        in real time with elapsed time indicators and a bulk checkout tool for administrators.
    </p>

    <div class="doc-item">
        <div class="doc-item-label">Elapsed time color coding</div>
        <div class="doc-item-desc">
            Each visitor row displays a progress bar that shows relative time compared
            to the longest current visit:
            <br><br>
            <strong style="color:#16a34a;">Green</strong> — checked in less than 1 hour ago.
            <br>
            <strong style="color:#ca8a04;">Yellow</strong> — checked in 1–2 hours ago.
            <br>
            <strong style="color:#dc2626;">Red</strong> — checked in more than 2 hours ago.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Stale visitor threshold</div>
        <div class="doc-item-desc">
            The dropdown at the top of the page sets how old a visit must be to be considered "flagged."
            Options: 4, 8, 12, 24, or 48 hours. The default is set from your
            <strong>Max Open Hours</strong> auto-checkout setting.
            <br><br>
            Flagged rows are highlighted with a red border. The flagged count appears
            next to the dropdown.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Select All Flagged</div>
        <div class="doc-item-desc">
            Checks the checkbox on every visitor row that exceeds the stale threshold.
            Use this to quickly select all visitors who have been inside longer than expected.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Check Out Selected</div>
        <div class="doc-item-desc">
            Immediately closes all checked visits. The system sets their check-out time to now
            and marks their status as <em>Auto-Completed</em>. A confirmation prompt appears
            before the action is submitted — this cannot be undone from the UI.
            <br><br>
            This is intended for administrative situations such as:
            after a fire drill when all visitors leave but no one checks them out,
            end of day when the front office is closing and stale records remain,
            or after a data migration when old records were imported as checked-in.
        </div>
    </div>

    <div class="doc-item">
        <div class="doc-item-label">Auto-refresh</div>
        <div class="doc-item-desc">
            The Live Logs page automatically reloads every 60 seconds to show new check-ins.
            The countdown is shown in the top-right corner. Reloading clears any checkbox
            selections, so complete your bulk checkout before the page reloads.
        </div>
    </div>
</div>

</div><!-- /.doc-body -->
