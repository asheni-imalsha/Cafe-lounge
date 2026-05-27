<?php
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../../src/auth.php';

class BookingController {
    public static function list(){
        $model = new Booking();
        $bookings = $model->all();
        require __DIR__ . '/../../views/bookings/list.php';
    }
    public static function create(){
        requireLogin();
        $errors = [];
        $model = new Booking();
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $space_name = trim($_POST['space_name'] ?? '');
            $space_type = $_POST['space_type'] ?? 'desk';
            $booking_date = $_POST['booking_date'] ?? '';
            if ($space_name === '' || $booking_date === '') $errors[] = 'Space name and booking date are required.';
            if (empty($errors)){
                $model->create(getCurrentUserId(), $space_name, $space_type, $booking_date);
                header('Location: /bookings.php'); exit;
            }
        }
        require __DIR__ . '/../../views/bookings/create.php';
    }
    public static function edit(){
        requireLogin();
        $model = new Booking();
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /bookings.php'); exit; }
        $booking = $model->find($id);
        if (!$booking || $booking['user_id'] != getCurrentUserId()){ echo 'Not allowed'; exit; }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $space_name = trim($_POST['space_name'] ?? '');
            $space_type = $_POST['space_type'] ?? 'desk';
            $booking_date = $_POST['booking_date'] ?? '';
            if ($space_name === '' || $booking_date === '') $errors[] = 'All fields required.';
            if (empty($errors)){
                $model->update($id, $space_name, $space_type, $booking_date);
                header('Location: /bookings.php'); exit;
            }
        }
        require __DIR__ . '/../../views/bookings/edit.php';
    }
    public static function delete(){
        requireLogin();
        $model = new Booking();
        $id = $_GET['id'] ?? null;
        if ($id){
            $b = $model->find($id);
            if ($b && $b['user_id'] == getCurrentUserId()) $model->delete($id);
        }
        header('Location: /bookings.php'); exit;
    }
}
