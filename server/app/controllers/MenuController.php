<?php
require_once __DIR__ . '/../models/CafeItem.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../../src/auth.php';

class MenuController {
    public static function list(){
        $model = new CafeItem();
        $items = $model->all();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_item_id'])){
            $menu_item_id = (int)$_POST['menu_item_id'];
            $quantity = max(1, (int)($_POST['quantity'] ?? 1));
            $cart = new Cart();
            $user_id = getCurrentUserId();
            session_id() ?: session_start();
            $cart->add($user_id, session_id(), $menu_item_id, $quantity);
            header('Location: /cart.php'); exit;
        }
        require __DIR__ . '/../../views/menu/list.php';
    }
}
