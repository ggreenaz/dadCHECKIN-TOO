<?php
define('BASE_PATH', dirname(__DIR__));

// Autoloader (PSR-4 style for App\)
spl_autoload_register(function (string $class): void {
    // App\Core\Database → app/Core/Database.php
    if (str_starts_with($class, 'App\\')) {
        $relative = str_replace(['App\\', '\\'], ['', '/'], $class);
        $file     = BASE_PATH . '/app/' . $relative . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// ── Bootstrap ────────────────────────────────────────────────────
$appCfg = require BASE_PATH . '/config/app.php';

date_default_timezone_set($appCfg['timezone']);

if ($appCfg['debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Session
$sessionCfg = $appCfg['session'];
session_name($sessionCfg['name']);
ini_set('session.gc_maxlifetime', $sessionCfg['lifetime']);
session_start();

// ── Route ────────────────────────────────────────────────────────
use App\Core\Router;
use App\Core\Request;

$router  = new Router();
$request = new Request();

require BASE_PATH . '/routes/web.php';

// Redirect to installer if not yet installed
$lockFile = BASE_PATH . '/config/installed.lock';
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!file_exists($lockFile) && !str_starts_with($uri, '/install') && !str_starts_with($uri, '/css')) {
    header('Location: /install');
    exit;
}

$router->dispatch($request);
