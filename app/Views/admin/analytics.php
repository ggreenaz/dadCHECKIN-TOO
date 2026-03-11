<?php use App\Core\View; ?>

<!-- Header -->
<div class="an-header">
    <div>
        <h1 class="an-title">Analytics</h1>
        <p class="an-subtitle">Visit patterns, peak hours, and visitor intelligence &mdash; last 30 days</p>
    </div>
    <a href="/logs" class="back-link" style="align-self:center;">&larr; Log Hub</a>
</div>

<!-- ── Section 1: Peak Hours Heatmap ──────────────────────────── -->
<div class="card">
    <div class="card-title">Peak Hours &mdash; When Does Your Building Get Busy?</div>
    <div class="an-heatmap-wrap">
        <table class="an-heatmap">
            <thead>
                <tr>
                    <th class="an-heat-corner"></th>
                    <?php foreach ([2=>'Mon',3=>'Tue',4=>'Wed',5=>'Thu',6=>'Fri'] as $dow => $label): ?>
                        <th class="an-heat-col-head"><?= $label ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php for ($hr = 7; $hr <= 17; $hr++):
                $label = date('g A', mktime($hr, 0, 0));
            ?>
                <tr>
                    <td class="an-heat-row-head"><?= $label ?></td>
                    <?php foreach ([2,3,4,5,6] as $dow):
                        $cnt = $heatMap[$dow][$hr] ?? 0;
                        $pct = $heatMax > 0 ? $cnt / $heatMax : 0;
                        // Intensity 0–10 for CSS class
                        $intensity = $cnt === 0 ? 0 : max(1, (int)round($pct * 10));
                    ?>
                    <td class="an-heat-cell heat-<?= $intensity ?>"
                        title="<?= $cnt ?> visit<?= $cnt !== 1 ? 's' : '' ?>">
                        <?php if ($cnt > 0): ?>
                            <span class="an-heat-num"><?= $cnt ?></span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>
    <div class="an-heat-legend">
        <span class="an-legend-label">Less</span>
        <?php for ($i = 0; $i <= 10; $i++): ?>
            <div class="an-legend-swatch heat-<?= $i ?>"></div>
        <?php endfor; ?>
        <span class="an-legend-label">More</span>
    </div>
</div>

<!-- ── Section 2: Two columns — DOW + Reasons ─────────────────── -->
<div class="an-two-col">

    <!-- Day of week -->
    <div class="card" style="margin-bottom:0">
        <div class="card-title">Visits by Day of Week</div>
        <div class="an-dow-chart">
        <?php foreach ([2=>'Mon',3=>'Tue',4=>'Wed',5=>'Thu',6=>'Fri',7=>'Sat',1=>'Sun'] as $dow => $label):
            $cnt = $dowCounts[$dow] ?? 0;
            $pct = $dowMax > 0 ? (int)round(($cnt / $dowMax) * 100) : 0;
            $isWeekend = in_array($dow, [1,7]);
        ?>
        <div class="an-dow-row">
            <div class="an-dow-label <?= $isWeekend ? 'an-dow-weekend' : '' ?>"><?= $label ?></div>
            <div class="an-dow-track">
                <div class="an-dow-fill <?= $isWeekend ? 'an-dow-fill-weekend' : '' ?>"
                     style="width:<?= $pct ?>%"></div>
            </div>
            <div class="an-dow-count"><?= $cnt ?></div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- Visit reasons -->
    <div class="card" style="margin-bottom:0">
        <div class="card-title">Visits by Reason</div>
        <div class="an-reason-chart">
        <?php
        $reasonColors = ['#60a5fa','#4ade80','#fbbf24','#f97316','#a78bfa','#f472b6','#34d399','#fb7185','#38bdf8','#c084fc'];
        foreach ($reasons as $i => $r):
            $pct     = (int)round(($r['cnt'] / $reasonMax) * 100);
            $sharePct= round(($r['cnt'] / $reasonTotal) * 100, 1);
            $color   = $reasonColors[$i % count($reasonColors)];
        ?>
        <div class="an-reason-row">
            <div class="an-reason-label"><?= View::e($r['label']) ?></div>
            <div class="an-reason-track">
                <div class="an-reason-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
            </div>
            <div class="an-reason-meta">
                <span class="an-reason-cnt"><?= $r['cnt'] ?></span>
                <span class="an-reason-pct"><?= $sharePct ?>%</span>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- ── Section 3: Busiest Hosts ───────────────────────────────── -->
<div class="card">
    <div class="card-title">Busiest Hosts</div>
    <div class="an-hosts">
    <?php foreach ($hosts as $i => $h):
        $pct    = $hostMax > 0 ? (int)round(($h['visit_cnt'] / $hostMax) * 90) : 0;
        $avgMin = (int)$h['avg_min'];
        $avgFmt = $avgMin >= 60
            ? (int)($avgMin/60).'h '.str_pad($avgMin%60,2,'0',STR_PAD_LEFT).'m'
            : $avgMin.'m';
    ?>
    <div class="an-host-row">
        <div class="an-host-rank"><?= $i+1 ?></div>
        <div class="an-host-info">
            <a href="/admin/history?host_id=<?= (int)$h['host_id'] ?>" class="an-host-name"><?= View::e($h['host_name']) ?></a>
            <?php if ($h['dept_name']): ?>
                <span class="an-host-dept"><?= View::e($h['dept_name']) ?></span>
            <?php endif; ?>
        </div>
        <div class="an-host-bar-wrap">
            <div class="an-host-bar" style="width:<?= $pct ?>%"></div>
        </div>
        <div class="an-host-stats">
            <span class="an-host-cnt"><?= $h['visit_cnt'] ?> visits</span>
            <?php if ($avgMin > 0): ?>
                <span class="an-host-avg">avg <?= $avgFmt ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- ── Section 4: Repeat Visitors ────────────────────────────── -->
<div class="an-two-col">

    <!-- Repeat rate summary -->
    <div class="card" style="margin-bottom:0">
        <div class="card-title">Visitor Return Rate</div>

        <?php
        $total      = (int)$repeatStats['total'];
        $repeats    = (int)$repeatStats['repeats'];
        $firstTimers= (int)$repeatStats['first_timers'];
        $repeatPct  = $total > 0 ? round(($repeats / $total) * 100) : 0;
        ?>

        <div class="an-return-ring-wrap">
            <svg class="an-ring-svg" viewBox="0 0 120 120">
                <!-- Background circle -->
                <circle cx="60" cy="60" r="48" fill="none" stroke="#e2e8f0" stroke-width="14"/>
                <!-- Filled arc for repeat % -->
                <?php
                $circumference = 2 * M_PI * 48;
                $dash = ($repeatPct / 100) * $circumference;
                ?>
                <circle cx="60" cy="60" r="48" fill="none"
                        stroke="#0073b1" stroke-width="14"
                        stroke-dasharray="<?= round($dash,2) ?> <?= round($circumference,2) ?>"
                        stroke-dashoffset="<?= round($circumference / 4, 2) ?>"
                        stroke-linecap="round"/>
                <text x="60" y="55" text-anchor="middle" font-size="20" font-weight="800" fill="#0f172a"><?= $repeatPct ?>%</text>
                <text x="60" y="72" text-anchor="middle" font-size="9" fill="#64748b">return rate</text>
            </svg>
        </div>

        <div class="an-return-stats">
            <div class="an-return-stat">
                <div class="an-return-value" style="color:var(--primary)"><?= $repeats ?></div>
                <div class="an-return-label">Repeat Visitors</div>
            </div>
            <div class="an-return-stat">
                <div class="an-return-value" style="color:var(--text-muted)"><?= $firstTimers ?></div>
                <div class="an-return-label">First-Timers</div>
            </div>
            <div class="an-return-stat">
                <div class="an-return-value"><?= (int)$repeatStats['max_visits'] ?></div>
                <div class="an-return-label">Most Visits (1 person)</div>
            </div>
        </div>
    </div>

    <!-- Top frequent visitors -->
    <div class="card" style="margin-bottom:0">
        <div class="card-title">Most Frequent Visitors</div>
        <div class="an-freq-list">
        <?php foreach ($topVisitors as $i => $tv):
            $pct    = $topVisitorMax > 0 ? (int)round(($tv['visit_cnt'] / $topVisitorMax) * 88) : 0;
            $avgFmt = '';
            if ($tv['avg_min'] > 0) {
                $m = (int)$tv['avg_min'];
                $avgFmt = $m >= 60
                    ? (int)($m/60).'h '.str_pad($m%60,2,'0',STR_PAD_LEFT).'m avg'
                    : $m.'m avg';
            }
        ?>
        <a href="/admin/visitor/<?= (int)$tv['visitor_id'] ?>" class="an-freq-row">
            <div class="an-freq-rank"><?= $i+1 ?></div>
            <div class="an-freq-info">
                <div class="an-freq-name"><?= View::e($tv['first_name'].' '.$tv['last_name']) ?></div>
                <div class="an-freq-bar-wrap">
                    <div class="an-freq-bar" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <div class="an-freq-stats">
                <span class="an-freq-cnt"><?= $tv['visit_cnt'] ?>x</span>
                <?php if ($avgFmt): ?><span class="an-freq-avg"><?= $avgFmt ?></span><?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
        </div>
    </div>

</div>
