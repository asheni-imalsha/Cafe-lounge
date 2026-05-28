<?php
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../../src/auth.php';

class BookingController {
    public static function list(){
        $model = new Booking();
        $filter = $_GET['filter'] ?? 'all';
        if ($filter === 'my'){
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            if (!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
            $bookings = $model->allForUser($_SESSION['user_id']);
        } else {
            $bookings = $model->all();
        }
        $filter = ($filter === 'my') ? 'my' : 'all';
        require __DIR__ . '/../../views/bookings/list.php';
    }
    public static function create(){
        requireLogin();
        $errors = [];
        $model = new Booking();
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            if (!validateCsrfToken($_POST['csrf_token'] ?? '')){ $errors[] = 'Invalid CSRF token.'; }
            $space_name = trim($_POST['space_name'] ?? '');
            $space_type = $_POST['space_type'] ?? 'desk';
            $booking_date = $_POST['booking_date'] ?? '';
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            if ($space_name === '' || $booking_date === '') $errors[] = 'Space name and booking date are required.';
                // validate date/time not in past
                if ($booking_date && $start_time){
                    try{
                        $dtStart = new DateTime($booking_date . ' ' . $start_time);
                        $now = new DateTime();
                        if ($dtStart < $now) $errors[] = 'Start time cannot be in the past.';
                    } catch(Exception $e){ $errors[] = 'Invalid date/time.'; }
                }
            if ($start_time === '' || $end_time === '') $errors[] = 'Start and end times are required.';
            if (empty($errors)){
                    // check availability
                    if (!$model->isAvailable($space_name, $booking_date, $start_time, $end_time)){
                        $errors[] = 'Selected space is already booked for that time.';
                    } else {
                        $model->create(getCurrentUserId(), $space_name, $space_type, $booking_date, $start_time, $end_time);
                        header('Location: bookings.php'); exit;
                    }
            }
        }
        require __DIR__ . '/../../views/bookings/create.php';
    }
    public static function edit(){
        requireLogin();
        $model = new Booking();
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: bookings.php'); exit; }
        $booking = $model->find($id);
        if (!$booking || $booking['user_id'] != getCurrentUserId()){ echo 'Not allowed'; exit; }
        $errors = [];
        // prevent editing past bookings: if booking end datetime is in the past, redirect with flash
        try{
            $end = $booking['end_time'] ?? $booking['start_time'] ?? null;
            $bookingDate = $booking['booking_date'] ?? null;
            if ($bookingDate && $end){
                $dtEnd = new DateTime($bookingDate . ' ' . $end);
                $now = new DateTime();
                if ($dtEnd < $now){
                    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                    $_SESSION['flash'] = ['type'=>'error','msg'=>'Cannot edit a booking that has already passed.'];
                    header('Location: bookings.php'); exit;
                }
            }
        } catch(Exception $e){ /* ignore */ }
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            if (!validateCsrfToken($_POST['csrf_token'] ?? '')){ $errors[] = 'Invalid CSRF token.'; }
            $space_name = trim($_POST['space_name'] ?? '');
            if ($space_name === 'Other' && !empty($_POST['space_name_other'])) $space_name = trim($_POST['space_name_other']);
            $space_type = $_POST['space_type'] ?? ($_POST['space_type_hidden'] ?? 'desk');
            $booking_date = $_POST['booking_date'] ?? '';
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            if ($space_name === '' || $booking_date === '') $errors[] = 'All fields required.';
            if ($start_time === '' || $end_time === '') $errors[] = 'Start and end times are required.';
            // validate date/time not in past
            if ($booking_date && $start_time){
                try{
                    $dtStart = new DateTime($booking_date . ' ' . $start_time);
                    $now = new DateTime();
                    if ($dtStart < $now) $errors[] = 'Start time cannot be in the past.';
                } catch(Exception $e){ $errors[] = 'Invalid date/time.'; }
            }
            if (empty($errors)){
                // check availability excluding this booking id
                if (!$model->isAvailable($space_name, $booking_date, $start_time, $end_time, $id)){
                    $errors[] = 'Selected space is already booked for that time.';
                } else {
                    $model->update($id, $space_name, $space_type, $booking_date, $start_time, $end_time);
                    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                    $_SESSION['flash'] = ['type'=>'success','msg'=>'Booking updated successfully.'];
                    header('Location: bookings.php'); exit;
                }
            }
        }
        require __DIR__ . '/../../views/bookings/edit.php';
    }
    public static function delete(){
        requireLogin();
        $model = new Booking();
        // prefer POST with CSRF, but accept GET as fallback
        $id = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            if (!validateCsrfToken($_POST['csrf_token'] ?? '')){
                if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid CSRF token.'];
                header('Location: bookings.php'); exit;
            }
            $id = $_POST['id'] ?? null;
        } else {
            $id = $_GET['id'] ?? null;
        }
        if ($id){
            $b = $model->find($id);
            if ($b && $b['user_id'] == getCurrentUserId()) $model->delete($id);
        }
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Booking deleted successfully.'];
        header('Location: bookings.php'); exit;
    }
}
