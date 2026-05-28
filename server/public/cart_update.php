<?php
require_once __DIR__ . '/../app/models/Cart.php';
require_once __DIR__ . '/../app/models/CafeItem.php';
require_once __DIR__ . '/../src/auth.php';
header('Content-Type: application/json');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$id = isset($_POST['id']) ? $_POST['id'] : null;
$qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : null;
// CSRF validation
if (!validateCsrfToken($_POST['csrf_token'] ?? '')){ echo json_encode(['error'=>'invalid_csrf']); exit; }
if ($id === null || $qty === null) { echo json_encode(['error'=>'missing parameters']); exit; }
$cart = new Cart();
$user = getCurrentUserId();
try{
    if ($user){
        // for logged in users, id is cart row id
        $ok = $cart->update((int)$id, max(0,$qty));
        if ($qty <= 0) { $cart->remove((int)$id); }
        $total = $cart->totalForUser($user);
    } else {
        // for guests, id is item_id stored in session
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if ($qty <= 0){ unset($_SESSION['cart'][(int)$id]); }
        else { $_SESSION['cart'][(int)$id] = $qty; }
        // compute total from session cart
        $total = 0;
        $ci = new CafeItem();
        foreach($_SESSION['cart'] as $iid => $q){
            $row = $ci->find($iid);
            if ($row) $total += $row['price'] * $q;
        }
    }
    echo json_encode(['success'=>true,'total'=>$total]);
} catch(Exception $e){ echo json_encode(['error'=>$e->getMessage()]); }

