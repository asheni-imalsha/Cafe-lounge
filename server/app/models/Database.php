<?php
class Database {
    private static $pdo = null;
    public static function get() {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../../config/config.php';
            $portPart = isset($config['db_port']) && $config['db_port'] ? ";port={$config['db_port']}" : '';
            $dsn = "mysql:host={$config['db_host']}{$portPart};dbname={$config['db_name']};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$pdo;
    }
}
