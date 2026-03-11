<?php use App\Core\View; ?>

<?php
// Field visibility helpers — defaults match sensible out-of-box behaviour
$kf = $kiosk_fields ?? [];
function kf_show(array $kf, string $field, bool $default = true): bool {
    return isset($kf[$field]) ? (bool)$kf[$field]['show'] : $default;
}
function kf_req(array $kf, string $field, bool $default = false): bool {
    return isset($kf[$field]) ? (bool)$kf[$field]['required'] : $default;
}
$showLastName = kf_show($kf, 'last_name', true);
$showPhone    = kf_show($kf, 'phone',     true);
$showEmail    = kf_show($kf, 'email',     true);
$showNotes    = kf_show($kf, 'notes',     true);
$reqLastName  = kf_req($kf,  'last_name', false);
$reqPhone     = kf_req($kf,  'phone',     false);
$reqEmail     = kf_req($kf,  'email',     false);
?>

<div class="checkin-wrap">
    <div class="card">

        <?php if (!empty($is_staff)): ?>
        <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
            <a href="/admin" class="button button-outline button-sm">← Back to Admin</a>
        </div>
        <?php endif; ?>

        <?php if (!empty($kiosk_visitor)): ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:24px;padding:10px 14px;background:var(--surface-2);border-radius:8px;border:1px solid var(--border);">
            <span style="color:green;font-size:1.2rem;">✓</span>
            <span style="font-size:0.9rem;color:var(--text-muted);">Identity verified</span>
            <?php if (empty($is_staff)): ?>
            <a href="/checkin/cancel" style="margin-left:auto;font-size:0.8rem;color:var(--text-muted);">Not you?</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="card-title">Visitor Check-In</div>
        <?php endif; ?>

        <form method="POST" action="/checkin">
            <div class="form-grid">

                <?php if (empty($kiosk_visitor)): ?>
                <!-- Manual entry fields -->
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" placeholder="First Name" required>
                </div>

                <?php if ($showLastName): ?>
                <div class="form-group">
                    <label for="last_name">Last Name<?= $reqLastName ? '' : ' <span style="color:var(--text-muted);font-weight:400;">(optional)</span>' ?></label>
                    <input type="text" name="last_name" id="last_name" placeholder="Last Name"
                           <?= $reqLastName ? 'required' : '' ?>>
                </div>
                <?php endif; ?>

                <?php if ($showPhone): ?>
                <div class="form-group">
                    <label for="phone">Phone Number<?= $reqPhone ? '' : ' <span style="color:var(--text-muted);font-weight:400;">(optional)</span>' ?></label>
                    <input type="tel" name="phone" id="phone" placeholder="Phone Number"
                           <?= $reqPhone ? 'required' : '' ?>>
                </div>
                <?php endif; ?>

                <?php if ($showEmail): ?>
                <div class="form-group">
                    <label for="email">Email<?= $reqEmail ? '' : ' <span style="color:var(--text-muted);font-weight:400;">(optional)</span>' ?></label>
                    <input type="email" name="email" id="email" placeholder="Email"
                           <?= $reqEmail ? 'required' : '' ?>>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <!-- LDAP mode: only phone needed (for check-out lookup) -->
                <?php if ($showPhone): ?>
                <div class="form-group form-group-full">
                    <label for="phone">Phone Number<?= $reqPhone ? '' : ' <span style="color:var(--text-muted);font-weight:400;">(optional)</span>' ?></label>
                    <input type="tel" name="phone" id="phone" placeholder="Phone Number"
                           <?= $reqPhone ? 'required' : '' ?>>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <div class="form-group form-group-full">
                    <label for="host_id">Who are you visiting?</label>
                    <select name="host_id" id="host_id" required>
                        <option value="" disabled selected>Select a host</option>
                        <?php foreach ($hosts as $host): ?>
                            <option value="<?= View::e($host['host_id']) ?>">
                                <?= View::e($host['name']) ?>
                                <?= !empty($host['department_name']) ? '— ' . View::e($host['department_name']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group form-group-full">
                    <label for="reason_id">Reason for Visit</label>
                    <select name="reason_id" id="reason_id" required>
                        <option value="" disabled selected>Select reason</option>
                        <?php foreach ($reasons as $reason): ?>
                            <option value="<?= View::e($reason['reason_id']) ?>">
                                <?= View::e($reason['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($showNotes): ?>
                <div class="form-group form-group-full">
                    <label for="notes">Notes <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Any additional notes"></textarea>
                </div>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" class="button">Check In</button>
                </div>

            </div>
        </form>
    </div>
</div>
