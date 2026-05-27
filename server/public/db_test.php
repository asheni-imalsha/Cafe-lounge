<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load config
$config = require __DIR__ . '/../config/config.php';

$portPart = isset($config['db_port']) && $config['db_port'] ? ";port={$config['db_port']}" : '';
$dsn = "mysql:host={$config['db_host']}{$portPart};dbname={$config['db_name']};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    echo "OK: Connected to database '{$config['db_name']}' on {$config['db_host']}" . (isset($config['db_port']) ? ":{$config['db_port']}" : '') . ".";
} catch (PDOException $e) {
    echo 'ERROR: Connection failed - ' . $e->getMessage();
}

// Quick query test
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "\nTables (" . count($tables) . "): ";
        echo implode(', ', $tables);
    } catch (Exception $e) {
        echo "\nERROR: Could not list tables - " . $e->getMessage();
    }
}
