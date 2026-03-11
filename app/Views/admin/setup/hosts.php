<?php use App\Core\View; ?>

<div class="setup-stage-header">
    <a href="/admin/setup" class="back-link">&larr; Back to Setup</a>
    <h2>Hosts</h2>
    <p>Add the people that visitors come to see. You can enter them one at a time or bulk-import from a CSV file.</p>
    <?php if (empty($departments)): ?>
    <p style="margin-top:8px;padding:10px 14px;background:var(--warning-bg,#fffbeb);border:1px solid var(--warning-border,#fcd34d);border-radius:6px;font-size:0.875rem;color:#92400e;">
        No departments configured yet. <a href="/admin/setup/departments" style="color:inherit;font-weight:600;">Add departments first</a> if you want to assign hosts to a department.
    </p>
    <?php endif; ?>
</div>

<div class="setup-stage-grid">

    <!-- Manual entry -->
    <div class="card">
        <div class="card-title">Add a Host</div>
        <form method="POST" action="/admin/setup/hosts/save">
            <div class="form-group" style="margin-bottom:12px;">
                <label for="name">Name <span style="color:var(--danger)">*</span></label>
                <input type="text" name="name" id="name" placeholder="Full name or title" required>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="department_id">Department</label>
                <select name="department_id" id="department_id">
                    <option value="">— None —</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= (int)$dept['department_id'] ?>"><?= View::e($dept['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($departments)): ?>
                    <small style="color:var(--text-muted)">
                        <a href="/admin/setup/departments">Add departments</a> to enable this dropdown.
                    </small>
                <?php endif; ?>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="For notifications (optional)">
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone" placeholder="Optional">
            </div>
            <button type="submit" class="button">Add Host</button>
        </form>
    </div>

    <!-- CSV import -->
    <div class="card">
        <div class="card-title">Bulk Import via CSV</div>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:16px;">
            Download the template below. Department names in the CSV must match exactly what you configured in the Departments stage.
        </p>
        <a href="/admin/setup/template/hosts" class="button button-outline" style="margin-bottom:20px;">
            &#8681; Download CSV Template
        </a>
        <?php if (!empty($departments)): ?>
        <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:16px;">
            The template is generated with your current departments as reference rows.
        </p>
        <?php endif; ?>
        <form method="POST" action="/admin/setup/hosts/upload" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom:12px;">
                <label>Upload Completed CSV</label>
                <input type="file" name="csv_file" accept=".csv,text/csv" required>
            </div>
            <button type="submit" class="button">Import CSV</button>
        </form>
        <div class="csv-format-hint">
            <strong>Columns:</strong> name, email, phone, department<br>
            Only <strong>name</strong> is required. Department must match an existing department name exactly.
        </div>
    </div>

</div>

<!-- Current hosts -->
<div class="card">
    <div class="card-title">Configured Hosts (<?= count($hosts) ?>)</div>
    <?php if (empty($hosts)): ?>
        <p class="text-muted">No hosts added yet.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hosts as $h): ?>
                    <tr>
                        <td><?= View::e($h['name']) ?></td>
                        <td><?= View::e($h['department_name'] ?? '—') ?></td>
                        <td><?= View::e($h['email'] ?? '—') ?></td>
                        <td><?= View::e($h['phone'] ?? '—') ?></td>
                        <td>
                            <form method="POST" action="/admin/setup/hosts/<?= (int)$h['host_id'] ?>/delete"
                                  onsubmit="return confirm('Remove this host?')">
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
    <a href="/admin/setup/departments" class="button button-outline">&larr; Departments</a>
    <a href="/admin/setup/reasons" class="button">Next: Visit Reasons &rarr;</a>
</div>
