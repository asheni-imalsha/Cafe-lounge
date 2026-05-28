<?php
require_once __DIR__ . '/Database.php';
class Cart {
    protected $pdo;
    public function __construct(){ $this->pdo = Database::get(); }
    public function add($user_id, $session_id, $item_id, $quantity){
        // If a cart row already exists for this user/session and item, increase quantity instead of inserting a duplicate.
        if ($user_id) {
            $stmt = $this->pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND item_id = ? LIMIT 1');
            $stmt->execute([$user_id, $item_id]);
            $row = $stmt->fetch();
            if ($row) {
                $newQty = (int)$row['quantity'] + (int)$quantity;
                $u = $this->pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
                return $u->execute([$newQty, $row['id']]);
            }
            $stmt = $this->pdo->prepare('INSERT INTO cart (user_id, session_id, item_id, quantity) VALUES (?, ?, ?, ?)');
            return $stmt->execute([$user_id, $session_id, $item_id, $quantity]);
        } else {
            // guest session-based cart: try to find by session_id and item_id
            if ($session_id) {
                $stmt = $this->pdo->prepare('SELECT id, quantity FROM cart WHERE session_id = ? AND item_id = ? LIMIT 1');
                $stmt->execute([$session_id, $item_id]);
                $row = $stmt->fetch();
                if ($row) {
                    $newQty = (int)$row['quantity'] + (int)$quantity;
                    $u = $this->pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
                    return $u->execute([$newQty, $row['id']]);
                }
            }
            $stmt = $this->pdo->prepare('INSERT INTO cart (user_id, session_id, item_id, quantity) VALUES (?, ?, ?, ?)');
            return $stmt->execute([$user_id, $session_id, $item_id, $quantity]);
        }
    }
    public function remove($id){
        $stmt = $this->pdo->prepare('DELETE FROM cart WHERE id = ?');
        return $stmt->execute([$id]);
    }
    public function itemsForUser($user_id){
        $stmt = $this->pdo->prepare('SELECT c.id AS cart_id, i.id AS item_id, i.name, i.price, i.image, i.description, c.quantity FROM cart c JOIN cafe_items i ON c.item_id = i.id WHERE c.user_id = ? ORDER BY c.added_at');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    public function itemsForSession($session_id){
        $stmt = $this->pdo->prepare('SELECT c.id AS cart_id, i.id AS item_id, i.name, i.price, i.image, i.description, c.quantity FROM cart c JOIN cafe_items i ON c.item_id = i.id WHERE c.session_id = ? ORDER BY c.added_at');
        $stmt->execute([$session_id]);
        return $stmt->fetchAll();
    }
    public function update($cartId, $quantity){
        $stmt = $this->pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
        return $stmt->execute([(int)$quantity, (int)$cartId]);
    }
    public function totalForUser($user_id){
        $items = $this->itemsForUser($user_id);
        $t = 0; foreach($items as $it) $t += $it['price'] * $it['quantity'];
        return $t;
    }
    public function totalForSession($session_id){
        $items = $this->itemsForSession($session_id);
        $t = 0; foreach($items as $it) $t += $it['price'] * $it['quantity'];
        return $t;
    }
}
