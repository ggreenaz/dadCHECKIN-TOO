<?php $docSub = 'Your command center — everything at a glance the moment you log in.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>The Dashboard is the first page you see after signing in. It gives you a real-time picture of your building right now, plus a 7-day trend and quick links to every other part of the system. You should never need to go anywhere else just to answer "who is inside?"</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you see on this page</h2>
        <div class="doc-item">
            <div class="doc-item-label">Hero bar</div>
            <div class="doc-item-desc">Shows a live clock, time-of-day greeting, and a real-time sentence telling you exactly how many visitors are inside right now. If any have been here 2+ hours, it flags them in red immediately.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">6 stat tiles</div>
            <div class="doc-item-desc"><strong>Inside Now</strong> — active check-ins. <strong>Today's Total</strong> — everyone who has checked in today. <strong>Completed</strong> — properly checked out. <strong>Extended</strong> — inside 2 or more hours. <strong>No Shows</strong> — marked no-show today. <strong>All-Time</strong> — total visit records ever.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Currently Inside</div>
            <div class="doc-item-desc">Live list of every visitor currently in the building. Each row shows their name (click for full profile), who they are visiting, their reason, a live elapsed timer, and a color bar — green under 1 hour, yellow 1–2 hours, red over 2 hours. Updates every second without reloading.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Checked Out Today</div>
            <div class="doc-item-desc">The last 6 visitors who completed their visit today, with duration shown.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">7-Day Sparkline</div>
            <div class="doc-item-desc">A small bar chart showing visit volume for the past 7 days. Today's bar is highlighted. Hover any bar for the exact count. Below the chart you'll see the top visit reason for today.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Find a Visitor</div>
            <div class="doc-item-desc">A quick search box. Type a name, phone number, or email and it takes you directly to filtered Visit History results.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Module tiles</div>
            <div class="doc-item-desc">8 tiles linking to every section of the admin area — Log Hub, Live Logs, History, Analytics, Hosts, Reasons, Kiosk Setup, and Settings. Each tile shows a live count where relevant.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>At the start of the day — glance at the hero bar and stat tiles to confirm your system is running and the building count is 0.</li>
            <li>During the day — watch the Currently Inside list. Any bar turning yellow or red means that visitor has been here a while. Click their name to view their full visit history.</li>
            <li>End of day — if the Inside Now count is not 0 but the building is empty, someone forgot to check out. Use auto-checkout in Settings to handle this automatically.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration</h2>
        <p>The Dashboard itself has no configuration options — it automatically pulls live data. However, what it displays depends on things you configure elsewhere:</p>
        <ul class="doc-ul">
            <li><strong>Auto-checkout</strong> — if visitors are staying open overnight, configure this in <a href="/admin/docs/settings">Settings</a>.</li>
            <li><strong>Hosts and Reasons</strong> — the visit detail shown in the Currently Inside list comes from <a href="/admin/docs/hosts">Hosts</a> and <a href="/admin/docs/reasons">Visit Reasons</a>.</li>
            <li><strong>Timezone</strong> — all times on the dashboard reflect the timezone set in <a href="/admin/docs/settings">Settings</a>.</li>
        </ul>
    </div>

</div>
