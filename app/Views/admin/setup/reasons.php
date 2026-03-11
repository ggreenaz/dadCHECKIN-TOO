<?php use App\Core\View; ?>

<div class="setup-stage-header">
    <a href="/admin/setup" class="back-link">&larr; Back to Setup</a>
    <h2>Visit Reasons</h2>
    <p>Define why visitors come to your location. These appear as a dropdown on the check-in form.</p>
</div>

<div class="setup-stage-grid">

    <div class="card">
        <div class="card-title">Add a Reason</div>
        <form method="POST" action="/admin/setup/reasons/save">
            <div class="form-group" style="margin-bottom:12px;">
                <label for="label">Label <span style="color:var(--danger)">*</span></label>
                <input type="text" name="label" id="label" placeholder="e.g. Appointment, Delivery, Interview" required>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label>
                    <input type="checkbox" name="requires_approval" value="1">
                    Requires approval before entry
                </label>
            </div>
            <button type="submit" class="button">Add Reason</button>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Bulk Import via CSV</div>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:16px;">
            Download the template, fill it in offline, then upload it here.
        </p>
        <a href="/admin/setup/template/reasons" class="button button-outline" style="margin-bottom:20px;">
            &#8681; Download CSV Template
        </a>
        <form method="POST" action="/admin/setup/reasons/upload" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom:12px;">
                <label>Upload Completed CSV</label>
                <input type="file" name="csv_file" accept=".csv,text/csv" required>
            </div>
            <button type="submit" class="button">Import CSV</button>
        </form>
        <div class="csv-format-hint">
            <strong>Columns:</strong> label<br>
            One reason per row. First row can be a header.
        </div>
    </div>

</div>

<div class="card">
    <div class="card-title">Configured Reasons (<?= count($reasons) ?>)</div>
    <?php if (empty($reasons)): ?>
        <p class="text-muted">No reasons added yet.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>#</th><th>Label</th><th>Requires Approval</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($reasons as $r): ?>
                    <tr>
                        <td><?= (int)$r['sort_order'] ?></td>
                        <td><?= View::e($r['label']) ?></td>
                        <td><?= $r['requires_approval'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <form method="POST" action="/admin/setup/reasons/<?= (int)$r['reason_id'] ?>/delete"
                                  onsubmit="return confirm('Remove this reason?')">
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
    <a href="/admin/setup/hosts" class="button button-outline">&larr; Hosts</a>
    <a href="/admin/setup/fields" class="button">Next: Custom Fields &rarr;</a>
</div>
