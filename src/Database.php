<?php
namespace App;

use PDO;
use PDOException;

class Database
{
    public static function connect(): PDO
    {
        // Load .env for local development (safe on Railway too)
        \App\Config::loadEnv();

        /**
         * Railway provides MYSQL* variables
         * Local dev uses DB_* variables
         * This fallback chain supports both
         */
        $host = $_ENV['MYSQLHOST']
            ?? \App\Config::get('DB_HOST')
            ?? '127.0.0.1';

        $port = $_ENV['MYSQLPORT']
            ?? \App\Config::get('DB_PORT')
            ?? '3306';

        $db = $_ENV['MYSQLDATABASE']
            ?? \App\Config::get('DB_NAME')
            ?? 'image_uploader';

        $user = $_ENV['MYSQLUSER']
            ?? \App\Config::get('DB_USER')
            ?? 'root';

        $pass = $_ENV['MYSQLPASSWORD']
            ?? \App\Config::get('DB_PASS')
            ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        try {
            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException(
                'Database connection failed: ' . $e->getMessage()
            );
        }
    }
}
