<?php
require_once __DIR__ . '/../app/models/Booking.php';
require_once __DIR__ . '/../src/auth.php';
header('Content-Type: application/json');
$filter = $_GET['filter'] ?? 'all';
$model = new Booking();
$result = [];
if ($filter === 'my'){
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (!isset($_SESSION['user_id'])) { echo json_encode(['error'=>'not_logged_in']); exit; }
    $rows = $model->allForUser($_SESSION['user_id']);
    foreach($rows as $r){ $r['is_owner'] = true; $result[] = $r; }
} else {
    $rows = $model->all();
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $me = $_SESSION['user_id'] ?? null;
    foreach($rows as $r){ $r['is_owner'] = ($me && isset($r['user_id']) && $r['user_id']==$me); $result[] = $r; }
}
echo json_encode($result);
