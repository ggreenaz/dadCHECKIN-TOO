<div class="card">
    <div class="card-title">
        Step 3 of 7 — Departments
        <span class="optional-tag">Optional</span>
    </div>

    <div class="feature-callout">
        <strong>New feature: Departments</strong>
        Departments let you group your hosts (counselors, staff) so visitors can narrow
        down who they're seeing. They also power department-level reporting.
        The old dadtoo system did not have departments — this is a brand new capability.
    </div>

    <?php if (!empty($existingDepartments)): ?>
        <div style="background:var(--success-bg,#f0fdf4);border:1px solid var(--success-border,#bbf7d0);
                    border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:0.875rem;">
            <strong style="color:#166534;">✓ <?= count($existingDepartments) ?> department(s) already configured:</strong>
            <ul style="margin:6px 0 0 16px;color:#166534;">
                <?php foreach ($existingDepartments as $d): ?>
                    <li><?= htmlspecialchars($d) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:6px;
                    padding:12px 16px;margin-bottom:20px;font-size:0.875rem;color:var(--text-muted);">
            <strong style="color:var(--text);">No departments found.</strong>
            Your migrated data does not include any departments — this is expected,
            since departments are a new feature. You can add them now or any time
            from <strong>Admin → Hosts → Departments</strong>.
        </div>
    <?php endif; ?>

    <!-- Toggle to add departments now -->
    <div style="margin-bottom:20px;">
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:600;">
            <input type="checkbox" id="add-dept-toggle"
                   onchange="document.getElementById('add-dept-form').style.display = this.checked ? 'block' : 'none'">
            Add departments now
        </label>
        <div style="font-size:0.82rem;color:var(--text-muted);margin-top:4px;margin-left:28px;">
            Enter names below — you can always add more later.
        </div>
    </div>

    <form method="POST" action="/install/guided-upgrade/departments/save">
        <div id="add-dept-form" style="display:none;margin-bottom:20px;">
            <label for="departments" style="font-weight:600;display:block;margin-bottom:6px;">Department Names</label>
            <textarea name="departments" id="departments" rows="5"
                      placeholder="Counseling&#10;Administration&#10;Special Education&#10;Main Office"
                      style="width:100%;resize:vertical;"><?= htmlspecialchars($_POST['departments'] ?? '') ?></textarea>
            <small style="color:var(--text-muted);font-size:0.8rem;">One department per line.</small>
        </div>

        <div class="step-actions">
            <button type="submit" class="button">Save &amp; Continue &rarr;</button>
            <button type="submit" name="configure_later" value="1" class="btn-later">Configure Later</button>
        </div>
    </form>
</div>
