<?php $docSub = 'A complete profile for any individual visitor — their full history, patterns, and stats in three views.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>The Visitor Profile is a dedicated page for a single individual. It shows everything dadCHECKIN-TOO knows about that person — every visit they have ever made, their patterns, their usual host, and how long their visits typically last. You get here by clicking any visitor name anywhere in the system.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you see on this page</h2>
        <div class="doc-item">
            <div class="doc-item-label">Hero banner</div>
            <div class="doc-item-desc">Dark header with the visitor's initials avatar, full name, phone number, and email. If they are currently inside the building right now, a green "Currently Inside" badge is shown.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Stat strip</div>
            <div class="doc-item-desc">Six at-a-glance stats: <strong>Total Visits</strong>, <strong>Avg Duration</strong>, <strong>Busiest Day</strong> of the week, <strong>Last Visit</strong> date, <strong>Usual Host</strong>, and <strong>First Seen</strong> date.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Day-of-week chart</div>
            <div class="doc-item-desc">Horizontal bars showing how many times this person has visited on each day of the week. Tells you when they tend to come in.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Time of Day panel</div>
            <div class="doc-item-desc">Visits broken into Morning (before 10), Midday (10–1), Afternoon (1–4), and Evening (after 4). Shows their preferred time to visit.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Duration breakdown</div>
            <div class="doc-item-desc">Visits grouped into Short (under 30 min), Medium (30–60 min), Long (1–2 hours), and Extended (over 2 hours). Helps you understand their typical visit length.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">View toggle — Timeline</div>
            <div class="doc-item-desc">All visits listed chronologically with a vertical spine and dots. Each entry shows date, time, host, reason, and duration.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">View toggle — Cards</div>
            <div class="doc-item-desc">A 2-column grid of visit cards, each color-coded by duration with a duration bar.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">View toggle — Duration</div>
            <div class="doc-item-desc">Visits sorted from longest to shortest, with a proportional bar for each. Useful for spotting outlier visits.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>Get here by clicking any visitor name in Dashboard, Live Logs, Log Hub, Visit History, or Analytics.</li>
            <li>Use the stat strip for a quick summary — most questions about a visitor can be answered in 5 seconds from those 6 numbers.</li>
            <li>Switch between Timeline, Cards, and Duration views using the toggle. Your preference is saved for next time.</li>
            <li>The pattern panels (day, time, duration) are especially useful if a visitor is flagged as a concern — you can see their entire behavioral pattern at a glance.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration</h2>
        <p>The Visitor Profile page has no configuration. All data is pulled automatically from visit records. Visitor information (name, phone, email) is captured at check-in through the <a href="/admin/docs/kiosk">Kiosk</a>.</p>
    </div>

</div>
