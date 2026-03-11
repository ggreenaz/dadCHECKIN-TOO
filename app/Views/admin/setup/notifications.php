<?php use App\Core\View; ?>

<div class="setup-stage-header">
    <a href="/admin/setup" class="back-link">&larr; Back to Setup</a>
    <h2>Notifications</h2>
    <p>Configure who gets notified when a visitor checks in. This step is optional — you can set it up later.</p>
</div>

<div class="card">
    <div class="card-title">Add Notification Rule</div>
    <form method="POST" action="/admin/setup/notifications/save">
        <div class="form-grid">
            <div class="form-group">
                <label for="trigger_event">Trigger</label>
                <select name="trigger_event" id="trigger_event">
                    <option value="check_in">When visitor checks in</option>
                    <option value="check_out">When visitor checks out</option>
                </select>
            </div>
            <div class="form-group">
                <label for="channel">Notify via</label>
                <select name="channel" id="channel">
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="slack">Slack webhook</option>
                    <option value="webhook">Webhook (POST)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="recipient_type">Recipient</label>
                <select name="recipient_type" id="recipient_type">
                    <option value="fixed_address">Fixed address / URL</option>
                    <option value="host">The host being visited</option>
                </select>
            </div>
            <div class="form-group">
                <label for="recipient_value">Address / URL</label>
                <input type="text" name="recipient_value" id="recipient_value"
                       placeholder="email@example.com, +15551234567, or https://...">
                <small style="color:var(--text-muted)">Leave blank if using "The host being visited"</small>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Add Rule</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-title">Notification Rules (<?= count($rules) ?>)</div>
    <?php if (empty($rules)): ?>
        <p class="text-muted">No notification rules configured. Visitors can still check in — hosts just won't be alerted automatically.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Trigger</th><th>Channel</th><th>Recipient</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($rules as $r): ?>
                    <tr>
                        <td><?= View::e(ucfirst(str_replace('_', ' ', $r['trigger_event']))) ?></td>
                        <td><?= View::e(ucfirst($r['channel'])) ?></td>
                        <td><?= View::e($r['recipient_type'] === 'host' ? 'The host being visited' : $r['recipient_value']) ?></td>
                        <td>
                            <form method="POST" action="/admin/setup/notifications/<?= (int)$r['rule_id'] ?>/delete"
                                  onsubmit="return confirm('Remove this rule?')">
                                <button type="submit" class="delete-button">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="setup-stage-nav">
    <a href="/admin/setup/fields" class="button button-outline">&larr; Custom Fields</a>
    <a href="/admin/setup/test" class="button">Next: Test Check-In &rarr;</a>
</div>
