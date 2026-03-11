<?php use App\Core\View; ?>

<?php
$ldapConfigured = !empty($orgSettings['auth_providers']['ldap']['enabled']);
$defaultProvider = $ldapConfigured ? 'ldap' : 'local';
$currentProvider = $user['auth_provider'] ?? $defaultProvider;

$permLabels = [
    'can_view_reports'      => ['label' => 'View Reports',       'desc' => 'Access the Reports Hub'],
    'can_configure_reports' => ['label' => 'Configure Reports',  'desc' => 'Set up schedules and recipients'],
    'can_manage_hosts'      => ['label' => 'Manage Hosts',       'desc' => 'Add and edit hosts'],
    'can_manage_reasons'    => ['label' => 'Manage Reasons',     'desc' => 'Add and edit visit reasons'],
    'can_view_analytics'    => ['label' => 'View Analytics',     'desc' => 'Access the analytics page'],
    'can_export_data'       => ['label' => 'Export Data',        'desc' => 'Download visit history'],
];

$userPerms = $user['permissions'] ?? [];
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $isEdit ? 'Edit User' : 'Add User' ?></h1>
        <p class="page-sub"><?= $isEdit ? 'Update user details, role, and permissions.' : 'Create a new admin panel user.' ?></p>
    </div>
    <a href="/admin/users" class="button button-outline">Cancel</a>
</div>

<div class="card" style="max-width:680px;">
    <form method="POST" action="<?= $isEdit ? '/admin/users/' . (int)($user['user_id'] ?? 0) : '/admin/users' ?>">

        <!-- Name -->
        <div class="form-group">
            <label for="name">Full Name <span style="color:var(--danger)">*</span></label>
            <input type="text" id="name" name="name" required
                   value="<?= View::e($user['name'] ?? '') ?>" placeholder="Jane Smith">
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email Address <span style="color:var(--danger)">*</span></label>
            <input type="email" id="email" name="email" required
                   value="<?= View::e($user['email'] ?? '') ?>" placeholder="jane@example.com">
        </div>

        <!-- Role -->
        <div class="form-group">
            <label for="role">Role <span style="color:var(--danger)">*</span></label>
            <select id="role" name="role">
                <?php
                $roles = ['super_admin' => 'Super Admin', 'org_admin' => 'Org Admin',
                          'location_admin' => 'Location Admin', 'staff' => 'Staff'];
                foreach ($roles as $val => $label):
                ?>
                    <option value="<?= $val ?>" <?= ($user['role'] ?? 'staff') === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="field-hint">Note: super_admin can only have one local account per organisation.</p>
        </div>

        <!-- Auth provider -->
        <div class="form-group">
            <label for="auth_provider">Authentication Provider</label>
            <select id="auth_provider" name="auth_provider" onchange="togglePasswordField(this.value)">
                <option value="local"     <?= $currentProvider === 'local'     ? 'selected' : '' ?>>Local (username + password)</option>
                <option value="ldap"      <?= $currentProvider === 'ldap'      ? 'selected' : '' ?>>LDAP / Active Directory</option>
                <option value="google"    <?= $currentProvider === 'google'    ? 'selected' : '' ?>>Google OAuth</option>
                <option value="microsoft" <?= $currentProvider === 'microsoft' ? 'selected' : '' ?>>Microsoft OAuth</option>
            </select>
        </div>

        <!-- Password (shown only for local) -->
        <div class="form-group" id="password-field" style="<?= $currentProvider !== 'local' ? 'display:none;' : '' ?>">
            <label for="password"><?= $isEdit ? 'New Password' : 'Password' ?> <?= !$isEdit ? '<span style="color:var(--danger)">*</span>' : '' ?></label>
            <input type="password" id="password" name="password"
                   placeholder="<?= $isEdit ? 'Leave blank to keep current password' : 'Minimum 8 characters' ?>"
                   <?= !$isEdit ? 'required' : '' ?>>
            <?php if ($isEdit): ?>
                <p class="field-hint">Leave blank to keep the existing password.</p>
            <?php endif; ?>
        </div>

        <!-- Permissions -->
        <div class="form-group">
            <label>Permissions</label>
            <p class="field-hint" style="margin-bottom:10px;">
                org_admin and super_admin have all permissions automatically — checkboxes apply to location_admin and staff only.
            </p>
            <div class="perm-checkboxes">
                <?php foreach ($permLabels as $key => $info): ?>
                    <label class="perm-checkbox-row">
                        <input type="checkbox" name="<?= $key ?>" value="1"
                               <?= !empty($userPerms[$key]) ? 'checked' : '' ?>>
                        <span>
                            <strong><?= $info['label'] ?></strong>
                            <span class="perm-checkbox-desc"> — <?= $info['desc'] ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="button">
                <?= $isEdit ? 'Save Changes' : 'Create User' ?>
            </button>
            <a href="/admin/users" class="button button-outline">Cancel</a>
        </div>

    </form>
</div>

<script>
function togglePasswordField(provider) {
    var field = document.getElementById('password-field');
    var input = document.getElementById('password');
    if (provider === 'local') {
        field.style.display = '';
        <?php if (!$isEdit): ?>
        input.required = true;
        <?php endif; ?>
    } else {
        field.style.display = 'none';
        input.required = false;
    }
}
// Set initial state
togglePasswordField(document.getElementById('auth_provider').value);
</script>
