<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/models/Booking.php';
require_once __DIR__ . '/../app/models/Database.php';
// expected params: booking_date (YYYY-MM-DD), start_time (HH:MM), end_time (HH:MM)
$date = $_GET['booking_date'] ?? $_POST['booking_date'] ?? null;
$start = $_GET['start_time'] ?? $_POST['start_time'] ?? null;
$end = $_GET['end_time'] ?? $_POST['end_time'] ?? null;
if (!$date || !$start || !$end){ echo json_encode(['error'=>'missing']); exit; }

try{
    $b = new Booking();
    // Use Booking model's PDO
    $pdo = (new ReflectionClass($b))->getProperty('pdo');
    $pdo->setAccessible(true);
    $pdo = $pdo->getValue($b);
    $stmt = $pdo->prepare('SELECT space_name FROM bookings WHERE DATE(booking_date) = ? AND NOT (end_time <= ? OR start_time >= ?)');
    $stmt->execute([$date, $start, $end]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['booked' => $rows]);
} catch(Exception $e){ echo json_encode(['error' => $e->getMessage()]); }
