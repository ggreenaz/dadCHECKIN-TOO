<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Core\View::e($title ?? 'Setup') ?> — dadCHECKIN-TOO</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .install-wrap   { max-width: 560px; margin: 48px auto; padding: 0 24px 80px; }
        .install-header { text-align: center; margin-bottom: 36px; }
        .install-header h1 { font-size: 1.5rem; font-weight: 700; }
        .install-header p  { color: var(--text-muted); margin-top: 6px; font-size: 0.9rem; }

        .step-track {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            margin-bottom: 36px;
            gap: 0;
        }
        .step-item { display: flex; flex-direction: column; align-items: center; }
        .step-circle {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 700;
            background: var(--border); color: var(--text-muted);
            position: relative; z-index: 1;
            border: 2px solid var(--border);
        }
        .step-circle.done   { background: var(--success); border-color: var(--success); color: #fff; }
        .step-circle.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .step-name { font-size: 0.7rem; color: var(--text-muted); margin-top: 6px; text-align: center; width: 80px; }
        .step-name.active { color: var(--primary); font-weight: 600; }
        .step-connector {
            height: 2px; width: 60px; background: var(--border);
            margin-top: 16px; flex-shrink: 0;
        }
        .step-connector.done { background: var(--success); }
    </style>
</head>
<body>
<div class="install-wrap">

    <div class="install-header">
        <h1>dadCHECKIN-TOO</h1>
        <p>Quick setup — you'll be running in under 2 minutes.</p>
    </div>

    <?php if (isset($step)): ?>
    <?php
    $steps   = ['Requirements', 'Database', 'Organization & Admin'];
    $total   = count($steps);
    $current = $step ?? 1;
    ?>
    <div class="step-track">
        <?php for ($i = 1; $i <= $total; $i++): ?>
            <div class="step-item">
                <div class="step-circle <?= $i < $current ? 'done' : ($i === $current ? 'active' : '') ?>">
                    <?= $i < $current ? '✓' : $i ?>
                </div>
                <div class="step-name <?= $i === $current ? 'active' : '' ?>"><?= $steps[$i - 1] ?></div>
            </div>
            <?php if ($i < $total): ?>
                <div class="step-connector <?= $i < $current ? 'done' : '' ?>"></div>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= \App\Core\View::e($flash['type']) ?>">
            <?= \App\Core\View::e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?= $content ?>

</div>
</body>
</html>
