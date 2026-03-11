<?php $docSub = 'Real-time view of every visitor currently inside with live elapsed-time progress bars.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>Live Logs shows every visitor currently checked in, updated in real time. The elapsed-time progress bars change color as time passes — making it immediately obvious at a glance who has been here a short time and who has been here a very long time.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you see on this page</h2>
        <div class="doc-item">
            <div class="doc-item-label">Visitor rows</div>
            <div class="doc-item-desc">One row per active visitor. Each row shows: their initials avatar, full name (click for profile), who they are visiting and why, their exact check-in time, a live elapsed timer, and a progress bar.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Progress bars</div>
            <div class="doc-item-desc">Bars scale relative to the longest current visitor — the person who has been here the longest fills 90% of the bar, and everyone else is proportional to them. Colors: <strong style="color:#16a34a">Green</strong> = under 1 hour, <strong style="color:#ca8a04">Yellow</strong> = 1–2 hours, <strong style="color:#dc2626">Red</strong> = over 2 hours.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Live elapsed timers</div>
            <div class="doc-item-desc">Each visitor's elapsed time updates every second automatically — no page refresh needed.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Auto-refresh</div>
            <div class="doc-item-desc">The page fully refreshes every 60 seconds to pick up any new check-ins or check-outs. A countdown shows how long until the next refresh.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Empty state</div>
            <div class="doc-item-desc">When nobody is inside, the page shows a clear message rather than a blank table.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>Keep this page open on a monitor at the front desk for continuous awareness of who is in the building.</li>
            <li>Red bars are your signal to investigate — that visitor has been here over 2 hours.</li>
            <li>Click any visitor's name to open their full Visitor Profile and see their complete history.</li>
            <li>Use the <strong>Live Demo</strong> button (on the Dashboard) to reset demo visit times and see the bars in action with fresh data.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration</h2>
        <p>The color thresholds (1 hour = yellow, 2 hours = red) are built into the system. The only external configuration that affects this page:</p>
        <ul class="doc-ul">
            <li><strong>Auto-checkout</strong> — configure in <a href="/admin/docs/settings">Settings</a> to automatically close visits that stay open too long, which removes them from this page.</li>
            <li><strong>Location filtering</strong> — if your organization has multiple locations, the live view can be filtered by location (contact your administrator to configure locations).</li>
        </ul>
    </div>

</div>
