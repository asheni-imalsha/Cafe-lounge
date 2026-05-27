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
    public function updateUsername($id, $newUsername) {
        $stmt = $this->pdo->prepare('UPDATE users SET username = ? WHERE id = ?');
        return $stmt->execute([$newUsername, $id]);
    }
    public function updatePassword($id, $newPasswordHash) {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$newPasswordHash, $id]);
    }
    public function updateProfile($id, $name, $email) {
        $stmt = $this->pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        return $stmt->execute([$name, $email, $id]);
    }
    public function verifyPassword($id, $password) {
        $stmt = $this->pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return false;
        return password_verify($password, $row['password_hash']);
    }
    public function usernameExists($username, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM users WHERE username = ? AND id != ?');
            $stmt->execute([$username, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM users WHERE username = ?');
            $stmt->execute([$username]);
        }
        $row = $stmt->fetch();
        return $row['count'] > 0;
    }
    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM users WHERE email = ?');
            $stmt->execute([$email]);
        }
        $row = $stmt->fetch();
        return $row['count'] > 0;
    }
}
