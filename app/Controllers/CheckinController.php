<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\VisitorModel;
use App\Models\VisitModel;
use App\Models\HostModel;
use App\Models\VisitReasonModel;
use App\Models\OrganizationModel;
use App\Auth\Providers\LdapProvider;

class CheckinController extends Controller
{
    private function getOrg(): ?array
    {
        $cfg = require BASE_PATH . '/config/app.php';
        return (new OrganizationModel())->findBySlug($cfg['org_slug']);
    }

    private function getOrgSettings(int $orgId): array
    {
        return Auth::getOrgSettings($orgId);
    }

    private function ldapEnabled(array $settings): bool
    {
        $providers = $settings['auth_providers'] ?? ['local'];
        return in_array('ldap', $providers) && !empty($settings['ldap_host_url']);
    }

    /**
     * Find an open visit for a visitor identified by email.
     * Returns the visit row or null.
     */
    private function findOpenVisit(string $email, int $orgId): ?array
    {
        if (!$email) return null;
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT vi.*, h.name AS host_name, vr.label AS reason_label
             FROM visits vi
             LEFT JOIN visitors v  ON vi.visitor_id = v.visitor_id
             LEFT JOIN hosts h     ON vi.host_id    = h.host_id
             LEFT JOIN visit_reasons vr ON vi.reason_id = vr.reason_id
             WHERE v.email = ?
               AND vi.organization_id = ?
               AND vi.check_out_time IS NULL
               AND vi.status NOT IN ('completed','no_show','cancelled')
             ORDER BY vi.check_in_time DESC
             LIMIT 1"
        );
        $stmt->execute([$email, $orgId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Kiosk entry point ────────────────────────────────────────

    public function index(array $params): void
    {
        $org = $this->getOrg();
        if (!$org) { http_response_code(500); die('Organization not configured.'); }

        $orgId    = (int)$org['organization_id'];
        $settings = $this->getOrgSettings($orgId);
        $isStaff  = Auth::check();

        // If staff is already logged in, use their identity as the kiosk visitor
        if ($isStaff && empty($_SESSION['kiosk_visitor'])) {
            $_SESSION['kiosk_visitor'] = [
                'name'     => $_SESSION['user_name']  ?? '',
                'email'    => $_SESSION['user_email'] ?? '',
                'username' => $_SESSION['user_email'] ?? '',
                'is_staff' => true,
            ];
        }

        // If LDAP is configured and visitor hasn't authenticated yet, show auth form
        if ($this->ldapEnabled($settings) && empty($_SESSION['kiosk_visitor'])) {
            $this->view->render('checkin/auth', [
                'title'    => 'Check-In',
                'org'      => $org,
                'settings' => $settings,
                'flash'    => $this->flash(),
            ]);
            return;
        }

        $kioskVisitor = $_SESSION['kiosk_visitor'] ?? null;

        // ── State check: are they already checked in? ────────────
        if ($kioskVisitor) {
            $openVisit = $this->findOpenVisit($kioskVisitor['email'] ?? '', $orgId);
            if ($openVisit) {
                $this->view->render('checkin/checkout', [
                    'title'         => 'Check Out',
                    'org'           => $org,
                    'visit'         => $openVisit,
                    'kiosk_visitor' => $kioskVisitor,
                    'flash'         => $this->flash(),
                    'is_staff'      => $isStaff,
                ]);
                return;
            }
        }

        // ── Not checked in — show check-in form ──────────────────
        $hosts   = (new HostModel())->getForOrg($orgId);
        $reasons = (new VisitReasonModel())->getForOrg($orgId);

        $this->view->render('checkin/index', [
            'title'         => 'Visitor Check-In',
            'org'           => $org,
            'hosts'         => $hosts,
            'reasons'       => $reasons,
            'flash'         => $this->flash(),
            'kiosk_visitor' => $kioskVisitor,
            'ldap_mode'     => $this->ldapEnabled($settings),
            'kiosk_fields'  => $settings['kiosk_fields'] ?? [],
            'is_staff'      => $isStaff,
        ]);
    }

    // ── Kiosk LDAP authentication ────────────────────────────────

    public function kioskAuth(array $params): void
    {
        $org = $this->getOrg();
        if (!$org) { http_response_code(500); return; }

        $orgId    = (int)$org['organization_id'];
        $settings = $this->getOrgSettings($orgId);

        if (!$this->ldapEnabled($settings)) {
            $this->redirect('/checkin');
            return;
        }

        $username = $this->request->clean('username');
        $password = $this->request->input('password', '');

        $info = (new LdapProvider())->verifyAndGetInfo(
            ['username' => $username, 'password' => $password],
            $settings
        );

        if (!$info) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid credentials. Please try again.'];
            $this->redirect('/checkin');
            return;
        }

        $_SESSION['kiosk_visitor'] = [
            'name'     => $info['name'],
            'email'    => $info['email'],
            'username' => $info['username'],
        ];

        $this->redirect('/checkin');
    }

    // ── Check-in form submit ─────────────────────────────────────

    public function store(array $params): void
    {
        $org = $this->getOrg();
        if (!$org) { http_response_code(500); return; }
        $orgId    = (int)$org['organization_id'];
        $settings = $this->getOrgSettings($orgId);

        $kioskVisitor = $_SESSION['kiosk_visitor'] ?? null;

        if ($kioskVisitor) {
            $fullName  = $kioskVisitor['name'] ?? '';
            $parts     = explode(' ', $fullName, 2);
            $firstName = $parts[0];
            $lastName  = $parts[1] ?? '';
            $email     = $kioskVisitor['email'] ?? null;
        } else {
            $firstName = $this->request->clean('first_name');
            $lastName  = $this->request->clean('last_name');
            $email     = $this->request->clean('email') ?: null;
        }

        $phone    = $this->request->clean('phone');
        $hostId   = (int)$this->request->input('host_id');
        $reasonId = (int)$this->request->input('reason_id');
        $notes    = $this->request->clean('notes');

        // Validate required fields based on kiosk config
        $kf = $settings['kiosk_fields'] ?? [];
        if (!$kioskVisitor && !empty($kf['last_name']['required']) && !$this->request->clean('last_name')) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Last name is required.'];
            $this->redirect('/checkin');
            return;
        }
        if (!empty($kf['phone']['required']) && !$phone) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Phone number is required.'];
            $this->redirect('/checkin');
            return;
        }
        if (!$kioskVisitor && !empty($kf['email']['required']) && !$this->request->clean('email')) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Email is required.'];
            $this->redirect('/checkin');
            return;
        }

        $visitorModel = new VisitorModel();
        ['visitor' => $visitor] = $visitorModel->findOrCreate([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'phone'      => $phone ?: null,
            'email'      => $email ?: null,
        ]);

        (new VisitModel())->insert([
            'visitor_id'      => $visitor['visitor_id'],
            'organization_id' => $orgId,
            'host_id'         => $hostId ?: null,
            'reason_id'       => $reasonId ?: null,
            'status'          => 'checked_in',
            'notes'           => $notes ?: null,
        ]);

        $wasStaff = !empty($kioskVisitor['is_staff']);

        if ($wasStaff) {
            unset($_SESSION['kiosk_visitor']);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'You are checked in. Have a great visit!'];
            $this->redirect('/admin');
            return;
        }

        // Keep kiosk_visitor in session so returning to /checkin shows checkout state
        // Show thank-you screen with countdown before returning to kiosk
        $_SESSION['checkin_success_name'] = $firstName;
        $this->redirect('/checkin/success');
    }

    // ── Kiosk check-out (state-aware) ────────────────────────────

    public function success(array $params): void
    {
        $firstName = $_SESSION['checkin_success_name'] ?? '';
        unset($_SESSION['checkin_success_name']);
        $this->view->render('checkin/success', [
            'title'     => 'Checked In!',
            'firstName' => $firstName,
        ]);
    }

    public function kioskCheckout(array $params): void
    {
        $org = $this->getOrg();
        if (!$org) { http_response_code(500); return; }
        $orgId = (int)$org['organization_id'];

        $kioskVisitor = $_SESSION['kiosk_visitor'] ?? null;
        if (!$kioskVisitor) {
            $this->redirect('/checkin');
            return;
        }

        $email    = $kioskVisitor['email'] ?? '';
        $wasStaff = !empty($kioskVisitor['is_staff']);

        // Find and close the open visit
        $openVisit = $this->findOpenVisit($email, $orgId);
        if ($openVisit) {
            Database::getInstance()->prepare(
                "UPDATE visits
                 SET check_out_time = NOW(), status = 'completed', updated_at = NOW()
                 WHERE visit_id = ?"
            )->execute([$openVisit['visit_id']]);
        }

        unset($_SESSION['kiosk_visitor']);
        $this->redirect($wasStaff ? '/admin' : '/checkin');
    }

    // ── Cancel kiosk session ─────────────────────────────────────

    public function kioskCancel(array $params): void
    {
        unset($_SESSION['kiosk_visitor']);
        $this->redirect(Auth::check() ? '/admin' : '/checkin');
    }
}
