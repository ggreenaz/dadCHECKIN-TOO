<?php use App\Core\View; ?>

<style>
.success-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    text-align: center;
    padding: 40px 24px;
}
.success-icon {
    font-size: 5rem;
    margin-bottom: 24px;
    animation: pop .4s ease;
}
@keyframes pop {
    0%   { transform: scale(0.5); opacity: 0; }
    80%  { transform: scale(1.1); }
    100% { transform: scale(1);   opacity: 1; }
}
.success-title {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 10px;
}
.success-sub {
    font-size: 1.1rem;
    color: var(--text-muted);
    margin-bottom: 40px;
}
.countdown-wrap {
    width: 100%;
    max-width: 360px;
    margin-bottom: 12px;
}
.countdown-bar-bg {
    height: 8px;
    background: var(--border);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}
.countdown-bar {
    height: 100%;
    background: var(--success);
    border-radius: 4px;
    width: 100%;
    transition: width 1s linear;
}
.countdown-label {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 24px;
}
.btn-done {
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 14px 40px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}
.btn-done:hover { background: var(--primary-hover); }
</style>

<div class="success-wrap">
    <div class="success-icon">✅</div>
    <div class="success-title">
        You're checked in<?= !empty($firstName) ? ', ' . View::e($firstName) . '!' : '!' ?>
    </div>
    <div class="success-sub">Have a great visit.</div>

    <div class="countdown-wrap">
        <div class="countdown-bar-bg">
            <div class="countdown-bar" id="cbar"></div>
        </div>
        <div class="countdown-label" id="clabel">Returning to home screen in <strong id="csec">10</strong> seconds…</div>
    </div>

    <a href="/checkin" class="btn-done">Done</a>
</div>

<script>
var total   = 10;
var elapsed = 0;
var bar     = document.getElementById('cbar');
var sec     = document.getElementById('csec');

var timer = setInterval(function() {
    elapsed++;
    var pct = ((total - elapsed) / total) * 100;
    bar.style.width = pct + '%';
    sec.textContent = total - elapsed;
    if (elapsed >= total) {
        clearInterval(timer);
        window.location.href = '/checkin';
    }
}, 1000);
</script>
