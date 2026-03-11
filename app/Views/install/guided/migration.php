<?php use App\Core\View; ?>

<div class="card">
    <div class="card-title">Step 2 of 7 — Data Migration</div>

    <div class="feature-callout">
        <strong>What's happening</strong>
        Your legacy dadtoo records are being mapped into the new CheckIn schema.
        Visits, hosts, reasons, and visitor profiles are all preserved.
        This is safe to run multiple times &mdash; already-migrated records are skipped.
    </div>

    <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= View::e($flash['type']) ?>" style="margin-bottom:16px;">
        <?= View::e($flash['message']) ?>
    </div>
    <?php endif; ?>

    <!-- Source DB field + Run button -->
    <div id="setup-form">
        <div class="form-group" style="margin-bottom:20px;">
            <label for="source_db">Source Database Name</label>
            <input type="text" id="source_db"
                   value="<?= View::e($sourceDb ?? '') ?>"
                   placeholder="e.g. dadtoo_upgrade_test">
            <small style="color:var(--text-muted);font-size:0.8rem;">
                The MySQL database containing your old dadtoo tables.
            </small>
        </div>
        <div class="step-actions">
            <button type="button" class="button" id="run-btn" onclick="startMigration()">
                Run Migration &rarr;
            </button>
        </div>
    </div>

    <!-- Progress UI — hidden until migration starts -->
    <div id="progress-wrap" style="display:none;margin-top:8px;">

        <!-- Stage labels -->
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:0.75rem;color:var(--text-muted);">
            <span id="lbl-1">Hosts</span>
            <span id="lbl-2">Reasons</span>
            <span id="lbl-3">Visitors</span>
            <span id="lbl-4">Visits</span>
        </div>

        <!-- Progress bar -->
        <div style="height:10px;background:var(--border);border-radius:5px;overflow:hidden;margin-bottom:16px;">
            <div id="prog-bar" style="height:100%;width:0%;background:var(--primary);border-radius:5px;
                 transition:width .3s ease;"></div>
        </div>

        <div id="stage-label" style="font-size:0.875rem;font-weight:600;margin-bottom:12px;color:var(--text-muted);">
            Starting…
        </div>

        <!-- Scrolling log -->
        <pre id="mig-log" style="background:var(--surface-2);border:1px solid var(--border);
            border-radius:6px;padding:14px;font-size:0.75rem;line-height:1.6;
            max-height:260px;overflow-y:auto;white-space:pre-wrap;margin-bottom:16px;"></pre>

        <!-- Result messages -->
        <div id="mig-success" style="display:none;" class="alert alert-success">
            ✓ Migration complete! All your historical data has been imported.
        </div>
        <div id="mig-error" style="display:none;" class="alert alert-error"></div>

        <!-- Actions shown after completion -->
        <div id="mig-actions" style="display:none;" class="step-actions">
            <a href="/install/guided-upgrade/departments" class="button">
                Continue to Departments &rarr;
            </a>
            <button type="button" class="btn-later" onclick="resetMigration()">
                Run Again
            </button>
        </div>
        <div id="mig-retry" style="display:none;" class="step-actions">
            <button type="button" class="button" onclick="resetMigration()">
                Retry Migration
            </button>
        </div>
    </div>
</div>

<script>
var evtSource = null;

function startMigration() {
    var srcDb = document.getElementById('source_db').value.trim();
    if (!srcDb) {
        alert('Please enter the source database name.');
        return;
    }

    // Switch UI
    document.getElementById('setup-form').style.display  = 'none';
    document.getElementById('progress-wrap').style.display = 'block';
    document.getElementById('prog-bar').style.width      = '0%';
    document.getElementById('stage-label').textContent   = 'Connecting…';
    document.getElementById('mig-log').textContent       = '';
    document.getElementById('mig-success').style.display = 'none';
    document.getElementById('mig-error').style.display   = 'none';
    document.getElementById('mig-actions').style.display = 'none';
    document.getElementById('mig-retry').style.display   = 'none';

    var url = '/install/guided-upgrade/migration/stream?source_db=' + encodeURIComponent(srcDb);
    evtSource = new EventSource(url);

    evtSource.onmessage = function(e) {
        var d = JSON.parse(e.data);
        var log = document.getElementById('mig-log');

        if (d.type === 'start') {
            appendLog(d.message);
        } else if (d.type === 'stage') {
            setStageActive(d.stage);
            document.getElementById('stage-label').textContent = d.label + '…';
            setProgress(d.pct);
        } else if (d.type === 'log') {
            appendLog(d.message);
        } else if (d.type === 'progress') {
            setProgress(d.pct);
        } else if (d.type === 'stage_done') {
            setStageActive(d.stage);
            appendLog('✓ ' + d.message);
            setProgress(d.pct);
            setStageDone(d.stage);
        } else if (d.type === 'done') {
            setProgress(100);
            document.getElementById('stage-label').textContent = '✓ Complete';
            document.getElementById('mig-success').style.display = 'block';
            document.getElementById('mig-actions').style.display = 'flex';
            evtSource.close();
        } else if (d.type === 'error') {
            document.getElementById('stage-label').textContent = 'Error';
            var err = document.getElementById('mig-error');
            err.style.display = 'block';
            err.textContent   = '✗ ' + d.message;
            document.getElementById('mig-retry').style.display = 'flex';
            evtSource.close();
        }
    };

    evtSource.onerror = function() {
        if (evtSource.readyState === EventSource.CLOSED) return;
        document.getElementById('stage-label').textContent = 'Connection lost';
        var err = document.getElementById('mig-error');
        err.style.display = 'block';
        err.textContent   = '✗ Connection to server lost. Check server logs and try again.';
        document.getElementById('mig-retry').style.display = 'flex';
        evtSource.close();
    };
}

function setProgress(pct) {
    document.getElementById('prog-bar').style.width = Math.min(pct, 100) + '%';
}

function setStageActive(n) {
    for (var i = 1; i <= 4; i++) {
        var el = document.getElementById('lbl-' + i);
        el.style.color      = '';
        el.style.fontWeight = '';
    }
    var active = document.getElementById('lbl-' + n);
    if (active) {
        active.style.color      = 'var(--primary)';
        active.style.fontWeight = '600';
    }
}

function setStageDone(n) {
    var el = document.getElementById('lbl-' + n);
    if (el) {
        el.style.color      = 'var(--success)';
        el.style.fontWeight = '600';
    }
}

function appendLog(msg) {
    var log = document.getElementById('mig-log');
    log.textContent += msg + '\n';
    log.scrollTop    = log.scrollHeight;
}

function resetMigration() {
    if (evtSource) { evtSource.close(); evtSource = null; }
    document.getElementById('setup-form').style.display   = 'block';
    document.getElementById('progress-wrap').style.display = 'none';
}
</script>
