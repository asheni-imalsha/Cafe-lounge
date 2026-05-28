<?php
require_once __DIR__ . '/../app/models/Cart.php';
require_once __DIR__ . '/../app/models/CafeItem.php';
require_once __DIR__ . '/../src/auth.php';
header('Content-Type: application/json');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$item_id = $_POST['item_id'] ?? null;
$qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
// CSRF validation
if (!validateCsrfToken($_POST['csrf_token'] ?? '')){ echo json_encode(['error'=>'invalid_csrf']); exit; }
if (!$item_id) { echo json_encode(['error'=>'missing item']); exit; }
$ci = new CafeItem();
$it = $ci->find($item_id);
if (!$it) { echo json_encode(['error'=>'item not found']); exit; }
$cart = new Cart();
$user = getCurrentUserId();
if ($user){
    // store in DB
    $sid = session_id();
    $added = $cart->add($user, $sid, $item_id, $qty);
    if ($added) echo json_encode(['success'=>true]); else echo json_encode(['error'=>'failed']);
} else {
    // store in session array
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $cur = $_SESSION['cart'][$item_id] ?? 0;
    $_SESSION['cart'][$item_id] = $cur + $qty;
    echo json_encode(['success'=>true]);
}
