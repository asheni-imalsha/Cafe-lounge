<?php
require_once __DIR__ . '/../models/CafeItem.php';
require_once __DIR__ . '/../../src/auth.php';

class MenuController {
    public static function list(){
        $model = new CafeItem();
        $items = $model->all();
        require __DIR__ . '/../../views/menu/index.php';
    }
}
