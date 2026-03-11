#!/usr/bin/env php
<?php
/**
 * run_migrations.php
 *
 * Runs any pending database migrations in order.
 * Each migration file in database/migrations/ is run exactly once.
 *
 * Usage:
 *   php scripts/run_migrations.php [--dry-run]
 */

define('BASE_PATH', dirname(__DIR__));

$dryRun = in_array('--dry-run', $argv ?? []);
$dbCfg  = require BASE_PATH . '/config/database.local.php';

try {
    $pdo = new PDO(
        "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$dbCfg['database']};charset=utf8mb4",
        $dbCfg['username'],
        $dbCfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

// Ensure migrations tracking table exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS schema_migrations (
        migration   VARCHAR(255) PRIMARY KEY,
        applied_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
");

// Get already-applied migrations
$applied = $pdo->query("SELECT migration FROM schema_migrations")
               ->fetchAll(PDO::FETCH_COLUMN);
$applied = array_flip($applied);

// Scan migration files
$dir   = BASE_PATH . '/database/migrations';
$files = glob($dir . '/*.sql');
sort($files);

if (empty($files)) {
    echo "No migration files found in {$dir}\n";
    exit(0);
}

$ran = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (isset($applied[$name])) {
        echo "  SKIP  {$name} (already applied)\n";
        continue;
    }

    $sql = file_get_contents($file);
    echo $dryRun ? "  WOULD RUN  {$name}\n" : "  RUNNING    {$name} ... ";

    if (!$dryRun) {
        try {
            $pdo->exec($sql);
            $pdo->prepare("INSERT INTO schema_migrations (migration) VALUES (?)")
                ->execute([$name]);
            echo "OK\n";
            $ran++;
        } catch (PDOException $e) {
            echo "FAILED\n";
            die("  Error: " . $e->getMessage() . "\n");
        }
    } else {
        $ran++;
    }
}

echo $dryRun
    ? "\nDry run: {$ran} migration(s) would be applied.\n"
    : "\nDone: {$ran} migration(s) applied.\n";
