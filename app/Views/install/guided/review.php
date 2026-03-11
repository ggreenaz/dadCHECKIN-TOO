<?php use App\Core\View; ?>

<div class="card">
    <div class="card-title">Step 7 of 7 — Review &amp; Launch</div>

    <div style="background:var(--success-bg,#f0fdf4);border:1px solid var(--success-border,#bbf7d0);
                border-radius:8px;padding:20px;margin-bottom:24px;">
        <div style="font-size:1.5rem;margin-bottom:8px;">🎉</div>
        <div style="font-weight:700;font-size:1.1rem;margin-bottom:6px;color:#166534;">
            Your upgrade is almost complete!
        </div>
        <p style="color:#166534;font-size:0.9rem;margin:0;">
            Click <strong>Launch CheckIn</strong> below to finalize the upgrade and go to your admin dashboard.
        </p>
    </div>

    <?php
    $stepDefs = [
        'organization'  => ['label' => 'Organization & Admin', 'optional' => false],
        'migration'     => ['label' => 'Data Migration',       'optional' => false],
        'departments'   => ['label' => 'Departments',          'optional' => true],
        'auth'          => ['label' => 'Authentication',       'optional' => true],
        'kiosk'         => ['label' => 'Kiosk Fields',         'optional' => true],
        'notifications' => ['label' => 'Notifications',        'optional' => true],
    ];
    $skippedArr = $skipped ?? [];
    ?>

    <div style="margin-bottom:24px;">
        <div style="font-weight:600;margin-bottom:12px;">Setup Summary</div>
        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
            <?php foreach ($stepDefs as $key => $info): ?>
            <?php $wasSkipped = in_array($key, $skippedArr); ?>
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:10px 12px;"><?= View::e($info['label']) ?></td>
                <td style="padding:10px 12px;">
                    <?php if ($wasSkipped): ?>
                        <span style="color:var(--text-muted);font-size:0.82rem;">
                            ↷ Configure Later
                        </span>
                    <?php else: ?>
                        <span style="color:var(--success);font-weight:600;">✓ Done</span>
                    <?php endif; ?>
                </td>
                <td style="padding:10px 12px;text-align:right;">
                    <?php if ($wasSkipped): ?>
                        <a href="/admin/setup/<?= View::e($key) ?>"
                           style="font-size:0.8rem;color:var(--primary);">Set up in Admin →</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php if (!empty($skippedArr)): ?>
    <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:6px;
                padding:12px 14px;font-size:0.82rem;color:var(--text-muted);margin-bottom:24px;">
        The steps marked <em>Configure Later</em> are available any time in
        <strong>Admin &rarr; Guided Setup</strong>. Your kiosk will work without them.
    </div>
    <?php endif; ?>

    <form method="POST" action="/install/guided-upgrade/finish">
        <button type="submit" class="button" style="font-size:1rem;padding:12px 28px;">
            Launch CheckIn &rarr;
        </button>
    </form>
</div>
