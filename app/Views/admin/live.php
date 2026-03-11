<?php use App\Core\View; ?>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= View::e($flash['type']) ?>" style="margin-bottom:16px;">
    <?= View::e($flash['message']) ?>
</div>
<?php endif; ?>

<div class="live-header">
    <div class="live-title-row">
        <div class="live-title-group">
            <span class="live-pulse-dot"></span>
            <h2 class="live-title">Live Logs</h2>
            <span class="live-subtitle">Real-time visitor tracking</span>
        </div>
        <div class="live-meta">
            <span class="live-count-badge"><?= count($activeVisits) ?> visitor<?= count($activeVisits) !== 1 ? 's' : '' ?> inside</span>
            <span class="live-refresh-label">Auto-refresh in <strong id="countdown-num">60</strong>s</span>
        </div>
    </div>
</div>

<!-- Stale / Bulk Checkout toolbar -->
<div class="card" style="margin-bottom:1rem;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <label style="font-weight:600;white-space:nowrap;">Flag visitors open longer than</label>
        <select id="stale-threshold" style="width:auto;">
            <option value="4">4 hours</option>
            <option value="8">8 hours</option>
            <option value="12">12 hours</option>
            <option value="24" <?= ($staleHours ?? 24) <= 24 ? 'selected' : '' ?>>24 hours</option>
            <option value="48" <?= ($staleHours ?? 24) > 24 ? 'selected' : '' ?>>48 hours</option>
        </select>
        <button type="button" class="button button-outline" id="select-stale-btn" onclick="selectStale()">
            Select All Flagged
        </button>
        <span id="stale-count-label" style="color:var(--text-muted);font-size:0.875rem;"></span>
    </div>

    <!-- Bulk action bar — shown when any are checked -->
    <form method="POST" action="/admin/live/bulk-checkout" id="bulk-form" style="display:none;margin-top:14px;padding-top:14px;border-top:1px solid var(--border);">
        <input type="hidden" name="visit_ids" id="bulk-ids-input" value="">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <strong id="selected-count-label" style="color:var(--text);"></strong>
            <button type="submit" class="button" style="background:var(--danger);border-color:var(--danger);"
                    onclick="return confirm('Check out all selected visitors?')">
                Check Out Selected
            </button>
            <button type="button" class="button button-outline" onclick="clearSelection()">Clear Selection</button>
        </div>
    </form>
</div>

<?php if (empty($activeVisits)): ?>
<div class="live-empty">
    <p class="live-empty-title">No active visitors right now.</p>
    <p class="live-empty-sub">Check-ins will appear here as they happen.</p>
</div>
<?php else: ?>

<div class="live-list" id="live-list">
<?php foreach ($activeVisits as $v):
    $isoUtc    = gmdate('Y-m-d\TH:i:s\Z', strtotime($v['check_in_time']));
    $displayIn = gmdate('g:i A', strtotime($v['check_in_time']));
?>
<div class="live-row" data-checkin="<?= $isoUtc ?>" data-visit-id="<?= (int)$v['visit_id'] ?>">
    <label class="live-row-check" style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;width:100%;">
        <input type="checkbox" class="visit-cb" value="<?= (int)$v['visit_id'] ?>"
               style="margin-top:4px;flex-shrink:0;" onchange="onCbChange()">
        <div style="flex:1;min-width:0;">
            <div class="live-row-info">
                <a href="/admin/visitor/<?= (int)$v['visitor_id'] ?>" class="live-row-name"
                   onclick="event.stopPropagation()"><?= View::e($v['first_name'] . ' ' . $v['last_name']) ?></a>
                <span class="live-row-meta">
                    <?= View::e($v['host_name'] ?? '—') ?>
                    &nbsp;&middot;&nbsp;
                    <?= View::e($v['reason_label'] ?? '—') ?>
                    &nbsp;&middot;&nbsp;
                    In at <?= $displayIn ?>
                </span>
                <span class="live-row-elapsed">—</span>
            </div>
            <div class="live-bar-track">
                <div class="live-bar-fill"></div>
            </div>
        </div>
    </label>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<script>
(function () {
    var staleThresholdSec = <?= (int)($staleHours ?? 24) ?> * 3600;
    var rows = document.querySelectorAll('.live-row');

    var rowData = [];
    rows.forEach(function (row) {
        var iso = row.dataset.checkin;
        var ts  = Math.floor(new Date(iso).getTime() / 1000);
        rowData.push({ row: row, ts: ts });
    });

    function formatElapsed(sec) {
        var h = Math.floor(sec / 3600);
        var m = Math.floor((sec % 3600) / 60);
        var s = sec % 60;
        if (h > 0) return h + 'h ' + String(m).padStart(2, '0') + 'm';
        if (m > 0) return m + 'm ' + String(s).padStart(2, '0') + 's';
        return s + 's';
    }

    function tick() {
        var now        = Math.floor(Date.now() / 1000);
        var threshold  = parseInt(document.getElementById('stale-threshold').value, 10) * 3600;
        var staleCount = 0;

        var maxElapsed = 1;
        rowData.forEach(function (d) {
            var e = now - d.ts;
            if (e > maxElapsed) maxElapsed = e;
        });

        rowData.forEach(function (d) {
            var elapsed = Math.max(0, now - d.ts);
            var pct     = (elapsed / maxElapsed) * 90;
            var isStale = elapsed >= threshold;

            if (isStale) staleCount++;

            var label = d.row.querySelector('.live-row-elapsed');
            if (label) label.textContent = formatElapsed(elapsed);

            var bar = d.row.querySelector('.live-bar-fill');
            if (bar) {
                bar.style.width = pct.toFixed(2) + '%';
                if (elapsed >= 7200) {
                    bar.style.background = 'linear-gradient(90deg, #f87171, #dc2626)';
                } else if (elapsed >= 3600) {
                    bar.style.background = 'linear-gradient(90deg, #fde047, #ca8a04)';
                } else {
                    bar.style.background = 'linear-gradient(90deg, #4ade80, #16a34a)';
                }
            }

            // Highlight stale rows
            d.row.style.outline = isStale ? '2px solid var(--danger)' : '';
            d.row.style.borderRadius = isStale ? 'var(--radius, 6px)' : '';
        });

        var lbl = document.getElementById('stale-count-label');
        if (lbl) {
            lbl.textContent = staleCount > 0
                ? staleCount + ' visitor' + (staleCount !== 1 ? 's' : '') + ' flagged'
                : 'No visitors flagged';
            lbl.style.color = staleCount > 0 ? 'var(--danger)' : 'var(--text-muted)';
        }
    }

    window.selectStale = function () {
        var now       = Math.floor(Date.now() / 1000);
        var threshold = parseInt(document.getElementById('stale-threshold').value, 10) * 3600;
        rowData.forEach(function (d) {
            var elapsed = Math.max(0, now - d.ts);
            var cb = d.row.querySelector('.visit-cb');
            if (cb) cb.checked = elapsed >= threshold;
        });
        onCbChange();
    };

    window.clearSelection = function () {
        document.querySelectorAll('.visit-cb').forEach(function (cb) { cb.checked = false; });
        onCbChange();
    };

    window.onCbChange = function () {
        var checked = Array.from(document.querySelectorAll('.visit-cb:checked'));
        var form    = document.getElementById('bulk-form');
        var idsInput = document.getElementById('bulk-ids-input');
        var selLbl  = document.getElementById('selected-count-label');

        // Single comma-separated field — avoids max_input_vars limit
        idsInput.value = checked.map(function (cb) { return cb.value; }).join(',');

        if (checked.length > 0) {
            form.style.display = 'block';
            selLbl.textContent = checked.length + ' visitor' + (checked.length !== 1 ? 's' : '') + ' selected';
        } else {
            form.style.display = 'none';
        }
    };

    // Re-run flagging when threshold changes
    document.getElementById('stale-threshold').addEventListener('change', function () {
        tick();
        clearSelection();
    });

    setInterval(tick, 1000);
    tick();

    // Countdown + page reload
    var countdown = 60;
    var numEl = document.getElementById('countdown-num');
    setInterval(function () {
        countdown--;
        if (numEl) numEl.textContent = countdown;
        if (countdown <= 0) location.reload();
    }, 1000);
})();
</script>
