<?php use App\Core\View; ?>

<div class="checkin-wrap">
    <div class="card" style="max-width:480px;margin:60px auto;text-align:center;">

        <?php if (!empty($is_staff)): ?>
        <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
            <a href="/admin" class="button button-outline button-sm">← Back to Admin</a>
        </div>
        <?php endif; ?>

        <div style="font-size:3rem;margin-bottom:12px;">✓</div>
        <h1 style="font-size:1.4rem;margin:0 0 4px;">You are checked in</h1>
        <p style="color:var(--text-muted);font-size:0.9rem;margin:0 0 28px;">
            Ready to leave? Check out below.
        </p>

        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:20px;margin-bottom:28px;text-align:left;">
            <div style="display:grid;grid-template-columns:auto 1fr;gap:8px 16px;font-size:0.9rem;">
                <?php if (!empty($visit['host_name'])): ?>
                <span style="color:var(--text-muted);">Visiting</span>
                <span style="font-weight:600;"><?= View::e($visit['host_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($visit['reason_label'])): ?>
                <span style="color:var(--text-muted);">Reason</span>
                <span><?= View::e($visit['reason_label']) ?></span>
                <?php endif; ?>
                <span style="color:var(--text-muted);">Checked in</span>
                <span><?= View::e(date('g:i A', strtotime($visit['check_in_time']))) ?></span>
            </div>
        </div>

        <form method="POST" action="/checkin/checkout">
            <button type="submit" class="button" style="width:100%;padding:14px;font-size:1.1rem;">
                Check Out
            </button>
        </form>

        <?php if (empty($is_staff)): ?>
        <p style="margin-top:16px;">
            <a href="/checkin/cancel" style="font-size:0.85rem;color:var(--text-muted);">Not you? Sign out</a>
        </p>
        <?php endif; ?>

    </div>
</div>
