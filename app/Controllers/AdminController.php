<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\VisitModel;
use App\Models\HostModel;
use App\Models\VisitReasonModel;
use App\Core\Database;

class AdminController extends Controller
{
    // ── Analytics ────────────────────────────────────────────────

    public function analytics(array $params): void
    {
        $this->requireAuth();
        $orgId = $this->orgId();
        $db    = Database::getInstance();

        // ── Peak hours heatmap (DOW 2-6 = Mon-Fri, hours 7-17) ──
        $stmt = $db->prepare(
            "SELECT DAYOFWEEK(check_in_time) AS dow,
                    HOUR(check_in_time)      AS hr,
                    COUNT(*)                 AS cnt
             FROM visits
             WHERE organization_id = ?
               AND status NOT IN ('cancelled')
               AND DAYOFWEEK(check_in_time) BETWEEN 2 AND 6
               AND HOUR(check_in_time) BETWEEN 7 AND 17
             GROUP BY dow, hr"
        );
        $stmt->execute([$orgId]);
        $heatRaw = $stmt->fetchAll();

        // Build a [dow][hr] => cnt lookup and find global max
        $heatMap = [];
        $heatMax = 1;
        foreach ($heatRaw as $r) {
            $heatMap[$r['dow']][$r['hr']] = (int)$r['cnt'];
            if ((int)$r['cnt'] > $heatMax) $heatMax = (int)$r['cnt'];
        }

        // ── Visit volume by day of week ──────────────────────────
        $stmt = $db->prepare(
            "SELECT DAYOFWEEK(check_in_time) AS dow, COUNT(*) AS cnt
             FROM visits
             WHERE organization_id = ? AND status NOT IN ('cancelled')
             GROUP BY dow ORDER BY dow"
        );
        $stmt->execute([$orgId]);
        $dowRaw    = $stmt->fetchAll();
        $dowCounts = array_fill(1, 7, 0);
        foreach ($dowRaw as $r) $dowCounts[(int)$r['dow']] = (int)$r['cnt'];
        $dowMax    = max($dowCounts) ?: 1;
        $dowLabels = [1=>'Sun',2=>'Mon',3=>'Tue',4=>'Wed',5=>'Thu',6=>'Fri',7=>'Sat'];

        // ── Busiest hosts ────────────────────────────────────────
        $stmt = $db->prepare(
            "SELECT h.host_id, h.name AS host_name,
                    d.name            AS dept_name,
                    COUNT(*)          AS visit_cnt,
                    ROUND(AVG(TIMESTAMPDIFF(MINUTE, v.check_in_time, v.check_out_time))) AS avg_min
             FROM visits v
             JOIN hosts h          ON v.host_id       = h.host_id
             LEFT JOIN departments d ON h.department_id = d.department_id
             WHERE v.organization_id = ? AND v.status = 'completed'
             GROUP BY h.host_id
             ORDER BY visit_cnt DESC
             LIMIT 15"
        );
        $stmt->execute([$orgId]);
        $hosts    = $stmt->fetchAll();
        $hostMax  = !empty($hosts) ? (int)$hosts[0]['visit_cnt'] : 1;

        // ── Repeat visitor stats ─────────────────────────────────
        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT visitor_id) AS total,
                    SUM(cnt > 1)               AS repeats,
                    SUM(cnt = 1)               AS first_timers,
                    MAX(cnt)                   AS max_visits
             FROM (
                 SELECT visitor_id, COUNT(*) AS cnt
                 FROM visits WHERE organization_id = ?
                 GROUP BY visitor_id
             ) t"
        );
        $stmt->execute([$orgId]);
        $repeatStats = $stmt->fetch();

        // Top frequent visitors
        $stmt = $db->prepare(
            "SELECT v.visitor_id, v.first_name, v.last_name,
                    COUNT(*)    AS visit_cnt,
                    MAX(vi.check_in_time) AS last_visit,
                    ROUND(AVG(TIMESTAMPDIFF(MINUTE, vi.check_in_time, vi.check_out_time))) AS avg_min
             FROM visits vi
             JOIN visitors v ON vi.visitor_id = v.visitor_id
             WHERE vi.organization_id = ? AND vi.status = 'completed'
             GROUP BY v.visitor_id
             ORDER BY visit_cnt DESC
             LIMIT 12"
        );
        $stmt->execute([$orgId]);
        $topVisitors    = $stmt->fetchAll();
        $topVisitorMax  = !empty($topVisitors) ? (int)$topVisitors[0]['visit_cnt'] : 1;

        // ── Reason breakdown ─────────────────────────────────────
        $stmt = $db->prepare(
            "SELECT vr.label, COUNT(*) AS cnt
             FROM visits v
             JOIN visit_reasons vr ON v.reason_id = vr.reason_id
             WHERE v.organization_id = ? AND v.status NOT IN ('cancelled')
             GROUP BY vr.reason_id ORDER BY cnt DESC"
        );
        $stmt->execute([$orgId]);
        $reasons    = $stmt->fetchAll();
        $reasonMax  = !empty($reasons) ? (int)$reasons[0]['cnt'] : 1;
        $reasonTotal= array_sum(array_column($reasons, 'cnt')) ?: 1;

        $this->view->render('admin/analytics', [
            'title'        => 'Analytics',
            'helpSlug'     => 'analytics',
            'heatMap'      => $heatMap,
            'heatMax'      => $heatMax,
            'dowCounts'    => $dowCounts,
            'dowMax'       => $dowMax,
            'dowLabels'    => $dowLabels,
            'hosts'        => $hosts,
            'hostMax'      => $hostMax,
            'repeatStats'  => $repeatStats,
            'topVisitors'  => $topVisitors,
            'topVisitorMax'=> $topVisitorMax,
            'reasons'      => $reasons,
            'reasonMax'    => $reasonMax,
            'reasonTotal'  => $reasonTotal,
            'flash'        => $this->flash(),
        ]);
    }

    // ── Docs ─────────────────────────────────────────────────────

    private const DOC_PAGES = [
        'dashboard' => ['title' => 'Dashboard',       'back' => '/admin',           'icon' => '🏠'],
        'loghub'    => ['title' => 'Log Hub',          'back' => '/logs',            'icon' => '🗂️'],
        'live'      => ['title' => 'Live Logs',        'back' => '/admin/live',      'icon' => '🟢'],
        'history'   => ['title' => 'Visit History',    'back' => '/admin/history',   'icon' => '📋'],
        'analytics' => ['title' => 'Analytics',        'back' => '/admin/analytics', 'icon' => '📊'],
        'visitor'   => ['title' => 'Visitor Profile',  'back' => '/admin/history',   'icon' => '👤'],
        'hosts'     => ['title' => 'Manage Hosts',     'back' => '/admin/hosts',     'icon' => '👥'],
        'reasons'   => ['title' => 'Visit Reasons',    'back' => '/admin/reasons',   'icon' => '📝'],
        'fields'    => ['title' => 'Custom Fields',    'back' => '/admin/fields',    'icon' => '🔧'],
        'import'    => ['title' => 'CSV Import',       'back' => '/admin/import',    'icon' => '📥'],
        'settings'  => ['title' => 'Settings',         'back' => '/admin/settings',  'icon' => '⚙️'],
        'kiosk'     => ['title' => 'Kiosk Setup',       'back' => '/admin/setup/kiosk',          'icon' => '📱'],
        'users'     => ['title' => 'System Users',      'back' => '/admin/users',                'icon' => '👤'],
        'setup'     => ['title' => 'System Setup',      'back' => '/admin/setup',                'icon' => '🛠️'],
        'auth'      => ['title' => 'Auth & LDAP Setup', 'back' => '/admin/setup/auth',           'icon' => '🔑'],
        'notifications'   => ['title' => 'Notifications',        'back' => '/admin/setup/notifications', 'icon' => '🔔'],
        'configuration'   => ['title' => 'Configuration Guide',  'back' => '/admin/settings',            'icon' => '📖'],
        'userguide'       => ['title' => 'End User Guide',        'back' => '/admin/docs/userguide',      'icon' => '📘'],
        'installguide'    => ['title' => 'Installation Guide',    'back' => '/admin/docs/installguide',   'icon' => '📋'],
    ];

    public function installGuide(array $params): void
    {
        $this->requireAuth();
        header('Content-Type: text/html; charset=UTF-8');
        include BASE_PATH . '/app/Views/admin/docs/installguide.php';
        exit;
    }

    public function userGuide(array $params): void
    {
        $this->requireAuth();
        // Serve the user guide as a standalone page (its own complete HTML document)
        header('Content-Type: text/html; charset=UTF-8');
        include BASE_PATH . '/app/Views/admin/docs/userguide.php';
        exit;
    }

    public function docsIndex(array $params): void
    {
        $this->requireAuth();
        $this->view->render('admin/docs/index', [
            'title'    => 'Help & Documentation',
            'docPages' => self::DOC_PAGES,
            'flash'    => $this->flash(),
        ]);
    }

    public function docs(array $params): void
    {
        $this->requireAuth();
        $page = preg_replace('/[^a-z0-9_-]/', '', strtolower($params['page'] ?? ''));
        $meta = self::DOC_PAGES[$page] ?? null;
        $file = BASE_PATH . '/app/Views/admin/docs/' . $page . '.php';

        if (!$meta || !file_exists($file)) {
            http_response_code(404);
            $this->view->render('admin/docs/index', [
                'title'    => 'Help & Documentation',
                'docPages' => self::DOC_PAGES,
                'flash'    => ['type' => 'error', 'message' => 'Documentation page not found.'],
            ]);
            return;
        }

        $this->view->render('admin/docs/' . $page, [
            'title'   => $meta['title'] . ' — Help',
            'docMeta' => $meta,
            'flash'   => $this->flash(),
        ]);
    }

    public function docsSearch(array $params): void
    {
        $this->requireAuth();
        $q       = trim((string)$this->request->get('q', ''));
        $results = [];

        if (strlen($q) >= 2) {
            foreach (self::DOC_PAGES as $slug => $meta) {
                $file = BASE_PATH . '/app/Views/admin/docs/' . $slug . '.php';
                if (!file_exists($file)) continue;

                // Strip PHP blocks, then HTML tags, to get searchable plain text
                $raw  = file_get_contents($file);
                $raw  = preg_replace('/<\?php.*?\?>/s', ' ', $raw);
                $text = strip_tags($raw);
                $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $text = preg_replace('/\s+/', ' ', trim($text));

                if (stripos($text, $q) === false) continue;

                // Collect up to 3 context snippets
                $snippets = [];
                $offset   = 0;
                while (count($snippets) < 3 && ($pos = stripos($text, $q, $offset)) !== false) {
                    $start   = max(0, $pos - 80);
                    $excerpt = substr($text, $start, 220);
                    if ($start > 0)                     $excerpt = '…' . ltrim($excerpt);
                    if ($start + 220 < strlen($text))   $excerpt .= '…';

                    $excerpt    = htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8');
                    $qEscaped   = preg_quote(htmlspecialchars($q, ENT_QUOTES, 'UTF-8'), '/');
                    $excerpt    = preg_replace('/(' . $qEscaped . ')/iu', '<mark>$1</mark>', $excerpt);
                    $snippets[] = $excerpt;
                    $offset     = $pos + strlen($q);
                }

                $results[] = [
                    'slug'     => $slug,
                    'title'    => $meta['title'],
                    'icon'     => $meta['icon'],
                    'back'     => $meta['back'],
                    'snippets' => $snippets,
                ];
            }
        }

        $this->view->render('admin/docs/search', [
            'title'    => $q ? 'Search: ' . $q . ' — Docs' : 'Search Docs',
            'q'        => $q,
            'results'  => $results,
            'docPages' => self::DOC_PAGES,
            'flash'    => $this->flash(),
        ]);
    }

    // ── Log Hub ──────────────────────────────────────────────────

    public function logHub(array $params): void
    {
        $this->requireAuth();
        $orgId = $this->orgId();
        $db    = Database::getInstance();
        $today = date('Y-m-d');

        // Today's stats
        $stmt = $db->prepare(
            "SELECT
                COUNT(*)                                                            AS total,
                SUM(status = 'checked_in')                                          AS active,
                SUM(status = 'completed')                                           AS completed,
                SUM(status = 'no_show')                                             AS no_show,
                SUM(status = 'cancelled')                                           AS cancelled,
                SUM(status = 'checked_in'
                    AND TIMESTAMPDIFF(MINUTE, check_in_time, NOW()) >= 120)         AS extended
             FROM visits
             WHERE organization_id = ? AND DATE(check_in_time) = ?"
        );
        $stmt->execute([$orgId, $today]);
        $stats = $stmt->fetch();

        // Active visitors with elapsed time
        $activeVisits = (new VisitModel())->getActiveVisits($orgId);

        // Recent completed visits (last 10)
        $stmt = $db->prepare(
            "SELECT vi.visit_id, v.visitor_id, v.first_name, v.last_name,
                    h.name AS host_name, vr.label AS reason_label,
                    vi.check_in_time, vi.check_out_time,
                    TIMESTAMPDIFF(MINUTE, vi.check_in_time, vi.check_out_time) AS duration_min
             FROM visits vi
             JOIN visitors v       ON vi.visitor_id = v.visitor_id
             LEFT JOIN hosts h     ON vi.host_id    = h.host_id
             LEFT JOIN visit_reasons vr ON vi.reason_id = vr.reason_id
             WHERE vi.organization_id = ?
               AND vi.status = 'completed'
               AND DATE(vi.check_in_time) = ?
             ORDER BY vi.check_out_time DESC
             LIMIT 8"
        );
        $stmt->execute([$orgId, $today]);
        $recentCompleted = $stmt->fetchAll();

        $this->view->render('admin/loghub', [
            'title'          => 'Log Hub',
            'helpSlug'       => 'loghub',
            'stats'          => $stats,
            'activeVisits'   => $activeVisits,
            'recentCompleted'=> $recentCompleted,
            'flash'          => $this->flash(),
        ]);
    }

    // ── Dashboard ────────────────────────────────────────────────

    public function dashboard(array $params): void
    {
        $this->requireAuth();
        $orgId = $this->orgId();
        $db    = Database::getInstance();
        $today = date('Y-m-d');

        // Today's full stat block
        $stmt = $db->prepare(
            "SELECT
                COUNT(*)                                                           AS total,
                SUM(status = 'completed')                                          AS completed,
                SUM(status = 'no_show')                                            AS no_show,
                SUM(status = 'checked_in')                                         AS active,
                SUM(status = 'checked_in'
                    AND TIMESTAMPDIFF(MINUTE, check_in_time, NOW()) >= 120)        AS extended
             FROM visits WHERE organization_id = ? AND DATE(check_in_time) = ?"
        );
        $stmt->execute([$orgId, $today]);
        $todayStats = $stmt->fetch();

        // All-time total
        $allTime = (int)$db->prepare("SELECT COUNT(*) FROM visits WHERE organization_id = ?")->execute([$orgId])
            ? $db->query("SELECT COUNT(*) FROM visits WHERE organization_id = {$orgId}")->fetchColumn()
            : 0;

        // Active visitors with elapsed time bars
        $activeVisits = (new VisitModel())->getActiveVisits($orgId);

        // Last 6 check-outs today
        $stmt = $db->prepare(
            "SELECT v.visitor_id, v.first_name, v.last_name,
                    h.name AS host_name, vr.label AS reason_label,
                    vi.check_out_time,
                    TIMESTAMPDIFF(MINUTE, vi.check_in_time, vi.check_out_time) AS duration_min
             FROM visits vi
             JOIN visitors v           ON vi.visitor_id = v.visitor_id
             LEFT JOIN hosts h         ON vi.host_id    = h.host_id
             LEFT JOIN visit_reasons vr ON vi.reason_id = vr.reason_id
             WHERE vi.organization_id = ? AND vi.status = 'completed'
               AND DATE(vi.check_in_time) = ?
             ORDER BY vi.check_out_time DESC LIMIT 6"
        );
        $stmt->execute([$orgId, $today]);
        $recentOut = $stmt->fetchAll();

        // Top reason today
        $stmt = $db->prepare(
            "SELECT vr.label, COUNT(*) AS cnt
             FROM visits vi
             LEFT JOIN visit_reasons vr ON vi.reason_id = vr.reason_id
             WHERE vi.organization_id = ? AND DATE(vi.check_in_time) = ?
             GROUP BY vi.reason_id ORDER BY cnt DESC LIMIT 1"
        );
        $stmt->execute([$orgId, $today]);
        $topReason = $stmt->fetch();

        // Host + reason counts for module tiles
        $hostCount   = (int)$db->query("SELECT COUNT(*) FROM hosts WHERE organization_id = {$orgId} AND active = 1")->fetchColumn();
        $reasonCount = (int)$db->query("SELECT COUNT(*) FROM visit_reasons WHERE organization_id = {$orgId} AND active = 1")->fetchColumn();

        // 7-day sparkline (visits per day)
        $stmt = $db->prepare(
            "SELECT DATE(check_in_time) AS day, COUNT(*) AS cnt
             FROM visits WHERE organization_id = ?
               AND check_in_time >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY day ORDER BY day ASC"
        );
        $stmt->execute([$orgId]);
        $sparkRows = $stmt->fetchAll();
        $sparkline = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $sparkline[$d] = 0;
        }
        foreach ($sparkRows as $r) {
            $sparkline[$r['day']] = (int)$r['cnt'];
        }

        $progress = $this->getSetupProgress($orgId);

        $this->view->render('admin/dashboard', [
            'title'        => 'Dashboard',
            'helpSlug'     => 'dashboard',
            'todayStats'   => $todayStats,
            'allTime'      => $allTime,
            'activeVisits' => $activeVisits,
            'recentOut'    => $recentOut,
            'topReason'    => $topReason,
            'hostCount'    => $hostCount,
            'reasonCount'  => $reasonCount,
            'sparkline'    => $sparkline,
            'progress'     => $progress,
            'flash'        => $this->flash(),
        ]);
    }

    private function getSetupProgress(int $orgId): array
    {
        $db = Database::getInstance();

        $hostCount   = (int)$db->prepare("SELECT COUNT(*) FROM hosts WHERE organization_id = ? AND active = 1")->execute([$orgId]) ? $db->query("SELECT COUNT(*) FROM hosts WHERE organization_id = {$orgId} AND active = 1")->fetchColumn() : 0;
        $reasonCount = (int)$db->query("SELECT COUNT(*) FROM visit_reasons WHERE organization_id = {$orgId} AND active = 1")->fetchColumn();
        $fieldCount  = (int)$db->query("SELECT COUNT(*) FROM custom_fields WHERE organization_id = {$orgId} AND active = 1")->fetchColumn();
        $org         = $db->query("SELECT name, timezone FROM organizations WHERE organization_id = {$orgId}")->fetch();

        $items = [
            [
                'label'    => 'Organization details',
                'done'     => $org && $org['name'] !== 'My Organization',
                'action'   => '/admin/settings',
                'hint'     => 'Set your organization name and timezone',
            ],
            [
                'label'    => 'Hosts added',
                'done'     => $hostCount > 0,
                'action'   => '/admin/hosts',
                'hint'     => $hostCount > 0 ? "{$hostCount} host(s) configured" : 'Add people visitors come to see',
            ],
            [
                'label'    => 'Visit reasons added',
                'done'     => $reasonCount > 0,
                'action'   => '/admin/reasons',
                'hint'     => $reasonCount > 0 ? "{$reasonCount} reason(s) configured" : 'Add reasons for visiting',
            ],
            [
                'label'    => 'Custom fields',
                'done'     => $fieldCount > 0,
                'action'   => '/admin/fields',
                'hint'     => $fieldCount > 0 ? "{$fieldCount} field(s) configured" : 'Optional extra fields on check-in form',
                'optional' => true,
            ],
            [
                'label'    => 'CSV data imported',
                'done'     => false,
                'action'   => '/admin/import',
                'hint'     => 'Bulk import hosts, reasons, or pre-registered visitors',
                'optional' => true,
            ],
        ];

        $required = array_filter($items, fn($i) => empty($i['optional']));
        $done     = array_filter($required, fn($i) => $i['done']);
        $pct      = count($required) > 0 ? round(count($done) / count($required) * 100) : 100;

        return ['items' => $items, 'percent' => $pct];
    }

    // ── Live Logs ────────────────────────────────────────────────

    public function live(array $params): void
    {
        $this->requireAuth();
        $orgId        = $this->orgId();
        $activeVisits = (new VisitModel())->getActiveVisits($orgId);

        // Pull the org's stale threshold from auto-checkout settings (default 24h)
        $db  = Database::getInstance();
        $row = $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
        $row->execute([$orgId]);
        $settings    = json_decode($row->fetchColumn() ?? '{}', true) ?: [];
        $staleHours  = (int)($settings['auto_checkout']['max_open_hours'] ?? 24);

        $this->view->render('admin/live', [
            'title'        => 'Live Logs',
            'helpSlug'     => 'live',
            'activeVisits' => $activeVisits,
            'staleHours'   => $staleHours,
            'flash'        => $this->flash(),
        ]);
    }

    public function bulkCheckout(array $params): void
    {
        $this->requireAuth();
        $orgId    = $this->orgId();
        $db       = Database::getInstance();
        // IDs arrive as a single comma-separated string (new form) or array (legacy fallback)
        $raw = $this->request->input('visit_ids', '');
        if (is_array($raw)) {
            $visitIds = array_values(array_filter(array_map('intval', $raw)));
        } else {
            $raw      = trim((string)$raw);
            $visitIds = $raw ? array_values(array_filter(array_map('intval', explode(',', $raw)))) : [];
        }

        if (empty($visitIds)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'No visits selected.'];
            $this->redirect('/admin/live');
            return;
        }

        $placeholders = implode(',', array_fill(0, count($visitIds), '?'));
        $bindings     = array_merge([$orgId], $visitIds);

        $stmt = $db->prepare(
            "UPDATE visits
             SET check_out_time = NOW(),
                 status         = 'auto_completed',
                 updated_at     = NOW()
             WHERE organization_id = ?
               AND visit_id IN ({$placeholders})
               AND status = 'checked_in'
               AND check_out_time IS NULL"
        );
        $stmt->execute($bindings);
        $closed = $stmt->rowCount();

        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => "{$closed} visitor" . ($closed !== 1 ? 's' : '') . " checked out.",
        ];
        $this->redirect('/admin/live');
    }

    public function liveDemo(array $params): void
    {
        $this->requireAuth();
        $db    = Database::getInstance();
        $orgId = $this->orgId();

        $ids = $db->query(
            "SELECT visit_id FROM visits
             WHERE organization_id = $orgId AND status = 'checked_in'
             ORDER BY visit_id ASC"
        )->fetchAll(\PDO::FETCH_COLUMN);

        if (!empty($ids)) {
            $count   = count($ids);
            $maxMins = 180;
            $stmt    = $db->prepare("UPDATE visits SET check_in_time = ? WHERE visit_id = ?");
            foreach ($ids as $i => $visitId) {
                $fraction  = $count > 1 ? $i / ($count - 1) : 0;
                $minsAgo   = max(5, (int)round($maxMins * $fraction));
                $checkIn   = date('Y-m-d H:i:s', time() - ($minsAgo * 60));
                $stmt->execute([$checkIn, $visitId]);
            }
        }

        $this->redirect('/admin/live');
    }

    public function livePoll(array $params): void
    {
        $this->requireAuth();
        $activeVisits = (new VisitModel())->getActiveVisits($this->orgId());

        header('Content-Type: application/json');
        echo json_encode(array_map(function ($v) {
            return [
                'visit_id'      => $v['visit_id'],
                'name'          => trim($v['first_name'] . ' ' . $v['last_name']),
                'phone'         => $v['phone'],
                'host_name'     => $v['host_name'] ?? '',
                'reason_label'  => $v['reason_label'] ?? '',
                'check_in_time' => $v['check_in_time'],
                'status'        => $v['status'],
            ];
        }, $activeVisits));
        exit;
    }

    // ── Visitor Profile ──────────────────────────────────────────

    public function visitorProfile(array $params): void
    {
        $this->requireAuth();
        $visitorId = (int)($params['id'] ?? 0);
        $orgId     = $this->orgId();
        $db        = Database::getInstance();

        // Basic visitor info
        $visitor = $db->prepare("SELECT * FROM visitors WHERE visitor_id = ?")->execute([$visitorId])
            ? $db->prepare("SELECT * FROM visitors WHERE visitor_id = ?")->execute([$visitorId]) && false
            : null;
        $stmt = $db->prepare("SELECT * FROM visitors WHERE visitor_id = ?");
        $stmt->execute([$visitorId]);
        $visitor = $stmt->fetch();

        if (!$visitor) {
            $this->redirect('/admin/history');
        }

        // Full visit history for this visitor within the org
        $stmt = $db->prepare("
            SELECT
                vi.visit_id,
                vi.status,
                vi.check_in_time,
                vi.check_out_time,
                vi.notes,
                h.name        AS host_name,
                d.name        AS dept_name,
                vr.label      AS reason_label,
                TIMESTAMPDIFF(MINUTE, vi.check_in_time, COALESCE(vi.check_out_time, NOW())) AS duration_min
            FROM visits vi
            LEFT JOIN hosts         h  ON vi.host_id   = h.host_id
            LEFT JOIN departments   d  ON h.department_id = d.department_id
            LEFT JOIN visit_reasons vr ON vi.reason_id  = vr.reason_id
            WHERE vi.visitor_id = ? AND vi.organization_id = ?
            ORDER BY vi.check_in_time DESC
        ");
        $stmt->execute([$visitorId, $orgId]);
        $visits = $stmt->fetchAll();

        // Stats
        $completed = array_filter($visits, fn($v) => $v['status'] === 'completed');
        $avgMin    = count($completed)
            ? (int)round(array_sum(array_column($completed, 'duration_min')) / count($completed))
            : null;

        // Most common host
        $hostCounts = array_count_values(array_filter(array_column($visits, 'host_name')));
        arsort($hostCounts);
        $usualHost = key($hostCounts) ?: null;

        // Most common reason
        $reasonCounts = array_count_values(array_filter(array_column($visits, 'reason_label')));
        arsort($reasonCounts);
        $usualReason = key($reasonCounts) ?: null;

        // Day-of-week pattern
        $dowCounts = array_fill(0, 7, 0);
        foreach ($visits as $v) {
            $dow = (int)date('w', strtotime($v['check_in_time']));
            $dowCounts[$dow]++;
        }
        $dowLabels  = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        $busiestDow = array_search(max($dowCounts), $dowCounts);

        $this->view->render('admin/visitor', [
            'title'       => $visitor['first_name'] . ' ' . $visitor['last_name'],
            'helpSlug'    => 'visitor',
            'visitor'     => $visitor,
            'visits'      => $visits,
            'stats'       => [
                'total'        => count($visits),
                'completed'    => count($completed),
                'avg_min'      => $avgMin,
                'usual_host'   => $usualHost,
                'usual_reason' => $usualReason,
                'busiest_dow'  => $busiestDow !== false ? $dowLabels[$busiestDow] : null,
                'dow_counts'   => $dowCounts,
                'dow_labels'   => $dowLabels,
                'first_visit'  => !empty($visits) ? end($visits)['check_in_time'] : null,
                'last_visit'   => !empty($visits) ? $visits[0]['check_in_time']   : null,
            ],
            'flash'       => $this->flash(),
        ]);
    }

    // ── History ──────────────────────────────────────────────────

    public function history(array $params): void
    {
        $this->requireAuth();
        $filters = [
            'date_from' => $this->request->get('date_from'),
            'date_to'   => $this->request->get('date_to'),
            'host_id'   => $this->request->get('host_id'),
            'status'    => $this->request->get('status'),
            'search'    => trim((string)$this->request->get('search')),
        ];
        $visits = (new VisitModel())->getHistory($this->orgId(), $filters);
        $hosts  = (new HostModel())->getForOrg($this->orgId());
        $this->view->render('admin/history', [
            'title'   => 'Visit History',
            'helpSlug' => 'history',
            'visits'  => $visits,
            'hosts'   => $hosts,
            'filters' => $filters,
            'flash'   => $this->flash(),
        ]);
    }

    // ── Hosts ────────────────────────────────────────────────────

    public function hosts(array $params): void
    {
        $this->requireRole('org_admin');
        $hosts = (new HostModel())->where(['organization_id' => $this->orgId()], 'name');
        $this->view->render('admin/hosts', [
            'title' => 'Manage Hosts',
            'hosts' => $hosts,
            'flash' => $this->flash(),
        ]);
    }

    public function storeHost(array $params): void
    {
        $this->requireRole('org_admin');
        (new HostModel())->insert([
            'organization_id' => $this->orgId(),
            'name'            => $this->request->clean('name'),
            'email'           => $this->request->clean('email') ?: null,
            'phone'           => $this->request->clean('phone') ?: null,
            'department'      => $this->request->clean('department') ?: null,
            'active'          => 1,
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Host added.'];
        $this->redirect('/admin/hosts');
    }

    public function deleteHost(array $params): void
    {
        $this->requireRole('org_admin');
        (new HostModel())->update(
            ['active' => 0],
            ['host_id' => (int)($params['id'] ?? 0), 'organization_id' => $this->orgId()]
        );
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Host deactivated.'];
        $this->redirect('/admin/hosts');
    }

    // ── Visit Reasons ────────────────────────────────────────────

    public function reasons(array $params): void
    {
        $this->requireRole('org_admin');
        $reasons = (new VisitReasonModel())->where(
            ['organization_id' => $this->orgId()],
            'sort_order, label'
        );
        $this->view->render('admin/reasons', [
            'title'   => 'Visit Reasons',
            'helpSlug' => 'reasons',
            'reasons' => $reasons,
            'flash'   => $this->flash(),
        ]);
    }

    public function storeReason(array $params): void
    {
        $this->requireRole('org_admin');
        $db    = Database::getInstance();
        $orgId = $this->orgId();
        $order = (int)$db->query(
            "SELECT COALESCE(MAX(sort_order),0) FROM visit_reasons WHERE organization_id = {$orgId}"
        )->fetchColumn();
        (new VisitReasonModel())->insert([
            'organization_id'  => $orgId,
            'label'            => $this->request->clean('label'),
            'requires_approval'=> (int)$this->request->input('requires_approval', 0),
            'active'           => 1,
            'sort_order'       => $order + 1,
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Reason added.'];
        $this->redirect('/admin/reasons');
    }

    public function deleteReason(array $params): void
    {
        $this->requireRole('org_admin');
        (new VisitReasonModel())->update(
            ['active' => 0],
            ['reason_id' => (int)($params['id'] ?? 0), 'organization_id' => $this->orgId()]
        );
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Reason deactivated.'];
        $this->redirect('/admin/reasons');
    }

    // ── Custom Fields ────────────────────────────────────────────

    public function fields(array $params): void
    {
        $this->requireRole('org_admin');
        $db     = Database::getInstance();
        $stmt   = $db->prepare(
            "SELECT * FROM custom_fields WHERE organization_id = ? ORDER BY sort_order, label"
        );
        $stmt->execute([$this->orgId()]);
        $fields = $stmt->fetchAll();
        $this->view->render('admin/fields', [
            'title'  => 'Custom Fields',
            'fields' => $fields,
            'flash'  => $this->flash(),
        ]);
    }

    public function storeField(array $params): void
    {
        $this->requireRole('org_admin');
        $db    = Database::getInstance();
        $orgId = $this->orgId();
        $order = (int)$db->query(
            "SELECT COALESCE(MAX(sort_order),0) FROM custom_fields WHERE organization_id = {$orgId}"
        )->fetchColumn();

        $options = null;
        if ($this->request->input('field_type') === 'select') {
            $raw     = $this->request->input('options', '');
            $options = json_encode(array_filter(array_map('trim', explode("\n", $raw))));
        }

        $db->prepare(
            "INSERT INTO custom_fields (organization_id, label, field_type, required, options, sort_order)
             VALUES (?, ?, ?, ?, ?, ?)"
        )->execute([
            $orgId,
            $this->request->clean('label'),
            $this->request->input('field_type', 'text'),
            (int)$this->request->input('required', 0),
            $options,
            $order + 1,
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Field added.'];
        $this->redirect('/admin/fields');
    }

    public function deleteField(array $params): void
    {
        $this->requireRole('org_admin');
        $db = Database::getInstance();
        $db->prepare(
            "UPDATE custom_fields SET active = 0 WHERE field_id = ? AND organization_id = ?"
        )->execute([(int)($params['id'] ?? 0), $this->orgId()]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Field removed.'];
        $this->redirect('/admin/fields');
    }
}
