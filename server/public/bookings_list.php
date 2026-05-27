<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../app/models/Booking.php';
header('Content-Type: application/json');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$user = getCurrentUserId();
if (!$user){ http_response_code(401); echo json_encode(['error'=>'unauthenticated']); exit; }
$b = new Booking();
$data = $b->allForUser($user);
echo json_encode($data);
