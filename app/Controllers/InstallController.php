<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class InstallController extends Controller
{
    private string $lockFile;
    private string $schemaFile;
    private string $dbLocalFile;

    private const UPGRADE_STEPS = [
        'organization'  => ['label' => 'Organization',   'optional' => false, 'next' => 'migration'],
        'migration'     => ['label' => 'Migration',       'optional' => false, 'next' => 'departments'],
        'departments'   => ['label' => 'Departments',     'optional' => true,  'next' => 'auth'],
        'auth'          => ['label' => 'Authentication',  'optional' => true,  'next' => 'kiosk'],
        'kiosk'         => ['label' => 'Kiosk',           'optional' => true,  'next' => 'notifications'],
        'notifications' => ['label' => 'Notifications',   'optional' => true,  'next' => 'review'],
        'review'        => ['label' => 'Review',          'optional' => false, 'next' => null],
    ];

    public function __construct($request)
    {
        parent::__construct($request);
        $this->lockFile    = BASE_PATH . '/config/installed.lock';
        $this->schemaFile  = BASE_PATH . '/database/schema.sql';
        $this->dbLocalFile = BASE_PATH . '/config/database.local.php';
    }

    /**
     * Detect an old dadtoo v1 config.php sitting in BASE_PATH and, if found,
     * parse the hardcoded credentials and write config/database.local.php so
     * the upgrade wizard can proceed without asking the user to re-enter anything.
     *
     * Returns true if credentials were successfully extracted and written.
     */
    private function autoDetectLegacyConfig(): bool
    {
        if (file_exists($this->dbLocalFile)) {
            return false; // Already have a local config — nothing to do
        }

        $legacyConfig = BASE_PATH . '/config.php';
        if (!file_exists($legacyConfig)) {
            return false;
        }

        $src = file_get_contents($legacyConfig);

        // Extract the four hardcoded variables from the getDBConnection() function
        $host = $user = $pass = $name = null;
        if (preg_match('/\$db_server\s*=\s*["\']([^"\']+)["\']/', $src, $m))   $host = $m[1];
        if (preg_match('/\$db_username\s*=\s*["\']([^"\']+)["\']/', $src, $m)) $user = $m[1];
        if (preg_match('/\$db_password\s*=\s*["\']([^"\']+)["\']/', $src, $m)) $pass = $m[1];
        if (preg_match('/\$db_database\s*=\s*["\']([^"\']+)["\']/', $src, $m)) $name = $m[1];

        if (!$host || !$user || !$name) {
            return false; // Could not parse — leave wizard to ask manually
        }

        // Write the v2-format local config so choosePath() can connect and detect
        $content = "<?php\nreturn [\n"
            . "    'host'     => " . var_export($host, true) . ",\n"
            . "    'port'     => 3306,\n"
            . "    'database' => " . var_export($name, true) . ",\n"
            . "    'username' => " . var_export($user, true) . ",\n"
            . "    'password' => " . var_export((string)$pass, true) . ",\n"
            . "];\n";

        return (bool) file_put_contents($this->dbLocalFile, $content);
    }

    // ── System Check ─────────────────────────────────────────────

    /**
     * Run all pre-flight checks and attempt to auto-fix what we can.
     * Returns an array of check result arrays:
     *   [ 'label', 'status' (ok|warn|fail), 'detail', 'fixed' (bool), 'cmd' (string|null) ]
     */
    private function runSysCheck(): array
    {
        $isRoot    = (function_exists('posix_getuid') && posix_getuid() === 0);
        $canShell  = function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))));
        $basePath  = BASE_PATH;
        $publicDir = $basePath . '/public';
        $configDir = $basePath . '/config';
        $checks    = [];

        // ── PHP extensions ────────────────────────────────────────
        $required = ['pdo_mysql', 'mbstring', 'curl', 'json', 'openssl'];
        $optional = ['ldap'];
        foreach ($required as $ext) {
            $checks[] = [
                'label'  => "PHP extension: <code>{$ext}</code>",
                'status' => extension_loaded($ext) ? 'ok' : 'fail',
                'detail' => extension_loaded($ext) ? 'Loaded' : 'Missing — required',
                'fixed'  => false,
                'cmd'    => extension_loaded($ext) ? null : "apt install php-{$ext} && systemctl restart apache2",
            ];
        }
        foreach ($optional as $ext) {
            $checks[] = [
                'label'  => "PHP extension: <code>{$ext}</code>",
                'status' => extension_loaded($ext) ? 'ok' : 'warn',
                'detail' => extension_loaded($ext) ? 'Loaded' : 'Not loaded — only needed for LDAP/AD login',
                'fixed'  => false,
                'cmd'    => extension_loaded($ext) ? null : "apt install php-ldap && systemctl restart apache2",
            ];
        }

        // ── PHP version ───────────────────────────────────────────
        $phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
        $checks[] = [
            'label'  => 'PHP version',
            'status' => $phpOk ? 'ok' : 'fail',
            'detail' => 'PHP ' . PHP_VERSION . ($phpOk ? '' : ' — 8.1+ required'),
            'fixed'  => false,
            'cmd'    => null,
        ];

        // ── config/ directory writable ────────────────────────────
        $configWritable = is_writable($configDir);
        $configFixed    = false;
        if (!$configWritable && $isRoot) {
            @chmod($configDir, 0775);
            @chown($configDir, 'www-data');
            $configWritable = is_writable($configDir);
            $configFixed    = $configWritable;
        }
        $checks[] = [
            'label'  => '<code>config/</code> directory writable',
            'status' => $configWritable ? 'ok' : 'fail',
            'detail' => $configWritable ? ($configFixed ? 'Fixed automatically' : 'Writable') : 'Not writable — wizard cannot save config',
            'fixed'  => $configFixed,
            'cmd'    => $configWritable ? null : "chmod 775 {$configDir} && chown www-data:www-data {$configDir}",
        ];

        // ── public/ directory permissions ─────────────────────────
        $publicReadable = is_readable($publicDir);
        $publicFixed    = false;
        if (!$publicReadable && $isRoot) {
            @chmod($publicDir, 0755);
            $publicReadable = is_readable($publicDir);
            $publicFixed    = $publicReadable;
        }
        $checks[] = [
            'label'  => '<code>public/</code> directory readable',
            'status' => $publicReadable ? 'ok' : 'fail',
            'detail' => $publicReadable ? ($publicFixed ? 'Fixed automatically' : 'Readable') : 'Not readable by web server',
            'fixed'  => $publicFixed,
            'cmd'    => $publicReadable ? null : "chmod 755 {$publicDir}",
        ];

        // ── DocumentRoot check ────────────────────────────────────
        $docRoot        = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        $expectedRoot   = rtrim($publicDir, '/');
        $docRootOk      = ($docRoot === $expectedRoot);
        $docRootFixed   = false;

        if (!$docRootOk && $isRoot && $canShell) {
            // Try to find and fix the Apache vhost config for this ServerName
            $serverName = $_SERVER['SERVER_NAME'] ?? '';
            $confFiles  = glob('/etc/apache2/sites-enabled/*.conf') ?: [];
            foreach ($confFiles as $confFile) {
                $contents = file_get_contents($confFile);
                if ($serverName && strpos($contents, $serverName) === false) continue;
                // Replace the DocumentRoot line
                $newContents = preg_replace(
                    '/DocumentRoot\s+\S+/m',
                    'DocumentRoot ' . $expectedRoot,
                    $contents
                );
                // Replace the <Directory ...> path as well
                $newContents = preg_replace(
                    '/<Directory\s+' . preg_quote($docRoot, '/') . '\s*>/m',
                    '<Directory ' . $expectedRoot . '>',
                    $newContents
                );
                if ($newContents !== $contents) {
                    file_put_contents($confFile, $newContents);
                    shell_exec('systemctl reload apache2 2>&1');
                    $docRootFixed = true;
                    break;
                }
            }
        }

        $checks[] = [
            'label'  => 'Apache <code>DocumentRoot</code>',
            'status' => ($docRootOk || $docRootFixed) ? 'ok' : 'warn',
            'detail' => ($docRootOk || $docRootFixed)
                ? ($docRootFixed ? "Fixed automatically → {$expectedRoot}" : "Correctly set to <code>{$expectedRoot}</code>")
                : "Currently <code>{$docRoot}</code> — should be <code>{$expectedRoot}</code>",
            'fixed'  => $docRootFixed,
            'cmd'    => ($docRootOk || $docRootFixed) ? null
                : "# In your Apache vhost config:\nDocumentRoot {$expectedRoot}\n\n# Then:\nsystemctl reload apache2",
        ];

        // ── mod_rewrite ───────────────────────────────────────────
        $rewriteOk    = false;
        $rewriteFixed = false;
        if (function_exists('apache_get_modules')) {
            $rewriteOk = in_array('mod_rewrite', apache_get_modules());
        } elseif ($canShell) {
            $rewriteOk = (strpos((string)shell_exec('apache2ctl -M 2>/dev/null'), 'rewrite') !== false);
        }
        if (!$rewriteOk && $isRoot && $canShell) {
            shell_exec('a2enmod rewrite 2>&1 && systemctl reload apache2 2>&1');
            $rewriteFixed = true;
            $rewriteOk    = true;
        }
        $checks[] = [
            'label'  => 'Apache <code>mod_rewrite</code>',
            'status' => $rewriteOk ? 'ok' : 'fail',
            'detail' => $rewriteOk ? ($rewriteFixed ? 'Enabled automatically' : 'Enabled') : 'Not enabled — all routes will return 404',
            'fixed'  => $rewriteFixed,
            'cmd'    => $rewriteOk ? null : "a2enmod rewrite && systemctl reload apache2",
        ];

        // ── Legacy config.php detected ────────────────────────────
        $legacyConfig = $basePath . '/config.php';
        if (file_exists($legacyConfig)) {
            $checks[] = [
                'label'  => 'Legacy <code>config.php</code> detected',
                'status' => 'ok',
                'detail' => 'dadtoo v1 config.php found — credentials will be read automatically',
                'fixed'  => false,
                'cmd'    => null,
            ];
        }

        return $checks;
    }

    public function sysCheck(array $params): void
    {
        $checks   = $this->runSysCheck();
        $hasFail  = !empty(array_filter($checks, fn($c) => $c['status'] === 'fail'));
        $hasFixed = !empty(array_filter($checks, fn($c) => $c['fixed']));

        $this->view->render('install/syscheck', [
            'title'    => 'System Check',
            'checks'   => $checks,
            'hasFail'  => $hasFail,
            'hasFixed' => $hasFixed,
            'flash'    => $this->flash(),
        ], 'install');
    }

    public function sysCheckContinue(array $params): void
    {
        // Only allow continue if all required checks pass
        $checks  = $this->runSysCheck();
        $hasFail = !empty(array_filter($checks, fn($c) => $c['status'] === 'fail'));
        if ($hasFail) {
            $this->redirect('/install/syscheck');
            return;
        }
        $_SESSION['syscheck_passed'] = true;
        $this->redirect('/install');
    }

    // ── Path chooser ─────────────────────────────────────────────

    public function choosePath(array $params): void
    {
        // Skip syscheck for upgrades — if a legacy config.php exists the server
        // is already running dadtoo v1 and known-good. Only run syscheck for
        // fresh installs where the user may not have configured anything yet.
        $isUpgrade = file_exists(BASE_PATH . '/config.php');
        if (!$isUpgrade && empty($_SESSION['syscheck_passed'])) {
            $this->redirect('/install/syscheck');
            return;
        }

        // If no v2 local config exists yet, check for a legacy dadtoo config.php
        // sitting in the same directory. If found, parse credentials and write the
        // local config automatically — the user never has to retype anything.
        $this->autoDetectLegacyConfig();

        // If a database config already exists, auto-detect and route
        if (file_exists($this->dbLocalFile)) {
            try {
                $dbCfg = require $this->dbLocalFile;
                $pdo   = new \PDO(
                    "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$dbCfg['database']};charset=utf8mb4",
                    $dbCfg['username'], $dbCfg['password'],
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5]
                );
                $dbType = $this->detectDatabase($pdo);

                if ($dbType === 'legacy_dadtoo') {
                    // Run new schema so checkin tables exist, then show upgrade chooser
                    try {
                        $sql = file_get_contents($this->schemaFile);
                        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                            $pdo->exec($stmt);
                        }
                    } catch (\PDOException $e) { /* tables may already exist */ }

                    $this->view->render('install/detected-upgrade', [
                        'title'  => 'Upgrade Detected',
                        'dbCfg'  => $dbCfg,
                        'flash'  => $this->flash(),
                    ], 'install');
                    return;
                }

                if ($dbType === 'current_checkin') {
                    $orgCount = $pdo->query("SELECT COUNT(*) FROM organizations")->fetchColumn();
                    if ($orgCount > 0) {
                        // Fully installed — write lock file if missing and redirect to admin
                        if (!file_exists($this->lockFile)) {
                            file_put_contents($this->lockFile, date('Y-m-d H:i:s'));
                        }
                        $this->redirect('/admin');
                        return;
                    }
                    // Schema exists but no org yet — mid-upgrade
                    $this->redirect('/install/guided-upgrade/organization');
                    return;
                }
            } catch (\PDOException $e) {
                // Can't connect — fall through to normal wizard
            }
        }

        // Fresh install — show 2-option chooser
        $this->view->render('install/step1', [
            'title' => 'Choose Setup Path',
            'flash' => $this->flash(),
        ], 'install');
    }

    public function guidedInfo(array $params): void
    {
        $_SESSION['install_guided']   = true;
        $_SESSION['install_max_step'] = max($_SESSION['install_max_step'] ?? 1, 2);
        $this->redirect('/install/2');
    }

    // ── Upgrade / migrate ─────────────────────────────────────────

    public function upgradePage(array $params): void
    {
        $this->view->render('install/upgrade', [
            'title' => 'Upgrade from dadtoo',
            'flash' => $this->flash(),
        ], 'install');
    }

    public function upgradeRun(array $params): void
    {
        $sourceDb = trim($this->request->input('source_db', 'dadtoodb_import'));
        $dryRun   = !empty($this->request->input('dry_run'));

        // Basic safety check — source DB must exist and be accessible with configured credentials
        try {
            $dbCfg = require BASE_PATH . '/config/database.local.php';
            $test  = new \PDO(
                "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$sourceDb};charset=utf8mb4",
                $dbCfg['username'], $dbCfg['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            $test->query("SELECT 1 FROM checkin_checkout LIMIT 1");
        } catch (\PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => "Cannot connect to source database '{$sourceDb}': " . $e->getMessage()];
            $this->redirect('/install/upgrade');
            return;
        }

        $script = BASE_PATH . '/scripts/migrate_from_dadtoodb.php';
        $flag   = $dryRun ? '--dry-run' : '';
        $cmd    = escapeshellcmd("php {$script}") . " --source=" . escapeshellarg($sourceDb) . " {$flag} 2>&1";
        $output = shell_exec($cmd);

        $this->view->render('install/upgrade', [
            'title'  => 'Upgrade from dadtoo',
            'output' => $output,
            'dryRun' => $dryRun,
            'flash'  => $this->flash(),
        ], 'install');
    }

    // ── Step router ──────────────────────────────────────────────

    public function step(array $params): void
    {
        $step       = (int)($params['step'] ?? 1);
        $maxReached = $_SESSION['install_max_step'] ?? 1;
        if ($step > $maxReached) {
            $this->redirect('/install');
            return;
        }
        $this->view->render('install/step' . $step, [
            'title' => 'Setup — Step ' . $step,
            'step'  => $step,
            'flash' => $this->flash(),
        ], 'install');
    }

    // ── Step 1: Requirements ─────────────────────────────────────

    public function requirements(array $params): void
    {
        $checks = $this->runChecks();
        $passed = !in_array(false, array_column($checks, 'pass'), true);
        if ($passed) {
            $_SESSION['install_max_step'] = max($_SESSION['install_max_step'] ?? 1, 2);
        }
        $this->view->render('install/step1', [
            'title'  => 'Setup — Step 1',
            'step'   => 1,
            'checks' => $checks,
            'passed' => $passed,
            'flash'  => $this->flash(),
        ], 'install');
    }

    private function runChecks(): array
    {
        $configDir = BASE_PATH . '/config';
        return [
            [
                'label' => 'PHP 8.1 or higher',
                'pass'  => version_compare(PHP_VERSION, '8.1.0', '>='),
                'note'  => 'PHP ' . PHP_VERSION . ' detected',
            ],
            [
                'label' => 'PDO extension',
                'pass'  => extension_loaded('pdo'),
                'note'  => extension_loaded('pdo') ? 'Loaded' : 'Missing — install php-pdo',
            ],
            [
                'label' => 'PDO MySQL driver',
                'pass'  => extension_loaded('pdo_mysql'),
                'note'  => extension_loaded('pdo_mysql') ? 'Loaded' : 'Missing — install php-mysql',
            ],
            [
                'label' => 'JSON extension',
                'pass'  => extension_loaded('json'),
                'note'  => extension_loaded('json') ? 'Loaded' : 'Missing — install php-json',
            ],
            [
                'label' => 'config/ directory writable',
                'pass'  => is_writable($configDir),
                'note'  => is_writable($configDir) ? 'Writable' : 'Run: chmod 775 ' . $configDir,
            ],
        ];
    }

    // ── Test connection (AJAX) ───────────────────────────────────

    public function testConnection(array $params): void
    {
        $host = trim($this->request->input('db_host', ''));
        $port = (int)$this->request->input('db_port', 0);
        $name = trim($this->request->input('db_name', ''));
        $user = trim($this->request->input('db_user', ''));
        $pass = $this->request->input('db_pass', '');

        // If no fields provided, use the saved local config (step 3 auto-check)
        if (!$host && !$name && !$user) {
            if (file_exists($this->dbLocalFile)) {
                $local = require $this->dbLocalFile;
                $host  = $local['host']     ?? 'localhost';
                $port  = $local['port']     ?? 3306;
                $name  = $local['database'] ?? '';
                $user  = $local['username'] ?? '';
                $pass  = $local['password'] ?? '';
            } else {
                $this->json(['success' => false, 'message' => 'No saved database configuration found.']);
                return;
            }
        }

        if (!$name || !$user) {
            $this->json(['success' => false, 'message' => 'Database name and username are required.']);
            return;
        }

        $port = $port ?: 3306;

        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
                $user, $pass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5]
            );
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            $this->json(['success' => true, 'message' => 'Connected successfully. MySQL ' . $version]);
        } catch (\PDOException $e) {
            $errCode = (int)$e->getCode();
            // Try without dbname to distinguish "DB doesn't exist" from "no permission"
            try {
                new \PDO(
                    "mysql:host={$host};port={$port};charset=utf8mb4",
                    $user, $pass,
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5]
                );
                // Server is reachable — figure out why the DB connection failed
                if ($errCode === 1044 || $errCode === 1045) {
                    $this->json(['success' => false, 'message' => "Database \"{$name}\" exists but your user does not have permission to access it. Run: GRANT ALL PRIVILEGES ON {$name}.* TO '{$user}'@'localhost';"]);
                } else {
                    $this->json(['success' => false, 'message' => "Database \"{$name}\" does not exist yet — it will be created when you proceed."]);
                }
            } catch (\PDOException $e2) {
                $this->json(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
            }
        }
    }

    // ── Step 2: Database ─────────────────────────────────────────

    public function saveDatabase(array $params): void
    {
        $host = trim($this->request->input('db_host', 'localhost'));
        $port = (int)$this->request->input('db_port', 3306);
        $name = trim($this->request->input('db_name', ''));
        $user = trim($this->request->input('db_user', ''));
        $pass = $this->request->input('db_pass', '');

        if (!$name || !$user) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Database name and username are required.'];
            $this->redirect('/install/2');
            return;
        }

        // Try connecting directly to the named database
        try {
            $pdo = new \PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'Unknown database')) {
                // Try creating it
                try {
                    $tmp = new \PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    ]);
                    $tmp->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo = new \PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass, [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    ]);
                } catch (\PDOException $e2) {
                    $_SESSION['flash'] = [
                        'type'    => 'error',
                        'message' => "Database \"{$name}\" does not exist and could not be created automatically. "
                                   . "Please create it in MySQL first and grant your user access, then try again.",
                    ];
                    $this->redirect('/install/2');
                    return;
                }
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()];
                $this->redirect('/install/2');
                return;
            }
        }

        // Detect what's in this database before running schema
        $dbType = $this->detectDatabase($pdo);

        // Write local DB config now (regardless of path)
        file_put_contents($this->dbLocalFile, "<?php\nreturn " . var_export([
            'host' => $host, 'port' => $port,
            'database' => $name, 'username' => $user, 'password' => $pass,
        ], true) . ";\n");

        if ($dbType === 'current_checkin') {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Database already configured. Redirecting to admin.'];
            $this->redirect('/admin');
            return;
        }

        if ($dbType === 'legacy_dadtoo') {
            // Run new schema on legacy DB so new tables exist alongside old ones
            try {
                $sql = file_get_contents($this->schemaFile);
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                    $pdo->exec($stmt);
                }
            } catch (\PDOException $e) {
                // Some statements may fail if tables already exist — that's OK for upgrade
            }
            $_SESSION['install_max_step'] = 2;
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Legacy dadtoo database detected. Choose your upgrade path.'];
            $this->redirect('/install/upgrade-path');
            return;
        }

        // Fresh install — run schema
        try {
            $sql = file_get_contents($this->schemaFile);
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                $pdo->exec($stmt);
            }
        } catch (\PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Schema error: ' . $e->getMessage()];
            $this->redirect('/install/2');
            return;
        }

        $_SESSION['install_max_step'] = 3;
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Database connected and tables created.'];
        $this->redirect('/install/3');
    }

    // ── Database detection ────────────────────────────────────────

    private function detectDatabase(\PDO $pdo): string
    {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        if (in_array('checkin_checkout', $tables) || in_array('visiting_persons', $tables)) {
            return 'legacy_dadtoo';
        }
        if (in_array('visits', $tables) && in_array('organizations', $tables)) {
            return 'current_checkin';
        }
        return 'fresh';
    }

    // ── Step 3: Org + Admin account ──────────────────────────────

    public function saveSetup(array $params): void
    {
        $orgName  = trim($this->request->input('org_name', '')) ?: 'My Organization';
        $timezone = $this->request->input('timezone', 'America/Chicago');
        $name     = trim($this->request->input('name', '')) ?: 'Administrator';
        $email    = trim($this->request->input('email', ''));
        $pass     = $this->request->input('password', '');
        $confirm  = $this->request->input('password_confirm', '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'A valid email address is required.'];
            $this->redirect('/install/3');
            return;
        }
        if (strlen($pass) < 8) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Password must be at least 8 characters.'];
            $this->redirect('/install/3');
            return;
        }
        if ($pass !== $confirm) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Passwords do not match.'];
            $this->redirect('/install/3');
            return;
        }

        $pdo  = $this->getPdo();
        $slug = $this->makeSlug($orgName);

        // Ensure unique slug
        $check = $pdo->prepare("SELECT COUNT(*) FROM organizations WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetchColumn() > 0) {
            $slug .= '-' . substr(md5(time()), 0, 4);
        }

        $pdo->prepare("INSERT INTO organizations (name, slug, timezone) VALUES (?, ?, ?)")
            ->execute([$orgName, $slug, $timezone]);
        $orgId = (int)$pdo->lastInsertId();

        $pdo->prepare("INSERT INTO locations (organization_id, name) VALUES (?, 'Main Office')")
            ->execute([$orgId]);

        $pdo->prepare(
            "INSERT INTO users (organization_id, name, email, role, auth_provider, password_hash)
             VALUES (?, ?, ?, 'org_admin', 'local', ?)"
        )->execute([$orgId, $name, $email, Auth::hashPassword($pass)]);

        // Update app config
        $this->updateAppConfig('org_slug', $slug);
        $this->updateAppConfig('timezone', $timezone);

        // If a migration is pending (quick upgrade path), run it now
        $pendingSource = $_SESSION['pending_migration_source'] ?? null;
        if ($pendingSource) {
            unset($_SESSION['pending_migration_source']);
            $script  = BASE_PATH . '/scripts/migrate_from_dadtoodb.php';
            $cmd     = escapeshellcmd("php {$script}") . ' --source=' . escapeshellarg($pendingSource) . ' 2>&1';
            $output  = shell_exec($cmd);
            $success = str_contains((string)$output, '✓ Migration complete.');

            file_put_contents($this->lockFile, date('Y-m-d H:i:s'));
            unset($_SESSION['install_max_step'], $_SESSION['install_guided']);

            $db   = Database::getInstance();
            $user = $db->prepare("SELECT * FROM users WHERE email = ? AND organization_id = ?");
            $user->execute([$email, $orgId]);
            $user = $user->fetch();
            if ($user) {
                \App\Core\Auth::loginUser($user);
            }

            $_SESSION['flash'] = $success
                ? ['type' => 'success', 'message' => 'Migration complete! Your data has been imported.']
                : ['type' => 'error',   'message' => 'Admin account created but migration reported errors. Check Admin → Upgrade.'];
            $this->redirect('/admin');
            return;
        }

        // Write lock file
        file_put_contents($this->lockFile, date('Y-m-d H:i:s'));

        $guided = !empty($_SESSION['install_guided']);
        unset($_SESSION['install_max_step'], $_SESSION['install_guided']);

        // Install auto-checkout cron
        $cronLine = "*/15 * * * * php " . BASE_PATH . "/scripts/auto_checkout.php >> /var/log/checkin-auto-checkout.log 2>&1";
        $this->installCronJob($cronLine, '# dadCHECKIN-TOO auto-checkout');

        // Seed demo data if requested
        if ($this->request->input('load_demo')) {
            $script = BASE_PATH . '/scripts/seed_demo.php';
            shell_exec(escapeshellcmd("php {$script}") . ' --org-id=' . (int)$orgId . ' 2>&1');
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Installation complete. Welcome to dadCHECKIN-TOO!'];

        if ($guided) {
            $db   = Database::getInstance();
            $user = $db->prepare("SELECT * FROM users WHERE email = ? AND organization_id = ?");
            $user->execute([$email, $orgId]);
            $user = $user->fetch();
            if ($user) {
                \App\Core\Auth::loginUser($user);
                $this->redirect('/admin/setup');
                return;
            }
        }

        $this->redirect('/auth/login');
    }

    // ── Upgrade prepare: create new DB, run schema, save config ──

    public function upgradePrepare(array $params): void
    {
        $sourceDb    = trim($this->request->input('source_db', ''));
        $newDbName   = trim($this->request->input('new_db_name', ''));
        $newDbUser   = trim($this->request->input('new_db_user', ''));
        $newDbPass   = $this->request->input('new_db_pass', '');
        $upgradeType = $this->request->input('upgrade_type', 'quick');

        // Read host/port from existing config
        $existing = file_exists($this->dbLocalFile) ? require $this->dbLocalFile : [];
        $host = $existing['host'] ?? 'localhost';
        $port = $existing['port'] ?? 3306;

        if (!$newDbName || $newDbName === $sourceDb) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'New database name must be different from the source database.'];
            $this->redirect('/install');
            return;
        }

        // Connect and create new DB if needed
        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $newDbUser, $newDbPass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$newDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo = new \PDO(
                "mysql:host={$host};port={$port};dbname={$newDbName};charset=utf8mb4",
                $newDbUser, $newDbPass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            // If the database already has CheckIn data in it, stop and warn rather than proceeding blindly
            $tableCount = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()")->fetchColumn();
            if ($tableCount > 0) {
                $hasOrg = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organizations'")->fetchColumn();
                if ($hasOrg) {
                    $orgCount = (int)$pdo->query("SELECT COUNT(*) FROM organizations")->fetchColumn();
                    if ($orgCount > 0) {
                        $_SESSION['flash'] = ['type' => 'error', 'message' => "The database \"{$newDbName}\" already contains CheckIn data. Please choose a different, empty database name."];
                        $this->redirect('/install');
                        return;
                    }
                }
            }
        } catch (\PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not connect or create database: ' . $e->getMessage()];
            $this->redirect('/install');
            return;
        }

        // Run schema on the new empty database
        try {
            $sql = file_get_contents($this->schemaFile);
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                $pdo->exec($stmt);
            }
        } catch (\PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Schema error: ' . $e->getMessage()];
            $this->redirect('/install');
            return;
        }

        // Back up the original config before overwriting — allows abort/rollback
        $backupFile = $this->dbLocalFile . '.bak';
        if (file_exists($this->dbLocalFile) && !file_exists($backupFile)) {
            copy($this->dbLocalFile, $backupFile);
        }

        // Save new DB config
        file_put_contents($this->dbLocalFile, "<?php\nreturn " . var_export([
            'host'     => $host,
            'port'     => $port,
            'database' => $newDbName,
            'username' => $newDbUser,
            'password' => $newDbPass,
        ], true) . ";\n");

        $_SESSION['guided_upgrade_source_db'] = $sourceDb;
        $_SESSION['guided_upgrade_new_db']    = $newDbName;
        $_SESSION['guided_upgrade_skipped']   = [];

        if ($upgradeType === 'guided') {
            $this->redirect('/install/guided-upgrade/organization');
        } else {
            $_SESSION['install_max_step'] = 3;
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'New database ready. Create your admin account, then migration will run automatically.'];
            // Store source for quick upgrade migration after step 3
            $_SESSION['pending_migration_source'] = $sourceDb;
            $this->redirect('/install/3');
        }
    }

    // ── Upgrade path chooser ────────────────────────────────────

    public function upgradePathPage(array $params): void
    {
        $dbCfg    = file_exists($this->dbLocalFile) ? require $this->dbLocalFile : [];
        $sourceDb = $dbCfg['database'] ?? '';
        $this->view->render('install/upgrade-path', [
            'title'    => 'Choose Upgrade Path',
            'sourceDb' => $sourceDb,
            'flash'    => $this->flash(),
        ], 'install');
    }

    // ── Quick upgrade (all-in-one) ────────────────────────────────

    public function quickUpgradeRun(array $params): void
    {
        $sourceDb = trim($this->request->input('source_db', ''));

        $script = BASE_PATH . '/scripts/migrate_from_dadtoodb.php';
        $cmd    = escapeshellcmd("php {$script}") . ' --source=' . escapeshellarg($sourceDb) . ' 2>&1';
        $output = shell_exec($cmd);

        if (!str_contains((string)$output, '✓ Migration complete.')) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Migration failed. See output below.'];
            $this->view->render('install/upgrade-path', [
                'title'           => 'Quick Upgrade',
                'sourceDb'        => $sourceDb,
                'migrationOutput' => $output,
                'flash'           => $this->flash(),
            ], 'install');
            return;
        }

        $_SESSION['install_max_step'] = 3;
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Migration complete! Now create your admin account.'];
        $this->redirect('/install/3');
    }

    // ── Guided upgrade step router ────────────────────────────────

    public function guidedUpgradeStart(array $params): void
    {
        $sourceDb = trim($this->request->input('source_db', ''));
        if ($sourceDb) {
            $_SESSION['guided_upgrade_source_db'] = $sourceDb;
        }
        $_SESSION['guided_upgrade_skipped'] = [];
        $this->redirect('/install/guided-upgrade/organization');
    }

    public function guidedUpgradeStep(array $params): void
    {
        $step = $params['step'] ?? 'organization';
        if (!array_key_exists($step, self::UPGRADE_STEPS)) {
            $this->redirect('/install/guided-upgrade/organization');
            return;
        }

        $extraData = [];
        if ($step === 'kiosk') {
            try {
                $pdo      = $this->getPdo();
                $org      = $pdo->query("SELECT settings FROM organizations LIMIT 1")->fetch();
                $settings = $org ? (json_decode($org['settings'] ?? '{}', true) ?? []) : [];
                $extraData['kioskFields'] = $settings['kiosk_fields'] ?? [];
            } catch (\PDOException $e) {
                $extraData['kioskFields'] = [];
            }
        }
        if ($step === 'auth') {
            try {
                $pdo   = $this->getPdo();
                $orgId = $_SESSION['guided_upgrade_org_id'] ?? null;
                if (!$orgId) {
                    $row   = $pdo->query("SELECT organization_id FROM organizations LIMIT 1")->fetch();
                    $orgId = $row ? (int)$row['organization_id'] : null;
                }
                if ($orgId) {
                    $row  = $pdo->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
                    $row->execute([$orgId]);
                    $raw  = $row->fetchColumn();
                    $extraData['settings'] = json_decode($raw ?? '{}', true) ?? [];
                } else {
                    $extraData['settings'] = [];
                }
            } catch (\PDOException $e) {
                $extraData['settings'] = [];
            }
        }
        if ($step === 'departments') {
            try {
                $pdo   = $this->getPdo();
                $orgId = $_SESSION['guided_upgrade_org_id'] ?? null;
                if (!$orgId) {
                    $row   = $pdo->query("SELECT organization_id FROM organizations LIMIT 1")->fetch();
                    $orgId = $row ? (int)$row['organization_id'] : null;
                }
                $existing = $orgId
                    ? $pdo->prepare("SELECT name FROM departments WHERE organization_id = ? ORDER BY name")
                    : null;
                if ($existing) {
                    $existing->execute([$orgId]);
                    $extraData['existingDepartments'] = $existing->fetchAll(\PDO::FETCH_COLUMN);
                } else {
                    $extraData['existingDepartments'] = [];
                }
            } catch (\PDOException $e) {
                $extraData['existingDepartments'] = [];
            }
        }

        $this->view->render('install/guided-upgrade', array_merge([
            'title'       => 'Guided Upgrade — ' . self::UPGRADE_STEPS[$step]['label'],
            'currentStep' => $step,
            'steps'       => self::UPGRADE_STEPS,
            'skipped'     => $_SESSION['guided_upgrade_skipped'] ?? [],
            'sourceDb'    => $_SESSION['guided_upgrade_source_db'] ?? '',
            'flash'       => $this->flash(),
        ], $extraData), 'guided-upgrade');
    }

    public function guidedUpgradeOrgSave(array $params): void
    {
        $orgName  = trim($this->request->input('org_name', '')) ?: 'My Organization';
        $timezone = $this->request->input('timezone', 'America/Chicago');
        $name     = trim($this->request->input('name', '')) ?: 'Administrator';
        $email    = trim($this->request->input('email', ''));
        $pass     = $this->request->input('password', '');
        $confirm  = $this->request->input('password_confirm', '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'A valid email address is required.'];
            $this->redirect('/install/guided-upgrade/organization');
            return;
        }
        if (strlen($pass) < 8) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Password must be at least 8 characters.'];
            $this->redirect('/install/guided-upgrade/organization');
            return;
        }
        if ($pass !== $confirm) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Passwords do not match.'];
            $this->redirect('/install/guided-upgrade/organization');
            return;
        }

        $pdo  = $this->getPdo();
        $slug = $this->makeSlug($orgName);

        $check = $pdo->prepare("SELECT COUNT(*) FROM organizations WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetchColumn() > 0) {
            $slug .= '-' . substr(md5(time()), 0, 4);
        }

        $pdo->prepare("INSERT INTO organizations (name, slug, timezone) VALUES (?, ?, ?)")
            ->execute([$orgName, $slug, $timezone]);
        $orgId = (int)$pdo->lastInsertId();

        $pdo->prepare("INSERT INTO locations (organization_id, name) VALUES (?, 'Main Office')")
            ->execute([$orgId]);

        $pdo->prepare(
            "INSERT INTO users (organization_id, name, email, role, auth_provider, password_hash)
             VALUES (?, ?, ?, 'org_admin', 'local', ?)"
        )->execute([$orgId, $name, $email, Auth::hashPassword($pass)]);

        $this->updateAppConfig('org_slug', $slug);
        $this->updateAppConfig('timezone', $timezone);

        // Log the new admin in
        $db   = Database::getInstance();
        $user = $db->prepare("SELECT * FROM users WHERE email = ? AND organization_id = ?");
        $user->execute([$email, $orgId]);
        $user = $user->fetch();
        if ($user) {
            \App\Core\Auth::loginUser($user);
        }

        $_SESSION['guided_upgrade_org_id'] = $orgId;
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Organization and admin account created.'];
        $this->redirect('/install/guided-upgrade/migration');
    }

    public function guidedUpgradeMigration(array $params): void
    {
        $sourceDb = trim($this->request->input('source_db',
            $_SESSION['guided_upgrade_source_db'] ?? ''));

        if ($sourceDb) {
            $_SESSION['guided_upgrade_source_db'] = $sourceDb;
        }

        $script = BASE_PATH . '/scripts/migrate_from_dadtoodb.php';
        $cmd    = escapeshellcmd("php {$script}") . ' --source=' . escapeshellarg($sourceDb) . ' 2>&1';
        $output = shell_exec($cmd);

        $success = str_contains((string)$output, '✓ Migration complete.');
        if ($success) {
            $_SESSION['guided_upgrade_migrated'] = true;
        }

        $this->view->render('install/guided-upgrade', [
            'title'           => 'Guided Upgrade — Migration',
            'currentStep'     => 'migration',
            'steps'           => self::UPGRADE_STEPS,
            'skipped'         => $_SESSION['guided_upgrade_skipped'] ?? [],
            'sourceDb'        => $sourceDb,
            'migrationOutput' => $output,
            'flash'           => $this->flash(),
        ], 'guided-upgrade');
    }

    public function guidedUpgradeMigrationStream(array $params): void
    {
        // Close session so other tabs aren't blocked during the long-running stream
        session_write_close();

        $dbCfg  = file_exists($this->dbLocalFile) ? require $this->dbLocalFile : [];
        $srcDb  = trim($this->request->input('source_db', ''));

        // SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        ob_implicit_flush(true);
        if (ob_get_level()) ob_end_flush();

        $sse = function(string $type, array $data) {
            echo 'data: ' . json_encode(array_merge(['type' => $type], $data)) . "\n\n";
            flush();
        };

        $host = $dbCfg['host'] ?? 'localhost';
        $port = $dbCfg['port'] ?? 3306;
        $user = $dbCfg['username'] ?? '';
        $pass = $dbCfg['password'] ?? '';
        $dst  = $dbCfg['database'] ?? '';

        try {
            $src = new \PDO("mysql:host={$host};port={$port};dbname={$srcDb};charset=utf8mb4",
                $user, $pass, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]);
            $dstPdo = new \PDO("mysql:host={$host};port={$port};dbname={$dst};charset=utf8mb4",
                $user, $pass, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]);
        } catch (\PDOException $e) {
            $sse('error', ['message' => 'Connection failed: ' . $e->getMessage()]);
            return;
        }

        $org = $dstPdo->query("SELECT organization_id FROM organizations LIMIT 1")->fetch();
        if (!$org) { $sse('error', ['message' => 'No organization found.']); return; }
        $orgId = (int)$org['organization_id'];

        $sse('start', ['message' => "Source: {$srcDb} → Destination: {$dst} (Org #{$orgId})"]);

        // ── Stage 1: Hosts (0–15%) ────────────────────────────────
        $sse('stage', ['stage' => 1, 'label' => 'Migrating hosts', 'pct' => 0]);

        $persons = $src->query("SELECT * FROM visiting_persons ORDER BY person_id")->fetchAll();
        $hostMap = [];
        $chkHost = $dstPdo->prepare("SELECT host_id FROM hosts WHERE organization_id = ? AND name = ?");
        $insHost = $dstPdo->prepare("INSERT INTO hosts (organization_id, name, active, created_at) VALUES (?, ?, 1, NOW())");

        foreach ($persons as $i => $p) {
            $chkHost->execute([$orgId, $p['person_name']]);
            $ex = $chkHost->fetch();
            if ($ex) {
                $hostMap[$p['person_id']] = (int)$ex['host_id'];
                $sse('log', ['message' => "SKIP  {$p['person_name']}"]);
            } else {
                $insHost->execute([$orgId, $p['person_name']]);
                $id = (int)$dstPdo->lastInsertId();
                $hostMap[$p['person_id']] = $id;
                $sse('log', ['message' => "ADDED {$p['person_name']}"]);
            }
            $sse('progress', ['pct' => (int)(($i + 1) / max(count($persons), 1) * 15)]);
        }
        $sse('stage_done', ['stage' => 1, 'message' => count($persons) . ' hosts', 'pct' => 15]);

        // ── Stage 2: Reasons (15–25%) ─────────────────────────────
        $sse('stage', ['stage' => 2, 'label' => 'Migrating visit reasons', 'pct' => 15]);

        $reasons   = $src->query("SELECT * FROM visit_reasons ORDER BY reason_id")->fetchAll();
        $reasonMap = [];
        $chkReason = $dstPdo->prepare("SELECT reason_id FROM visit_reasons WHERE organization_id = ? AND label = ?");
        $insReason = $dstPdo->prepare("INSERT INTO visit_reasons (organization_id, label, active, sort_order) VALUES (?, ?, 1, ?)");

        foreach ($reasons as $i => $r) {
            $chkReason->execute([$orgId, $r['reason_description']]);
            $ex = $chkReason->fetch();
            if ($ex) {
                $reasonMap[$r['reason_id']] = (int)$ex['reason_id'];
                $sse('log', ['message' => "SKIP  {$r['reason_description']}"]);
            } else {
                $insReason->execute([$orgId, $r['reason_description'], $i + 1]);
                $id = (int)$dstPdo->lastInsertId();
                $reasonMap[$r['reason_id']] = $id;
                $sse('log', ['message' => "ADDED {$r['reason_description']}"]);
            }
            $sse('progress', ['pct' => 15 + (int)(($i + 1) / max(count($reasons), 1) * 10)]);
        }
        $sse('stage_done', ['stage' => 2, 'message' => count($reasons) . ' reasons', 'pct' => 25]);

        // ── Stage 3: Visitors (25–50%) ────────────────────────────
        $sse('stage', ['stage' => 3, 'label' => 'Migrating visitors', 'pct' => 25]);

        $activeVisitors = $src->query(
            "SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email
             FROM users u INNER JOIN checkin_checkout cc ON cc.user_id = u.user_id
             WHERE u.first_name IS NOT NULL AND u.first_name != ''"
        )->fetchAll();

        $visitorMap = [];
        $added = 0; $skipped = 0;
        $chkEmail = $dstPdo->prepare("SELECT visitor_id FROM visitors WHERE email = ?");
        $chkName  = $dstPdo->prepare("SELECT visitor_id FROM visitors WHERE first_name = ? AND last_name = ?");
        $insVis   = $dstPdo->prepare("INSERT INTO visitors (first_name, last_name, email, phone, created_at) VALUES (?, ?, ?, NULL, NOW())");
        $total    = count($activeVisitors);

        foreach ($activeVisitors as $i => $v) {
            $email = trim($v['email'] ?? '') ?: null;
            $found = false;
            if ($email) {
                $chkEmail->execute([$email]);
                $ex = $chkEmail->fetch();
                if ($ex) { $visitorMap[$v['user_id']] = (int)$ex['visitor_id']; $skipped++; $found = true; }
            }
            if (!$found) {
                $chkName->execute([$v['first_name'], $v['last_name']]);
                $ex = $chkName->fetch();
                if ($ex) { $visitorMap[$v['user_id']] = (int)$ex['visitor_id']; $skipped++; $found = true; }
            }
            if (!$found) {
                $insVis->execute([$v['first_name'], $v['last_name'], $email]);
                $visitorMap[$v['user_id']] = (int)$dstPdo->lastInsertId();
                $added++;
            }
            if ($i % 50 === 0 || $i === $total - 1) {
                $sse('progress', ['pct' => 25 + (int)(($i + 1) / max($total, 1) * 25)]);
            }
        }
        $sse('stage_done', ['stage' => 3, 'message' => "{$total} visitors — Added: {$added}, Skipped: {$skipped}", 'pct' => 50]);

        // ── Stage 4: Visits (50–100%) ─────────────────────────────
        $sse('stage', ['stage' => 4, 'label' => 'Migrating visits', 'pct' => 50]);

        $done = array_flip(
            $dstPdo->query("SELECT legacy_id FROM visits WHERE legacy_id IS NOT NULL")->fetchAll(\PDO::FETCH_COLUMN)
        );

        $visits = $src->query("SELECT * FROM checkin_checkout ORDER BY record_id")->fetchAll();
        $insVisit = $dstPdo->prepare(
            "INSERT INTO visits (organization_id, visitor_id, host_id, reason_id,
             check_in_time, check_out_time, status, legacy_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $inserted = 0; $skippedV = 0; $unmapped = 0;
        $totalV   = count($visits);

        foreach ($visits as $i => $cc) {
            $lid = (int)$cc['record_id'];
            if (isset($done[$lid])) { $skippedV++; }
            else {
                $visitorId = $visitorMap[$cc['user_id']] ?? null;
                if (!$visitorId) { $unmapped++; }
                else {
                    $checkOut = $cc['checkout_time'] ?: null;
                    $insVisit->execute([
                        $orgId, $visitorId,
                        $hostMap[$cc['visiting_person_id']] ?? null,
                        $reasonMap[$cc['visit_reason_id']]  ?? null,
                        $cc['checkin_time'], $checkOut,
                        $checkOut ? 'completed' : 'checked_in', $lid,
                        $cc['created_at'] ?? $cc['checkin_time'],
                        $cc['updated_at'] ?? $cc['checkin_time'],
                    ]);
                    $inserted++;
                }
            }
            if ($i % 100 === 0 || $i === $totalV - 1) {
                $sse('progress', ['pct' => 50 + (int)(($i + 1) / max($totalV, 1) * 50)]);
            }
        }
        $sse('stage_done', ['stage' => 4,
            'message' => "{$totalV} visits — Migrated: {$inserted}, Skipped: {$skippedV}, Unmapped: {$unmapped}",
            'pct' => 100]);

        // ── Auto-close stale open records from v1 ────────────────
        // Any visit still "checked_in" with a check-in time older than 24 hours
        // was never properly closed in v1. Mark them all as auto_completed so
        // the Live Logs page starts clean for every upgrader.
        $staleStmt = $dstPdo->prepare(
            "UPDATE visits
             SET check_out_time = check_in_time,
                 status         = 'auto_completed',
                 updated_at     = NOW()
             WHERE organization_id = ?
               AND check_out_time IS NULL
               AND status = 'checked_in'
               AND check_in_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        $staleStmt->execute([$orgId]);
        $staleClosed = $staleStmt->rowCount();
        if ($staleClosed > 0) {
            $sse('log', ['message' => "Auto-closed {$staleClosed} stale v1 record(s) with no checkout time."]);
        }

        $sse('done', ['success' => true, 'message' => '✓ Migration complete.']);
    }

    public function guidedUpgradeDeptsSave(array $params): void
    {
        if ($this->request->input('configure_later')) {
            $_SESSION['guided_upgrade_skipped'][] = 'departments';
            $this->redirect('/install/guided-upgrade/auth');
            return;
        }

        $raw   = trim($this->request->input('departments', ''));
        $names = array_filter(array_map('trim', explode("\n", $raw)));

        if ($names) {
            $pdo   = $this->getPdo();
            $orgId = $_SESSION['guided_upgrade_org_id'] ?? null;
            if (!$orgId) {
                $row   = $pdo->query("SELECT organization_id FROM organizations LIMIT 1")->fetch();
                $orgId = $row ? (int)$row['organization_id'] : null;
            }
            if ($orgId) {
                $chk = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE organization_id = ? AND name = ?");
                $ins = $pdo->prepare("INSERT INTO departments (organization_id, name) VALUES (?, ?)");
                foreach ($names as $dname) {
                    $chk->execute([$orgId, $dname]);
                    if (!$chk->fetchColumn()) {
                        $ins->execute([$orgId, $dname]);
                    }
                }
            }
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => count($names) . ' department(s) saved.'];
        $this->redirect('/install/guided-upgrade/auth');
    }

    public function guidedUpgradeAuthSave(array $params): void
    {
        if ($this->request->input('configure_later')) {
            $_SESSION['guided_upgrade_skipped'][] = 'auth';
            $this->redirect('/install/guided-upgrade/kiosk');
            return;
        }

        try {
            $pdo   = $this->getPdo();
            $orgId = $_SESSION['guided_upgrade_org_id'] ?? null;
            if (!$orgId) {
                $row   = $pdo->query("SELECT organization_id FROM organizations LIMIT 1")->fetch();
                $orgId = $row ? (int)$row['organization_id'] : null;
            }
            if ($orgId) {
                $row  = $pdo->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
                $row->execute([$orgId]);
                $settings = json_decode($row->fetchColumn() ?? '{}', true) ?? [];

                // Auth providers
                $enabled = $this->request->input('auth_providers', []);
                if (!is_array($enabled)) $enabled = [];
                if (!in_array('local', $enabled)) $enabled[] = 'local';
                $settings['auth_providers'] = array_values($enabled);

                // LDAP settings
                $settings['ldap_host_url']       = trim($this->request->input('ldap_host_url', ''));
                $settings['ldap_bind_user']       = trim($this->request->input('ldap_bind_user', ''));
                $settings['ldap_contexts']        = trim($this->request->input('ldap_contexts', ''));
                $settings['ldap_base_dn']         = trim($this->request->input('ldap_base_dn', ''));
                $settings['ldap_user_type']       = $this->request->input('ldap_user_type', 'ms_ad');
                $settings['ldap_user_attribute']  = trim($this->request->input('ldap_user_attribute', ''));
                $settings['ldap_user_filter']     = trim($this->request->input('ldap_user_filter', ''));
                $settings['ldap_search_sub']      = !empty($this->request->input('ldap_search_sub'));
                $settings['ldap_customize_labels']= !empty($this->request->input('ldap_customize_labels'));
                $settings['ldap_login_label']     = trim($this->request->input('ldap_login_label', ''));
                $settings['ldap_login_hint']      = trim($this->request->input('ldap_login_hint', ''));
                $newPass = $this->request->input('ldap_bind_password', '');
                if ($newPass !== '') {
                    $settings['ldap_bind_password'] = $newPass;
                }

                $pdo->prepare("UPDATE organizations SET settings = ? WHERE organization_id = ?")
                    ->execute([json_encode($settings), $orgId]);
            }
        } catch (\PDOException $e) { /* non-fatal */ }

        $this->redirect('/install/guided-upgrade/kiosk');
    }

    public function guidedUpgradeKioskSave(array $params): void
    {
        if ($this->request->input('configure_later')) {
            $_SESSION['guided_upgrade_skipped'][] = 'kiosk';
            $this->redirect('/install/guided-upgrade/notifications');
            return;
        }

        $fields = ['last_name', 'phone', 'email', 'notes'];
        $kf     = [];
        foreach ($fields as $f) {
            $kf[$f] = [
                'show'     => !empty($this->request->input('show_' . $f)),
                'required' => !empty($this->request->input('required_' . $f)),
            ];
        }

        try {
            $pdo   = $this->getPdo();
            $orgId = $_SESSION['guided_upgrade_org_id'] ?? null;
            if (!$orgId) {
                $row   = $pdo->query("SELECT organization_id FROM organizations LIMIT 1")->fetch();
                $orgId = $row ? (int)$row['organization_id'] : null;
            }
            if ($orgId) {
                $row  = $pdo->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
                $row->execute([$orgId]);
                $settings = json_decode($row->fetchColumn() ?? '{}', true) ?? [];
                $settings['kiosk_fields'] = $kf;
                $pdo->prepare("UPDATE organizations SET settings = ? WHERE organization_id = ?")
                    ->execute([json_encode($settings), $orgId]);
            }
        } catch (\PDOException $e) { /* non-fatal */ }

        $this->redirect('/install/guided-upgrade/notifications');
    }

    public function guidedUpgradeNotificationsSave(array $params): void
    {
        if ($this->request->input('configure_later')) {
            $_SESSION['guided_upgrade_skipped'][] = 'notifications';
            $this->redirect('/install/guided-upgrade/review');
            return;
        }

        $notifyHosts = !empty($this->request->input('notify_hosts'));

        try {
            $pdo   = $this->getPdo();
            $orgId = $_SESSION['guided_upgrade_org_id'] ?? null;
            if (!$orgId) {
                $row   = $pdo->query("SELECT organization_id FROM organizations LIMIT 1")->fetch();
                $orgId = $row ? (int)$row['organization_id'] : null;
            }
            if ($orgId) {
                $row  = $pdo->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
                $row->execute([$orgId]);
                $settings = json_decode($row->fetchColumn() ?? '{}', true) ?? [];
                $settings['notify_hosts'] = $notifyHosts;
                $pdo->prepare("UPDATE organizations SET settings = ? WHERE organization_id = ?")
                    ->execute([json_encode($settings), $orgId]);
            }
        } catch (\PDOException $e) { /* non-fatal */ }

        $this->redirect('/install/guided-upgrade/review');
    }

    public function guidedUpgradeFinish(array $params): void
    {
        file_put_contents($this->lockFile, date('Y-m-d H:i:s'));
        $skipped = $_SESSION['guided_upgrade_skipped'] ?? [];
        unset(
            $_SESSION['guided_upgrade_skipped'],
            $_SESSION['guided_upgrade_source_db'],
            $_SESSION['guided_upgrade_migrated'],
            $_SESSION['guided_upgrade_org_id'],
            $_SESSION['install_max_step']
        );

        // ── Install auto-checkout cron job ───────────────────────
        // Try to install via www-data's crontab. If that fails (permissions),
        // we silently skip — the admin can add it manually from Settings docs.
        $cronLine  = "*/15 * * * * php " . BASE_PATH . "/scripts/auto_checkout.php >> /var/log/checkin-auto-checkout.log 2>&1";
        $marker    = '# dadCHECKIN-TOO auto-checkout';
        $this->installCronJob($cronLine, $marker);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Upgrade complete! Welcome to dadCHECKIN-TOO.'];
        $this->redirect('/admin/setup');
    }

    private function installCronJob(string $cronLine, string $marker): void
    {
        try {
            $existing = shell_exec('crontab -l 2>/dev/null') ?? '';
            if (str_contains($existing, $marker)) return; // already installed
            $new = rtrim($existing) . "\n{$marker}\n{$cronLine}\n";
            $tmp = tempnam(sys_get_temp_dir(), 'cron');
            file_put_contents($tmp, $new);
            shell_exec("crontab {$tmp}");
            @unlink($tmp);
        } catch (\Throwable $e) {
            // Non-fatal — admin can install manually
        }
    }

    // ── Check if a database name is available ────────────────────

    public function checkDbAvailable(array $params): void
    {
        $name     = trim($this->request->input('db_name', ''));
        $existing = file_exists($this->dbLocalFile) ? require $this->dbLocalFile : [];
        $host     = $existing['host']     ?? 'localhost';
        $port     = $existing['port']     ?? 3306;
        $user     = $existing['username'] ?? '';
        $pass     = $existing['password'] ?? '';

        if (!$name) {
            $this->json(['available' => false, 'message' => 'No database name provided.']);
            return;
        }

        try {
            $pdo  = new \PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $user, $pass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5]
            );
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?");
            $stmt->execute([$name]);
            $exists = (int)$stmt->fetchColumn() > 0;
            $this->json(['available' => !$exists]);
        } catch (\PDOException $e) {
            // Can't connect at all — assume name is available and let the user proceed
            $this->json(['available' => true, 'message' => 'Could not verify — proceed with caution.']);
        }
    }

    // ── Root-assisted database creation ──────────────────────────

    public function createDatabase(array $params): void
    {
        $rootUser  = trim($this->request->input('root_user', 'root'));
        $rootPass  = $this->request->input('root_pass', '');
        $newDb     = trim($this->request->input('new_db_name', ''));
        $grantUser = trim($this->request->input('grant_user', ''));
        $grantPass = $this->request->input('grant_pass', '');

        $existing  = file_exists($this->dbLocalFile) ? require $this->dbLocalFile : [];
        $host      = $existing['host']     ?? 'localhost';
        $port      = $existing['port']     ?? 3306;
        $appUser   = $existing['username'] ?? $grantUser;
        $appPass   = $existing['password'] ?? '';

        if (!$newDb || !$grantUser) {
            $this->json(['success' => false, 'message' => 'Database name and application username are required.']);
            return;
        }

        // ── Step 1: Check if the database already exists using app credentials.
        //    Do this BEFORE touching admin credentials so we never hit socket-auth
        //    errors just because the user entered a name that already exists.
        try {
            new \PDO(
                "mysql:host={$host};port={$port};dbname={$newDb};charset=utf8mb4",
                $appUser, $appPass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5]
            );
            // Connection succeeded — database already exists and app user can reach it
            $this->json([
                'success' => false,
                'code'    => 'already_exists',
                'message' => "The database \"{$newDb}\" already exists. Scroll down, enter that name in the New Database Details section, and click Check Database — you are ready to proceed.",
            ]);
            return;
        } catch (\PDOException $e) {
            // 1049 = unknown database — does not exist yet, continue to create it
            // Any other error (wrong user/pass, host) — also continue; admin creds may differ
            if ((int)$e->getCode() !== 1049) {
                // App credentials can't connect for an unrelated reason; still try with admin creds below
            }
        }

        // ── Step 2: Connect as admin and create the database
        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $rootUser, $rootPass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5]
            );

            $pdo->exec("CREATE DATABASE `{$newDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$newDb}`.* TO '{$grantUser}'@'localhost'");
            $pdo->exec("FLUSH PRIVILEGES");

            $this->json(['success' => true, 'message' => "Database '{$newDb}' created and access granted to '{$grantUser}'."]);
        } catch (\PDOException $e) {
            $code    = $e->getCode();
            $message = $e->getMessage();

            // 1698 = auth_socket — root has no password, can only connect via OS socket
            if ($code == 1698 || str_contains($message, '1698')) {
                $this->json([
                    'success' => false,
                    'code'    => 'auth_socket',
                    'message' => "Your MySQL '{$rootUser}' account uses socket authentication — "
                               . "it has no password and cannot be accessed by the web server. "
                               . "See the instructions below to create an admin account that works here.",
                ]);
                return;
            }

            $this->json(['success' => false, 'message' => 'Failed: ' . $message]);
        }
    }

    // ── Abort upgrade ────────────────────────────────────────────

    public function abortConfirm(array $params): void
    {
        $backupFile = $this->dbLocalFile . '.bak';
        $newDb      = $_SESSION['guided_upgrade_new_db'] ?? null;

        $this->view->render('install/abort-confirm', [
            'title'      => 'Abort Upgrade',
            'hasBackup'  => file_exists($backupFile),
            'newDb'      => $newDb,
            'flash'      => $this->flash(),
        ], 'install');
    }

    public function abortUpgrade(array $params): void
    {
        $backupFile = $this->dbLocalFile . '.bak';
        $dropNewDb  = !empty($this->request->input('drop_new_db'));
        $newDb      = $_SESSION['guided_upgrade_new_db'] ?? null;

        // Restore original config
        if (file_exists($backupFile)) {
            copy($backupFile, $this->dbLocalFile);
            unlink($backupFile);
        }

        // Remove lock file if somehow written
        if (file_exists($this->lockFile)) {
            unlink($this->lockFile);
        }

        // Optionally drop the new (partial) database
        if ($dropNewDb && $newDb) {
            try {
                $cfg = require $backupFile ?: $this->dbLocalFile;
                $pdo = new \PDO(
                    "mysql:host={$cfg['host']};port={$cfg['port']};charset=utf8mb4",
                    $cfg['username'], $cfg['password'],
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
                $pdo->exec("DROP DATABASE IF EXISTS `{$newDb}`");
            } catch (\PDOException $e) { /* non-fatal */ }
        }

        // Clear all upgrade session state
        unset(
            $_SESSION['guided_upgrade_source_db'],
            $_SESSION['guided_upgrade_new_db'],
            $_SESSION['guided_upgrade_skipped'],
            $_SESSION['guided_upgrade_migrated'],
            $_SESSION['guided_upgrade_org_id'],
            $_SESSION['pending_migration_source'],
            $_SESSION['install_max_step']
        );

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Upgrade cancelled. Your original configuration has been restored.'];
        $this->redirect('/install');
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function getPdo(): \PDO
    {
        $local = file_exists($this->dbLocalFile)
            ? require $this->dbLocalFile
            : require BASE_PATH . '/config/database.php';
        $dsn = "mysql:host={$local['host']};port={$local['port']};dbname={$local['database']};charset=utf8mb4";
        return new \PDO($dsn, $local['username'], $local['password'], [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    }

    private function makeSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-') ?: 'org';
    }

    private function updateAppConfig(string $key, string $value): void
    {
        $file    = BASE_PATH . '/config/app.php';
        $content = file_get_contents($file);
        $content = preg_replace(
            "/('" . preg_quote($key, '/') . "'\s*=>\s*)'[^']*'/",
            "$1'" . addslashes($value) . "'",
            $content
        );
        file_put_contents($file, $content);
    }
}
