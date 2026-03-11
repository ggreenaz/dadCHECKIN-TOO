<?php use App\Core\View; ?>

<!-- Hub header -->
<div class="hub-header">
    <div class="hub-title-group">
        <h1 class="hub-title">Log Hub</h1>
        <span class="hub-subtitle">Central command &mdash; <?= date('l, F j, Y') ?></span>
    </div>
    <div class="hub-live-indicator">
        <span class="live-pulse-dot"></span>
        <span>Live</span>
    </div>
</div>

<!-- Stat bar -->
<div class="hub-stats">
    <div class="hub-stat hub-stat-primary">
        <div class="hub-stat-value"><?= (int)($stats['active'] ?? 0) ?></div>
        <div class="hub-stat-label">Inside Now</div>
    </div>
    <div class="hub-stat">
        <div class="hub-stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
        <div class="hub-stat-label">Today&rsquo;s Total</div>
    </div>
    <div class="hub-stat hub-stat-warning">
        <div class="hub-stat-value"><?= (int)($stats['extended'] ?? 0) ?></div>
        <div class="hub-stat-label">Extended (2h+)</div>
    </div>
    <div class="hub-stat hub-stat-success">
        <div class="hub-stat-value"><?= (int)($stats['completed'] ?? 0) ?></div>
        <div class="hub-stat-label">Completed</div>
    </div>
    <div class="hub-stat hub-stat-danger">
        <div class="hub-stat-value"><?= (int)($stats['no_show'] ?? 0) ?></div>
        <div class="hub-stat-label">No Shows</div>
    </div>
</div>

<!-- Quick visitor search -->
<div class="hub-search-bar">
    <form method="GET" action="/admin/history" class="hub-search-form">
        <input type="text" name="search" placeholder="&#128269;  Search visitor by name or phone&hellip;"
               class="hub-search-input" autocomplete="off">
        <button type="submit" class="button">Search</button>
    </form>
</div>

<!-- Action tiles -->
<div class="hub-tiles">

    <a href="/admin/live/demo" class="hub-tile hub-tile-live">
        <div class="hub-tile-icon">
            <span class="live-pulse-dot" style="width:16px;height:16px;"></span>
        </div>
        <div class="hub-tile-body">
            <div class="hub-tile-title">Live Logs</div>
            <div class="hub-tile-desc">Real-time view of every visitor currently inside with elapsed-time progress bars</div>
        </div>
        <div class="hub-tile-count"><?= (int)($stats['active'] ?? 0) ?> inside</div>
        <span class="hub-tile-arrow">&rsaquo;</span>
    </a>

    <a href="/admin/history" class="hub-tile">
        <div class="hub-tile-icon hub-icon-history">&#128203;</div>
        <div class="hub-tile-body">
            <div class="hub-tile-title">Visit History</div>
            <div class="hub-tile-desc">Browse, filter, and export the complete log of all visits by date, host, or status</div>
        </div>
        <div class="hub-tile-count"><?= (int)($stats['total'] ?? 0) ?> today</div>
        <span class="hub-tile-arrow">&rsaquo;</span>
    </a>

    <a href="/admin/history" class="hub-tile">
        <div class="hub-tile-icon hub-icon-visitor">&#128100;</div>
        <div class="hub-tile-body">
            <div class="hub-tile-title">Visitor Profiles</div>
            <div class="hub-tile-desc">Click any visitor in History or Live Logs to see their full visit history and patterns</div>
        </div>
        <div class="hub-tile-count">&nbsp;</div>
        <span class="hub-tile-arrow">&rsaquo;</span>
    </a>

    <a href="/admin/analytics" class="hub-tile">
        <div class="hub-tile-icon hub-icon-analytics">&#128202;</div>
        <div class="hub-tile-body">
            <div class="hub-tile-title">Analytics</div>
            <div class="hub-tile-desc">Peak hours heatmap, visit patterns by day of week, busiest hosts, repeat visitor rates</div>
        </div>
        <div class="hub-tile-count">&nbsp;</div>
        <span class="hub-tile-arrow">&rsaquo;</span>
    </a>

</div>

<!-- Two-column feed -->
<div class="hub-feeds">

    <!-- Currently inside -->
    <div class="card hub-feed-card">
        <div class="card-title">
            Currently Inside
            <span class="hub-feed-badge"><?= count($activeVisits) ?></span>
        </div>
        <?php if (empty($activeVisits)): ?>
            <p class="text-muted">No active visitors right now.</p>
        <?php else: ?>
            <div class="hub-feed-list" id="hub-live-list">
            <?php foreach ($activeVisits as $v):
                $isoUtc  = gmdate('Y-m-d\TH:i:s\Z', strtotime($v['check_in_time']));
            ?>
            <a href="/admin/visitor/<?= (int)$v['visitor_id'] ?>" class="hub-feed-row" data-checkin="<?= $isoUtc ?>">
                <div class="hub-feed-avatar"><?= strtoupper(substr($v['first_name'],0,1).substr($v['last_name'],0,1)) ?></div>
                <div class="hub-feed-info">
                    <div class="hub-feed-name"><?= View::e($v['first_name'].' '.$v['last_name']) ?></div>
                    <div class="hub-feed-meta"><?= View::e($v['host_name'] ?? '—') ?> &middot; <?= View::e($v['reason_label'] ?? '—') ?></div>
                    <div class="hub-feed-bar-wrap">
                        <div class="hub-feed-bar-fill" data-checkin="<?= $isoUtc ?>"></div>
                    </div>
                </div>
                <div class="hub-feed-elapsed" data-checkin="<?= $isoUtc ?>">—</div>
            </a>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recently completed -->
    <div class="card hub-feed-card">
        <div class="card-title">
            Checked Out Today
            <span class="hub-feed-badge"><?= count($recentCompleted) ?></span>
        </div>
        <?php if (empty($recentCompleted)): ?>
            <p class="text-muted">No completed visits yet today.</p>
        <?php else: ?>
            <div class="hub-feed-list">
            <?php foreach ($recentCompleted as $v): ?>
            <a href="/admin/visitor/<?= (int)$v['visitor_id'] ?>" class="hub-feed-row hub-feed-row-done">
                <div class="hub-feed-avatar hub-feed-avatar-done"><?= strtoupper(substr($v['first_name'],0,1).substr($v['last_name'],0,1)) ?></div>
                <div class="hub-feed-info">
                    <div class="hub-feed-name"><?= View::e($v['first_name'].' '.$v['last_name']) ?></div>
                    <div class="hub-feed-meta"><?= View::e($v['host_name'] ?? '—') ?> &middot; <?= View::e($v['reason_label'] ?? '—') ?></div>
                </div>
                <div class="hub-feed-dur">
                    <?php if ($v['duration_min'] > 0):
                        $h = (int)($v['duration_min']/60);
                        $m = $v['duration_min']%60;
                        echo $h > 0 ? $h.'h '.str_pad($m,2,'0',STR_PAD_LEFT).'m' : $m.'m';
                    endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
(function () {
    var items = document.querySelectorAll('[data-checkin]');
    var parsed = [];
    items.forEach(function (el) {
        parsed.push({
            el:  el,
            ts:  Math.floor(new Date(el.dataset.checkin).getTime() / 1000),
            isFill: el.classList.contains('hub-feed-bar-fill'),
            isElapsed: el.classList.contains('hub-feed-elapsed'),
        });
    });

    // Separate fills and elapsed labels from rows
    var fills   = document.querySelectorAll('.hub-feed-bar-fill');
    var elapsed = document.querySelectorAll('.hub-feed-elapsed');
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
        var maxElapsed = 1;
        parsedFills.forEach(function(d){ var e=now-d.ts; if(e>maxElapsed) maxElapsed=e; });

        parsedFills.forEach(function(d) {
            var e   = Math.max(0, now - d.ts);
            var pct = (e / maxElapsed) * 88;
            d.el.style.width = pct.toFixed(1) + '%';
            if      (e >= 7200) d.el.style.background = 'linear-gradient(90deg,#f87171,#dc2626)';
            else if (e >= 3600) d.el.style.background = 'linear-gradient(90deg,#fde047,#ca8a04)';
            else                d.el.style.background = 'linear-gradient(90deg,#4ade80,#16a34a)';
        });

        parsedElapsed.forEach(function(d) {
            var e = Math.max(0, now - d.ts);
            d.el.textContent = fmt(e);
        });
    }

    setInterval(tick, 1000);
    tick();
})();
</script>
