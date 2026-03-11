<div class="card">
    <div class="card-title">Step 5 — Hosts &amp; Visit Reasons</div>
    <p style="margin-bottom: 20px; color: var(--text-muted);">
        Add the people visitors will come to see and common reasons for visiting.
        You can skip this and add them later from the admin panel.
    </p>
    <form method="POST" action="/install/5/save">
        <div class="form-group form-group-full" style="margin-bottom: 24px;">
            <label>Hosts <small style="color:var(--text-muted);font-weight:400;">(people visitors come to see)</small></label>
            <?php for ($i = 0; $i < 5; $i++): ?>
                <input type="text" name="hosts[]"
                       placeholder="e.g. Front Desk, HR Department, Dr. Smith"
                       style="margin-bottom: 8px;">
            <?php endfor; ?>
        </div>
        <div class="form-group form-group-full" style="margin-bottom: 24px;">
            <label>Visit Reasons</label>
            <?php
            $defaults = ['Appointment', 'Delivery', 'Interview', 'General Inquiry', ''];
            for ($i = 0; $i < 5; $i++):
            ?>
                <input type="text" name="reasons[]"
                       placeholder="e.g. Appointment, Delivery..."
                       value="<?= \App\Core\View::e($defaults[$i]) ?>"
                       style="margin-bottom: 8px;">
            <?php endfor; ?>
        </div>
        <div class="form-actions">
            <button type="submit" class="button">Save &amp; Finish</button>
            <button type="submit" class="button button-outline"
                    formaction="/install/5/save"
                    onclick="document.querySelectorAll('input[name]').forEach(e => e.value = '')">
                Skip
            </button>
        </div>
    </form>
</div>
