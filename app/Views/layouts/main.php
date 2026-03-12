<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Core\View::e($title ?? 'dadCHECKIN-TOO') ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <?php
    // Inject theme CSS overrides if a custom theme is saved
    try {
        $__db  = \App\Core\Database::getInstance();
        $__cfg = require BASE_PATH . '/config/app.php';
        $__org = $__db->prepare("SELECT settings FROM organizations WHERE slug = ? LIMIT 1");
        $__org->execute([$__cfg['org_slug'] ?? '']);
        $__settings = json_decode($__org->fetchColumn() ?? '{}', true) ?: [];
        $__theme    = $__settings['theme'] ?? [];
        if (!empty($__theme['primary'])) {
            $__p  = htmlspecialchars($__theme['primary'],     ENT_QUOTES);
            $__h  = htmlspecialchars($__theme['header_bg'],   ENT_QUOTES);
            $__bg = htmlspecialchars($__theme['bg'],          ENT_QUOTES);
            $__ht = htmlspecialchars($__theme['header_text'] ?? '#ffffff', ENT_QUOTES);
            // Derive hover as slightly darker version of primary
            echo "<style>:root{--primary:{$__p};--primary-hover:{$__p};--header-bg:{$__h};--header-text:{$__ht};--bg:{$__bg};}
.site-header{background:{$__h}!important;color:{$__ht}!important;}
.site-header a,.site-brand,.site-org,.header-nav a{color:{$__ht}!important;}
.button{background:{$__p}!important;}
.button:hover{filter:brightness(.9);}
body{background:{$__bg}!important;}</style>\n";
        }
    } catch (\Throwable $__e) { /* DB not ready yet — skip */ }
    ?>
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <?php if (!empty($__theme['logo'])): ?>
            <img src="/uploads/logos/<?= htmlspecialchars($__theme['logo'], ENT_QUOTES) ?>"
                 alt="Logo" style="height:32px;border-radius:4px;margin-right:4px;">
        <?php endif; ?>
        <span class="site-brand">dadCHECKIN-TOO</span>
        <?php if (!empty($org)): ?>
            <span class="site-org"><?= \App\Core\View::e($org['name']) ?></span>
        <?php endif; ?>
        <?php if (\App\Core\Auth::check()):
            $__role     = $_SESSION['user_role'] ?? 'staff';
            $__name     = $_SESSION['user_name'] ?? 'User';
            $__initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_filter(explode(' ', $__name))));
            $__initials = substr($__initials, 0, 2);
            $__roleLabels = [
                'super_admin'    => 'Super Admin',
                'org_admin'      => 'Org Admin',
                'location_admin' => 'Location Admin',
                'staff'          => 'Staff',
            ];
            $__roleLabel = $__roleLabels[$__role] ?? ucfirst($__role);
            $__isSuperAdmin = ($__role === 'super_admin');
        ?>
            <nav class="header-nav">
                <a href="/admin">Dashboard</a>
                <a href="/logs">Log Hub</a>
                <a href="/admin/live">Live Logs</a>
                <a href="/admin/hosts">Hosts</a>
                <?php if (in_array($__role, ['org_admin','super_admin'])): ?>
                    <a href="/admin/users">Users</a>
                <?php endif; ?>
                <a href="/admin/reasons">Reasons</a>
                <a href="/admin/history">History</a>
                <a href="/admin/analytics">Analytics</a>
                <?php if ($__isSuperAdmin): ?>
                    <a href="/admin/setup/auth">Auth Setup</a>
                <?php endif; ?>
                <?php if (!empty($helpSlug)): ?>
                    <a href="/admin/docs/<?= \App\Core\View::e($helpSlug) ?>" class="page-help-btn">
                        <span class="page-help-btn-icon">?</span> Help
                    </a>
                <?php else: ?>
                    <a href="/admin/docs" class="page-help-btn">
                        <span class="page-help-btn-icon">?</span> Help
                    </a>
                <?php endif; ?>

                <!-- User avatar with dropdown -->
                <div class="user-avatar-wrap">
                    <button class="user-avatar" id="user-avatar-btn" aria-expanded="false"
                            title="<?= \App\Core\View::e($__name) ?> — <?= \App\Core\View::e($__roleLabel) ?>">
                        <?= \App\Core\View::e($__initials) ?>
                    </button>
                    <div class="user-dropdown" id="user-dropdown" hidden>
                        <div class="user-dropdown-info">
                            <div class="user-dropdown-name"><?= \App\Core\View::e($__name) ?></div>
                            <div class="user-dropdown-email"><?= \App\Core\View::e($_SESSION['user_email'] ?? '') ?></div>
                            <span class="user-role-badge user-role-<?= \App\Core\View::e($__role) ?>">
                                <?= \App\Core\View::e($__roleLabel) ?>
                            </span>
                        </div>
                        <div class="user-dropdown-divider"></div>
                        <?php if ($__isSuperAdmin): ?>
                            <a href="/admin/setup" class="user-dropdown-item">System Setup</a>
                            <a href="/admin/setup/auth" class="user-dropdown-item">Auth &amp; LDAP</a>
                            <a href="/admin/setup/notifications" class="user-dropdown-item">Notifications &amp; SMTP</a>
                            <div class="user-dropdown-divider"></div>
                        <?php endif; ?>
                        <a href="/admin/settings" class="user-dropdown-item">Settings</a>
                        <a href="/admin/settings/theme" class="user-dropdown-item">Theme &amp; Appearance</a>
                        <a href="/admin/docs" class="user-dropdown-item">Help &amp; Docs</a>
                        <div class="user-dropdown-divider"></div>
                        <a href="/auth/logout" class="user-dropdown-item user-dropdown-signout">Sign Out</a>
                    </div>
                </div>
            </nav>
        <?php endif; ?>
    </div>
</header>

<main class="page-wrapper">
    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= \App\Core\View::e($flash['type']) ?>">
            <?= \App\Core\View::e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<footer class="site-footer">
    <p>dadCHECKIN-TOO &mdash; Visitor Management</p>
</footer>

<script>
(function () {
    var btn = document.getElementById('user-avatar-btn');
    var dd  = document.getElementById('user-dropdown');
    if (!btn || !dd) return;
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = !dd.hidden;
        dd.hidden = open;
        btn.setAttribute('aria-expanded', !open);
    });
    document.addEventListener('click', function () {
        if (!dd.hidden) { dd.hidden = true; btn.setAttribute('aria-expanded', 'false'); }
    });
    dd.addEventListener('click', function (e) { e.stopPropagation(); });
})();
</script>
</body>
</html>
