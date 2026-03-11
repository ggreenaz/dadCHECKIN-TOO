<?php $docSub = 'Configure your organization details, timezone, auto-checkout, kiosk fields, and authentication.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>Settings is where you configure the core behavior of your dadCHECKIN-TOO installation. Changes here affect the entire system — all users, all visits, and all reports. Only Organization Administrators can access this page.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Organization Settings</h2>
        <div class="doc-item">
            <div class="doc-item-label">Organization Name</div>
            <div class="doc-item-desc">The name that appears in the header of every page and on the kiosk check-in screen. Set this to your school, office, or facility name.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Timezone</div>
            <div class="doc-item-desc">All visit timestamps are stored in UTC and displayed in this timezone. <strong>This is critical to set correctly.</strong> If your timezone is wrong, all check-in and check-out times will be off, and the end-of-day auto-checkout will fire at the wrong time. Set this before going live.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Auto-Checkout</h2>
        <p>Auto-checkout automatically closes visits that are still open at the end of the day or that have been open too long. This prevents stale "ghost" visitors from inflating your Inside Now count overnight.</p>
        <div class="doc-item">
            <div class="doc-item-label">Enable auto-checkout</div>
            <div class="doc-item-desc">Toggle this on to activate the feature. It does nothing until the cron job is also set up on the server. Off by default.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">End-of-day checkout time</div>
            <div class="doc-item-desc">The time of day (in your local timezone) when any still-open visits are automatically closed. For a school, this is typically 5:00 PM or 6:00 PM. For an office building, perhaps 7:00 PM.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Max open hours</div>
            <div class="doc-item-desc">A safety net — any visit open longer than this many hours is closed regardless of time of day. Prevents a visit from staying open for days if the end-of-day cron was missed. Recommended: 10–12 hours.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Mark closed visits as</div>
            <div class="doc-item-desc"><strong>Auto-Completed</strong> is recommended — it lets you distinguish in reports between visits that were properly checked out by staff vs. ones the system closed automatically. If you choose <strong>Completed</strong>, auto-closed visits are indistinguishable from manual checkouts.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Cron job setup</div>
            <div class="doc-item-desc">Auto-checkout only runs when triggered by a server cron job. Add this to your server's crontab to run every 15 minutes:<br><br>
            <code>*/15 * * * * php /var/www/checkin/scripts/auto_checkout.php &gt;&gt; /var/log/checkin-auto-checkout.log 2&gt;&amp;1</code><br><br>
            The script logs every visit it closes to that log file for auditing.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Kiosk Fields</h2>
        <p>Controls which standard fields appear on the visitor check-in form. Click <strong>Manage Kiosk Fields</strong> to go to the Kiosk Setup page. See <a href="/admin/docs/kiosk">Kiosk Setup documentation</a> for full details.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Authentication</h2>
        <p>Configure how administrators log in to dadCHECKIN-TOO. Click <strong>Manage Authentication Settings</strong> to configure login providers:</p>
        <div class="doc-item">
            <div class="doc-item-label">Local accounts</div>
            <div class="doc-item-desc">Username and password stored in dadCHECKIN-TOO's own database. Simple to set up, no external dependencies.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">LDAP / Active Directory</div>
            <div class="doc-item-desc">Staff sign in with their existing network credentials. Requires your AD server address, bind credentials, and search base. Contact your network administrator for these details.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Google SSO / Microsoft Azure</div>
            <div class="doc-item-desc">Single sign-on via Google or Microsoft accounts. Requires creating an OAuth application in Google Cloud Console or Azure Active Directory and entering the client ID and secret here.</div>
        </div>
    </div>

</div>
