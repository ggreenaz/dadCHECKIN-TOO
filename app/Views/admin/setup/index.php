<?php use App\Core\View; ?>

<div class="setup-intro">
    <h2>Guided Configuration</h2>
    <p>Work through each stage at your own pace, in any order. You can come back here any time from the admin dashboard.</p>
</div>

<?php
$required = array_filter($stages, fn($s) => !$s['optional']);
$done     = array_filter($required, fn($s) => $s['done']);
$pct      = count($required) ? round(count($done) / count($required) * 100) : 100;
?>

<div class="setup-progress-bar-card">
    <div class="setup-pb-header">
        <span>Required steps complete</span>
        <span class="setup-pb-pct"><?= $pct ?>%</span>
    </div>
    <div class="progress-bar-wrap">
        <div class="progress-bar-fill" style="width:<?= $pct ?>%"></div>
    </div>
</div>

<div class="setup-timeline">
    <?php $i = 1; foreach ($stages as $slug => $stage): ?>
    <div class="timeline-item <?= $stage['done'] ? 'done' : '' ?>">
        <div class="timeline-connector <?= $stage['done'] ? 'done' : '' ?>"></div>
        <div class="timeline-dot <?= $stage['done'] ? 'done' : '' ?>">
            <?php if ($stage['done']): ?>✓<?php else: ?><?= $i ?><?php endif; ?>
        </div>
        <div class="timeline-card">
            <div class="timeline-card-header">
                <div class="timeline-icon"><?= $stage['icon'] ?></div>
                <div class="timeline-info">
                    <div class="timeline-label">
                        <?= View::e($stage['label']) ?>
                        <?php if ($stage['optional']): ?>
                            <span class="optional-badge">optional</span>
                        <?php endif; ?>
                    </div>
                    <div class="timeline-desc"><?= View::e($stage['desc']) ?></div>
                </div>
                <div class="timeline-meta">
                    <?php if (isset($stage['count'])): ?>
                        <span class="timeline-count"><?= $stage['count'] ?> configured</span>
                    <?php endif; ?>
                    <a href="/admin/setup/<?= $slug ?>" class="button <?= $stage['done'] ? 'button-outline' : '' ?> button-sm">
                        <?= $stage['done'] ? 'Edit' : 'Configure' ?> &rsaquo;
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php $i++; endforeach; ?>
</div>

<?php if ($pct >= 100): ?>
<div class="alert alert-success" style="margin-top: 24px;">
    All required steps are complete. Your CheckIn system is fully configured!
    <a href="/checkin" style="margin-left:12px;font-weight:600;">View Check-In Page &rarr;</a>
</div>
<?php endif; ?>
