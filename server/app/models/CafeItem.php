<?php
require_once __DIR__ . '/Database.php';
class CafeItem {
    protected $pdo;
    public function __construct(){ $this->pdo = Database::get(); }
    public function all(){
        return $this->pdo->query('SELECT * FROM cafe_items ORDER BY name')->fetchAll();
    }
    public function find($id){
        $stmt = $this->pdo->prepare('SELECT * FROM cafe_items WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
