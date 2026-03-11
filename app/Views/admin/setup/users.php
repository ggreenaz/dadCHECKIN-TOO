<?php use App\Core\View; ?>

<div class="setup-stage-header">
    <a href="/admin/setup" class="back-link">&larr; Back to Setup</a>
    <h2>Users</h2>
    <p>Add staff accounts that can access the admin panel. The admin account created during install is already here.</p>
</div>

<?php
$enabledProviders = $settings['auth_providers'] ?? ['local'];
$roleLabels = [
    'org_admin'      => 'Org Admin',
    'location_admin' => 'Location Admin',
    'staff'          => 'Staff',
];
?>

<div class="setup-stage-grid">

    <!-- Manual entry -->
    <div class="card">
        <div class="card-title">Add a User</div>
        <form method="POST" action="/admin/setup/users/save">
            <div class="form-group" style="margin-bottom:12px;">
                <label>Full Name <span style="color:var(--danger)">*</span></label>
                <input type="text" name="name" placeholder="Jane Smith" required>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label>Email <span style="color:var(--danger)">*</span></label>
                <input type="email" name="email" placeholder="jane@yourorg.com" required>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label>Role</label>
                <select name="role">
                    <option value="staff">Staff</option>
                    <option value="location_admin">Location Admin</option>
                    <option value="org_admin">Org Admin</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label>Sign-in Method</label>
                <select name="auth_provider" id="add_provider">
                    <?php foreach ($enabledProviders as $p): ?>
                    <option value="<?= View::e($p) ?>"><?= View::e(ucfirst($p === 'local' ? 'Local (email + password)' : $p)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" id="add_password_group" style="margin-bottom:16px;">
                <label>Password</label>
                <input type="password" name="password" id="add_password" placeholder="At least 8 characters">
                <small style="color:var(--text-muted)">Required for local accounts. Leave blank for SSO/LDAP users.</small>
            </div>
            <button type="submit" class="button">Add User</button>
        </form>
    </div>

    <!-- CSV import -->
    <div class="card">
        <div class="card-title">Bulk Import via CSV</div>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:16px;">
            Import multiple users at once. For SSO or LDAP users, leave the password column blank.
        </p>
        <a href="/admin/setup/template/users" class="button button-outline" style="margin-bottom:20px;">
            &#8681; Download CSV Template
        </a>
        <form method="POST" action="/admin/setup/users/upload" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom:12px;">
                <label>Upload Completed CSV</label>
                <input type="file" name="csv_file" accept=".csv,text/csv" required>
            </div>
            <button type="submit" class="button">Import CSV</button>
        </form>
        <div class="csv-format-hint">
            <strong>Columns:</strong> name, email, role, auth_provider, password<br>
            Valid roles: <code>staff</code>, <code>location_admin</code>, <code>org_admin</code><br>
            Valid providers: <code>local</code>, <code>google</code>, <code>microsoft</code>, <code>ldap</code>
        </div>
    </div>

</div>

<!-- Current users -->
<div class="card">
    <div class="card-title">Users (<?= count($users) ?>)</div>
    <?php if (empty($users)): ?>
        <p class="text-muted">No users found.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Sign-in</th>
                        <th>Last Login</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= View::e($u['name']) ?></td>
                        <td><?= View::e($u['email']) ?></td>
                        <td><?= View::e($roleLabels[$u['role']] ?? $u['role']) ?></td>
                        <td>
                            <span class="badge badge-<?= View::e($u['auth_provider']) ?>">
                                <?= View::e(ucfirst($u['auth_provider'])) ?>
                            </span>
                        </td>
                        <td><?= $u['last_login'] ? View::e(date('M j, Y', strtotime($u['last_login']))) : '<span style="color:var(--text-muted)">Never</span>' ?></td>
                        <td>
                            <?php if ((int)$u['user_id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                            <form method="POST" action="/admin/setup/users/<?= (int)$u['user_id'] ?>/delete"
                                  onsubmit="return confirm('Remove <?= View::e($u['name']) ?>?')">
                                <button type="submit" class="delete-button">Remove</button>
                            </form>
                            <?php else: ?>
                            <span style="color:var(--text-muted);font-size:0.8rem;">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="setup-stage-nav">
    <a href="/admin/setup/auth" class="button button-outline">&larr; Authentication</a>
    <a href="/admin/setup/notifications" class="button">Next: Notifications &rarr;</a>
</div>

<script>
// Show/hide password field based on selected auth provider
document.getElementById('add_provider').addEventListener('change', function() {
    var pg = document.getElementById('add_password_group');
    var pw = document.getElementById('add_password');
    if (this.value === 'local') {
        pg.style.display = '';
        pw.required = true;
    } else {
        pg.style.display = 'none';
        pw.required = false;
        pw.value = '';
    }
});
</script>
