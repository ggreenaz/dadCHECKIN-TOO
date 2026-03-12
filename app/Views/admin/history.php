<?php
use App\Core\View;

// ── Compute summary stats from $visits ────────────────────────────
$totalCount     = count($visits);
$completedCount = 0;
$noShowCount    = 0;
$totalMinutes   = 0;
$durCount       = 0;

// Day-by-day bucketing + peak hour per day
$byDay    = [];
$dayHours = []; // day => [hour => count]
foreach ($visits as $v) {
    $day  = substr($v['check_in_time'], 0, 10); // YYYY-MM-DD
    $hour = (int)date('G', strtotime($v['check_in_time']));
    $byDay[$day] = ($byDay[$day] ?? 0) + 1;
    $dayHours[$day][$hour] = ($dayHours[$day][$hour] ?? 0) + 1;

    if (in_array($v['status'], ['completed','auto_completed'])) $completedCount++;
    if ($v['status'] === 'no_show') $noShowCount++;
    if ($v['duration_min'] > 0) {
        $totalMinutes += $v['duration_min'];
        $durCount++;
    }
}
ksort($byDay);
$avgMin    = $durCount > 0 ? (int)round($totalMinutes / $durCount) : 0;
$avgFmt    = $avgMin >= 60
    ? (int)($avgMin/60).'h '.str_pad($avgMin%60,2,'0',STR_PAD_LEFT).'m'
    : ($avgMin > 0 ? $avgMin.'m' : '—');

$dayMax    = $byDay ? max($byDay) : 1;
$dayKeys   = array_keys($byDay);
$daySpan   = count($dayKeys);
?>

<!-- ── Page header ─────────────────────────────────────────────── -->
<div class="an-header">
    <div>
        <h1 class="an-title">Visit History</h1>
        <p class="an-subtitle"><?= $totalCount ?> records<?= $totalCount ? ' · '.reset($dayKeys).' → '.end($dayKeys) : '' ?></p>
    </div>
    <a href="/logs" class="back-link" style="align-self:center;">&larr; Log Hub</a>
</div>

<!-- ── Summary strip ───────────────────────────────────────────── -->
<div class="hist-strip">
    <div class="hist-strip-stat">
        <div class="hist-strip-val"><?= $totalCount ?></div>
        <div class="hist-strip-lbl">Total Visits</div>
    </div>
    <div class="hist-strip-stat hist-strip-stat-success">
        <div class="hist-strip-val"><?= $completedCount ?></div>
        <div class="hist-strip-lbl">Completed</div>
    </div>
    <div class="hist-strip-stat hist-strip-stat-danger">
        <div class="hist-strip-val"><?= $noShowCount ?></div>
        <div class="hist-strip-lbl">No Shows</div>
    </div>
    <div class="hist-strip-stat">
        <div class="hist-strip-val"><?= $avgFmt ?></div>
        <div class="hist-strip-lbl">Avg Duration</div>
    </div>
    <div class="hist-strip-stat">
        <div class="hist-strip-val"><?= $daySpan ?></div>
        <div class="hist-strip-lbl">Days Shown</div>
    </div>
</div>

<!-- ── Filter panel ────────────────────────────────────────────── -->
<div class="card">
    <div class="card-title">Filter</div>
    <form method="GET" action="/admin/history" class="toolbar-form">
        <div class="form-inline">
            <div class="form-group" style="flex:1.5;min-width:180px;">
                <label for="search">Search</label>
                <input type="text" name="search" id="search" placeholder="Name, phone, or email&hellip;"
                       value="<?= View::e($filters['search'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="date_from">From</label>
                <input type="date" name="date_from" id="date_from"
                       value="<?= View::e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="date_to">To</label>
                <input type="date" name="date_to" id="date_to"
                       value="<?= View::e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="host_id">Host</label>
                <select name="host_id" id="host_id">
                    <option value="">All Hosts</option>
                    <?php foreach ($hosts as $h): ?>
                        <option value="<?= (int)$h['host_id'] ?>"
                            <?= (string)($filters['host_id'] ?? '') === (string)$h['host_id'] ? 'selected' : '' ?>>
                            <?= View::e($h['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="">All</option>
                    <?php foreach (['checked_in','completed','auto_completed','no_show','cancelled'] as $s): ?>
                        <option value="<?= $s ?>"
                            <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="align-self: flex-end; display:flex; gap:8px;">
                <button type="submit" class="button">Filter</button>
                <a href="/admin/history" class="button" style="background:var(--surface-2);color:var(--text-muted);">Reset</a>
            </div>
        </div>
    </form>
</div>

<?php if (!empty($byDay)): ?>
<!-- ── Day-by-day bar chart ────────────────────────────────────── -->
<div class="card">
    <div class="card-title">Visits by Day</div>
    <div class="hist-daychart">
        <?php foreach ($byDay as $day => $cnt):
            $pct      = $dayMax > 0 ? (int)round(($cnt / $dayMax) * 100) : 0;
            $dayLabel = date('D M j', strtotime($day));
            // Peak hour determines bar color: morning=blue, midday=teal, afternoon=orange
            $hours    = $dayHours[$day] ?? [];
            $peakHour = $hours ? array_search(max($hours), $hours) : 10;
            if ($peakHour < 10)      $barGrad = 'linear-gradient(180deg,#93c5fd,#3b82f6)';   // morning – blue
            elseif ($peakHour < 13) $barGrad = 'linear-gradient(180deg,#5eead4,#0d9488)';   // midday – teal
            elseif ($peakHour < 15) $barGrad = 'linear-gradient(180deg,#fcd34d,#d97706)';   // early-afternoon – amber
            else                    $barGrad = 'linear-gradient(180deg,#fb923c,#ea580c)';   // late-afternoon – orange
            $isWeekend = in_array(date('N', strtotime($day)), [6, 7]);
        ?>
        <div class="hist-daychart-col <?= $isWeekend ? 'hist-daychart-col-wknd' : '' ?>">
            <div class="hist-daychart-bar-wrap">
                <div class="hist-daychart-bar" style="height:<?= $pct ?>%;background:<?= $barGrad ?>"
                     title="<?= $cnt ?> visit<?= $cnt !== 1 ? 's' : '' ?> on <?= $dayLabel ?> · peak ~<?= date('g A', mktime($peakHour,0,0)) ?>">
                </div>
            </div>
            <div class="hist-daychart-cnt"><?= $cnt ?></div>
            <div class="hist-daychart-lbl"><?= date('D', strtotime($day)) ?><br><span><?= date('M j', strtotime($day)) ?></span></div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="hist-daychart-legend">
        <span><span class="hist-legend-swatch" style="background:#3b82f6"></span>Morning peak (&lt;10 AM)</span>
        <span><span class="hist-legend-swatch" style="background:#0d9488"></span>Midday peak (10 AM–1 PM)</span>
        <span><span class="hist-legend-swatch" style="background:#d97706"></span>Early afternoon (1–3 PM)</span>
        <span><span class="hist-legend-swatch" style="background:#ea580c"></span>Late afternoon (3 PM+)</span>
    </div>
</div>
<?php endif; ?>

<?php if (empty($visits)): ?>
<div class="card"><p class="text-muted">No visits match your filters.</p></div>
<?php else: ?>

<!-- ── View toggle ─────────────────────────────────────────────── -->
<div class="hist-toggle-bar">
    <span class="hist-toggle-label"><?= $totalCount ?> records &mdash; View as:</span>
    <div class="vp-toggle-btns">
        <button class="vp-toggle-btn" id="btn-table"    onclick="histView('table')">Table</button>
        <button class="vp-toggle-btn" id="btn-cards"    onclick="histView('cards')">Cards</button>
        <button class="vp-toggle-btn" id="btn-timeline" onclick="histView('timeline')">Timeline</button>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     VIEW 1: TABLE
     ══════════════════════════════════════════════════════════════ -->
<div id="view-table" class="hist-view">
<div class="card" style="padding:0">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Visitor</th>
                    <th>Host</th>
                    <th>Reason</th>
                    <th>Location</th>
                    <th>Check-In</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($visits as $v):
                    $durMin = (int)$v['duration_min'];
                    $durBar = '';
                    if ($durMin > 0) {
                        $pct   = min(100, (int)round(($durMin / 240) * 100)); // 240min = 4hr max
                        $color = $durMin >= 120 ? '#f87171' : ($durMin >= 60 ? '#fbbf24' : '#4ade80');
                        $durFmt = $durMin >= 60
                            ? (int)($durMin/60).'h '.str_pad($durMin%60,2,'0',STR_PAD_LEFT).'m'
                            : $durMin.'m';
                        $durBar = '<div class="hist-tbl-dur"><div class="hist-tbl-dur-bar" style="width:'.$pct.'%;background:'.$color.'"></div><span>'.$durFmt.'</span></div>';
                    } else {
                        $durBar = '<span class="text-muted">—</span>';
                    }
                ?>
                <tr>
                    <td>
                        <a href="/admin/visitor/<?= (int)$v['visitor_id'] ?>" class="visitor-link">
                            <strong><?= View::e($v['first_name'].' '.$v['last_name']) ?></strong>
                        </a>
                        <?php if ($v['phone']): ?>
                            <div style="font-size:0.75rem;color:var(--text-muted)"><?= View::e($v['phone']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= View::e($v['host_name'] ?? '—') ?></td>
                    <td><?= View::e($v['reason_label'] ?? '—') ?></td>
                    <td><?= View::e($v['location_name'] ?? '—') ?></td>
                    <td style="white-space:nowrap;font-size:0.82rem"><?= View::e($v['check_in_time']) ?></td>
                    <td><?= $durBar ?></td>
                    <td><span class="status-badge status-<?= View::e($v['status']) ?>"><?= View::e(ucfirst(str_replace('_', ' ', $v['status']))) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     VIEW 2: CARDS
     ══════════════════════════════════════════════════════════════ -->
<div id="view-cards" class="hist-view" style="display:none">
<div class="hist-cards-grid">
<?php foreach ($visits as $v):
    $durMin = (int)$v['duration_min'];
    $durFmt = '';
    $barPct = 0;
    $barColor = '#4ade80';
    if ($durMin > 0) {
        $durFmt   = $durMin >= 60 ? (int)($durMin/60).'h '.str_pad($durMin%60,2,'0',STR_PAD_LEFT).'m' : $durMin.'m';
        $barPct   = min(100, (int)round(($durMin / 240) * 100));
        $barColor = $durMin >= 120 ? '#f87171' : ($durMin >= 60 ? '#fbbf24' : '#4ade80');
    }
    $initials = strtoupper(substr($v['first_name'],0,1).substr($v['last_name'],0,1));
    $dateDisp = date('D, M j', strtotime($v['check_in_time']));
    $timeDisp = date('g:i A', strtotime($v['check_in_time']));
    $statusClass = 'status-'.$v['status'];
?>
<div class="hist-card">
    <div class="hist-card-header" style="background:<?= $barColor ?>22;border-left:4px solid <?= $barColor ?>">
        <div class="hist-card-avatar"><?= $initials ?></div>
        <div class="hist-card-meta">
            <a href="/admin/visitor/<?= (int)$v['visitor_id'] ?>" class="hist-card-name visitor-link">
                <?= View::e($v['first_name'].' '.$v['last_name']) ?>
            </a>
            <div class="hist-card-date"><?= $dateDisp ?> &middot; <?= $timeDisp ?></div>
        </div>
        <span class="status-badge <?= $statusClass ?>" style="margin-left:auto;white-space:nowrap">
            <?= ucfirst(str_replace('_',' ',$v['status'])) ?>
        </span>
    </div>
    <div class="hist-card-body">
        <div class="hist-card-row">
            <span class="hist-card-icon">&#128100;</span>
            <span><?= View::e($v['host_name'] ?? '—') ?></span>
        </div>
        <div class="hist-card-row">
            <span class="hist-card-icon">&#128203;</span>
            <span><?= View::e($v['reason_label'] ?? '—') ?></span>
        </div>
        <?php if ($v['location_name']): ?>
        <div class="hist-card-row">
            <span class="hist-card-icon">&#128205;</span>
            <span><?= View::e($v['location_name']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($durFmt): ?>
        <div class="hist-card-dur-wrap">
            <div class="hist-card-dur-bar" style="width:<?= $barPct ?>%;background:<?= $barColor ?>"></div>
        </div>
        <div class="hist-card-dur-label">Duration: <strong><?= $durFmt ?></strong></div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     VIEW 3: TIMELINE
     ══════════════════════════════════════════════════════════════ -->
<div id="view-timeline" class="hist-view" style="display:none">
<div class="hist-timeline">
<?php
$prevDay = null;
foreach ($visits as $v):
    $day = date('l, F j, Y', strtotime($v['check_in_time']));
    $timeStr = date('g:i A', strtotime($v['check_in_time']));
    $durMin = (int)$v['duration_min'];
    $durFmt = $durMin > 0
        ? ($durMin >= 60 ? (int)($durMin/60).'h '.str_pad($durMin%60,2,'0',STR_PAD_LEFT).'m' : $durMin.'m')
        : null;
    $dotColor = $durMin >= 120 ? '#f87171' : ($durMin >= 60 ? '#fbbf24' : '#60a5fa');
    if ($v['status'] === 'no_show') $dotColor = '#94a3b8';
    if ($v['status'] === 'completed') $dotColor = '#4ade80';

    if ($day !== $prevDay):
        $prevDay = $day;
?>
    <div class="hist-tl-day-header"><?= $day ?></div>
<?php endif; ?>
    <div class="hist-tl-row">
        <div class="hist-tl-dot" style="background:<?= $dotColor ?>"></div>
        <div class="hist-tl-line"></div>
        <div class="hist-tl-content">
            <div class="hist-tl-top">
                <a href="/admin/visitor/<?= (int)$v['visitor_id'] ?>" class="hist-tl-name visitor-link">
                    <?= View::e($v['first_name'].' '.$v['last_name']) ?>
                </a>
                <span class="hist-tl-time"><?= $timeStr ?></span>
                <span class="status-badge status-<?= View::e($v['status']) ?>">
                    <?= ucfirst(str_replace('_',' ',$v['status'])) ?>
                </span>
            </div>
            <div class="hist-tl-meta">
                <?= View::e($v['host_name'] ?? '—') ?>
                <?php if ($v['reason_label']): ?> &middot; <?= View::e($v['reason_label']) ?><?php endif; ?>
                <?php if ($durFmt): ?> &middot; <strong><?= $durFmt ?></strong><?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
</div>

<?php endif; ?>

<script>
(function () {
    var pref = localStorage.getItem('hist_view') || 'table';
    histView(pref);
})();

function histView(view) {
    ['table','cards','timeline'].forEach(function(v) {
        var el = document.getElementById('view-' + v);
        var btn = document.getElementById('btn-' + v);
        if (!el || !btn) return;
        var active = v === view;
        el.style.display  = active ? '' : 'none';
        btn.classList.toggle('active', active);
    });
    localStorage.setItem('hist_view', view);
}
</script>
