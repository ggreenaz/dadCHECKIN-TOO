<?php use App\Core\View; ?>

<div class="setup-stage-header">
    <a href="/admin/setup" class="back-link">&larr; Back to Setup</a>
    <h2>Departments</h2>
    <p>Define the departments or divisions in your organization. Hosts can then be assigned to a department, making it easier for visitors to find who they need.</p>
</div>

<div class="setup-stage-grid">

    <!-- Manual entry -->
    <div class="card">
        <div class="card-title">Add a Department</div>
        <form method="POST" action="/admin/setup/departments/save">
            <div class="form-group" style="margin-bottom:12px;">
                <label for="name">Department Name <span style="color:var(--danger)">*</span></label>
                <input type="text" name="name" id="name" placeholder="e.g. Human Resources, IT, Reception" required>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label for="description">Description <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                <input type="text" name="description" id="description" placeholder="Brief description">
            </div>
            <button type="submit" class="button">Add Department</button>
        </form>
    </div>

    <!-- CSV import -->
    <div class="card">
        <div class="card-title">Bulk Import via CSV</div>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:16px;">
            Download the template, fill it in, then upload it here.
        </p>
        <a href="/admin/setup/template/departments" class="button button-outline" style="margin-bottom:20px;">
            &#8681; Download CSV Template
        </a>
        <form method="POST" action="/admin/setup/departments/upload" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom:12px;">
                <label>Upload Completed CSV</label>
                <input type="file" name="csv_file" accept=".csv,text/csv" required>
            </div>
            <button type="submit" class="button">Import CSV</button>
        </form>
        <div class="csv-format-hint">
            <strong>Columns:</strong> name, description<br>
            Only <strong>name</strong> is required. First row can be a header.
        </div>
    </div>

</div>

<!-- Current departments -->
<div class="card">
    <div class="card-title">Configured Departments (<?= count($departments) ?>)</div>
    <?php if (empty($departments)): ?>
        <p class="text-muted">No departments added yet. Departments are optional but recommended — they help visitors find the right person.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                    <tr>
                        <td><?= View::e($dept['name']) ?></td>
                        <td><?= View::e($dept['description'] ?? '—') ?></td>
                        <td>
                            <form method="POST" action="/admin/setup/departments/<?= (int)$dept['department_id'] ?>/delete"
                                  onsubmit="return confirm('Remove this department? Hosts assigned to it will become unassigned.')">
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
    <a href="/admin/setup/organization" class="button button-outline">&larr; Organization</a>
    <a href="/admin/setup/hosts" class="button">Next: Hosts &rarr;</a>
</div>
