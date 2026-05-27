<?php
require_once __DIR__ . '/Database.php';
class Cart {
    protected $pdo;
    public function __construct(){ $this->pdo = Database::get(); }
    public function add($user_id, $session_id, $item_id, $quantity){
        $stmt = $this->pdo->prepare('INSERT INTO cart (user_id, session_id, item_id, quantity) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$user_id, $session_id, $item_id, $quantity]);
    }
    public function remove($id){
        $stmt = $this->pdo->prepare('DELETE FROM cart WHERE id = ?');
        return $stmt->execute([$id]);
    }
    public function itemsForUser($user_id){
        $stmt = $this->pdo->prepare('SELECT c.id, i.name, i.price, c.quantity FROM cart c JOIN cafe_items i ON c.item_id = i.id WHERE c.user_id = ? ORDER BY c.added_at');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    public function itemsForSession($session_id){
        $stmt = $this->pdo->prepare('SELECT c.id, i.name, i.price, c.quantity FROM cart c JOIN cafe_items i ON c.item_id = i.id WHERE c.session_id = ? ORDER BY c.added_at');
        $stmt->execute([$session_id]);
        return $stmt->fetchAll();
    }
}
