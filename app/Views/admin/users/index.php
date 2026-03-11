<?php use App\Core\View; ?>

<!-- Page header -->
<div class="page-header">
    <div>
        <h1 class="page-title">System Users</h1>
        <p class="page-sub">Manage who can access the admin panel, their roles, and permissions.</p>
    </div>
    <a href="/admin/users/new" class="button">+ Add User</a>
</div>

<?php
// Check if LDAP is configured for this org
$ldapConfigured = !empty($orgSettings['auth_providers']['ldap']['enabled']);
$ldapMode       = $orgSettings['ldap_access_mode'] ?? 'open';
?>

<!-- LDAP Access Mode card (only shown when LDAP is configured) -->
<?php if ($ldapConfigured): ?>
<div class="card" style="margin-bottom:16px;">
    <div class="card-title">LDAP Access Mode</div>
    <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:12px;">
        Controls whether <em>any</em> LDAP user can sign in, or only those explicitly listed here.
    </p>
    <form method="POST" action="/admin/users/ldap-mode" style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
        <label class="radio-label">
            <input type="radio" name="ldap_access_mode" value="open" <?= $ldapMode === 'open' ? 'checked' : '' ?>>
            <span>
                <strong>Open</strong> — any user who exists in your LDAP/AD directory can sign in automatically.
            </span>
        </label>
        <label class="radio-label">
            <input type="radio" name="ldap_access_mode" value="closed" <?= $ldapMode === 'closed' ? 'checked' : '' ?>>
            <span>
                <strong>Closed</strong> — only LDAP users listed in the table below are allowed to sign in.
            </span>
        </label>
        <button type="submit" class="button button-sm">Save Mode</button>
    </form>
</div>
<?php endif; ?>

<!-- Users table -->
<div class="card">
    <div class="card-title">Users <span class="hub-feed-badge"><?= count($users) ?></span></div>

    <?php if (empty($users)): ?>
        <p class="text-muted">No users found. <a href="/admin/users/new">Add the first user</a>.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table users-table">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Auth</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u):
                    $inactive = !(bool)$u['active'];
                    $perms    = $u['permissions'] ?? [];
                    $initials = strtoupper(
                        substr(explode(' ', trim($u['name']))[0], 0, 1) .
                        (str_contains(trim($u['name']), ' ')
                            ? substr(trim($u['name']), strrpos(trim($u['name']), ' ') + 1, 1)
                            : '')
                    );
                ?>
                    <tr class="<?= $inactive ? 'user-row-inactive' : '' ?>">
                        <!-- Avatar -->
                        <td>
                            <div class="user-list-avatar"><?= View::e($initials) ?></div>
                        </td>
                        <!-- Name -->
                        <td>
                            <strong><?= View::e($u['name']) ?></strong>
                        </td>
                        <!-- Email -->
                        <td style="color:var(--text-muted);font-size:0.85rem;">
                            <?= View::e($u['email']) ?>
                        </td>
                        <!-- Role badge -->
                        <td>
                            <span class="role-badge role-badge-<?= View::e($u['role']) ?>">
                                <?= View::e(str_replace('_', ' ', $u['role'])) ?>
                            </span>
                        </td>
                        <!-- Permissions summary -->
                        <td>
                            <?php
                            $activePerm = array_keys(array_filter($perms));
                            $permLabels = [
                                'can_view_reports'      => 'Reports',
                                'can_configure_reports' => 'Cfg Reports',
                                'can_manage_hosts'      => 'Hosts',
                                'can_manage_reasons'    => 'Reasons',
                                'can_view_analytics'    => 'Analytics',
                                'can_export_data'       => 'Export',
                            ];
                            $adminRoles = ['super_admin', 'org_admin'];
                            if (in_array($u['role'], $adminRoles)): ?>
                                <span class="perm-tag" style="background:#d1fae5;color:#065f46;">All permissions</span>
                            <?php elseif (empty($activePerm)): ?>
                                <span style="color:var(--text-muted);font-size:0.8rem;">None</span>
                            <?php else: ?>
                                <div class="perm-tags">
                                    <?php foreach ($activePerm as $p): ?>
                                        <span class="perm-tag"><?= View::e($permLabels[$p] ?? $p) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <!-- Auth provider -->
                        <td>
                            <span class="auth-badge auth-badge-<?= View::e($u['auth_provider']) ?>">
                                <?= View::e(ucfirst($u['auth_provider'])) ?>
                            </span>
                        </td>
                        <!-- Last login -->
                        <td style="font-size:0.82rem;color:var(--text-muted);">
                            <?= $u['last_login'] ? View::e(date('M j, Y', strtotime($u['last_login']))) : '—' ?>
                        </td>
                        <!-- Status -->
                        <td>
                            <?php if ($u['active']): ?>
                                <span style="color:var(--success);font-size:0.8rem;font-weight:600;">Active</span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);font-size:0.8rem;">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <!-- Actions -->
                        <td>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <a href="/admin/users/<?= (int)$u['user_id'] ?>/edit"
                                   class="button button-sm button-outline">Edit</a>
                                <?php if ($u['active']): ?>
                                    <form method="POST" action="/admin/users/<?= (int)$u['user_id'] ?>/deactivate"
                                          onsubmit="return confirm('Deactivate <?= View::e(addslashes($u['name'])) ?>?')">
                                        <button type="submit" class="delete-button">Deactivate</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="/admin/users/<?= (int)$u['user_id'] ?>/reactivate">
                                        <button type="submit" class="button button-sm">Reactivate</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
