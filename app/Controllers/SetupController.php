<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;
use App\Auth\ProviderFactory;

class SetupController extends Controller
{
    // ── Timeline overview ────────────────────────────────────────

    public function index(array $params): void
    {
        $this->requireRole('org_admin');
        $stages = $this->getStages();

        $this->view->render('admin/setup/index', [
            'title'    => 'Guided Configuration',
            'helpSlug' => 'setup',
            'stages'   => $stages,
            'flash'    => $this->flash(),
        ]);
    }

    // ── Individual stage ─────────────────────────────────────────

    // Stages that require super_admin (system-level config)
    private const SUPER_ADMIN_STAGES = [];

    public function stage(array $params): void
    {
        $slug = $params['stage'] ?? '';
        if (in_array($slug, self::SUPER_ADMIN_STAGES, true)) {
            $this->requireRole('org_admin');
        } else {
            $this->requireRole('org_admin');
        }
        $stages = $this->getStages();

        if (!isset($stages[$slug])) {
            $this->redirect('/admin/setup');
            return;
        }

        $orgId = $this->orgId();
        $db    = Database::getInstance();
        $data  = ['title' => $stages[$slug]['label'], 'stages' => $stages, 'stage' => $slug, 'flash' => $this->flash()];

        switch ($slug) {
            case 'organization':
                $org = $db->prepare("SELECT * FROM organizations WHERE organization_id = ?");
                $org->execute([$orgId]);
                $data['org'] = $org->fetch();
                break;

            case 'departments':
                $stmt = $db->prepare("SELECT * FROM departments WHERE organization_id = ? ORDER BY sort_order, name");
                $stmt->execute([$orgId]);
                $data['departments'] = $stmt->fetchAll();
                break;

            case 'hosts':
                $stmt = $db->prepare(
                    "SELECT h.*, d.name AS department_name
                     FROM hosts h
                     LEFT JOIN departments d ON h.department_id = d.department_id
                     WHERE h.organization_id = ? ORDER BY h.name"
                );
                $stmt->execute([$orgId]);
                $data['hosts'] = $stmt->fetchAll();
                $dstmt = $db->prepare("SELECT * FROM departments WHERE organization_id = ? AND active = 1 ORDER BY sort_order, name");
                $dstmt->execute([$orgId]);
                $data['departments'] = $dstmt->fetchAll();
                break;

            case 'reasons':
                $stmt = $db->prepare("SELECT * FROM visit_reasons WHERE organization_id = ? ORDER BY sort_order, label");
                $stmt->execute([$orgId]);
                $data['reasons'] = $stmt->fetchAll();
                break;

            case 'fields':
                $stmt = $db->prepare("SELECT * FROM custom_fields WHERE organization_id = ? ORDER BY sort_order, label");
                $stmt->execute([$orgId]);
                $data['fields'] = $stmt->fetchAll();
                break;

            case 'kiosk':
                $data['settings'] = Auth::getOrgSettings($orgId);
                break;

            case 'auth':
                $data['settings']      = Auth::getOrgSettings($orgId);
                $data['all_providers'] = ProviderFactory::all();
                break;

            case 'users':
                $stmt = $db->prepare("SELECT * FROM users WHERE organization_id = ? ORDER BY name");
                $stmt->execute([$orgId]);
                $data['users']    = $stmt->fetchAll();
                $data['settings'] = Auth::getOrgSettings($orgId);
                break;

            case 'notifications':
                $stmt = $db->prepare("SELECT * FROM notification_rules WHERE organization_id = ?");
                $stmt->execute([$orgId]);
                $data['rules'] = $stmt->fetchAll();
                $stmt2 = $db->prepare("SELECT * FROM hosts WHERE organization_id = ? AND active = 1 ORDER BY name");
                $stmt2->execute([$orgId]);
                $data['hosts'] = $stmt2->fetchAll();
                $stmt3 = $db->prepare("SELECT * FROM visit_reasons WHERE organization_id = ? AND active = 1 ORDER BY sort_order");
                $stmt3->execute([$orgId]);
                $data['reasons'] = $stmt3->fetchAll();
                break;

            case 'test':
                $cfg = require BASE_PATH . '/config/app.php';
                $data['checkin_url'] = rtrim($cfg['url'], '/') . '/checkin';
                break;
        }

        $helpMap = [
            'kiosk'         => 'kiosk',
            'auth'          => 'auth',
            'notifications' => 'notifications',
        ];
        if (isset($helpMap[$slug])) {
            $data['helpSlug'] = $helpMap[$slug];
        }
        $this->view->render('admin/setup/' . $slug, $data);
    }

    // ── Save handlers ────────────────────────────────────────────

    public function saveOrg(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId    = $this->orgId();
        $name     = trim($this->request->input('org_name', ''));
        $timezone = $this->request->input('timezone', 'UTC');

        if (!$name) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Organization name is required.'];
            $this->redirect('/admin/setup/organization');
            return;
        }

        $db = Database::getInstance();
        $db->prepare("UPDATE organizations SET name = ?, timezone = ? WHERE organization_id = ?")
           ->execute([$name, $timezone, $orgId]);

        $this->syncAppConfig('timezone', $timezone);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Organization details saved.'];
        $this->redirect('/admin/setup/organization');
    }

    // ── Departments ──────────────────────────────────────────────

    public function saveDepartment(array $params): void
    {
        $this->requireRole('org_admin');
        $name = trim($this->request->input('name', ''));
        if (!$name) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Department name is required.'];
            $this->redirect('/admin/setup/departments');
            return;
        }
        $orgId = $this->orgId();
        $db    = Database::getInstance();
        $order = (int)$db->query("SELECT COALESCE(MAX(sort_order),0) FROM departments WHERE organization_id = {$orgId}")->fetchColumn();
        $db->prepare("INSERT IGNORE INTO departments (organization_id, name, description, sort_order) VALUES (?,?,?,?)")
           ->execute([$orgId, $name, trim($this->request->input('description', '')) ?: null, $order + 1]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Department added.'];
        $this->redirect('/admin/setup/departments');
    }

    public function deleteDepartment(array $params): void
    {
        $this->requireRole('org_admin');
        $db    = Database::getInstance();
        $id    = (int)($params['id'] ?? 0);
        $orgId = $this->orgId();
        // Check if any hosts use this department
        $chk = $db->prepare("SELECT COUNT(*) FROM hosts WHERE department_id = ? AND organization_id = ?");
        $chk->execute([$id, $orgId]);
        $count = (int)$chk->fetchColumn();
        if ($count > 0) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => "Cannot remove — {$count} host(s) are assigned to this department."];
            $this->redirect('/admin/setup/departments');
            return;
        }
        $db->prepare("DELETE FROM departments WHERE department_id = ? AND organization_id = ?")
           ->execute([$id, $orgId]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Department removed.'];
        $this->redirect('/admin/setup/departments');
    }

    public function uploadDepartments(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $file  = $this->request->file('csv_file');

        [$rows, $error] = $this->parseCSV($file);
        if ($error) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $error];
            $this->redirect('/admin/setup/departments');
            return;
        }

        $db       = Database::getInstance();
        $order    = (int)$db->query("SELECT COALESCE(MAX(sort_order),0) FROM departments WHERE organization_id = {$orgId}")->fetchColumn();
        $stmt     = $db->prepare("INSERT IGNORE INTO departments (organization_id, name, description, sort_order) VALUES (?,?,?,?)");
        $imported = 0;
        foreach ($rows as $i => $row) {
            if ($i === 0 && $this->isHeader($row, ['name', 'department'])) continue;
            $name = trim($row[0] ?? '');
            if (!$name) continue;
            $stmt->execute([$orgId, $name, trim($row[1] ?? '') ?: null, ++$order]);
            $imported++;
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => "{$imported} department(s) imported."];
        $this->redirect('/admin/setup/departments');
    }

    // ── Hosts ────────────────────────────────────────────────────

    public function saveHost(array $params): void
    {
        $this->requireRole('org_admin');
        $name = trim($this->request->input('name', ''));
        if (!$name) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Host name is required.'];
            $this->redirect('/admin/setup/hosts');
            return;
        }
        $deptId = (int)$this->request->input('department_id', 0) ?: null;
        $db     = Database::getInstance();
        $db->prepare("INSERT INTO hosts (organization_id, department_id, name, email, phone) VALUES (?,?,?,?,?)")
           ->execute([
               $this->orgId(), $deptId, $name,
               trim($this->request->input('email', '')) ?: null,
               trim($this->request->input('phone', '')) ?: null,
           ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Host added.'];
        $this->redirect('/admin/setup/hosts');
    }

    public function deleteHost(array $params): void
    {
        $this->requireRole('org_admin');
        $db = Database::getInstance();
        $db->prepare("DELETE FROM hosts WHERE host_id = ? AND organization_id = ?")
           ->execute([(int)($params['id'] ?? 0), $this->orgId()]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Host removed.'];
        $this->redirect('/admin/setup/hosts');
    }

    public function saveReason(array $params): void
    {
        $this->requireRole('org_admin');
        $label = trim($this->request->input('label', ''));
        if (!$label) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Reason label is required.'];
            $this->redirect('/admin/setup/reasons');
            return;
        }
        $orgId = $this->orgId();
        $db    = Database::getInstance();
        $order = (int)$db->query("SELECT COALESCE(MAX(sort_order),0) FROM visit_reasons WHERE organization_id = {$orgId}")->fetchColumn();
        $db->prepare("INSERT INTO visit_reasons (organization_id, label, requires_approval, sort_order) VALUES (?,?,?,?)")
           ->execute([$orgId, $label, (int)$this->request->input('requires_approval', 0), $order + 1]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Reason added.'];
        $this->redirect('/admin/setup/reasons');
    }

    public function deleteReason(array $params): void
    {
        $this->requireRole('org_admin');
        $db = Database::getInstance();
        $db->prepare("DELETE FROM visit_reasons WHERE reason_id = ? AND organization_id = ?")
           ->execute([(int)($params['id'] ?? 0), $this->orgId()]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Reason removed.'];
        $this->redirect('/admin/setup/reasons');
    }

    public function saveField(array $params): void
    {
        $this->requireRole('org_admin');
        $label = trim($this->request->input('label', ''));
        if (!$label) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Field label is required.'];
            $this->redirect('/admin/setup/fields');
            return;
        }
        $orgId   = $this->orgId();
        $db      = Database::getInstance();
        $order   = (int)$db->query("SELECT COALESCE(MAX(sort_order),0) FROM custom_fields WHERE organization_id = {$orgId}")->fetchColumn();
        $options = null;
        if ($this->request->input('field_type') === 'select') {
            $raw     = $this->request->input('options', '');
            $options = json_encode(array_values(array_filter(array_map('trim', explode("\n", $raw)))));
        }
        $db->prepare("INSERT INTO custom_fields (organization_id, label, field_type, required, options, sort_order) VALUES (?,?,?,?,?,?)")
           ->execute([$orgId, $label, $this->request->input('field_type', 'text'), (int)$this->request->input('required', 0), $options, $order + 1]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Field added.'];
        $this->redirect('/admin/setup/fields');
    }

    public function deleteField(array $params): void
    {
        $this->requireRole('org_admin');
        $db = Database::getInstance();
        $db->prepare("DELETE FROM custom_fields WHERE field_id = ? AND organization_id = ?")
           ->execute([(int)($params['id'] ?? 0), $this->orgId()]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Field removed.'];
        $this->redirect('/admin/setup/fields');
    }

    public function saveNotification(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId   = $this->orgId();
        $channel = $this->request->input('channel', 'email');
        $trigger = $this->request->input('trigger_event', 'check_in');
        $recType = $this->request->input('recipient_type', 'fixed_address');
        $recVal  = trim($this->request->input('recipient_value', ''));

        if (!$recVal && $recType !== 'host') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Recipient address or URL is required unless using "The host being visited".'];
            $this->redirect('/admin/setup/notifications');
            return;
        }

        $db = Database::getInstance();
        $db->prepare("INSERT INTO notification_rules (organization_id, trigger_event, channel, recipient_type, recipient_value) VALUES (?,?,?,?,?)")
           ->execute([$orgId, $trigger, $channel, $recType, $recVal]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Notification rule added.'];
        $this->redirect('/admin/setup/notifications');
    }

    public function deleteNotification(array $params): void
    {
        $this->requireRole('org_admin');
        $db = Database::getInstance();
        $db->prepare("DELETE FROM notification_rules WHERE rule_id = ? AND organization_id = ?")
           ->execute([(int)($params['id'] ?? 0), $this->orgId()]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Rule removed.'];
        $this->redirect('/admin/setup/notifications');
    }

    // ── LDAP test connection (AJAX) ──────────────────────────────

    public function testLdapConnection(array $params): void
    {
        $this->requireRole('org_admin');

        if (!extension_loaded('ldap')) {
            $this->json(['success' => false, 'message' => 'The PHP ldap extension is not installed on this server. Install php-ldap and restart your web server.']);
            return;
        }

        $hostUrl  = trim($this->request->input('ldap_host_url', ''));
        $bindUser = trim($this->request->input('ldap_bind_user', ''));
        $bindPass = $this->request->input('ldap_bind_password', '');
        $baseDn   = trim($this->request->input('ldap_base_dn', ''));
        $contexts = trim($this->request->input('ldap_contexts', ''));

        if (!$hostUrl) {
            $this->json(['success' => false, 'message' => 'Host URL is required (e.g. ldap://ldap.yourdomain.com/).']);
            return;
        }

        // Use only the first URL for the test (semicolon = failover list)
        $firstUrl = trim(explode(';', $hostUrl)[0]);

        // Step 1 — connect
        $conn = @ldap_connect($firstUrl);
        if (!$conn) {
            $this->json(['success' => false, 'message' => "Could not parse or reach LDAP server at \"{$firstUrl}\". Check the Host URL format."]);
            return;
        }

        $host = parse_url($firstUrl, PHP_URL_HOST) ?? $firstUrl;

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 5);

        // Step 2 — bind
        if ($bindUser) {
            $bound = @ldap_bind($conn, $bindUser, $bindPass);
        } else {
            $bound = @ldap_bind($conn);
        }

        if (!$bound) {
            $err = ldap_error($conn);
            ldap_unbind($conn);
            $msg = $bindUser
                ? "Connected to server but bind failed for \"{$bindUser}\". Check the bind DN and password. Server said: {$err}"
                : "Connected to server but anonymous bind was rejected. Server said: {$err}";
            $this->json(['success' => false, 'message' => $msg]);
            return;
        }

        // Step 3 — verify at least one search context is readable
        $searchBases = [];
        if ($contexts) {
            $searchBases = array_filter(array_map('trim', explode(';', $contexts)));
        }
        if (!$searchBases && $baseDn) {
            $searchBases = [$baseDn];
        }

        if ($searchBases) {
            $searchOk = false;
            foreach ($searchBases as $base) {
                // Try ldap_search (recursive) first — AD allows this even when ldap_list is restricted
                $res = @ldap_search($conn, $base, '(objectClass=user)', ['dn'], 1, 1);
                if ($res !== false) { $searchOk = true; break; }
                // Fall back to a subtree search with a more permissive filter
                $res = @ldap_search($conn, $base, '(objectClass=*)', ['dn'], 1, 1);
                if ($res !== false) { $searchOk = true; break; }
            }
            // Note: if search fails, we still treat a successful bind as a pass.
            // Active Directory often restricts directory listing but bind success
            // confirms the server, credentials, and SSL settings are correct.
        }

        ldap_unbind($conn);

        $detail = $bindUser ? "Bound as \"{$bindUser}\"." : "Anonymous bind succeeded.";
        if (!empty($searchOk)) $detail .= " Directory context is readable.";
        $this->json(['success' => true, 'message' => "Connected to {$host}. {$detail}"]);
    }

    // ── Kiosk field settings ─────────────────────────────────────

    public function saveKioskSettings(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId    = $this->orgId();
        $settings = Auth::getOrgSettings($orgId);

        $fields = ['last_name', 'phone', 'email', 'notes'];
        $kiosk  = [];

        foreach ($fields as $field) {
            $show     = !empty($this->request->input("show_{$field}"));
            $required = !empty($this->request->input("required_{$field}"));
            $kiosk[$field] = [
                'show'     => $show,
                'required' => $show && $required, // can't be required if not shown
            ];
        }

        $settings['kiosk_fields'] = $kiosk;
        Auth::saveOrgSettings($orgId, $settings);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kiosk field settings saved.'];
        $this->redirect('/admin/setup/kiosk');
    }

    // ── Auth providers ───────────────────────────────────────────


    public function saveAuthProviders(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId    = $this->orgId();
        $existing = Auth::getOrgSettings($orgId);

        $enabled = $this->request->input('auth_providers', []);
        if (!is_array($enabled)) $enabled = [];
        if (!in_array('local', $enabled)) $enabled[] = 'local'; // local always available

        $existing['auth_providers'] = array_values($enabled);

        // Google
        $existing['google_client_id']     = trim($this->request->input('google_client_id', ''));
        $existing['google_client_secret']  = trim($this->request->input('google_client_secret', ''))
                                            ?: ($existing['google_client_secret'] ?? ''); // keep existing if blank

        // Microsoft
        $existing['microsoft_client_id']     = trim($this->request->input('microsoft_client_id', ''));
        $existing['microsoft_client_secret'] = trim($this->request->input('microsoft_client_secret', ''))
                                              ?: ($existing['microsoft_client_secret'] ?? '');
        $existing['microsoft_tenant_id']     = trim($this->request->input('microsoft_tenant_id', 'common')) ?: 'common';

        // LDAP
        $existing['ldap_host_url']       = trim($this->request->input('ldap_host_url', ''));
        $existing['ldap_base_dn']        = trim($this->request->input('ldap_base_dn', ''));
        $existing['ldap_bind_user']      = trim($this->request->input('ldap_bind_user', ''));
        $existing['ldap_bind_password']  = trim($this->request->input('ldap_bind_password', ''))
                                          ?: ($existing['ldap_bind_password'] ?? '');
        $existing['ldap_user_type']      = trim($this->request->input('ldap_user_type', 'ms_ad'));
        $existing['ldap_contexts']       = trim($this->request->input('ldap_contexts', ''));
        $existing['ldap_search_sub']     = (bool)$this->request->input('ldap_search_sub', 0);
        $existing['ldap_user_attribute'] = trim($this->request->input('ldap_user_attribute', ''));
        $existing['ldap_user_filter']    = trim($this->request->input('ldap_user_filter', ''));
        $ldapCustomize = (bool)$this->request->input('ldap_customize_labels', 0);
        $existing['ldap_customize_labels'] = $ldapCustomize;
        if ($ldapCustomize) {
            $existing['ldap_login_label'] = trim($this->request->input('ldap_login_label', '')) ?: 'Username';
            $existing['ldap_login_hint']  = trim($this->request->input('ldap_login_hint', ''));
        } else {
            // Derive from preset so the login page always has a value to read
            $labelDefaults = [
                'ms_ad'    => ['label' => 'Username', 'hint' => 'Enter your network username (e.g. jsmith — not jsmith@domain.com)'],
                'novell'   => ['label' => 'Username', 'hint' => 'Enter your Novell directory username'],
                'posix'    => ['label' => 'Username', 'hint' => 'Enter your system username'],
                'samba'    => ['label' => 'Username', 'hint' => 'Enter your Samba network username'],
                'inet_org' => ['label' => 'Username', 'hint' => 'Enter your directory username'],
                'custom'   => ['label' => 'Username', 'hint' => ''],
            ];
            $preset = $labelDefaults[$existing['ldap_user_type']] ?? $labelDefaults['ms_ad'];
            $existing['ldap_login_label'] = $preset['label'];
            $existing['ldap_login_hint']  = $preset['hint'];
        }

        Auth::saveOrgSettings($orgId, $existing);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Authentication providers saved.'];
        $this->redirect('/admin/setup/auth');
    }

    // ── Users ────────────────────────────────────────────────────

    public function saveUser(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId    = $this->orgId();
        $name     = trim($this->request->input('name', ''));
        $email    = trim($this->request->input('email', ''));
        $role     = $this->request->input('role', 'staff');
        $provider = $this->request->input('auth_provider', 'local');
        $pass     = $this->request->input('password', '');

        if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Name and a valid email are required.'];
            $this->redirect('/admin/setup/users');
            return;
        }

        if ($provider === 'local' && strlen($pass) < 8) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Password must be at least 8 characters for local accounts.'];
            $this->redirect('/admin/setup/users');
            return;
        }

        $validRoles = ['org_admin', 'location_admin', 'staff'];
        if (!in_array($role, $validRoles)) $role = 'staff';

        $db   = Database::getInstance();
        $hash = ($provider === 'local' && $pass) ? Auth::hashPassword($pass) : null;

        try {
            $db->prepare(
                "INSERT INTO users (organization_id, name, email, role, auth_provider, password_hash)
                 VALUES (?, ?, ?, ?, ?, ?)"
            )->execute([$orgId, $name, $email, $role, $provider, $hash]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'User added.'];
        } catch (\PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Email already exists for this organization.'];
        }

        $this->redirect('/admin/setup/users');
    }

    public function deleteUser(array $params): void
    {
        $this->requireRole('org_admin');
        $db     = Database::getInstance();
        $id     = (int)($params['id'] ?? 0);
        $orgId  = $this->orgId();

        // Prevent deleting self
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'You cannot remove your own account.'];
            $this->redirect('/admin/setup/users');
            return;
        }

        $db->prepare("DELETE FROM users WHERE user_id = ? AND organization_id = ?")
           ->execute([$id, $orgId]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'User removed.'];
        $this->redirect('/admin/setup/users');
    }

    public function uploadUsers(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $file  = $this->request->file('csv_file');

        [$rows, $error] = $this->parseCSV($file);
        if ($error) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $error];
            $this->redirect('/admin/setup/users');
            return;
        }

        $db       = Database::getInstance();
        $stmt     = $db->prepare(
            "INSERT IGNORE INTO users (organization_id, name, email, role, auth_provider, password_hash)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $imported = 0;
        $skipped  = 0;
        $validRoles     = ['org_admin', 'location_admin', 'staff'];
        $validProviders = ['local', 'google', 'microsoft', 'ldap'];

        foreach ($rows as $i => $row) {
            if ($i === 0 && $this->isHeader($row, ['name', 'email'])) continue;
            $name     = trim($row[0] ?? '');
            $email    = trim($row[1] ?? '');
            $role     = trim($row[2] ?? 'staff');
            $provider = trim($row[3] ?? 'local');
            $pass     = trim($row[4] ?? '');

            if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $skipped++; continue; }
            if (!in_array($role, $validRoles))         $role = 'staff';
            if (!in_array($provider, $validProviders)) $provider = 'local';

            $hash = ($provider === 'local' && $pass) ? Auth::hashPassword($pass) : null;
            $stmt->execute([$orgId, $name, $email, $role, $provider, $hash]);
            $imported++;
        }

        $msg = "{$imported} user(s) imported.";
        if ($skipped) $msg .= " {$skipped} row(s) skipped (invalid or missing email).";
        $_SESSION['flash'] = ['type' => 'success', 'message' => $msg];
        $this->redirect('/admin/setup/users');
    }

    // ── CSV upload ───────────────────────────────────────────────

    public function uploadCSV(array $params): void
    {
        $this->requireRole('org_admin');
        $stage = $params['stage'] ?? '';
        $orgId = $this->orgId();
        $file  = $this->request->file('csv_file');

        [$rows, $error] = $this->parseCSV($file);
        if ($error) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $error];
            $this->redirect('/admin/setup/' . $stage);
            return;
        }

        $db       = Database::getInstance();
        $imported = 0;

        switch ($stage) {
            case 'hosts':
                // Build department name → id map for this org
                $deptMap = [];
                $deptRows = $db->prepare("SELECT department_id, name FROM departments WHERE organization_id = ?");
                $deptRows->execute([$orgId]);
                foreach ($deptRows->fetchAll() as $d) {
                    $deptMap[strtolower(trim($d['name']))] = (int)$d['department_id'];
                }
                $stmt = $db->prepare("INSERT INTO hosts (organization_id, name, email, phone, department_id) VALUES (?,?,?,?,?)");
                foreach ($rows as $i => $row) {
                    if ($i === 0 && $this->isHeader($row, ['name'])) continue;
                    $name = trim($row[0] ?? '');
                    if (!$name) continue;
                    $deptName = strtolower(trim($row[3] ?? ''));
                    $deptId   = $deptMap[$deptName] ?? null;
                    $stmt->execute([$orgId, $name, trim($row[1] ?? '') ?: null, trim($row[2] ?? '') ?: null, $deptId]);
                    $imported++;
                }
                break;

            case 'reasons':
                $order = (int)$db->query("SELECT COALESCE(MAX(sort_order),0) FROM visit_reasons WHERE organization_id = {$orgId}")->fetchColumn();
                $stmt  = $db->prepare("INSERT INTO visit_reasons (organization_id, label, sort_order) VALUES (?,?,?)");
                foreach ($rows as $i => $row) {
                    if ($i === 0 && $this->isHeader($row, ['label', 'reason'])) continue;
                    $label = trim($row[0] ?? '');
                    if (!$label) continue;
                    $stmt->execute([$orgId, $label, ++$order]);
                    $imported++;
                }
                break;

            case 'fields':
                $order = (int)$db->query("SELECT COALESCE(MAX(sort_order),0) FROM custom_fields WHERE organization_id = {$orgId}")->fetchColumn();
                $stmt  = $db->prepare("INSERT INTO custom_fields (organization_id, label, field_type, required, sort_order) VALUES (?,?,?,?,?)");
                foreach ($rows as $i => $row) {
                    if ($i === 0 && $this->isHeader($row, ['label', 'field'])) continue;
                    $label = trim($row[0] ?? '');
                    if (!$label) continue;
                    $type     = in_array(trim($row[1] ?? ''), ['text','textarea','select','checkbox','date']) ? trim($row[1]) : 'text';
                    $required = strtolower(trim($row[2] ?? '')) === 'yes' ? 1 : 0;
                    $stmt->execute([$orgId, $label, $type, $required, ++$order]);
                    $imported++;
                }
                break;
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => "{$imported} record(s) imported successfully."];
        $this->redirect('/admin/setup/' . $stage);
    }

    // ── CSV template download ─────────────────────────────────────

    public function downloadTemplate(array $params): void
    {
        $this->requireAuth();
        $stage = $params['stage'] ?? '';

        // Hosts template is dynamic — pulls real departments from the DB
        if ($stage === 'hosts') {
            $orgId = $this->orgId();
            $db    = Database::getInstance();
            $depts = $db->prepare("SELECT name FROM departments WHERE organization_id = ? AND active = 1 ORDER BY sort_order, name");
            $depts->execute([$orgId]);
            $deptNames = array_column($depts->fetchAll(), 'name');

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="hosts-template.csv"');
            header('Cache-Control: no-cache');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name', 'email', 'phone', 'department']);
            if (!empty($deptNames)) {
                foreach ($deptNames as $dept) {
                    fputcsv($out, ['Example Host', 'host@example.com', '555-0100', $dept]);
                }
            } else {
                fputcsv($out, ['Front Desk', 'frontdesk@example.com', '555-0100', '']);
                fputcsv($out, ['HR Manager', 'hr@example.com', '555-0200', '']);
            }
            fclose($out);
            exit;
        }

        $templates = [
            'departments' => [
                'filename' => 'departments-template.csv',
                'rows'     => [
                    ['name', 'description'],
                    ['Human Resources', 'HR and employee relations'],
                    ['Information Technology', 'IT support and infrastructure'],
                    ['Reception', 'Front desk and visitor management'],
                    ['Finance', 'Accounting and payroll'],
                    ['Operations', ''],
                ],
            ],
            'reasons' => [
                'filename' => 'visit-reasons-template.csv',
                'rows'     => [
                    ['label'],
                    ['Appointment'],
                    ['Delivery'],
                    ['Interview'],
                    ['General Inquiry'],
                    ['Vendor Visit'],
                    ['Maintenance'],
                ],
            ],
            'fields' => [
                'filename' => 'custom-fields-template.csv',
                'rows'     => [
                    ['label', 'field_type', 'required'],
                    ['Company Name', 'text', 'no'],
                    ['Badge Number', 'text', 'yes'],
                    ['Vehicle Plate', 'text', 'no'],
                    ['Purpose Details', 'textarea', 'no'],
                    ['Agree to Terms', 'checkbox', 'yes'],
                ],
            ],
            'users' => [
                'filename' => 'users-template.csv',
                'rows'     => [
                    ['name', 'email', 'role', 'auth_provider', 'password'],
                    ['Jane Admin', 'jane@example.com', 'org_admin', 'local', 'ChangeMe123'],
                    ['John Staff', 'john@example.com', 'staff', 'local', 'ChangeMe123'],
                    ['LDAP User', 'ldapuser@domain.com', 'staff', 'ldap', ''],
                    ['Google User', 'user@gmail.com', 'staff', 'google', ''],
                ],
            ],
            'visitors' => [
                'filename' => 'visitors-template.csv',
                'rows'     => [
                    ['first_name', 'last_name', 'phone', 'email'],
                    ['Jane', 'Doe', '555-1234', 'jane.doe@example.com'],
                    ['John', 'Smith', '555-5678', 'john.smith@example.com'],
                    ['Maria', 'Garcia', '555-9012', ''],
                ],
            ],
        ];

        if (!isset($templates[$stage])) {
            http_response_code(404);
            return;
        }

        $tpl = $templates[$stage];
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $tpl['filename'] . '"');
        header('Cache-Control: no-cache');

        $out = fopen('php://output', 'w');
        foreach ($tpl['rows'] as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    // ── Stage definitions ─────────────────────────────────────────

    private function getStages(): array
    {
        $orgId = $this->orgId();
        $db    = Database::getInstance();

        $org         = $db->query("SELECT name, timezone FROM organizations WHERE organization_id = {$orgId}")->fetch();
        $deptCount   = (int)$db->query("SELECT COUNT(*) FROM departments WHERE organization_id = {$orgId}")->fetchColumn();
        $hostCount   = (int)$db->query("SELECT COUNT(*) FROM hosts WHERE organization_id = {$orgId}")->fetchColumn();
        $reasonCount = (int)$db->query("SELECT COUNT(*) FROM visit_reasons WHERE organization_id = {$orgId}")->fetchColumn();
        $fieldCount  = (int)$db->query("SELECT COUNT(*) FROM custom_fields WHERE organization_id = {$orgId}")->fetchColumn();
        $userCount   = (int)$db->query("SELECT COUNT(*) FROM users WHERE organization_id = {$orgId}")->fetchColumn();
        $notifCount  = (int)$db->query("SELECT COUNT(*) FROM notification_rules WHERE organization_id = {$orgId}")->fetchColumn();
        $orgSettings = Auth::getOrgSettings($orgId);
        $authEnabled = $orgSettings['auth_providers'] ?? ['local'];

        return [
            'organization' => [
                'label'    => 'Organization',
                'desc'     => 'Set your organization name and timezone.',
                'icon'     => '🏢',
                'done'     => !empty($org['name']) && $org['name'] !== 'My Organization',
                'optional' => false,
            ],
            'departments' => [
                'label'    => 'Departments',
                'desc'     => 'Groups or divisions hosts belong to.',
                'icon'     => '🏗️',
                'done'     => $deptCount > 0,
                'count'    => $deptCount,
                'optional' => true,
            ],
            'hosts' => [
                'label'    => 'Hosts',
                'desc'     => 'People that visitors come to see.',
                'icon'     => '👤',
                'done'     => $hostCount > 0,
                'count'    => $hostCount,
                'optional' => false,
            ],
            'reasons' => [
                'label'    => 'Visit Reasons',
                'desc'     => 'Why visitors are coming.',
                'icon'     => '📋',
                'done'     => $reasonCount > 0,
                'count'    => $reasonCount,
                'optional' => false,
            ],
            'fields' => [
                'label'    => 'Custom Fields',
                'desc'     => 'Extra fields on the check-in form.',
                'icon'     => '📝',
                'done'     => $fieldCount > 0,
                'count'    => $fieldCount,
                'optional' => true,
            ],
            'kiosk' => [
                'label'    => 'Kiosk Fields',
                'desc'     => 'Choose which fields visitors see on the check-in form.',
                'icon'     => '🖥️',
                'done'     => isset($orgSettings['kiosk_fields']),
                'optional' => true,
            ],
            'auth' => [
                'label'    => 'Authentication',
                'desc'     => 'How staff sign in: local, LDAP, Google, or Microsoft.',
                'icon'     => '🔑',
                'done'     => count($authEnabled) > 1 || !empty($orgSettings['google_client_id'])
                           || !empty($orgSettings['ldap_host']) || !empty($orgSettings['microsoft_client_id']),
                'optional' => true,
            ],
            'users' => [
                'label'    => 'Users',
                'desc'     => 'Staff accounts that can access the admin panel.',
                'icon'     => '👥',
                'done'     => $userCount > 1, // >1 because install creates the first admin
                'count'    => $userCount,
                'optional' => true,
            ],
            'notifications' => [
                'label'    => 'Notifications',
                'desc'     => 'Alert hosts when their visitor arrives.',
                'icon'     => '🔔',
                'done'     => $notifCount > 0,
                'count'    => $notifCount,
                'optional' => true,
            ],
            'test' => [
                'label'    => 'Test Check-In',
                'desc'     => 'Verify everything works before going live.',
                'icon'     => '✅',
                'done'     => false,
                'optional' => true,
            ],
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function parseCSV(?array $file): array
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return [[], 'No file uploaded or upload error.'];
        }
        $mime    = mime_content_type($file['tmp_name']);
        $allowed = ['text/plain', 'text/csv', 'application/csv', 'application/octet-stream'];
        if (!in_array($mime, $allowed, true)) {
            return [[], 'Invalid file type. Please upload a CSV file.'];
        }
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) return [[], 'Could not read uploaded file.'];
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);
        return empty($rows) ? [[], 'File is empty.'] : [$rows, null];
    }

    private function isHeader(array $row, array $keywords): bool
    {
        $first = strtolower(trim($row[0] ?? ''));
        foreach ($keywords as $kw) {
            if (str_contains($first, $kw)) return true;
        }
        return false;
    }

    private function syncAppConfig(string $key, string $value): void
    {
        $file    = BASE_PATH . '/config/app.php';
        $content = file_get_contents($file);
        $content = preg_replace(
            "/('" . preg_quote($key, '/') . "'\s*=>\s*(?:getenv\([^)]+\)\s*\?:\s*)?)'[^']*'/",
            "$1'" . addslashes($value) . "'",
            $content
        );
        file_put_contents($file, $content);
    }
}
