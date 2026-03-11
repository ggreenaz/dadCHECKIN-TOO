<?php use App\Core\View; ?>
<?php
$v        = $visitor;
$fullName = View::e($v['first_name'] . ' ' . $v['last_name']);
$initials = strtoupper(substr($v['first_name'],0,1) . substr($v['last_name'],0,1));
$isActive = !empty(array_filter($visits, fn($vi) => $vi['status'] === 'checked_in'));

$maxDurMin = !empty($visits)
    ? (max(array_filter(array_map(fn($vi) => (int)$vi['duration_min'], $visits))) ?: 1)
    : 1;

// Duration view: sort by duration descending
$visitsByDur = $visits;
usort($visitsByDur, fn($a,$b) => (int)$b['duration_min'] - (int)$a['duration_min']);

function fmtMin(int $min): string {
    $h = (int)($min / 60);
    $m = $min % 60;
    if ($h > 0) return $h . 'h ' . str_pad($m, 2, '0', STR_PAD_LEFT) . 'm';
    return $m . 'm';
}

function chips($vi): string {
    $out = '';
    if (!empty($vi['host_name']))   $out .= '<span class="vp-chip">'        . htmlspecialchars($vi['host_name'],ENT_QUOTES)   . '</span>';
    if (!empty($vi['dept_name']))   $out .= '<span class="vp-chip chip-dept">'  . htmlspecialchars($vi['dept_name'],ENT_QUOTES)   . '</span>';
    if (!empty($vi['reason_label']))$out .= '<span class="vp-chip chip-reason">' . htmlspecialchars($vi['reason_label'],ENT_QUOTES) . '</span>';
    return $out;
}
?>

<!-- Back -->
<a href="javascript:history.back()" class="back-link">&larr; Back</a>

<!-- ── Hero header ──────────────────────────────────────────────── -->
<div class="vp-hero">
    <div class="vp-hero-left">
        <div class="vp-avatar-lg <?= $isActive ? 'vp-avatar-active' : '' ?>"><?= $initials ?></div>
        <div class="vp-hero-identity">
            <h1 class="vp-hero-name"><?= $fullName ?></h1>
            <div class="vp-hero-contact">
                <?php if ($v['phone']): ?>
                    <span>&#128222; <?= View::e($v['phone']) ?></span>
                <?php endif; ?>
                <?php if ($v['email']): ?>
                    <span>&#9993; <?= View::e($v['email']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if ($isActive): ?>
    <div class="vp-active-pill">
        <span class="live-pulse-dot" style="width:10px;height:10px;"></span>
        Currently Inside
    </div>
    <?php endif; ?>
</div>

<!-- ── Stat strip ───────────────────────────────────────────────── -->
<div class="vp-strip">
    <div class="vp-strip-stat">
        <div class="vp-strip-value"><?= $stats['total'] ?></div>
        <div class="vp-strip-label">Total Visits</div>
    </div>
    <div class="vp-strip-divider"></div>
    <div class="vp-strip-stat">
        <div class="vp-strip-value"><?= $stats['avg_min'] !== null ? fmtMin($stats['avg_min']) : '—' ?></div>
        <div class="vp-strip-label">Avg Duration</div>
    </div>
    <div class="vp-strip-divider"></div>
    <div class="vp-strip-stat">
        <div class="vp-strip-value"><?= $stats['busiest_dow'] ?? '—' ?></div>
        <div class="vp-strip-label">Busiest Day</div>
    </div>
    <div class="vp-strip-divider"></div>
    <div class="vp-strip-stat">
        <div class="vp-strip-value"><?= $stats['last_visit'] ? date('M j', strtotime($stats['last_visit'])) : '—' ?></div>
        <div class="vp-strip-label">Last Visit</div>
    </div>
    <div class="vp-strip-divider"></div>
    <div class="vp-strip-stat">
        <div class="vp-strip-value"><?= $stats['usual_host'] ? explode(' ', $stats['usual_host'])[count(explode(' ', $stats['usual_host']))-1] : '—' ?></div>
        <div class="vp-strip-label">Usual Host</div>
    </div>
    <div class="vp-strip-divider"></div>
    <div class="vp-strip-stat">
        <div class="vp-strip-value"><?= $stats['first_visit'] ? date('M Y', strtotime($stats['first_visit'])) : '—' ?></div>
        <div class="vp-strip-label">First Seen</div>
    </div>
</div>

<!-- ── Pattern panels ───────────────────────────────────────────── -->
<div class="vp-patterns-row">

    <!-- Day of week -->
    <div class="vp-pattern-panel">
        <div class="vp-panel-title">When Do They Visit?</div>
        <div class="vp-dow-chart">
            <?php
            $maxDow = max($stats['dow_counts']) ?: 1;
            foreach ($stats['dow_labels'] as $i => $lbl):
                $cnt = $stats['dow_counts'][$i];
                $pct = (int)round(($cnt / $maxDow) * 100);
                $isTop = ($cnt === max($stats['dow_counts']) && $cnt > 0);
            ?>
            <div class="vp-dow-col">
                <div class="vp-dow-count-label"><?= $cnt > 0 ? $cnt : '' ?></div>
                <div class="vp-dow-track">
                    <div class="vp-dow-bar <?= $isTop ? 'vp-dow-bar-top' : ($cnt > 0 ? 'vp-dow-bar-active' : '') ?>"
                         style="height:<?= $pct ?>%"></div>
                </div>
                <div class="vp-dow-day <?= $isTop ? 'vp-dow-day-top' : '' ?>"><?= $lbl ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Time of day -->
    <div class="vp-pattern-panel">
        <div class="vp-panel-title">Time of Day</div>
        <div class="vp-tod-chart">
            <?php
            $todBuckets = ['Morning' => 0, 'Midday' => 0, 'Afternoon' => 0, 'Evening' => 0];
            foreach ($visits as $vi) {
                $hour = (int)date('G', strtotime($vi['check_in_time']));
                if      ($hour < 11) $todBuckets['Morning']++;
                elseif  ($hour < 13) $todBuckets['Midday']++;
                elseif  ($hour < 17) $todBuckets['Afternoon']++;
                else                 $todBuckets['Evening']++;
            }
            $maxTod = max($todBuckets) ?: 1;
            $todColors = ['Morning'=>'#60a5fa','Midday'=>'#fbbf24','Afternoon'=>'#f97316','Evening'=>'#818cf8'];
            foreach ($todBuckets as $label => $cnt):
                $pct = (int)round(($cnt / $maxTod) * 100);
            ?>
            <div class="vp-tod-row">
                <div class="vp-tod-label"><?= $label ?></div>
                <div class="vp-tod-track">
                    <div class="vp-tod-fill" style="width:<?= $pct ?>%;background:<?= $todColors[$label] ?>"></div>
                </div>
                <div class="vp-tod-count"><?= $cnt ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Duration breakdown -->
    <div class="vp-pattern-panel">
        <div class="vp-panel-title">How Long Do They Stay?</div>
        <?php
        $durBuckets = ['Short (< 30m)' => 0, 'Medium (30m–1h)' => 0, 'Long (1–2h)' => 0, 'Extended (2h+)' => 0];
        foreach ($visits as $vi) {
            $d = (int)$vi['duration_min'];
            if      ($d < 30)  $durBuckets['Short (< 30m)']++;
            elseif  ($d < 60)  $durBuckets['Medium (30m–1h)']++;
            elseif  ($d < 120) $durBuckets['Long (1–2h)']++;
            else               $durBuckets['Extended (2h+)']++;
        }
        $maxDurB = max($durBuckets) ?: 1;
        $durColors = ['Short (< 30m)'=>'#4ade80','Medium (30m–1h)'=>'#60a5fa','Long (1–2h)'=>'#fbbf24','Extended (2h+)'=>'#f87171'];
        foreach ($durBuckets as $label => $cnt):
            $pct = (int)round(($cnt / $maxDurB) * 100);
        ?>
        <div class="vp-tod-row">
            <div class="vp-tod-label" style="min-width:130px"><?= $label ?></div>
            <div class="vp-tod-track">
                <div class="vp-tod-fill" style="width:<?= $pct ?>%;background:<?= $durColors[$label] ?>"></div>
            </div>
            <div class="vp-tod-count"><?= $cnt ?></div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- ── Visit History ─────────────────────────────────────────────── -->
<div class="card">
    <div class="vp-history-header">
        <div class="card-title" style="margin:0;padding:0;border:none;">
            Visit History
            <span class="vp-history-count"><?= $stats['total'] ?> visit<?= $stats['total'] !== 1 ? 's' : '' ?></span>
        </div>
        <div class="vp-view-toggle" id="viewToggle">
            <button class="vp-toggle-btn active" data-view="timeline">&#9135; Timeline</button>
            <button class="vp-toggle-btn"        data-view="cards">&#9707; Cards</button>
            <button class="vp-toggle-btn"        data-view="duration">&#9646; Duration</button>
        </div>
    </div>

    <?php if (empty($visits)): ?>
        <p class="text-muted" style="margin-top:16px;">No visits on record.</p>
    <?php else: ?>

    <!-- ── VIEW: TIMELINE ────────────────────────────────────────── -->
    <div id="view-timeline" class="vp-view">
        <div class="vp-timeline">
        <?php foreach ($visits as $i => $vi):
            $durMin  = (int)$vi['duration_min'];
            $isNow   = $vi['status'] === 'checked_in';
            $barPct  = $durMin > 0 ? min(90, (int)round(($durMin / $maxDurMin) * 90)) : 0;
        ?>
        <div class="vp-tl-item">
            <div class="vp-tl-spine">
                <div class="vp-tl-dot <?= $isNow ? 'vp-tl-dot-active' : '' ?>"></div>
                <?php if ($i < count($visits)-1): ?><div class="vp-tl-line"></div><?php endif; ?>
            </div>
            <div class="vp-tl-body">
                <div class="vp-tl-top">
                    <div class="vp-tl-date">
                        <?= date('D, M j, Y', strtotime($vi['check_in_time'])) ?>
                        <span class="vp-tl-time"><?= date('g:i A', strtotime($vi['check_in_time'])) ?></span>
                    </div>
                    <span class="status-badge status-<?= $vi['status'] ?>"><?= ucfirst(str_replace('_',' ',$vi['status'])) ?></span>
                </div>
                <div class="vp-tl-chips"><?= chips($vi) ?></div>
                <?php if ($isNow): ?>
                    <div class="vp-tl-bar-row">
                        <span class="vp-tl-dur" style="color:#16a34a">Active now</span>
                        <div class="vp-tl-track"><div class="vp-tl-fill" style="width:35%;background:linear-gradient(90deg,#4ade80,#16a34a);animation:pulse-width 2s ease-in-out infinite;"></div></div>
                    </div>
                <?php elseif ($durMin > 0): ?>
                    <div class="vp-tl-bar-row">
                        <span class="vp-tl-dur"><?= fmtMin($durMin) ?></span>
                        <div class="vp-tl-track"><div class="vp-tl-fill" style="width:<?= $barPct ?>%"></div></div>
                    </div>
                <?php endif; ?>
                <?php if ($vi['notes']): ?><div class="vp-tl-notes"><?= View::e($vi['notes']) ?></div><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- ── VIEW: CARDS ───────────────────────────────────────────── -->
    <div id="view-cards" class="vp-view" style="display:none;">
        <div class="vp-cards-grid">
        <?php foreach ($visits as $vi):
            $durMin = (int)$vi['duration_min'];
            $isNow  = $vi['status'] === 'checked_in';
            $barPct = $durMin > 0 ? min(90, (int)round(($durMin / $maxDurMin) * 90)) : 0;
        ?>
        <div class="vp-card <?= $isNow ? 'vp-card-active' : '' ?>">
            <div class="vp-card-top">
                <div class="vp-card-date">
                    <div class="vp-card-day"><?= date('D', strtotime($vi['check_in_time'])) ?></div>
                    <div class="vp-card-num"><?= date('j', strtotime($vi['check_in_time'])) ?></div>
                    <div class="vp-card-mon"><?= date('M Y', strtotime($vi['check_in_time'])) ?></div>
                </div>
                <div class="vp-card-main">
                    <div class="vp-card-time"><?= date('g:i A', strtotime($vi['check_in_time'])) ?></div>
                    <div class="vp-card-chips"><?= chips($vi) ?></div>
                </div>
                <span class="status-badge status-<?= $vi['status'] ?>"><?= ucfirst(str_replace('_',' ',$vi['status'])) ?></span>
            </div>
            <?php if ($isNow): ?>
                <div class="vp-card-bar-row">
                    <span class="vp-card-dur" style="color:#16a34a">Active now</span>
                    <div class="vp-card-track"><div class="vp-card-fill" style="width:35%;background:linear-gradient(90deg,#4ade80,#16a34a);animation:pulse-width 2s ease-in-out infinite;"></div></div>
                </div>
            <?php elseif ($durMin > 0): ?>
                <div class="vp-card-bar-row">
                    <span class="vp-card-dur"><?= fmtMin($durMin) ?></span>
                    <div class="vp-card-track"><div class="vp-card-fill" style="width:<?= $barPct ?>%"></div></div>
                </div>
            <?php endif; ?>
            <?php if ($vi['notes']): ?><div class="vp-card-notes"><?= View::e($vi['notes']) ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- ── VIEW: DURATION ────────────────────────────────────────── -->
    <div id="view-duration" class="vp-view" style="display:none;">
        <div class="vp-dur-list">
        <?php foreach ($visitsByDur as $vi):
            $durMin = (int)$vi['duration_min'];
            $isNow  = $vi['status'] === 'checked_in';
            $barPct = $durMin > 0 ? min(90, (int)round(($durMin / $maxDurMin) * 90)) : 20;
            if      ($durMin >= 120) $barColor = 'linear-gradient(90deg,#f87171,#dc2626)';
            elseif  ($durMin >= 60)  $barColor = 'linear-gradient(90deg,#fde047,#ca8a04)';
            else                     $barColor = 'linear-gradient(90deg,#4ade80,#16a34a)';
            if ($isNow) $barColor = 'linear-gradient(90deg,#4ade80,#16a34a)';
        ?>
        <div class="vp-dur-row">
            <div class="vp-dur-label">
                <span class="vp-dur-time"><?= $isNow ? 'Active now' : fmtMin($durMin) ?></span>
                <span class="vp-dur-date"><?= date('M j, Y', strtotime($vi['check_in_time'])) ?> &middot; <?= View::e($vi['reason_label'] ?? '—') ?></span>
            </div>
            <div class="vp-dur-track">
                <div class="vp-dur-fill" style="width:<?= $barPct ?>%;background:<?= $barColor ?>;<?= $isNow ? 'animation:pulse-width 2s ease-in-out infinite' : '' ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <div class="vp-dur-legend">
            <span class="vp-legend-dot" style="background:#16a34a"></span> Under 1h &nbsp;
            <span class="vp-legend-dot" style="background:#ca8a04"></span> 1–2h &nbsp;
            <span class="vp-legend-dot" style="background:#dc2626"></span> Over 2h
        </div>
    </div>

    <?php endif; ?>
</div>

<style>
@keyframes pulse-width { 0%,100%{opacity:1} 50%{opacity:0.55} }
</style>

<script>
(function () {
    var STORAGE_KEY = 'vp_view_pref';
    var btns  = document.querySelectorAll('.vp-toggle-btn');
    var views = document.querySelectorAll('.vp-view');

    function show(name) {
        btns.forEach(function(b)  { b.classList.toggle('active', b.dataset.view === name); });
        views.forEach(function(v) { v.style.display = v.id === 'view-' + name ? '' : 'none'; });
        try { localStorage.setItem(STORAGE_KEY, name); } catch(e) {}
    }

    btns.forEach(function(btn) {
        btn.addEventListener('click', function() { show(btn.dataset.view); });
    });

    // Restore saved preference
    var saved = 'timeline';
    try { saved = localStorage.getItem(STORAGE_KEY) || 'timeline'; } catch(e) {}
    show(saved);
})();
</script>
