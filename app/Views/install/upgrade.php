<?php use App\Core\View; ?>

<div class="card" style="max-width:640px;margin:0 auto;">
    <div class="card-title">Upgrade from dadtoo</div>
    <p style="color:var(--text-muted);margin-bottom:20px;">
        This tool migrates your existing dadtoo database into the new CheckIn schema.
        It maps visitors, hosts, visit reasons, and all check-in history automatically.
        <strong>It is safe to run multiple times</strong> — records already migrated are skipped.
    </p>

    <?php if (!empty($output)): ?>
    <div style="margin-bottom:24px;">
        <div style="font-weight:600;margin-bottom:8px;">
            <?= !empty($dryRun) ? 'Dry Run Output (no data written)' : 'Migration Output' ?>
        </div>
        <pre style="background:var(--surface-2);border:1px solid var(--border);border-radius:6px;padding:16px;font-size:0.8rem;overflow-x:auto;white-space:pre-wrap;"><?= View::e($output) ?></pre>
        <?php if (empty($dryRun) && str_contains($output, '✓ Migration complete.')): ?>
        <div class="alert alert-success" style="margin-top:12px;">
            Migration complete. <a href="/admin" style="font-weight:600;">Go to the admin dashboard &rarr;</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/install/upgrade/run">
        <div class="form-group" style="margin-bottom:16px;">
            <label for="source_db">Source Database Name</label>
            <input type="text" name="source_db" id="source_db"
                   value="dadtoodb_import"
                   placeholder="e.g. dadtoodb_import">
            <small style="color:var(--text-muted);font-size:0.8rem;">
                The MySQL database that contains the old dadtoo tables
                (checkin_checkout, users, visiting_persons, visit_reasons).
            </small>
        </div>

        <div class="form-group" style="margin-bottom:20px;">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="dry_run" value="1">
                <span>Dry run — show what would be migrated without writing anything</span>
            </label>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="button">Run Migration</button>
            <a href="/install" class="button button-outline">← Back</a>
        </div>
    </form>
</div>

<div class="card" style="max-width:640px;margin:20px auto;">
    <div class="card-title">What gets migrated</div>
    <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
        <thead>
            <tr style="border-bottom:2px solid var(--border);">
                <th style="text-align:left;padding:8px;">Old table</th>
                <th style="text-align:left;padding:8px;">New table</th>
                <th style="text-align:left;padding:8px;">Notes</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:8px;font-family:monospace;">visiting_persons</td>
                <td style="padding:8px;font-family:monospace;">hosts</td>
                <td style="padding:8px;color:var(--text-muted);">Counselors / staff</td>
            </tr>
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:8px;font-family:monospace;">visit_reasons</td>
                <td style="padding:8px;font-family:monospace;">visit_reasons</td>
                <td style="padding:8px;color:var(--text-muted);">reason_description → label</td>
            </tr>
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:8px;font-family:monospace;">users</td>
                <td style="padding:8px;font-family:monospace;">visitors</td>
                <td style="padding:8px;color:var(--text-muted);">Only the ~520 with actual visits</td>
            </tr>
            <tr>
                <td style="padding:8px;font-family:monospace;">checkin_checkout</td>
                <td style="padding:8px;font-family:monospace;">visits</td>
                <td style="padding:8px;color:var(--text-muted);">6,430 historical records</td>
            </tr>
        </tbody>
    </table>
</div>
