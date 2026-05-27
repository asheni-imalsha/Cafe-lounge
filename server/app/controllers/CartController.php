<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../../src/auth.php';

class CartController {
    public static function view(){
        $cart = new Cart();
        // ensure session
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $user_id = getCurrentUserId();
        if (isset($_GET['remove'])){
            // if logged in, remove from DB; otherwise remove from session
            if ($user_id) {
                $cart->remove((int)$_GET['remove']);
            } else {
                $rid = (int)$_GET['remove'];
                if (isset($_SESSION['cart'][$rid])) unset($_SESSION['cart'][$rid]);
            }
            header('Location: cart.php'); exit;
        }

        $items = [];
        if ($user_id) {
            $items = $cart->itemsForUser($user_id);
            // normalized keys: cart_id, item_id, name, price, image, description, quantity
            foreach($items as &$r){ $r['cart_id'] = $r['cart_id']; }
        } else {
            $sessionCart = $_SESSION['cart'] ?? [];
            if (!empty($sessionCart)){
                require_once __DIR__ . '/../models/CafeItem.php';
                $ci = new CafeItem();
                foreach($sessionCart as $itemId => $qty){
                    $row = $ci->find($itemId);
                    if ($row) $items[] = ['cart_id'=>null,'item_id'=>$itemId,'name'=>$row['name'],'price'=>$row['price'],'image'=>$row['image'] ?? null,'description'=>$row['description'] ?? null,'quantity'=>$qty];
                }
            }
        }
        $total = 0; foreach ($items as $it) $total += $it['price'] * $it['quantity'];
        require __DIR__ . '/../../views/cart/view.php';
    }
}
