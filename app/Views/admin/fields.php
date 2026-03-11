<?php use App\Core\View; ?>

<div class="card">
    <div class="card-title">Add Custom Field</div>
    <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:16px;">
        Custom fields appear on the visitor check-in form. Use them to collect any extra information you need.
    </p>
    <form method="POST" action="/admin/fields">
        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="label">Field Label</label>
                <input type="text" name="label" id="label" placeholder="e.g. Company Name, Badge Number, Vehicle Plate" required>
            </div>
            <div class="form-group">
                <label for="field_type">Field Type</label>
                <select name="field_type" id="field_type" onchange="toggleOptions(this.value)">
                    <option value="text">Text (single line)</option>
                    <option value="textarea">Text area (multi-line)</option>
                    <option value="select">Dropdown (select)</option>
                    <option value="checkbox">Checkbox (yes/no)</option>
                    <option value="date">Date</option>
                </select>
            </div>
            <div class="form-group" style="align-items:flex-end;">
                <label>
                    <input type="checkbox" name="required" value="1"> Required field
                </label>
            </div>
            <div class="form-group form-group-full" id="options-group" style="display:none;">
                <label for="options">Dropdown Options <small style="color:var(--text-muted);font-weight:400;">(one per line)</small></label>
                <textarea name="options" id="options" rows="4" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Add Field</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-title">Custom Fields</div>
    <?php if (empty($fields)): ?>
        <p class="text-muted">No custom fields configured yet.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>Label</th><th>Type</th><th>Required</th><th>Active</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($fields as $f): ?>
                    <tr>
                        <td><?= View::e($f['label']) ?></td>
                        <td><?= View::e($f['field_type']) ?></td>
                        <td><?= $f['required'] ? 'Yes' : 'No' ?></td>
                        <td><?= $f['active'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <form method="POST" action="/admin/fields/<?= (int)$f['field_id'] ?>/delete"
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

<script>
function toggleOptions(type) {
    document.getElementById('options-group').style.display = type === 'select' ? 'block' : 'none';
}
</script>
