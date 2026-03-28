<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host = Env::get('DB_HOST', 'localhost');
            $port = Env::get('DB_PORT', '3306');
            $name = Env::get('DB_NAME', 'freelance');
            $user = Env::get('DB_USER', 'root');
            $pass = Env::get('DB_PASSWORD', '');

            try {
                self::$instance = new PDO(
                    "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                ErrorHandler::log('Database connection failed: ' . $e->getMessage());
                throw $e;
            }
        }
        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
