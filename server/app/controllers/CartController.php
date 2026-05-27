<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../../src/auth.php';

class CartController {
    public static function view(){
        $cart = new Cart();
        session_id() ?: session_start();
        $user_id = getCurrentUserId();
        if (isset($_GET['remove'])){
            $cart->remove((int)$_GET['remove']);
            header('Location: /cart.php'); exit;
        }
        if ($user_id) $items = $cart->itemsForUser($user_id);
        else $items = $cart->itemsForSession(session_id());
        $total = 0; foreach ($items as $it) $total += $it['price'] * $it['quantity'];
        require __DIR__ . '/../../views/cart/view.php';
    }
}
