<?php $docSub = 'Define why people visit — the options visitors see on the check-in form.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>Visit Reasons are the options a visitor taps on the kiosk when explaining why they are here — "Meeting," "Drop-off," "Volunteer," "Job Interview," etc. Well-chosen reasons give your Analytics meaningful data and make it easy to spot patterns in who is visiting and why.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you see on this page</h2>
        <div class="doc-item">
            <div class="doc-item-label">Reasons list</div>
            <div class="doc-item-desc">All active visit reasons for your organization with their label and a delete button.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Add Reason form</div>
            <div class="doc-item-desc">A single field — the label — that is exactly what visitors see on the kiosk. Keep it short and clear.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>Type the reason label (e.g., "Parent Meeting") and click Add.</li>
            <li>Keep the list short — 5 to 10 reasons is ideal. Too many options slow down the check-in process.</li>
            <li>Use broad categories rather than very specific ones (e.g., "Drop-off" rather than "Lunch drop-off" and "Homework drop-off" separately).</li>
            <li>To remove a reason, click delete. Past visits with that reason keep their label in history — it is not erased.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration tips</h2>
        <div class="doc-item">
            <div class="doc-item-label">Label naming</div>
            <div class="doc-item-desc">Use title case (e.g., "Job Interview" not "job interview"). Labels appear exactly as typed on the kiosk and in all reports.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Analytics impact</div>
            <div class="doc-item-desc">The Visits by Reason chart in Analytics shows every reason as its own bar. If you have too many reasons, the chart becomes hard to read. Aim for clarity over specificity.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Making reasons optional</div>
            <div class="doc-item-desc">The reason field on the kiosk can be made optional or hidden entirely via <a href="/admin/docs/kiosk">Kiosk Setup</a>. If hidden, no reason data is collected.</div>
        </div>
    </div>

</div>
