<?php
require_once __DIR__ . '/Database.php';
class Booking {
    protected $pdo;
    public function __construct(){ $this->pdo = Database::get(); }
    public function all(){
        return $this->pdo->query('SELECT b.*, u.username FROM bookings b JOIN users u ON b.user_id = u.id ORDER BY b.booking_date')->fetchAll();
    }
    public function find($id){
        $stmt = $this->pdo->prepare('SELECT * FROM bookings WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function create($user_id, $space_name, $space_type, $booking_date){
        $stmt = $this->pdo->prepare('INSERT INTO bookings (user_id, space_name, space_type, booking_date) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$user_id, $space_name, $space_type, $booking_date]);
    }
    public function update($id, $space_name, $space_type, $booking_date){
        $stmt = $this->pdo->prepare('UPDATE bookings SET space_name=?, space_type=?, booking_date=? WHERE id=?');
        return $stmt->execute([$space_name, $space_type, $booking_date, $id]);
    }
    public function delete($id){
        $stmt = $this->pdo->prepare('DELETE FROM bookings WHERE id=?');
        return $stmt->execute([$id]);
    }
}
