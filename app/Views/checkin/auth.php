<?php use App\Core\View; ?>

<div class="checkin-wrap">
    <div class="card" style="max-width:420px;margin:60px auto;">

        <div style="text-align:center;margin-bottom:28px;">
            <div style="font-size:2.5rem;margin-bottom:8px;">✓</div>
            <h1 style="font-size:1.4rem;margin:0 0 4px;"><?= View::e($org['name'] ?? 'Visitor Check-In') ?></h1>
            <p style="color:var(--text-muted);font-size:0.9rem;margin:0;">Please sign in to continue</p>
        </div>

        <?php
        $label = $settings['ldap_login_label'] ?? 'Username';
        $hint  = $settings['ldap_login_hint']  ?? '';
        ?>

        <form method="POST" action="/checkin/auth">
            <div class="form-group" style="margin-bottom:<?= $hint ? '4px' : '16px' ?>;">
                <label for="username"><?= View::e($label) ?></label>
                <input type="text" name="username" id="username"
                       placeholder="<?= View::e($label) ?>"
                       required autocomplete="username" autofocus>
            </div>
            <?php if ($hint): ?>
            <p style="color:var(--text-muted);font-size:0.8rem;margin:0 0 16px;">
                <?= View::e($hint) ?>
            </p>
            <?php endif; ?>

            <div class="form-group" style="margin-bottom:24px;">
                <label for="password">Password</label>
                <input type="password" name="password" id="password"
                       placeholder="Password"
                       required autocomplete="current-password">
            </div>

            <button type="submit" class="button" style="width:100%;padding:12px;">
                Sign In &amp; Continue
            </button>
        </form>

        <div style="text-align:center;margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
            <a href="/auth/login" style="font-size:0.85rem;color:var(--text-muted);">Staff / Admin login &rarr;</a>
        </div>

    </div>
</div>
