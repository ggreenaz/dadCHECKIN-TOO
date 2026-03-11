<?php use App\Core\View; ?>

<div class="card">
    <div class="card-title">Add Host</div>
    <form method="POST" action="/admin/hosts">
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Email (optional)">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone" placeholder="Phone (optional)">
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" name="department" id="department" placeholder="Department (optional)">
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Add Host</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-title">Hosts</div>
    <?php if (empty($hosts)): ?>
        <p class="text-muted">No hosts configured yet.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>Name</th><th>Department</th><th>Email</th><th>Phone</th><th>Active</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($hosts as $h): ?>
                    <tr>
                        <td><?= View::e($h['name']) ?></td>
                        <td><?= View::e($h['department'] ?? '—') ?></td>
                        <td><?= View::e($h['email'] ?? '—') ?></td>
                        <td><?= View::e($h['phone'] ?? '—') ?></td>
                        <td><?= $h['active'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <form method="POST" action="/admin/hosts/<?= (int)$h['host_id'] ?>/delete"
                                  onsubmit="return confirm('Deactivate this host?')">
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
