<?php
require_once __DIR__ . '/../app/models/Booking.php';
header('Content-Type: application/json');
$id = $_GET['id'] ?? null;
if (!$id) { echo json_encode(['error'=>'missing id']); exit; }
$b = (new Booking())->find($id);
if (!$b) { echo json_encode(['error'=>'not found']); exit; }
// normalize response
echo json_encode($b);
