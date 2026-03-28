<?php

/**
 * Database migration runner.
 * Usage: php migrations/migrate.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/Env.php';
Core\Env::load(BASE_PATH . '/.env');

$host = Core\Env::get('DB_HOST', 'localhost');
$port = Core\Env::get('DB_PORT', '3306');
$user = Core\Env::get('DB_USER', 'root');
$pass = Core\Env::get('DB_PASSWORD', '');
$dbName = Core\Env::get('DB_NAME', 'freelance');

// Create database if it doesn't exist
$pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Database '{$dbName}' ready.\n";

// Connect to the database
$pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Run migration files in order
$files = glob(__DIR__ . '/0*.sql');
sort($files);

foreach ($files as $file) {
    $name = basename($file);
    echo "Running {$name}... ";
    $sql = file_get_contents($file);
    // Split by semicolons for multi-statement files
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if ($stmt !== '') {
            $pdo->exec($stmt);
        }
    }
    echo "OK\n";
}

echo "\nAll migrations complete.\n";
