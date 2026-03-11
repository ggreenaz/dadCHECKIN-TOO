<?php use App\Core\View; ?>

<style>
.sc-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
.sc-table th {
    text-align: left; padding: 8px 12px;
    background: var(--surface-2); color: var(--text-muted);
    font-size: 0.75rem; letter-spacing: .05em; text-transform: uppercase;
    border-bottom: 2px solid var(--border);
}
.sc-table td { padding: 10px 12px; border-bottom: 1px solid var(--border); vertical-align: top; font-size: 0.9rem; }
.sc-table tr:last-child td { border-bottom: none; }

.sc-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 700;
}
.sc-badge.ok   { background: #d1fae5; color: #065f46; }
.sc-badge.warn { background: #fef3c7; color: #92400e; }
.sc-badge.fail { background: #fee2e2; color: #991b1b; }

.sc-fixed { font-size: 0.78rem; color: var(--success); font-weight: 600; margin-top: 4px; }
.sc-cmd {
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 8px 12px;
    margin-top: 8px;
    font-family: monospace;
    font-size: 0.82rem;
    color: var(--text);
    white-space: pre-wrap;
    word-break: break-all;
}

.sc-summary {
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 14px;
    font-weight: 600;
}
.sc-summary.pass { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.sc-summary.fail { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.sc-summary.warn { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.sc-summary-icon { font-size: 1.6rem; flex-shrink: 0; }
.sc-summary-body p { font-weight: 400; font-size: 0.88rem; margin: 3px 0 0; }
</style>

<?php
$failCount  = count(array_filter($checks, fn($c) => $c['status'] === 'fail'));
$warnCount  = count(array_filter($checks, fn($c) => $c['status'] === 'warn'));
$fixedCount = count(array_filter($checks, fn($c) => $c['fixed']));
$allOk      = $failCount === 0;
?>

<?php if ($hasFixed): ?>
<div class="alert alert-success" style="margin-bottom:16px;">
    <?= $fixedCount ?> issue<?= $fixedCount !== 1 ? 's' : '' ?> fixed automatically.
</div>
<?php endif; ?>

<?php if ($failCount > 0): ?>
<div class="sc-summary fail">
    <div class="sc-summary-icon">✗</div>
    <div class="sc-summary-body">
        <?= $failCount ?> required check<?= $failCount !== 1 ? 's' : '' ?> failed.
        <p>Fix the items marked <strong>Failed</strong> below, then reload this page to re-run the check.</p>
    </div>
</div>
<?php elseif ($warnCount > 0): ?>
<div class="sc-summary warn">
    <div class="sc-summary-icon">⚠</div>
    <div class="sc-summary-body">
        All required checks passed — <?= $warnCount ?> optional item<?= $warnCount !== 1 ? 's' : '' ?> with warnings.
        <p>You can continue now. Warnings won't block installation.</p>
    </div>
</div>
<?php else: ?>
<div class="sc-summary pass">
    <div class="sc-summary-icon">✓</div>
    <div class="sc-summary-body">
        All checks passed — your server is ready.
        <p>Click Continue to proceed with installation.</p>
    </div>
</div>
<?php endif; ?>

<table class="sc-table">
    <thead>
        <tr>
            <th style="width:40%">Check</th>
            <th style="width:15%">Status</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($checks as $c): ?>
    <tr>
        <td><?= $c['label'] ?></td>
        <td>
            <span class="sc-badge <?= $c['status'] ?>">
                <?= $c['status'] === 'ok' ? '✓ OK' : ($c['status'] === 'warn' ? '⚠ Warning' : '✗ Failed') ?>
            </span>
        </td>
        <td>
            <?= View::e($c['detail']) ?>
            <?php if ($c['fixed']): ?>
                <div class="sc-fixed">✓ Fixed automatically</div>
            <?php endif; ?>
            <?php if (!empty($c['cmd'])): ?>
                <div class="sc-cmd"><?= htmlspecialchars($c['cmd']) ?></div>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div style="display:flex; gap:12px; justify-content:space-between; margin-top:24px; flex-wrap:wrap;">
    <form method="POST" action="/install/syscheck">
        <button type="submit" class="button" style="background:var(--surface-2);color:var(--text);border:1px solid var(--border);">
            ↻ Re-run Check
        </button>
    </form>

    <?php if ($allOk): ?>
    <form method="POST" action="/install/syscheck/continue">
        <button type="submit" class="button">Continue to Install →</button>
    </form>
    <?php else: ?>
    <button class="button" disabled style="opacity:.5;cursor:not-allowed;" title="Fix all failed checks first">
        Continue to Install →
    </button>
    <?php endif; ?>
</div>
