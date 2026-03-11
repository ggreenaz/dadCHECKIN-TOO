<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Core\View::e($title ?? 'Guided Upgrade') ?> — dadCHECKIN-TOO</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .upgrade-wrap  { max-width: 680px; margin: 48px auto; padding: 0 24px 80px; }
        .upgrade-header { text-align: center; margin-bottom: 32px; }
        .upgrade-header h1 { font-size: 1.5rem; font-weight: 700; }
        .upgrade-header p  { color: var(--text-muted); margin-top: 6px; font-size: 0.9rem; }

        .upgrade-progress { margin-bottom: 36px; }
        .progress-track {
            height: 6px; background: var(--border); border-radius: 3px;
            margin-bottom: 14px; overflow: hidden;
        }
        .progress-fill {
            height: 100%; background: var(--primary); border-radius: 3px;
            transition: width .4s ease;
        }
        .step-labels {
            display: flex; justify-content: space-between; gap: 2px;
        }
        .step-lbl {
            flex: 1; font-size: 0.67rem; text-align: center;
            color: var(--text-muted); line-height: 1.3;
        }
        .step-lbl.active  { color: var(--primary); font-weight: 600; }
        .step-lbl.done    { color: var(--success); }

        .feature-callout {
            background: var(--surface-2);
            border-left: 3px solid var(--primary);
            border-radius: 0 6px 6px 0;
            padding: 12px 16px; margin-bottom: 20px; font-size: 0.875rem;
        }
        .feature-callout strong { display: block; margin-bottom: 4px; }

        .optional-tag {
            display: inline-block; font-size: 0.7rem;
            background: var(--surface-2); border: 1px solid var(--border);
            color: var(--text-muted); padding: 2px 8px; border-radius: 12px;
            margin-left: 8px; vertical-align: middle;
        }
        .step-actions {
            display: flex; gap: 12px; align-items: center; margin-top: 24px;
        }
        .btn-later {
            background: none; border: 1px solid var(--border); color: var(--text-muted);
            padding: 8px 16px; border-radius: 6px; cursor: pointer;
            font-size: 0.875rem; text-decoration: none;
        }
        .btn-later:hover { border-color: var(--text-muted); color: var(--text); }
    </style>
</head>
<body>
<div class="upgrade-wrap">

    <div class="upgrade-header">
        <h1>Guided Upgrade</h1>
        <p>Migrating from dadtoo &mdash; walk through each step at your own pace.</p>
    </div>

    <?php
    $stepKeys   = array_keys($steps);
    $totalSteps = count($stepKeys);
    $curIdx     = array_search($currentStep, $stepKeys);
    $pct        = $totalSteps > 1 ? round($curIdx / ($totalSteps - 1) * 100) : 0;
    $skippedArr = $skipped ?? [];
    ?>

    <div class="upgrade-progress">
        <div class="progress-track">
            <div class="progress-fill" style="width:<?= $pct ?>%"></div>
        </div>
        <div class="step-labels">
            <?php foreach ($steps as $key => $info):
                $idx    = array_search($key, $stepKeys);
                $isDone = $idx < $curIdx;
                $isAct  = $key === $currentStep;
                $cls    = $isDone ? 'done' : ($isAct ? 'active' : '');
            ?>
                <div class="step-lbl <?= $cls ?>">
                    <?= $isDone ? '✓ ' : '' ?><?= htmlspecialchars($info['label']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= \App\Core\View::e($flash['type']) ?>">
            <?= \App\Core\View::e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?= $content ?>

    <div style="text-align:center;margin-top:32px;padding-top:20px;border-top:1px solid var(--border);">
        <a href="/install/abort"
           style="font-size:0.8rem;color:var(--text-muted);text-decoration:none;"
           onmouseover="this.style.color='var(--danger)'"
           onmouseout="this.style.color='var(--text-muted)'">
            ⛔ Abort upgrade and return to original system
        </a>
    </div>

</div>
</body>
</html>
