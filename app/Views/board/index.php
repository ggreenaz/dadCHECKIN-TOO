<?php use App\Core\View; ?>

<div class="board-header">
    <h2>Live Visitor Board</h2>
    <span class="board-count" id="board-count"><?= count($visits) ?> active</span>
</div>

<div class="table-wrapper" id="board-table-wrap">
    <?php if (empty($visits)): ?>
        <p class="board-empty">No visitors currently checked in.</p>
    <?php else: ?>
        <table class="data-table" id="board-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Host</th>
                    <th>Reason</th>
                    <th>Checked In</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($visits as $v): ?>
                <tr>
                    <td><?= View::e($v['first_name'] . ' ' . $v['last_name']) ?></td>
                    <td><?= View::e($v['host_name'] ?? '—') ?></td>
                    <td><?= View::e($v['reason_label'] ?? '—') ?></td>
                    <td><?= View::e(date('g:i A', strtotime($v['check_in_time']))) ?></td>
                    <td><span class="status-badge status-<?= View::e($v['status']) ?>"><?= View::e(ucfirst(str_replace('_', ' ', $v['status']))) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
(function () {
    const countEl = document.getElementById('board-count');
    const wrapEl  = document.getElementById('board-table-wrap');

    function statusLabel(s) {
        return s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    function renderTable(visits) {
        countEl.textContent = visits.length + ' active';
        if (!visits.length) {
            wrapEl.innerHTML = '<p class="board-empty">No visitors currently checked in.</p>';
            return;
        }
        let rows = visits.map(v => `
            <tr>
                <td>${escHtml(v.first_name + ' ' + v.last_name)}</td>
                <td>${escHtml(v.host_name || '—')}</td>
                <td>${escHtml(v.reason_label || '—')}</td>
                <td>${escHtml(formatTime(v.check_in_time))}</td>
                <td><span class="status-badge status-${escHtml(v.status)}">${escHtml(statusLabel(v.status))}</span></td>
            </tr>`).join('');
        wrapEl.innerHTML = `<table class="data-table">
            <thead><tr><th>Name</th><th>Host</th><th>Reason</th><th>Checked In</th><th>Status</th></tr></thead>
            <tbody>${rows}</tbody></table>`;
    }

    function formatTime(dt) {
        return new Date(dt.replace(' ', 'T')).toLocaleTimeString([], {hour: 'numeric', minute: '2-digit'});
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    async function poll() {
        try {
            const res  = await fetch('/board/poll');
            const data = await res.json();
            renderTable(data.visits || []);
        } catch (e) {}
    }

    setInterval(poll, 15000);
})();
</script>
