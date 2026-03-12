<div class="card" style="text-align: center; padding: 40px 40px 32px;">
    <div style="font-size: 3.5rem; margin-bottom: 12px; color: var(--success);">&#10003;</div>
    <div class="card-title" style="border: none; text-align: center; font-size: 1.3rem; margin-bottom: 8px;">Setup Complete!</div>
    <p style="color: var(--text-muted); margin-bottom: 32px; font-size: 0.95rem;">
        Your dadCHECKIN-TOO platform is ready. Before you dive in, choose how you'd like to get started.
    </p>

    <!-- Choice Cards -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 28px; text-align: left;">

        <!-- Demo Data Card -->
        <label id="card-demo" style="cursor:pointer; display:block; border: 2px solid var(--primary); border-radius: var(--radius);
                    padding: 24px; background: color-mix(in srgb, var(--primary) 6%, var(--surface));">
            <input type="radio" name="start_mode" value="demo" form="finish-form"
                   style="display:none;" checked onchange="updateSelection()">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div id="check-demo" style="width:22px;height:22px;border-radius:50%;background:var(--primary);
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M2 6l3 3 5-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div id="ring-demo" style="display:none;width:22px;height:22px;border-radius:50%;border:2px solid var(--border);flex-shrink:0;"></div>
                <span style="font-weight:700; font-size:1rem; color:var(--primary);">Try it with demo data</span>
            </div>
            <p style="font-size:0.82rem; color:var(--text-muted); line-height:1.6; margin:0;">
                Instantly loads <strong>80 sample visitors</strong>, <strong>15 hosts</strong>,
                <strong>420 historical visits</strong>, and <strong>8 active check-ins</strong>
                so every page — Live Logs, History, Dashboard — has realistic data to explore right away.
            </p>
            <div style="margin-top:14px; font-size:0.78rem; padding:8px 12px;
                        background:color-mix(in srgb, var(--primary) 10%, transparent);
                        border-radius:6px; color:var(--primary); font-weight:600;">
                Recommended &mdash; great for evaluating the system
            </div>
            <p style="font-size:0.75rem; color:var(--text-muted); margin:10px 0 0;">
                You can remove demo data any time from <strong>Admin &rarr; Settings &rarr; Expunge Demo Data</strong>.
            </p>
        </label>

        <!-- Fresh Start Card -->
        <label id="card-fresh" style="cursor:pointer; display:block; border: 2px solid var(--border); border-radius: var(--radius);
                    padding: 24px; background: var(--surface);">
            <input type="radio" name="start_mode" value="fresh" form="finish-form"
                   style="display:none;" onchange="updateSelection()">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div id="check-fresh" style="display:none;width:22px;height:22px;border-radius:50%;background:var(--primary);
                            align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M2 6l3 3 5-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div id="ring-fresh" style="width:22px;height:22px;border-radius:50%;border:2px solid var(--border);flex-shrink:0;"></div>
                <span style="font-weight:700; font-size:1rem;">Start fresh</span>
            </div>
            <p style="font-size:0.82rem; color:var(--text-muted); line-height:1.6; margin:0;">
                Skip demo data and go straight to configuration. You'll set up
                <strong>hosts</strong>, <strong>visit reasons</strong>, <strong>departments</strong>,
                and other settings before the system is ready for real visitors.
            </p>
            <div style="margin-top:14px; font-size:0.78rem; padding:8px 12px;
                        background:var(--surface-2);
                        border-radius:6px; color:var(--text-muted); font-weight:600;">
                For production deployments ready to configure now
            </div>
        </label>
    </div>

    <!-- Your URLs summary -->
    <div style="background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--radius);
                padding: 16px 20px; margin-bottom: 28px; text-align: left; font-size: 0.85rem;">
        <p style="font-weight: 600; margin-bottom: 10px; color:var(--text-muted); font-size:0.78rem; text-transform:uppercase; letter-spacing:.05em;">Your URLs</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 24px;">
            <p style="margin:0 0 4px;"><strong>Visitor Check-In:</strong> <code>/checkin</code></p>
            <p style="margin:0 0 4px;"><strong>Live Board:</strong> <code>/board</code></p>
            <p style="margin:0;"><strong>Visitor Check-Out:</strong> <code>/depart</code></p>
            <p style="margin:0;"><strong>Admin Panel:</strong> <code>/admin</code></p>
        </div>
    </div>

    <form id="finish-form" method="POST" action="/install/finish" onsubmit="handleFinish(event)">
        <input type="hidden" name="load_demo" id="load_demo_input" value="1">
        <button type="submit" id="finish-btn" class="button" style="font-size:1rem;padding:12px 36px;">
            Continue &rarr;
        </button>
    </form>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65);
     z-index:9999; align-items:center; justify-content:center; flex-direction:column; gap:20px;">
    <div style="background:var(--surface); border-radius:var(--radius); padding:40px 48px; text-align:center; max-width:360px;">
        <div style="margin-bottom:20px;">
            <svg width="48" height="48" viewBox="0 0 48 48" style="animation:spin 1s linear infinite;">
                <circle cx="24" cy="24" r="20" fill="none" stroke="var(--border)" stroke-width="4"/>
                <path d="M24 4 a20 20 0 0 1 20 20" fill="none" stroke="var(--primary)" stroke-width="4" stroke-linecap="round"/>
            </svg>
        </div>
        <div style="font-weight:700; font-size:1.1rem; margin-bottom:8px;">Loading demo data&hellip;</div>
        <div style="font-size:0.85rem; color:var(--text-muted); line-height:1.5;">
            Creating sample visitors, hosts, and visit history.<br>This takes just a moment.
        </div>
    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }

#card-demo, #card-fresh { transition: border-color .15s, background .15s; }
#card-demo:hover, #card-fresh:hover { border-color: var(--primary); }
#check-demo, #check-fresh { display: flex; }
</style>

<script>
function updateSelection() {
    const demo  = document.querySelector('input[value="demo"]').checked;
    const cardDemo  = document.getElementById('card-demo');
    const cardFresh = document.getElementById('card-fresh');
    const checkDemo  = document.getElementById('check-demo');
    const ringDemo   = document.getElementById('ring-demo');
    const checkFresh = document.getElementById('check-fresh');
    const ringFresh  = document.getElementById('ring-fresh');
    const btn = document.getElementById('finish-btn');
    const loadDemoInput = document.getElementById('load_demo_input');

    if (demo) {
        cardDemo.style.borderColor  = 'var(--primary)';
        cardDemo.style.background   = 'color-mix(in srgb, var(--primary) 6%, var(--surface))';
        cardFresh.style.borderColor = 'var(--border)';
        cardFresh.style.background  = 'var(--surface)';
        checkDemo.style.display = 'flex';
        ringDemo.style.display  = 'none';
        checkFresh.style.display = 'none';
        ringFresh.style.display  = 'block';
        btn.textContent = 'Load Demo Data & Continue →';
        loadDemoInput.value = '1';
    } else {
        cardFresh.style.borderColor = 'var(--primary)';
        cardFresh.style.background  = 'color-mix(in srgb, var(--primary) 6%, var(--surface))';
        cardDemo.style.borderColor  = 'var(--border)';
        cardDemo.style.background   = 'var(--surface)';
        checkFresh.style.display = 'flex';
        ringFresh.style.display  = 'none';
        checkDemo.style.display  = 'none';
        ringDemo.style.display   = 'block';
        btn.textContent = 'Continue Without Demo Data →';
        loadDemoInput.value = '0';
    }
}

function handleFinish(e) {
    if (document.getElementById('load_demo_input').value === '1') {
        document.getElementById('loading-overlay').style.display = 'flex';
        document.getElementById('finish-btn').disabled = true;
    }
}

// Init state
updateSelection();
</script>
