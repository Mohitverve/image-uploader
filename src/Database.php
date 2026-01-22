<?php
namespace App;

use PDO;
use PDOException;

class Database
{
    public static function connect(): PDO
    {
        \App\Config::loadEnv();

        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s",
            \App\Config::get('DB_HOST'),
            \App\Config::get('DB_PORT', 5432),
            \App\Config::get('DB_NAME')
        );

        try {
            return new PDO(
                $dsn,
                \App\Config::get('DB_USER'),
                \App\Config::get('DB_PASS'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            throw new \RuntimeException(
                'Database connection failed: ' . $e->getMessage()
            );
        }
    }
}
