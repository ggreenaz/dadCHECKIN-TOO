<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class SettingsController extends Controller
{
    public function index(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $db    = Database::getInstance();
        $stmt  = $db->prepare("SELECT * FROM organizations WHERE organization_id = ?");
        $stmt->execute([$orgId]);
        $org      = $stmt->fetch();
        $settings = json_decode($org['settings'] ?? '{}', true) ?: [];
        $demoData = $settings['demo_data'] ?? null;

        $this->view->render('admin/settings', [
            'title'        => 'Organization Settings',
            'helpSlug'     => 'settings',
            'org'          => $org,
            'hasDemoData'  => !empty($demoData),
            'demoSeededAt' => $demoData['seeded_at'] ?? '',
            'flash'        => $this->flash(),
        ]);
    }

    public function expungeDemo(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $db    = Database::getInstance();

        $row = $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
        $row->execute([$orgId]);
        $settings = json_decode($row->fetchColumn() ?? '{}', true) ?: [];
        $demo     = $settings['demo_data'] ?? null;

        if (!$demo) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'No demo data found.'];
            $this->redirect('/admin/settings');
            return;
        }

        // Delete visits
        if (!empty($demo['visit_ids'])) {
            $ph = implode(',', array_fill(0, count($demo['visit_ids']), '?'));
            $db->prepare("DELETE FROM visits WHERE visit_id IN ({$ph}) AND organization_id = ?")
               ->execute(array_merge($demo['visit_ids'], [$orgId]));
        }
        // Delete visitors (only if they have no other visits)
        if (!empty($demo['visitor_ids'])) {
            foreach ($demo['visitor_ids'] as $vid) {
                $chk = $db->prepare("SELECT COUNT(*) FROM visits WHERE visitor_id = ?");
                $chk->execute([$vid]);
                if ((int)$chk->fetchColumn() === 0) {
                    $db->prepare("DELETE FROM visitors WHERE visitor_id = ?")->execute([$vid]);
                }
            }
        }
        // Delete hosts
        if (!empty($demo['host_ids'])) {
            $ph = implode(',', array_fill(0, count($demo['host_ids']), '?'));
            $db->prepare("DELETE FROM hosts WHERE host_id IN ({$ph}) AND organization_id = ?")
               ->execute(array_merge($demo['host_ids'], [$orgId]));
        }
        // Delete visit reasons
        if (!empty($demo['reason_ids'])) {
            $ph = implode(',', array_fill(0, count($demo['reason_ids']), '?'));
            $db->prepare("DELETE FROM visit_reasons WHERE reason_id IN ({$ph}) AND organization_id = ?")
               ->execute(array_merge($demo['reason_ids'], [$orgId]));
        }
        // Delete departments
        if (!empty($demo['department_ids'])) {
            $ph = implode(',', array_fill(0, count($demo['department_ids']), '?'));
            $db->prepare("DELETE FROM departments WHERE department_id IN ({$ph}) AND organization_id = ?")
               ->execute(array_merge($demo['department_ids'], [$orgId]));
        }

        unset($settings['demo_data']);
        $db->prepare("UPDATE organizations SET settings = ? WHERE organization_id = ?")
           ->execute([json_encode($settings), $orgId]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Demo data expunged. Your installation is now clean.'];
        $this->redirect('/admin/settings');
    }

    public function save(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId    = $this->orgId();
        $name     = trim($this->request->input('org_name', ''));
        $timezone = $this->request->input('timezone', 'UTC');

        if (!$name) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Organization name cannot be blank.'];
            $this->redirect('/admin/settings');
            return;
        }

        $db = Database::getInstance();
        $db->prepare("UPDATE organizations SET name = ?, timezone = ? WHERE organization_id = ?")
           ->execute([$name, $timezone, $orgId]);

        // Keep app.php timezone in sync
        $appFile    = BASE_PATH . '/config/app.php';
        $content    = file_get_contents($appFile);
        $content    = preg_replace(
            "/('timezone'\s*=>\s*getenv\('APP_TIMEZONE'\)\s*\?:\s*)'[^']*'/",
            "$1'" . addslashes($timezone) . "'",
            $content
        );
        file_put_contents($appFile, $content);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Settings saved.'];
        $this->redirect('/admin/settings');
    }

    public function theme(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $db    = Database::getInstance();
        $row   = $db->prepare("SELECT * FROM organizations WHERE organization_id = ?");
        $row->execute([$orgId]);
        $org   = $row->fetch();

        $settings = json_decode($org['settings'] ?? '{}', true) ?: [];

        $this->view->render('admin/theme', [
            'title' => 'Theme & Appearance',
            'org'   => $org,
            'theme' => $settings['theme'] ?? [],
            'flash' => $this->flash(),
        ]);
    }

    public function saveTheme(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId  = $this->orgId();
        $db     = Database::getInstance();
        $row    = $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
        $row->execute([$orgId]);
        $existing = json_decode($row->fetchColumn() ?? '{}', true) ?: [];

        $theme = [
            'preset'       => preg_replace('/[^a-z]/', '', $this->request->input('preset', 'default')),
            'primary'      => $this->sanitizeHex($this->request->input('primary',     '#0073b1')),
            'header_bg'    => $this->sanitizeHex($this->request->input('header_bg',   '#0c2340')),
            'bg'           => $this->sanitizeHex($this->request->input('bg',           '#f1f5f9')),
            'header_text'  => $this->sanitizeHex($this->request->input('header_text', '#ffffff')),
            'use_org_name' => (bool)$this->request->input('use_org_name', false),
        ];

        // Handle logo upload
        $uploadDir = BASE_PATH . '/public/uploads/logos';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $removeLogo = (int)$this->request->input('remove_logo', 0);
        if ($removeLogo && !empty($existing['theme']['logo'])) {
            $old = $uploadDir . '/' . $existing['theme']['logo'];
            if (file_exists($old)) unlink($old);
            $theme['logo'] = '';
        } else {
            $theme['logo'] = $existing['theme']['logo'] ?? '';
        }

        $file = $_FILES['logo'] ?? null;
        if ($file && $file['error'] === UPLOAD_ERR_OK && $file['size'] <= 2 * 1024 * 1024) {
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed  = ['png', 'jpg', 'jpeg', 'gif', 'svg'];
            if (in_array($ext, $allowed)) {
                // Remove old logo
                if (!empty($existing['theme']['logo'])) {
                    $old = $uploadDir . '/' . $existing['theme']['logo'];
                    if (file_exists($old)) unlink($old);
                }
                $filename = 'logo_' . $orgId . '_' . time() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename);
                $theme['logo'] = $filename;
            }
        }

        $existing['theme'] = $theme;
        $db->prepare("UPDATE organizations SET settings = ? WHERE organization_id = ?")
           ->execute([json_encode($existing), $orgId]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Theme saved.'];
        $this->redirect('/admin/settings/theme');
    }

    private function sanitizeHex(string $value): string
    {
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) return $value;
        return '#000000';
    }

    public function saveAutoCheckout(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $db    = Database::getInstance();

        // Load existing settings JSON so we don't overwrite other keys
        $row  = $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
        $row->execute([$orgId]);
        $existing = json_decode($row->fetchColumn() ?? '{}', true) ?: [];

        $existing['auto_checkout'] = [
            'enabled'       => (bool)$this->request->input('ac_enabled', false),
            'checkout_time' => preg_replace('/[^0-9:]/', '', $this->request->input('ac_time', '17:00')),
            'max_open_hours'=> min(24, max(1, (int)$this->request->input('ac_max_hours', 10))),
            'status'        => in_array($this->request->input('ac_status'), ['completed','auto_completed'])
                                    ? $this->request->input('ac_status')
                                    : 'auto_completed',
        ];

        $db->prepare("UPDATE organizations SET settings = ? WHERE organization_id = ?")
           ->execute([json_encode($existing), $orgId]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Auto-checkout settings saved.'];
        $this->redirect('/admin/settings');
    }
}
