<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\UserModel;

class UserController extends Controller
{
    private const PERMISSIONS = [
        'can_view_reports',
        'can_configure_reports',
        'can_manage_hosts',
        'can_manage_reasons',
        'can_view_analytics',
        'can_export_data',
    ];

    // ── Index ────────────────────────────────────────────────────

    public function index(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $model = new UserModel();
        $users = $model->getForOrg($orgId);

        // Decode permissions JSON for each user
        foreach ($users as &$u) {
            $u['permissions'] = !empty($u['permissions'])
                ? (array)(json_decode($u['permissions'], true) ?? [])
                : [];
        }
        unset($u);

        // Load org settings to check if LDAP is configured
        $db  = Database::getInstance();
        $row = $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
        $row->execute([$orgId]);
        $orgSettings = json_decode($row->fetchColumn() ?? '{}', true) ?: [];

        $this->view->render('admin/users/index', [
            'title'       => 'System Users',
            'helpSlug'    => 'users',
            'users'       => $users,
            'orgSettings' => $orgSettings,
            'flash'       => $this->flash(),
        ]);
    }

    // ── Create (show form) ───────────────────────────────────────

    public function create(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $db    = Database::getInstance();
        $row   = $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
        $row->execute([$orgId]);
        $orgSettings = json_decode($row->fetchColumn() ?? '{}', true) ?: [];

        $this->view->render('admin/users/form', [
            'title'       => 'Add User',
            'helpSlug'    => 'users',
            'user'        => null,
            'isEdit'      => false,
            'orgSettings' => $orgSettings,
            'flash'       => $this->flash(),
        ]);
    }

    // ── Store (save new user) ────────────────────────────────────

    public function store(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $db    = Database::getInstance();

        $name     = trim($this->request->input('name', ''));
        $email    = trim($this->request->input('email', ''));
        $role     = $this->request->input('role', 'staff');
        $provider = $this->request->input('auth_provider', 'local');
        $password = $this->request->input('password', '');

        // Validation
        if (!$name || !$email) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Name and email are required.'];
            $this->redirect('/admin/users/new');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid email address.'];
            $this->redirect('/admin/users/new');
            return;
        }

        // Check for duplicate email within org
        $existing = $db->prepare("SELECT user_id FROM users WHERE email = ? AND organization_id = ?");
        $existing->execute([$email, $orgId]);
        if ($existing->fetch()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'A user with that email already exists.'];
            $this->redirect('/admin/users/new');
            return;
        }

        // No second local super_admin per org
        if ($role === 'super_admin' && $provider === 'local') {
            $chk = $db->prepare(
                "SELECT user_id FROM users WHERE organization_id = ? AND role = 'super_admin' AND auth_provider = 'local' AND active = 1"
            );
            $chk->execute([$orgId]);
            if ($chk->fetch()) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'A local super_admin already exists for this organisation. Only one is allowed.'];
                $this->redirect('/admin/users/new');
                return;
            }
        }

        // Password required for local auth
        $passwordHash = null;
        if ($provider === 'local') {
            if (!$password) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'A password is required for local authentication.'];
                $this->redirect('/admin/users/new');
                return;
            }
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        // Permissions — only meaningful for location_admin/staff
        $permissions = $this->collectPermissions();

        $model = new UserModel();
        $model->create([
            'organization_id' => $orgId,
            'name'            => $name,
            'email'           => $email,
            'role'            => $role,
            'permissions'     => json_encode($permissions),
            'auth_provider'   => $provider,
            'password_hash'   => $passwordHash,
            'active'          => 1,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'User created successfully.'];
        $this->redirect('/admin/users');
    }

    // ── Edit (show form) ─────────────────────────────────────────

    public function edit(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId  = $this->orgId();
        $userId = (int)($params['id'] ?? 0);
        $model  = new UserModel();
        $user   = $model->findById($userId, $orgId);

        if (!$user) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'User not found.'];
            $this->redirect('/admin/users');
            return;
        }

        $user['permissions'] = !empty($user['permissions'])
            ? (array)(json_decode($user['permissions'], true) ?? [])
            : [];

        $db  = Database::getInstance();
        $row = $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
        $row->execute([$orgId]);
        $orgSettings = json_decode($row->fetchColumn() ?? '{}', true) ?: [];

        $this->view->render('admin/users/form', [
            'title'       => 'Edit User',
            'helpSlug'    => 'users',
            'user'        => $user,
            'isEdit'      => true,
            'orgSettings' => $orgSettings,
            'flash'       => $this->flash(),
        ]);
    }

    // ── Update (save edits) ──────────────────────────────────────

    public function update(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId  = $this->orgId();
        $userId = (int)($params['id'] ?? 0);
        $model  = new UserModel();
        $user   = $model->findById($userId, $orgId);

        if (!$user) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'User not found.'];
            $this->redirect('/admin/users');
            return;
        }

        $name     = trim($this->request->input('name', ''));
        $email    = trim($this->request->input('email', ''));
        $role     = $this->request->input('role', 'staff');
        $provider = $this->request->input('auth_provider', 'local');
        $password = $this->request->input('password', '');

        if (!$name || !$email) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Name and email are required.'];
            $this->redirect('/admin/users/' . $userId . '/edit');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid email address.'];
            $this->redirect('/admin/users/' . $userId . '/edit');
            return;
        }

        // Check duplicate email (exclude current user)
        $db = Database::getInstance();
        $existing = $db->prepare(
            "SELECT user_id FROM users WHERE email = ? AND organization_id = ? AND user_id != ?"
        );
        $existing->execute([$email, $orgId, $userId]);
        if ($existing->fetch()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Another user with that email already exists.'];
            $this->redirect('/admin/users/' . $userId . '/edit');
            return;
        }

        // No second local super_admin per org (exclude current user)
        if ($role === 'super_admin' && $provider === 'local') {
            $chk = $db->prepare(
                "SELECT user_id FROM users WHERE organization_id = ? AND role = 'super_admin' AND auth_provider = 'local' AND active = 1 AND user_id != ?"
            );
            $chk->execute([$orgId, $userId]);
            if ($chk->fetch()) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'A local super_admin already exists for this organisation.'];
                $this->redirect('/admin/users/' . $userId . '/edit');
                return;
            }
        }

        $data = [
            'name'          => $name,
            'email'         => $email,
            'role'          => $role,
            'auth_provider' => $provider,
            'permissions'   => json_encode($this->collectPermissions()),
        ];

        // Only update password if a new one is supplied
        if ($provider === 'local' && $password) {
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $model->updateUser($userId, $orgId, $data);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'User updated.'];
        $this->redirect('/admin/users');
    }

    // ── Deactivate ───────────────────────────────────────────────

    public function deactivate(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId  = $this->orgId();
        $userId = (int)($params['id'] ?? 0);

        // Prevent self-deactivation
        if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'You cannot deactivate your own account.'];
            $this->redirect('/admin/users');
            return;
        }

        $model = new UserModel();
        $model->deactivate($userId, $orgId);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'User deactivated.'];
        $this->redirect('/admin/users');
    }

    // ── Reactivate ───────────────────────────────────────────────

    public function reactivate(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId  = $this->orgId();
        $userId = (int)($params['id'] ?? 0);
        $model  = new UserModel();
        $model->reactivate($userId, $orgId);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'User reactivated.'];
        $this->redirect('/admin/users');
    }

    // ── Save LDAP access mode ────────────────────────────────────

    public function saveLdapMode(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $mode  = $this->request->input('ldap_access_mode', 'open');

        if (!in_array($mode, ['open', 'closed'])) {
            $mode = 'open';
        }

        $db  = Database::getInstance();
        $row = $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
        $row->execute([$orgId]);
        $settings = json_decode($row->fetchColumn() ?? '{}', true) ?: [];

        $settings['ldap_access_mode'] = $mode;

        $db->prepare("UPDATE organizations SET settings = ? WHERE organization_id = ?")
           ->execute([json_encode($settings), $orgId]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'LDAP access mode saved.'];
        $this->redirect('/admin/users');
    }

    // ── Helpers ──────────────────────────────────────────────────

    /** Collect permissions checkboxes from POST */
    private function collectPermissions(): array
    {
        $permissions = [];
        foreach (self::PERMISSIONS as $perm) {
            $permissions[$perm] = (bool)$this->request->input($perm, false);
        }
        return $permissions;
    }
}
