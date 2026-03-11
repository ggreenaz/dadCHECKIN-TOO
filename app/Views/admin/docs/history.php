<?php $docSub = 'Browse, filter, search, and view every visit ever recorded — in table, card, or timeline format.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>Visit History is your complete audit log of every visit. You can filter by date range, host, or status, search by visitor name or phone number, and view the results in three different visual formats. It also shows a day-by-day bar chart of visit volume across your filtered date range.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you see on this page</h2>
        <div class="doc-item">
            <div class="doc-item-label">Summary strip</div>
            <div class="doc-item-desc">Five tiles at the top showing totals computed from your current filter: Total Visits, Completed, No Shows, Average Duration, and Days Shown.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Day-by-day bar chart</div>
            <div class="doc-item-desc">A visual bar chart with one bar per day across your filtered range. Bar height shows visit volume. Bar color reflects the peak arrival hour that day: blue = morning, teal = midday, amber = early afternoon, orange = late afternoon. Hover a bar to see the count and peak time.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Filter panel</div>
            <div class="doc-item-desc">Filter by: <strong>Search</strong> (name, phone, or email), <strong>From / To</strong> dates, <strong>Host</strong>, and <strong>Status</strong>. Combine any filters together. Click Reset to clear all filters.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">View toggle — Table</div>
            <div class="doc-item-desc">The classic data table with all visit details. Each row includes a mini duration bar showing how long the visit lasted (colored green/yellow/red by duration). Visitor names link to their full profile.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">View toggle — Cards</div>
            <div class="doc-item-desc">A 2-column card grid. Each card shows the visitor's name, date and time, host, reason, location, and a duration bar. Card border color matches visit duration severity.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">View toggle — Timeline</div>
            <div class="doc-item-desc">A chronological list grouped by day with a vertical spine and colored dots per visit. Useful for reviewing a day's activity in sequence.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>To find a specific person — type their name or phone in the Search box and click Filter.</li>
            <li>To review a date range — set From and To dates, then choose a host if you want to narrow by staff member.</li>
            <li>To audit a specific visit type — use the Status filter (Completed, No Show, etc.).</li>
            <li>Switch between Table, Cards, and Timeline views using the toggle above the results. Your preference is remembered for next time.</li>
            <li>Click any visitor name to open their full Visitor Profile with their complete history across all visits.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration</h2>
        <p>Visit History has no configuration of its own. The data it shows is determined by:</p>
        <ul class="doc-ul">
            <li><strong>Hosts</strong> — the host names shown in filter and results are managed in <a href="/admin/docs/hosts">Manage Hosts</a>.</li>
            <li><strong>Visit Reasons</strong> — the reason labels come from <a href="/admin/docs/reasons">Visit Reasons</a>.</li>
            <li><strong>Result limit</strong> — by default shows the most recent 200 records. Apply date filters to narrow results if you have a large dataset.</li>
        </ul>
    </div>

</div>
