<div class="card">
    <div class="card-title">
        Step 6 of 7 — Notifications
        <span class="optional-tag">Optional</span>
    </div>

    <div class="feature-callout">
        <strong>New feature: Host notifications</strong>
        CheckIn can automatically notify a host (counselor or staff member) when a visitor
        checks in to see them. Notifications are sent by email. You can configure the
        SMTP server and per-host email addresses in the admin panel after setup.
    </div>

    <p style="color:var(--text-muted);margin-bottom:20px;font-size:0.9rem;">
        Enable notifications now, then complete the SMTP configuration in
        <strong>Admin &rarr; Settings</strong> after the upgrade is complete.
    </p>

    <form method="POST" action="/install/guided-upgrade/notifications/save">
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px;">
            <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer;
                          padding:14px;border:1px solid var(--border);border-radius:8px;">
                <input type="checkbox" name="notify_hosts" value="1"
                       <?= !empty($_POST['notify_hosts']) ? 'checked' : '' ?>
                       style="margin-top:2px;flex-shrink:0;">
                <div>
                    <strong>Email hosts when visitors check in</strong>
                    <div style="font-size:0.82rem;color:var(--text-muted);margin-top:2px;">
                        When a visitor selects a host and checks in, that host receives an
                        email with the visitor's name, reason, and check-in time.
                    </div>
                </div>
            </label>
        </div>

        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:6px;
                    padding:12px 14px;font-size:0.82rem;color:var(--text-muted);margin-bottom:20px;">
            SMTP configuration is set at <strong>Admin &rarr; Settings &rarr; Notifications</strong>.
            Each host's email address is set on their profile in <strong>Admin &rarr; Hosts</strong>.
        </div>

        <div class="step-actions">
            <button type="submit" class="button">Save &amp; Continue &rarr;</button>
            <button type="submit" name="configure_later" value="1" class="btn-later">Configure Later</button>
        </div>
    </form>
</div>
