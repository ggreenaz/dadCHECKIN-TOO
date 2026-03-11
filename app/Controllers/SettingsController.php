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
        $org   = $db->prepare("SELECT * FROM organizations WHERE organization_id = ?");
        $org->execute([$orgId]);
        $org = $org->fetch();

        $this->view->render('admin/settings', [
            'title'    => 'Organization Settings',
            'helpSlug' => 'settings',
            'org'      => $org,
            'flash'    => $this->flash(),
        ]);
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
