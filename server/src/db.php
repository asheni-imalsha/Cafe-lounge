<?php
$config = require __DIR__ . '/../config/config.php';
try {
    $portPart = isset($config['db_port']) && $config['db_port'] ? ";port={$config['db_port']}" : '';
    $dsn = "mysql:host={$config['db_host']}{$portPart};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}
