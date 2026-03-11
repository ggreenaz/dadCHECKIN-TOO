<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require BASE_PATH . '/config/database.php';
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['driver'],
                $cfg['host'],
                $cfg['port'],
                $cfg['database'],
                $cfg['charset']
            );
            try {
                self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
            } catch (PDOException $e) {
                // Never expose credentials in output
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    // Prevent cloning / serialisation
    private function __construct() {}
    private function __clone() {}
}
