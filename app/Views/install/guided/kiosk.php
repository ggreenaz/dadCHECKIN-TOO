<?php
$kf = $kioskFields ?? [];
function gu_kf_show(array $kf, string $f, bool $d = true): bool {
    return isset($kf[$f]) ? (bool)$kf[$f]['show'] : $d;
}
function gu_kf_req(array $kf, string $f, bool $d = false): bool {
    return isset($kf[$f]) ? (bool)$kf[$f]['required'] : $d;
}
?>
<div class="card">
    <div class="card-title">
        Step 5 of 7 — Kiosk Fields
        <span class="optional-tag">Optional</span>
    </div>

    <div class="feature-callout">
        <strong>New feature: Configurable check-in form</strong>
        You now control exactly which fields appear on the kiosk check-in screen.
        Toggle each field on or off, and mark it required or optional.
        In LDAP mode, name and email are filled automatically from the directory.
    </div>

    <form method="POST" action="/install/guided-upgrade/kiosk/save">
        <table style="width:100%;border-collapse:collapse;margin-bottom:8px;">
            <thead>
                <tr style="border-bottom:2px solid var(--border);text-align:left;">
                    <th style="padding:10px 12px;font-weight:600;">Field</th>
                    <th style="padding:10px 12px;font-weight:600;text-align:center;">Show</th>
                    <th style="padding:10px 12px;font-weight:600;text-align:center;">Required</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom:1px solid var(--border);opacity:.6;">
                    <td style="padding:14px 12px;">
                        <strong>First Name</strong>
                        <div style="font-size:0.8rem;color:var(--text-muted);">Always shown and required</div>
                    </td>
                    <td style="text-align:center;"><input type="checkbox" checked disabled></td>
                    <td style="text-align:center;"><input type="checkbox" checked disabled></td>
                </tr>
                <?php foreach ([
                    'last_name' => ['Last Name', '', true, false],
                    'phone'     => ['Phone Number', 'Used for manual check-out lookup', true, false],
                    'email'     => ['Email', '', true, false],
                    'notes'     => ['Notes', 'Optional comments from the visitor', false, false],
                ] as $field => [$label, $hint, $defShow, $defReq]): ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:14px 12px;">
                        <strong><?= $label ?></strong>
                        <?php if ($hint): ?>
                        <div style="font-size:0.8rem;color:var(--text-muted);"><?= $hint ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" name="show_<?= $field ?>" value="1"
                               id="show_<?= $field ?>"
                               <?= gu_kf_show($kf, $field, $defShow) ? 'checked' : '' ?>
                               onchange="guToggleReq('<?= $field ?>', this.checked)">
                    </td>
                    <td style="text-align:center;padding:14px 12px;">
                        <input type="checkbox" name="required_<?= $field ?>" value="1"
                               id="req_<?= $field ?>"
                               <?= gu_kf_req($kf, $field, $defReq) ? 'checked' : '' ?>
                               <?= gu_kf_show($kf, $field, $defShow) ? '' : 'disabled' ?>>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="step-actions">
            <button type="submit" class="button">Save &amp; Continue &rarr;</button>
            <button type="submit" name="configure_later" value="1" class="btn-later">Configure Later</button>
        </div>
    </form>
</div>

<script>
function guToggleReq(field, shown) {
    var req = document.getElementById('req_' + field);
    if (!shown) { req.checked = false; req.disabled = true; }
    else        { req.disabled = false; }
}
</script>
