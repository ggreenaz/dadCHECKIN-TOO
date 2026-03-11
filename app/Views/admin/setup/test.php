<?php use App\Core\View; ?>

<div class="setup-stage-header">
    <a href="/admin/setup" class="back-link">&larr; Back to Setup</a>
    <h2>Test Check-In</h2>
    <p>Open the check-in page and do a test run to make sure everything is working before going live.</p>
</div>

<div class="card" style="text-align:center;padding:40px;">
    <div style="font-size:3rem;margin-bottom:16px;">🚀</div>
    <div class="card-title" style="border:none;justify-content:center;">Ready to Test</div>
    <p style="color:var(--text-muted);max-width:400px;margin:0 auto 28px;">
        Open the visitor check-in page in a new tab. Try checking in a test visitor, then check them out.
        If everything works, you're live.
    </p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="/checkin" target="_blank" class="button">Open Check-In Page &rarr;</a>
        <a href="/depart" target="_blank" class="button button-outline">Open Check-Out Page</a>
        <a href="/board" target="_blank" class="button button-outline">Open Live Board</a>
    </div>
</div>

<div class="card">
    <div class="card-title">Check-In Page URL</div>
    <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:12px;">
        Share this URL with whoever will be staffing the check-in desk, or set it as the kiosk browser's home page.
    </p>
    <div style="display:flex;align-items:center;gap:12px;background:var(--surface-2);
                border:1px solid var(--border);border-radius:var(--radius);padding:12px 16px;">
        <code style="flex:1;font-size:0.9rem;"><?= View::e($checkin_url) ?></code>
        <button onclick="copyUrl(this,'<?= View::e($checkin_url) ?>')"
                class="button button-outline" style="flex-shrink:0;">Copy</button>
    </div>
</div>

<script>
function copyUrl(btn, text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            btn.textContent = 'Copied!';
            setTimeout(function(){ btn.textContent = 'Copy'; }, 2000);
        });
    } else {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity  = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try {
            document.execCommand('copy');
            btn.textContent = 'Copied!';
            setTimeout(function(){ btn.textContent = 'Copy'; }, 2000);
        } catch(e) {
            btn.textContent = 'Select & copy manually';
            setTimeout(function(){ btn.textContent = 'Copy'; }, 3000);
        }
        document.body.removeChild(ta);
    }
}
</script>

<div class="setup-stage-nav">
    <a href="/admin/setup/notifications" class="button button-outline">&larr; Notifications</a>
    <a href="/admin/setup" class="button">Back to Timeline</a>
</div>
