<?php
// Debug script: prints DSN and attempts PDO connection using server config.
require_once __DIR__ . '/../app/models/Database.php';

// Show config and DSN
$config = require __DIR__ . '/../config/config.php';
$portPart = isset($config['db_port']) && $config['db_port'] ? ";port={$config['db_port']}" : '';
$dsn = "mysql:host={$config['db_host']}{$portPart};dbname={$config['db_name']};charset=utf8mb4";

echo "DSN: " . htmlspecialchars($dsn) . "\n";
echo "User: " . htmlspecialchars($config['db_user']) . "\n";

try {
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connection successful.\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
    echo "Tables:\n";
    foreach ($tables as $t) echo " - " . $t[0] . "\n";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
