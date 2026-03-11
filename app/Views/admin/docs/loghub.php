<?php $docSub = 'Central command for everything log-related — live, history, profiles, and analytics in one place.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>The Log Hub is the launching point for every log-related view in dadCHECKIN-TOO. Instead of navigating through menus, you can see today's key numbers at a glance and jump directly to the tool you need. Think of it as the front door to all your visitor data.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you see on this page</h2>
        <div class="doc-item">
            <div class="doc-item-label">Live indicator</div>
            <div class="doc-item-desc">A pulsing green dot in the header confirms the system is live and the data you see is current.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">5 stat cards</div>
            <div class="doc-item-desc"><strong>Inside Now</strong> — active visitors. <strong>Today's Total</strong> — all check-ins today. <strong>Extended (2h+)</strong> — visitors who have been inside over 2 hours. <strong>Completed</strong> — properly checked out. <strong>No Shows</strong> — marked no-show today.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Quick visitor search</div>
            <div class="doc-item-desc">Search by visitor name or phone number. Results open in Visit History with your search pre-applied.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Action tiles</div>
            <div class="doc-item-desc">Four large tiles — Live Logs, Visit History, Visitor Profiles, and Analytics — each showing a live count and a one-line description. Click any tile to go directly to that section.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Currently Inside feed</div>
            <div class="doc-item-desc">A live list of visitors in the building right now, with mini progress bars showing elapsed time. Click any name to open their Visitor Profile.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Checked Out Today feed</div>
            <div class="doc-item-desc">The most recent completed visits from today, with the duration of each visit displayed.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>Use the Log Hub as your starting point for any question about visitor data.</li>
            <li>If you need to find a specific person quickly, use the search bar — it takes you straight to their records.</li>
            <li>Use the action tiles to navigate: Live Logs for right now, History for the past, Analytics for patterns.</li>
            <li>Click any visitor name in either feed to open their full Visitor Profile.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration</h2>
        <p>The Log Hub has no configuration of its own — all data it displays comes from live visits in the system. To change what appears here, configure the underlying data:</p>
        <ul class="doc-ul">
            <li>Visitor and visit data is created at check-in via the <a href="/admin/docs/kiosk">Kiosk</a>.</li>
            <li>The 2h+ Extended threshold is fixed at 2 hours system-wide.</li>
        </ul>
    </div>

</div>
