<?php $docSub = 'Peak hours, visit patterns by day, busiest hosts, and visitor return rates — last 30 days.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>Analytics turns 30 days of visit data into visual patterns. Instead of reading rows of data, you can see exactly when your building gets busy, which hosts receive the most visitors, what brings people in, and how many visitors come back more than once.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you see on this page</h2>
        <div class="doc-item">
            <div class="doc-item-label">Peak Hours Heatmap</div>
            <div class="doc-item-desc">A grid of Monday–Friday (columns) by hour 7 AM–5 PM (rows). Each cell is color-coded from light (few visits) to dark (many visits). The more intense the color, the busier that time slot is. Hover any cell to see the exact count. This answers "when does our front desk need the most coverage?"</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Visits by Day of Week</div>
            <div class="doc-item-desc">Horizontal bar chart showing total visits per day of the week over the past 30 days. Weekend bars are shown at reduced opacity. Tells you which days are consistently your busiest.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Visits by Reason</div>
            <div class="doc-item-desc">Horizontal bars — one per visit reason — showing volume and percentage share of total visits. Tells you why people are coming to your building.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Busiest Hosts</div>
            <div class="doc-item-desc">Ranked list of hosts by visit count over 30 days. Each row shows their rank, name, department, visit count, and average visit duration. Click a host name to see all their visits in History.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Visitor Return Rate</div>
            <div class="doc-item-desc">An SVG donut ring showing what percentage of unique visitors in the past 30 days have visited more than once. Also shows total repeat visitors, first-timers, and the most visits by a single person.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Most Frequent Visitors</div>
            <div class="doc-item-desc">Top 10 visitors by visit count over 30 days, with their visit count, average duration, and a link to their full profile.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>Use the heatmap for staffing decisions — if Tuesday at 10 AM is always dark, you need coverage then.</li>
            <li>Use Visits by Reason to understand why people visit — if one reason dominates, it may deserve a dedicated process.</li>
            <li>Use Busiest Hosts to recognize which staff receive the most visitors and plan accordingly.</li>
            <li>A high return rate is generally positive — it means people trust your process and come back.</li>
            <li>Click any host or visitor name to drill into their specific records in Visit History or Visitor Profile.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration</h2>
        <p>Analytics covers the past 30 days automatically — no date selection needed. What appears in the charts depends on:</p>
        <ul class="doc-ul">
            <li><strong>Visit Reasons</strong> — the reason breakdown only works well if reasons are meaningful. Configure them in <a href="/admin/docs/reasons">Visit Reasons</a>.</li>
            <li><strong>Hosts with departments</strong> — the Busiest Hosts section shows department names when hosts have one assigned. Set departments during <a href="/admin/setup/hosts">host setup</a>.</li>
            <li><strong>Data volume</strong> — analytics becomes more meaningful after a few weeks of real visit data.</li>
        </ul>
    </div>

</div>
