<?php use App\Core\View; ?>

<div style="text-align:center;margin-bottom:28px;">
    <div style="font-size:2.5rem;margin-bottom:8px;">🔄</div>
    <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:8px;">Legacy dadtoo Data Detected</h2>
    <p style="color:var(--text-muted);font-size:0.9rem;">
        We found your existing dadtoo database structure. Choose how you want to upgrade.
    </p>
</div>

<?php if (!empty($migrationOutput)): ?>
<div class="card" style="margin-bottom:24px;">
    <div style="font-weight:600;margin-bottom:8px;">Migration Output</div>
    <pre style="background:var(--surface-2);border:1px solid var(--border);border-radius:6px;
                padding:14px;font-size:0.78rem;overflow-x:auto;white-space:pre-wrap;max-height:200px;overflow-y:auto;"><?= View::e($migrationOutput) ?></pre>
    <?php if (!str_contains($migrationOutput, '✓ Migration complete.')): ?>
    <div class="alert alert-error" style="margin-top:12px;">
        Migration encountered errors. Please review the output above before continuing.
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;">

    <!-- Quick Upgrade -->
    <div class="card" style="text-align:center;padding:28px 24px;">
        <div style="font-size:1.8rem;margin-bottom:12px;">⚡</div>
        <div style="font-weight:700;font-size:1.1rem;margin-bottom:10px;">Quick Upgrade</div>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:20px;line-height:1.5;">
            Migrate your data automatically, create an admin account, and go straight to the dashboard.
            Configure everything else at your own pace.
        </p>
        <form method="POST" action="/install/upgrade/quick">
            <div class="form-group" style="text-align:left;margin-bottom:16px;">
                <label for="quick_source_db" style="font-size:0.8rem;">Source Database</label>
                <input type="text" name="source_db" id="quick_source_db"
                       value="<?= View::e($sourceDb ?? '') ?>"
                       placeholder="dadtoo database name">
                <small style="color:var(--text-muted);font-size:0.75rem;">
                    The old dadtoo database to migrate from.
                </small>
            </div>
            <button type="submit" class="button" style="width:100%;">Run Quick Upgrade &rarr;</button>
        </form>
    </div>

    <!-- Guided Upgrade -->
    <div class="card" style="text-align:center;padding:28px 24px;border:2px solid var(--primary);position:relative;">
        <div style="position:absolute;top:-11px;left:50%;transform:translateX(-50%);
                    background:var(--primary);color:#fff;font-size:0.7rem;font-weight:700;
                    padding:2px 12px;border-radius:20px;letter-spacing:.04em;">RECOMMENDED</div>
        <div style="font-size:1.8rem;margin-bottom:12px;">🗺️</div>
        <div style="font-weight:700;font-size:1.1rem;margin-bottom:10px;">Guided Upgrade</div>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:20px;line-height:1.5;">
            Walk through each step — migration, departments, authentication, kiosk setup, and more.
            Optional steps can be skipped and configured later.
        </p>
        <form method="POST" action="/install/guided-upgrade/start">
            <div class="form-group" style="text-align:left;margin-bottom:16px;">
                <label for="guided_source_db" style="font-size:0.8rem;">Source Database</label>
                <input type="text" name="source_db" id="guided_source_db"
                       value="<?= View::e($sourceDb ?? '') ?>"
                       placeholder="dadtoo database name">
                <small style="color:var(--text-muted);font-size:0.75rem;">
                    The old dadtoo database to migrate from.
                </small>
            </div>
            <button type="submit" class="button" style="width:100%;background:var(--primary);">Start Guided Upgrade &rarr;</button>
        </form>
    </div>

</div>

<p style="text-align:center;color:var(--text-muted);font-size:0.8rem;">
    <a href="/install/2" style="color:var(--text-muted);">← Enter different database credentials</a>
</p>
