<?php use App\Core\View; ?>

<?php
// Helper: get saved setting for a field, with defaults
$kf = $settings['kiosk_fields'] ?? [];

function kf_show(array $kf, string $field, bool $default = true): bool {
    return isset($kf[$field]) ? (bool)$kf[$field]['show'] : $default;
}
function kf_req(array $kf, string $field, bool $default = false): bool {
    return isset($kf[$field]) ? (bool)$kf[$field]['required'] : $default;
}
?>

<?php include __DIR__ . '/../setup_nav.php'; ?>

<div class="card">
    <div class="card-title">Kiosk Fields</div>
    <p style="color:var(--text-muted);margin-bottom:24px;">
        Control which fields appear on the visitor check-in form. First Name is always shown.
        In LDAP/directory mode, name and email are filled automatically from the directory.
    </p>

    <form method="POST" action="/admin/setup/kiosk/save">

        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid var(--border);text-align:left;">
                    <th style="padding:10px 12px;font-weight:600;">Field</th>
                    <th style="padding:10px 12px;font-weight:600;text-align:center;">Show</th>
                    <th style="padding:10px 12px;font-weight:600;text-align:center;">Required</th>
                </tr>
            </thead>
            <tbody>

                <!-- First Name — always on -->
                <tr style="border-bottom:1px solid var(--border);opacity:.6;">
                    <td style="padding:14px 12px;">
                        <strong>First Name</strong>
                        <div style="font-size:0.8rem;color:var(--text-muted);">Always shown and required</div>
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" checked disabled>
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" checked disabled>
                    </td>
                </tr>

                <!-- Last Name -->
                <tr style="border-bottom:1px solid var(--border);" id="row_last_name">
                    <td style="padding:14px 12px;">
                        <strong>Last Name</strong>
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" name="show_last_name" id="show_last_name" value="1"
                               <?= kf_show($kf, 'last_name', true) ? 'checked' : '' ?>
                               onchange="toggleRequired('last_name', this.checked)">
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" name="required_last_name" id="req_last_name" value="1"
                               <?= kf_req($kf, 'last_name', false) ? 'checked' : '' ?>
                               <?= kf_show($kf, 'last_name', true) ? '' : 'disabled' ?>>
                    </td>
                </tr>

                <!-- Phone -->
                <tr style="border-bottom:1px solid var(--border);" id="row_phone">
                    <td style="padding:14px 12px;">
                        <strong>Phone Number</strong>
                        <div style="font-size:0.8rem;color:var(--text-muted);">Used for check-out lookup</div>
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" name="show_phone" id="show_phone" value="1"
                               <?= kf_show($kf, 'phone', true) ? 'checked' : '' ?>
                               onchange="toggleRequired('phone', this.checked)">
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" name="required_phone" id="req_phone" value="1"
                               <?= kf_req($kf, 'phone', false) ? 'checked' : '' ?>
                               <?= kf_show($kf, 'phone', true) ? '' : 'disabled' ?>>
                    </td>
                </tr>

                <!-- Email -->
                <tr style="border-bottom:1px solid var(--border);" id="row_email">
                    <td style="padding:14px 12px;">
                        <strong>Email</strong>
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" name="show_email" id="show_email" value="1"
                               <?= kf_show($kf, 'email', true) ? 'checked' : '' ?>
                               onchange="toggleRequired('email', this.checked)">
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" name="required_email" id="req_email" value="1"
                               <?= kf_req($kf, 'email', false) ? 'checked' : '' ?>
                               <?= kf_show($kf, 'email', true) ? '' : 'disabled' ?>>
                    </td>
                </tr>

                <!-- Notes -->
                <tr id="row_notes">
                    <td style="padding:14px 12px;">
                        <strong>Notes</strong>
                        <div style="font-size:0.8rem;color:var(--text-muted);">Optional comments from the visitor</div>
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" name="show_notes" id="show_notes" value="1"
                               <?= kf_show($kf, 'notes', true) ? 'checked' : '' ?>
                               onchange="toggleRequired('notes', this.checked)">
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" name="required_notes" id="req_notes" value="1"
                               <?= kf_req($kf, 'notes', false) ? 'checked' : '' ?>
                               <?= kf_show($kf, 'notes', true) ? '' : 'disabled' ?>>
                    </td>
                </tr>

            </tbody>
        </table>

        <div class="form-actions" style="margin-top:24px;">
            <button type="submit" class="button">Save Kiosk Settings</button>
        </div>
    </form>
</div>

<div style="display:flex;justify-content:space-between;margin-top:16px;">
    <a href="/admin/setup/fields" class="button button-outline">← Custom Fields</a>
    <a href="/admin/setup/auth" class="button button-outline">Authentication →</a>
</div>

<script>
function toggleRequired(field, shown) {
    var req = document.getElementById('req_' + field);
    if (!shown) {
        req.checked  = false;
        req.disabled = true;
    } else {
        req.disabled = false;
    }
}
</script>
