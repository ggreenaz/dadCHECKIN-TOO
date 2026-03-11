<?php use App\Core\View; ?>

<div class="setup-stage-header">
    <a href="/admin/setup" class="back-link">&larr; Back to Setup</a>
    <h2>Custom Fields</h2>
    <p>Add extra fields to the visitor check-in form — company name, badge number, vehicle plate, anything you need to capture.</p>
</div>

<div class="setup-stage-grid">

    <div class="card">
        <div class="card-title">Add a Field</div>
        <form method="POST" action="/admin/setup/fields/save">
            <div class="form-group" style="margin-bottom:12px;">
                <label for="label">Field Label <span style="color:var(--danger)">*</span></label>
                <input type="text" name="label" id="label" placeholder="e.g. Company Name, Badge Number" required>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="field_type">Field Type</label>
                <select name="field_type" id="field_type" onchange="toggleOptions(this.value)">
                    <option value="text">Text (single line)</option>
                    <option value="textarea">Text area (multi-line)</option>
                    <option value="select">Dropdown</option>
                    <option value="checkbox">Checkbox (yes/no)</option>
                    <option value="date">Date</option>
                </select>
            </div>
            <div class="form-group" id="options-group" style="display:none;margin-bottom:12px;">
                <label for="options">Dropdown Options <small style="font-weight:400;color:var(--text-muted)">(one per line)</small></label>
                <textarea name="options" id="options" rows="4" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label>
                    <input type="checkbox" name="required" value="1"> Required field
                </label>
            </div>
            <button type="submit" class="button">Add Field</button>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Bulk Import via CSV</div>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:16px;">
            Define multiple fields at once using a CSV file.
        </p>
        <a href="/admin/setup/template/fields" class="button button-outline" style="margin-bottom:20px;">
            &#8681; Download CSV Template
        </a>
        <form method="POST" action="/admin/setup/fields/upload" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom:12px;">
                <label>Upload Completed CSV</label>
                <input type="file" name="csv_file" accept=".csv,text/csv" required>
            </div>
            <button type="submit" class="button">Import CSV</button>
        </form>
        <div class="csv-format-hint">
            <strong>Columns:</strong> label, field_type, required<br>
            Types: text, textarea, select, checkbox, date<br>
            Required: yes or no
        </div>
    </div>

</div>

<div class="card">
    <div class="card-title">Configured Fields (<?= count($fields) ?>)</div>
    <?php if (empty($fields)): ?>
        <p class="text-muted">No custom fields added yet. This step is optional.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Label</th><th>Type</th><th>Required</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($fields as $f): ?>
                    <tr>
                        <td><?= View::e($f['label']) ?></td>
                        <td><?= View::e($f['field_type']) ?></td>
                        <td><?= $f['required'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <form method="POST" action="/admin/setup/fields/<?= (int)$f['field_id'] ?>/delete"
                                  onsubmit="return confirm('Remove this field?')">
                                <button type="submit" class="delete-button">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="setup-stage-nav">
    <a href="/admin/setup/reasons" class="button button-outline">&larr; Visit Reasons</a>
    <a href="/admin/setup/notifications" class="button">Next: Notifications &rarr;</a>
</div>

<script>
function toggleOptions(type) {
    document.getElementById('options-group').style.display = type === 'select' ? 'block' : 'none';
}
</script>
