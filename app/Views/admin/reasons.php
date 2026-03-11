<?php use App\Core\View; ?>

<div class="card">
    <div class="card-title">Add Visit Reason</div>
    <form method="POST" action="/admin/reasons">
        <div class="form-grid">
            <div class="form-group">
                <label for="label">Label</label>
                <input type="text" name="label" id="label" placeholder="e.g. Appointment, Delivery..." required>
            </div>
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="0" min="0">
            </div>
            <div class="form-group form-check-group">
                <label>
                    <input type="checkbox" name="requires_approval" value="1">
                    Requires approval
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Add Reason</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-title">Visit Reasons</div>
    <?php if (empty($reasons)): ?>
        <p class="text-muted">No reasons configured yet.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>#</th><th>Label</th><th>Requires Approval</th><th>Active</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reasons as $r): ?>
                    <tr>
                        <td><?= (int)$r['sort_order'] ?></td>
                        <td><?= View::e($r['label']) ?></td>
                        <td><?= $r['requires_approval'] ? 'Yes' : 'No' ?></td>
                        <td><?= $r['active'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <form method="POST" action="/admin/reasons/<?= (int)$r['reason_id'] ?>/delete"
                                  onsubmit="return confirm('Deactivate this reason?')">
                                <button type="submit" class="delete-button">Deactivate</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
