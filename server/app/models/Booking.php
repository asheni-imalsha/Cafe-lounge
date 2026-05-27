<?php
require_once __DIR__ . '/Database.php';
class Booking {
    protected $pdo;
    public function __construct(){ $this->pdo = Database::get(); }
    public function all(){
        try {
            return $this->pdo->query('SELECT b.*, u.username, s.id AS space_type_id, s.type_name AS space_type_name, s.image AS space_image FROM bookings b JOIN users u ON b.user_id = u.id LEFT JOIN space_types s ON b.space_type = s.type_name ORDER BY b.booking_date')->fetchAll();
        } catch (Exception $e) {
            // If space_types or other related table is missing, return bookings without join or empty list
            try {
                return $this->pdo->query('SELECT b.*, u.username FROM bookings b JOIN users u ON b.user_id = u.id ORDER BY b.booking_date')->fetchAll();
            } catch (Exception $e2) {
                return [];
            }
        }
    }
    public function find($id){
        try {
            $stmt = $this->pdo->prepare('SELECT b.*, u.username, s.id AS space_type_id, s.type_name AS space_type_name, s.image AS space_image FROM bookings b JOIN users u ON b.user_id = u.id LEFT JOIN space_types s ON b.space_type = s.type_name WHERE b.id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            // fallback when space_types table does not exist
            try{
                $stmt = $this->pdo->prepare('SELECT b.*, u.username FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.id = ?');
                $stmt->execute([$id]);
                return $stmt->fetch();
            } catch (Exception $e2) {
                return null;
            }
        }
    }

    public function allForUser($userId){
        try{
            $stmt = $this->pdo->prepare('SELECT b.*, u.username, s.id AS space_type_id, s.type_name AS space_type_name, s.image AS space_image FROM bookings b JOIN users u ON b.user_id = u.id LEFT JOIN space_types s ON b.space_type = s.type_name WHERE b.user_id = ? ORDER BY b.booking_date');
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch(Exception $e){
            try{
                $stmt = $this->pdo->prepare('SELECT b.*, u.username FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.user_id = ? ORDER BY b.booking_date');
                $stmt->execute([$userId]);
                return $stmt->fetchAll();
            } catch(Exception $e2){
                return [];
            }
        }
    }
    public function isAvailable(string $space_name, string $booking_date, string $start_time, string $end_time, $excludeBookingId = null): bool {
        // Check for overlapping bookings for the same space and date (compare date part)
        if ($excludeBookingId) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as cnt FROM bookings WHERE space_name = ? AND DATE(booking_date) = ? AND NOT (end_time <= ? OR start_time >= ?) AND id != ?');
            $stmt->execute([$space_name, $booking_date, $start_time, $end_time, $excludeBookingId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as cnt FROM bookings WHERE space_name = ? AND DATE(booking_date) = ? AND NOT (end_time <= ? OR start_time >= ?)');
            $stmt->execute([$space_name, $booking_date, $start_time, $end_time]);
        }
        $row = $stmt->fetch();
        return ((int)$row['cnt'] === 0);
    }
    public function create($user_id, $space_name, $space_type, $booking_date, $start_time = null, $end_time = null){
        $stmt = $this->pdo->prepare('INSERT INTO bookings (user_id, space_name, space_type, booking_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)');
        return $stmt->execute([$user_id, $space_name, $space_type, $booking_date, $start_time, $end_time]);
    }
    public function update($id, $space_name, $space_type, $booking_date, $start_time = null, $end_time = null){
        $stmt = $this->pdo->prepare('UPDATE bookings SET space_name=?, space_type=?, booking_date=?, start_time=?, end_time=? WHERE id=?');
        return $stmt->execute([$space_name, $space_type, $booking_date, $start_time, $end_time, $id]);
    }
    public function delete($id){
        $stmt = $this->pdo->prepare('DELETE FROM bookings WHERE id=?');
        return $stmt->execute([$id]);
    }
}
