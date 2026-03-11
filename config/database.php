<?php
// config/database.php
// During install, database.local.php is written with the actual credentials.

$defaults = [
    'driver'   => 'mysql',
    'host'     => getenv('DB_HOST')     ?: 'localhost',
    'port'     => (int)(getenv('DB_PORT') ?: 3306),
    'database' => getenv('DB_DATABASE') ?: 'checkin',
    'username' => getenv('DB_USERNAME') ?: 'dadadmin',
    'password' => getenv('DB_PASSWORD') ?: 'K!imeck79',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];

$localFile = __DIR__ . '/database.local.php';
if (file_exists($localFile)) {
    $local    = require $localFile;
    $defaults = array_merge($defaults, $local);
}

return $defaults;
