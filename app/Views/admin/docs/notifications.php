<?php
$docMeta = $docMeta ?? ['title' => 'Notifications', 'back' => '/admin/setup/notifications', 'icon' => '🔔'];
include __DIR__ . '/_header.php';
?>

<div class="doc-section">
    <div class="doc-section-title">What Are Notifications?</div>
    <div class="doc-section-body">
        <p>Notification rules tell dadCHECKIN-TOO to automatically alert someone whenever a visitor checks in or checks out. For example, when a visitor arrives to see a specific host, that host can receive an immediate email so they know to come to the front desk.</p>
        <p>This page is <strong>Super Admin only</strong> because notification delivery requires SMTP or external service credentials that affect the entire organization.</p>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">How a Notification Rule Works</div>
    <div class="doc-section-body">
        <div class="doc-item">
            <div class="doc-item-label">Trigger</div>
            <div class="doc-item-desc">When the rule fires — either <strong>When visitor checks in</strong> or <strong>When visitor checks out</strong>.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Channel</div>
            <div class="doc-item-desc">How the notification is delivered:
                <ul style="margin:6px 0 0 16px;font-size:0.875rem;">
                    <li><strong>Email</strong> — sends an HTML email via your configured SMTP server</li>
                    <li><strong>SMS</strong> — sends a text message (requires an SMS gateway)</li>
                    <li><strong>Slack webhook</strong> — posts a message to a Slack channel</li>
                    <li><strong>Webhook (POST)</strong> — sends a JSON payload to any URL you specify</li>
                </ul>
            </div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Recipient</div>
            <div class="doc-item-desc">Who receives the notification:
                <ul style="margin:6px 0 0 16px;font-size:0.875rem;">
                    <li><strong>Fixed address / URL</strong> — always sends to the same email, phone number, or webhook URL you specify</li>
                    <li><strong>The host being visited</strong> — dynamically looks up the host's contact info from the visit record</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">Adding a Notification Rule</div>
    <div class="doc-section-body">
        <p>Use the <strong>Add Notification Rule</strong> form at the top of the page:</p>
        <ol style="margin:8px 0 0 20px;font-size:0.875rem;line-height:1.8;">
            <li>Select the <strong>Trigger</strong> (check in or check out).</li>
            <li>Select the <strong>Channel</strong> (Email, SMS, Slack, or Webhook).</li>
            <li>Select the <strong>Recipient</strong> type.</li>
            <li>If using a fixed address, enter the email, phone number, or URL in the <strong>Address / URL</strong> field. Leave blank if using "The host being visited."</li>
            <li>Click <strong>Add Rule</strong>.</li>
        </ol>
        <p style="margin-top:12px;">Rules take effect immediately. You can add multiple rules — for example, one email to the host and one Slack message to a front-desk channel on every check-in.</p>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">Managing Existing Rules</div>
    <div class="doc-section-body">
        <p>The <strong>Notification Rules</strong> table lists all configured rules. Each row shows the trigger, channel, and recipient. Use the <strong>Delete</strong> button to remove a rule you no longer need.</p>
        <p>Rules are evaluated in the order they appear. All matching rules fire when a visit event occurs — there is no "stop after first match" behavior.</p>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">Email Notifications</div>
    <div class="doc-section-body">
        <div class="doc-item">
            <div class="doc-item-label">SMTP Required</div>
            <div class="doc-item-desc">Email notifications require a working SMTP configuration for your organization. SMTP credentials are set by the Super Admin in the system configuration. Without SMTP, email rules are saved but messages will not be delivered.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Message Content</div>
            <div class="doc-item-desc">Emails are sent as formatted HTML and include the visitor's name, check-in time, host name, and visit reason. The format is clean and minimal.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">"The host being visited" recipient</div>
            <div class="doc-item-desc">When this option is selected, dadCHECKIN-TOO uses the email address on file for the host assigned to the visit. If the host has no email address, the notification is skipped. Ensure hosts have email addresses set on the <a href="/admin/hosts">Hosts</a> page.</div>
        </div>
    </div>
</div>

<div class="doc-section">
    <div class="doc-section-title">Slack &amp; Webhook Notifications</div>
    <div class="doc-section-body">
        <div class="doc-item">
            <div class="doc-item-label">Slack Incoming Webhook</div>
            <div class="doc-item-desc">Create an Incoming Webhook in your Slack workspace (Slack App settings &rarr; Incoming Webhooks) and paste the webhook URL as the recipient. Messages will appear in the channel you designate when setting up the webhook.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Generic Webhook</div>
            <div class="doc-item-desc">dadCHECKIN-TOO sends a JSON POST request to the URL you specify. The payload includes the visit event type, visitor details, host, reason, and timestamp. Use this to integrate with any system that accepts webhooks (Zapier, Make, custom APIs, etc.).</div>
        </div>
    </div>
</div>
