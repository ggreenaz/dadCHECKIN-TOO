<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\VisitModel;
use App\Models\OrganizationModel;

class BoardController extends Controller
{
    private function getOrg(): ?array
    {
        $cfg = require BASE_PATH . '/config/app.php';
        return (new OrganizationModel())->findBySlug($cfg['org_slug']);
    }

    public function index(array $params): void
    {
        $org = $this->getOrg();
        if (!$org) { http_response_code(500); die('Organization not configured.'); }

        $visits = (new VisitModel())->getActiveVisits((int)$org['organization_id']);

        $this->view->render('board/index', [
            'title'  => 'Live Visitor Board',
            'org'    => $org,
            'visits' => $visits,
        ]);
    }

    public function poll(array $params): void
    {
        $org = $this->getOrg();
        if (!$org) { $this->json(['error' => 'Not configured'], 500); return; }

        $visits = (new VisitModel())->getActiveVisits((int)$org['organization_id']);
        $this->json(['visits' => $visits, 'ts' => time()]);
    }
}
