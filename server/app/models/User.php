<?php
require_once __DIR__ . '/Database.php';
class User {
    protected $pdo;
    public function __construct() { $this->pdo = Database::get(); }
    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT id, username, name, email, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function findByUsernameOrEmail($identifier) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch();
    }
    public function create($username, $name, $email, $password_hash) {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, name, email, password_hash) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$username, $name, $email, $password_hash]);
    }
}
