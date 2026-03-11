<?php
$docMeta = $docMeta ?? ['title' => 'System Setup', 'back' => '/admin/setup', 'icon' => '🛠️'];
include __DIR__ . '/_header.php';
?>

<div class="doc-section">
    <div class="doc-section-title">What is System Setup?</div>
    <div class="doc-section-body">
        <p>The System Setup page is a step-by-step configuration timeline that walks you through every aspect of getting dadCHECKIN-TOO ready for use. Each stage has a completion indicator so you can see at a glance what has been configured and what still needs attention.</p>
        <p>You do not have to complete every stage — optional stages can be skipped and configured later. Required stages (Organization, Hosts, Visit Reasons) must be completed before the kiosk is fully functional.</p>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">Setup Stages</div>
    <div class="doc-section-body">
        <div class="doc-item">
            <div class="doc-item-label">Organization</div>
            <div class="doc-item-desc">Set your organization name and default timezone. The timezone affects all timestamps, reports, and auto-checkout times.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Departments</div>
            <div class="doc-item-desc">Optional. Create departments or divisions that hosts belong to. This makes it easier for visitors to find the right person when checking in.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Hosts</div>
            <div class="doc-item-desc">Required. Add the people that visitors come to see. Hosts can be assigned to departments, and their contact info is used for notifications.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Visit Reasons</div>
            <div class="doc-item-desc">Required. Define the reasons visitors check in (e.g., Meeting, Delivery, Interview). Visitors select one of these reasons at the kiosk.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Custom Fields</div>
            <div class="doc-item-desc">Optional. Add extra fields to the check-in form — for example a badge number, vehicle plate, or company name. Fields can be text, dropdown, or checkbox.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Kiosk Fields</div>
            <div class="doc-item-desc">Optional. Choose which standard fields (last name, phone, email, notes) appear on the visitor-facing kiosk form, and whether they are required.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Authentication <span style="font-size:0.78rem;color:var(--text-muted);">(Super Admin only)</span></div>
            <div class="doc-item-desc">Configure how staff sign in — Local accounts, LDAP/Active Directory, Google SSO, or Microsoft/Azure AD. See the Auth &amp; LDAP help page for full details.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Users</div>
            <div class="doc-item-desc">Add staff accounts that can log into the admin panel. Roles control what each person can see and do. For full user management, visit the <a href="/admin/users">System Users</a> page.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Notifications <span style="font-size:0.78rem;color:var(--text-muted);">(Super Admin only)</span></div>
            <div class="doc-item-desc">Set up rules to automatically alert hosts when their visitor arrives. Supports email, SMS, Slack, and webhooks.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Test Check-In</div>
            <div class="doc-item-desc">Run a test visitor check-in to verify the full flow works before going live. The test URL is shown so you can open it on a tablet or kiosk device.</div>
        </div>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">CSV Bulk Import</div>
    <div class="doc-section-body">
        <p>Most setup stages that involve lists (Hosts, Reasons, Custom Fields, Users) include a <strong>Download Template</strong> link. You can fill in the CSV template and upload it to bulk-import data instead of adding records one at a time.</p>
        <p>For more details on importing, see the <a href="/admin/docs/import">CSV Import help page</a>.</p>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">Coming Back to Setup</div>
    <div class="doc-section-body">
        <p>Setup is not a one-time wizard — you can return to any stage at any time from the <strong>System Setup</strong> link in your user dropdown menu (top right). Changes take effect immediately.</p>
        <p>Individual settings pages (Hosts, Reasons, Custom Fields) are also accessible from the main navigation at any time without going through the setup timeline.</p>
    </div>
</div>
