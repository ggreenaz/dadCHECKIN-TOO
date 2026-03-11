<?php use App\Core\View; ?>

<div class="card">
    <div style="text-align:center;padding:8px 0 20px;">
        <div style="font-size:2.5rem;margin-bottom:12px;">⛔</div>
        <div style="font-weight:700;font-size:1.2rem;margin-bottom:8px;">Abort the Upgrade?</div>
        <p style="color:var(--text-muted);font-size:0.9rem;max-width:420px;margin:0 auto;">
            This will stop the upgrade process and return your system to its previous state.
        </p>
    </div>

    <?php if ($hasBackup): ?>
    <div style="background:var(--success-bg,#f0fdf4);border:1px solid var(--success-border,#bbf7d0);
                border-radius:6px;padding:14px 16px;margin-bottom:20px;font-size:0.875rem;">
        <strong style="color:#166534;">✓ Your original configuration can be restored.</strong><br>
        <span style="color:#166534;">
            A backup of your original database settings was saved when the upgrade started.
            Aborting will restore it automatically.
        </span>
    </div>
    <?php else: ?>
    <div style="background:var(--warning-bg,#fffbeb);border:1px solid var(--warning-border,#fcd34d);
                border-radius:6px;padding:14px 16px;margin-bottom:20px;font-size:0.875rem;color:#78350f;">
        <strong>⚠ No backup config found.</strong><br>
        You may need to manually restore your original database settings after aborting.
    </div>
    <?php endif; ?>

    <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:6px;
                padding:14px 16px;margin-bottom:24px;font-size:0.875rem;">
        <strong>What happens when you abort:</strong>
        <ul style="margin:8px 0 0 16px;line-height:1.8;color:var(--text-muted);">
            <li>Your original dadtoo database is <strong style="color:var(--text);">completely untouched</strong> — no data was changed.</li>
            <li>Your original database configuration will be restored.</li>
            <li>The install wizard will be reset.</li>
            <?php if ($newDb): ?>
            <li>The new <code><?= View::e($newDb) ?></code> database (if created) can optionally be removed.</li>
            <?php endif; ?>
        </ul>
    </div>

    <form method="POST" action="/install/abort">
        <?php if ($newDb): ?>
        <div style="margin-bottom:20px;">
            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;
                          padding:14px;border:1px solid var(--border);border-radius:8px;">
                <input type="checkbox" name="drop_new_db" value="1" style="margin-top:2px;flex-shrink:0;">
                <div>
                    <strong>Also delete the new <code><?= View::e($newDb) ?></code> database</strong>
                    <div style="font-size:0.82rem;color:var(--text-muted);margin-top:2px;">
                        Removes the partially created CheckIn database. Leave unchecked to keep it
                        for a future upgrade attempt.
                    </div>
                </div>
            </label>
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:12px;align-items:center;">
            <button type="submit" class="button"
                    style="background:var(--danger,#dc2626);border-color:var(--danger,#dc2626);">
                Yes, Abort Upgrade
            </button>
            <a href="javascript:history.back()" class="button button-outline">
                ← Keep Going
            </a>
        </div>
    </form>
</div>
