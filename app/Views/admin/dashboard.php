<?php use App\Core\View; ?>

<?php
// Hour-based greeting
$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$activeCount = (int)($todayStats['active'] ?? 0);
$extendedCount = (int)($todayStats['extended'] ?? 0);
?>

<!-- ══════════════════════════════════════════════════════════════
     HERO COMMAND BAR
     ══════════════════════════════════════════════════════════════ -->
<div class="db-hero">
    <div class="db-hero-left">
        <div class="db-greeting"><?= $greeting ?></div>
        <div class="db-hero-date" id="db-live-clock"><?= date('l, F j, Y') ?></div>
        <?php if ($activeCount > 0): ?>
            <div class="db-hero-status">
                <span class="live-pulse-dot" style="width:8px;height:8px;margin-right:6px;"></span>
                <strong><?= $activeCount ?></strong> visitor<?= $activeCount !== 1 ? 's' : '' ?> inside right now
                <?php if ($extendedCount > 0): ?>
                    &nbsp;&middot;&nbsp;<span style="color:#f87171"><?= $extendedCount ?> extended (2h+)</span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="db-hero-status" style="color:var(--text-muted)">No active visitors right now</div>
        <?php endif; ?>
    </div>
    <div class="db-hero-right">
        <a href="/checkin" class="db-cta-btn db-cta-primary">
            <span>&#43;</span> Check Someone In
        </a>
        <a href="/admin/live/demo" class="db-cta-btn db-cta-secondary">
            <span class="live-pulse-dot" style="width:8px;height:8px;"></span> Live Demo
        </a>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     STAT STRIP — 6 tiles
     ══════════════════════════════════════════════════════════════ -->
<div class="db-stats">
    <div class="db-stat db-stat-live">
        <div class="db-stat-val"><?= (int)($todayStats['active'] ?? 0) ?></div>
        <div class="db-stat-lbl">Inside Now</div>
        <?php if ($activeCount > 0): ?>
            <div class="db-stat-sub">
                <span class="live-pulse-dot" style="width:6px;height:6px;"></span> Live
            </div>
        <?php endif; ?>
    </div>
    <div class="db-stat">
        <div class="db-stat-val"><?= (int)($todayStats['total'] ?? 0) ?></div>
        <div class="db-stat-lbl">Today&rsquo;s Total</div>
        <div class="db-stat-sub"><?= date('M j') ?></div>
    </div>
    <div class="db-stat db-stat-success">
        <div class="db-stat-val"><?= (int)($todayStats['completed'] ?? 0) ?></div>
        <div class="db-stat-lbl">Completed</div>
        <div class="db-stat-sub">checked out</div>
    </div>
    <div class="db-stat db-stat-warning">
        <div class="db-stat-val"><?= (int)($todayStats['extended'] ?? 0) ?></div>
        <div class="db-stat-lbl">Extended</div>
        <div class="db-stat-sub">2 hrs or more</div>
    </div>
    <div class="db-stat db-stat-danger">
        <div class="db-stat-val"><?= (int)($todayStats['no_show'] ?? 0) ?></div>
        <div class="db-stat-lbl">No Shows</div>
        <div class="db-stat-sub">today</div>
    </div>
    <div class="db-stat db-stat-alltime">
        <div class="db-stat-val"><?= number_format($allTime) ?></div>
        <div class="db-stat-lbl">All-Time Visits</div>
        <div class="db-stat-sub">total records</div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     TWO-COLUMN MAIN AREA
     ══════════════════════════════════════════════════════════════ -->
<div class="db-main-cols">

    <!-- LEFT: Who's Inside + Recent Outs -->
    <div class="db-col-primary">

        <!-- Currently Inside -->
        <div class="card db-live-card">
            <div class="card-title">
                Currently Inside
                <span class="hub-feed-badge"><?= count($activeVisits) ?></span>
                <a href="/admin/live" class="db-card-link">Full view &rsaquo;</a>
            </div>
            <?php if (empty($activeVisits)): ?>
                <div class="db-empty">
                    <div class="db-empty-icon">&#127968;</div>
                    <div>Building is clear &mdash; no visitors inside.</div>
                </div>
            <?php else: ?>
                <div class="db-live-list" id="db-live-list">
                <?php foreach ($activeVisits as $v):
                    $isoUtc = gmdate('Y-m-d\TH:i:s\Z', strtotime($v['check_in_time']));
                    $initials = strtoupper(substr($v['first_name'],0,1).substr($v['last_name'],0,1));
                ?>
                <a href="/admin/visitor/<?= (int)$v['visitor_id'] ?>" class="db-live-row" data-checkin="<?= $isoUtc ?>">
                    <div class="db-live-avatar"><?= $initials ?></div>
                    <div class="db-live-info">
                        <div class="db-live-name"><?= View::e($v['first_name'].' '.$v['last_name']) ?></div>
                        <div class="db-live-meta">
                            <?= View::e($v['host_name'] ?? '—') ?>
                            <?php if ($v['reason_label']): ?> &middot; <?= View::e($v['reason_label']) ?><?php endif; ?>
                        </div>
                        <div class="db-live-bar-wrap">
                            <div class="db-live-bar-fill" data-checkin="<?= $isoUtc ?>"></div>
                        </div>
                    </div>
                    <div class="db-live-elapsed" data-checkin="<?= $isoUtc ?>">—</div>
                </a>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Checked Out Today -->
        <?php if (!empty($recentOut)): ?>
        <div class="card">
            <div class="card-title">
                Checked Out Today
                <span class="hub-feed-badge"><?= (int)($todayStats['completed'] ?? 0) ?></span>
                <a href="/admin/history" class="db-card-link">Full history &rsaquo;</a>
            </div>
            <div class="db-out-list">
            <?php foreach ($recentOut as $v):
                $durMin = (int)$v['duration_min'];
                $durFmt = $durMin >= 60
                    ? (int)($durMin/60).'h '.str_pad($durMin%60,2,'0',STR_PAD_LEFT).'m'
                    : $durMin.'m';
                $initials = strtoupper(substr($v['first_name'],0,1).substr($v['last_name'],0,1));
            ?>
            <a href="/admin/visitor/<?= (int)$v['visitor_id'] ?>" class="db-out-row">
                <div class="db-out-avatar"><?= $initials ?></div>
                <div class="db-out-info">
                    <div class="db-out-name"><?= View::e($v['first_name'].' '.$v['last_name']) ?></div>
                    <div class="db-out-meta"><?= View::e($v['host_name'] ?? '—') ?></div>
                </div>
                <div class="db-out-dur"><?= $durFmt ?></div>
            </a>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /db-col-primary -->

    <!-- RIGHT: 7-day spark + Top reason + Module tiles -->
    <div class="db-col-secondary">

        <!-- 7-Day Activity Sparkline -->
        <div class="card db-spark-card">
            <div class="card-title">Last 7 Days</div>
            <?php
            $sparkMax = max(array_values($sparkline)) ?: 1;
            $sparkDays = array_keys($sparkline);
            ?>
            <div class="db-spark-chart">
            <?php foreach ($sparkline as $day => $cnt):
                $pct = (int)round(($cnt / $sparkMax) * 100);
                $isToday = ($day === date('Y-m-d'));
            ?>
                <div class="db-spark-col">
                    <div class="db-spark-bar-wrap">
                        <div class="db-spark-bar <?= $isToday ? 'db-spark-today' : '' ?>"
                             style="height:<?= max(4, $pct) ?>%"
                             title="<?= $cnt ?> visit<?= $cnt!==1?'s':'' ?> on <?= date('D M j', strtotime($day)) ?>">
                        </div>
                    </div>
                    <div class="db-spark-lbl <?= $isToday ? 'db-spark-lbl-today' : '' ?>">
                        <?= date('D', strtotime($day)) ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php if ($topReason): ?>
            <div class="db-spark-footer">
                Top reason today: <strong><?= View::e($topReason['label']) ?></strong>
                <span>(<?= $topReason['cnt'] ?> visit<?= $topReason['cnt']!==1?'s':'' ?>)</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick search -->
        <div class="card db-search-card">
            <div class="card-title">Find a Visitor</div>
            <form method="GET" action="/admin/history" class="db-search-form">
                <input type="text" name="search" placeholder="Name or phone number&hellip;"
                       class="db-search-input" autocomplete="off">
                <button type="submit" class="button">Search</button>
            </form>
        </div>

        <!-- Module tiles -->
        <div class="db-modules">

            <a href="/logs" class="db-module db-module-hub">
                <div class="db-module-icon">&#9776;</div>
                <div class="db-module-body">
                    <div class="db-module-title">Log Hub</div>
                    <div class="db-module-desc">Central command</div>
                </div>
                <span class="db-module-arrow">&rsaquo;</span>
            </a>

            <a href="/admin/live" class="db-module db-module-live">
                <div class="db-module-icon">
                    <span class="live-pulse-dot" style="width:12px;height:12px;"></span>
                </div>
                <div class="db-module-body">
                    <div class="db-module-title">Live Logs</div>
                    <div class="db-module-desc"><?= $activeCount ?> inside now</div>
                </div>
                <span class="db-module-arrow">&rsaquo;</span>
            </a>

            <a href="/admin/history" class="db-module">
                <div class="db-module-icon">&#128203;</div>
                <div class="db-module-body">
                    <div class="db-module-title">Visit History</div>
                    <div class="db-module-desc"><?= (int)($todayStats['total'] ?? 0) ?> today</div>
                </div>
                <span class="db-module-arrow">&rsaquo;</span>
            </a>

            <a href="/admin/analytics" class="db-module">
                <div class="db-module-icon">&#128202;</div>
                <div class="db-module-body">
                    <div class="db-module-title">Analytics</div>
                    <div class="db-module-desc">Patterns &amp; trends</div>
                </div>
                <span class="db-module-arrow">&rsaquo;</span>
            </a>

            <a href="/admin/hosts" class="db-module">
                <div class="db-module-icon">&#128100;</div>
                <div class="db-module-body">
                    <div class="db-module-title">Hosts</div>
                    <div class="db-module-desc"><?= $hostCount ?> configured</div>
                </div>
                <span class="db-module-arrow">&rsaquo;</span>
            </a>

            <a href="/admin/reasons" class="db-module">
                <div class="db-module-icon">&#128196;</div>
                <div class="db-module-body">
                    <div class="db-module-title">Visit Reasons</div>
                    <div class="db-module-desc"><?= $reasonCount ?> reasons</div>
                </div>
                <span class="db-module-arrow">&rsaquo;</span>
            </a>

            <a href="/admin/setup/kiosk" class="db-module">
                <div class="db-module-icon">&#128241;</div>
                <div class="db-module-body">
                    <div class="db-module-title">Kiosk Setup</div>
                    <div class="db-module-desc">Configure check-in screen</div>
                </div>
                <span class="db-module-arrow">&rsaquo;</span>
            </a>

            <a href="/admin/settings" class="db-module">
                <div class="db-module-icon">&#9881;</div>
                <div class="db-module-body">
                    <div class="db-module-title">Settings</div>
                    <div class="db-module-desc">Org &amp; preferences</div>
                </div>
                <span class="db-module-arrow">&rsaquo;</span>
            </a>

            <?php if (in_array($_SESSION['user_role'] ?? '', ['org_admin','super_admin'])): ?>
            <a href="/admin/users" class="db-module">
                <div class="db-module-icon">&#128100;</div>
                <div class="db-module-body">
                    <div class="db-module-title">Users</div>
                    <div class="db-module-desc">Roles &amp; permissions</div>
                </div>
                <span class="db-module-arrow">&rsaquo;</span>
            </a>
            <?php endif; ?>

        </div><!-- /db-modules -->

    </div><!-- /db-col-secondary -->

</div><!-- /db-main-cols -->

<!-- Setup progress (only shown if incomplete) -->
<?php if ($progress['percent'] < 100): ?>
<div class="card setup-progress-card" style="margin-top:8px;">
    <div class="setup-progress-header">
        <div>
            <div class="card-title" style="margin-bottom:4px;padding-bottom:0;border:none;">Finish Setting Up</div>
            <p style="color:var(--text-muted);font-size:0.85rem;">A few more steps to get the most out of CheckIn.</p>
        </div>
        <span class="setup-pct"><?= $progress['percent'] ?>%</span>
    </div>
    <div class="progress-bar-wrap">
        <div class="progress-bar-fill" style="width:<?= $progress['percent'] ?>%"></div>
    </div>
    <div class="setup-items">
        <?php foreach ($progress['items'] as $item): ?>
        <a href="<?= View::e($item['action']) ?>" class="setup-item <?= $item['done'] ? 'done' : '' ?>">
            <span class="setup-item-icon"><?= $item['done'] ? '✓' : '○' ?></span>
            <span class="setup-item-body">
                <span class="setup-item-label">
                    <?= View::e($item['label']) ?>
                    <?php if (!empty($item['optional'])): ?>
                        <span class="optional-badge">optional</span>
                    <?php endif; ?>
                </span>
                <span class="setup-item-hint"><?= View::e($item['hint']) ?></span>
            </span>
            <span class="setup-item-arrow">&rsaquo;</span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
/* Live clock */
(function () {
    function pad(n) { return String(n).padStart(2, '0'); }
    function updateClock() {
        var now = new Date();
        var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var h = now.getHours(), m = now.getMinutes();
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        var el = document.getElementById('db-live-clock');
        if (el) el.textContent = days[now.getDay()] + ', ' + months[now.getMonth()] + ' ' + now.getDate() + ', ' + now.getFullYear() + ' \u2014 ' + h + ':' + pad(m) + ' ' + ampm;
    }
    updateClock();
    setInterval(updateClock, 30000);
})();

/* Live elapsed bars */
(function () {
    var fills   = document.querySelectorAll('.db-live-bar-fill');
    var elapsed = document.querySelectorAll('.db-live-elapsed');
    var parsedFills   = Array.from(fills).map(function(el){ return { el:el, ts: Math.floor(new Date(el.dataset.checkin).getTime()/1000) }; });
    var parsedElapsed = Array.from(elapsed).map(function(el){ return { el:el, ts: Math.floor(new Date(el.dataset.checkin).getTime()/1000) }; });

    function fmt(sec) {
        var h = Math.floor(sec/3600), m = Math.floor((sec%3600)/60);
        if (h > 0) return h+'h '+String(m).padStart(2,'0')+'m';
        if (m > 0) return m+'m';
        return sec+'s';
    }

    function tick() {
        var now = Math.floor(Date.now()/1000);
        var maxE = 1;
        parsedFills.forEach(function(d){ var e=now-d.ts; if(e>maxE) maxE=e; });

        parsedFills.forEach(function(d) {
            var e   = Math.max(0, now - d.ts);
            var pct = (e / maxE) * 88;
            d.el.style.width = pct.toFixed(1) + '%';
            if      (e >= 7200) d.el.style.background = 'linear-gradient(90deg,#f87171,#dc2626)';
            else if (e >= 3600) d.el.style.background = 'linear-gradient(90deg,#fde047,#ca8a04)';
            else                d.el.style.background = 'linear-gradient(90deg,#4ade80,#16a34a)';
        });

        parsedElapsed.forEach(function(d) {
            d.el.textContent = fmt(Math.max(0, now - d.ts));
        });
    }

    if (parsedFills.length) { setInterval(tick, 1000); tick(); }
})();
</script>
