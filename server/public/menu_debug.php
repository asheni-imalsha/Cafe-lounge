<?php
require_once __DIR__ . '/../app/models/CafeItem.php';
require_once __DIR__ . '/../app/models/Database.php';
header('Content-Type: application/json');
try{
    $model = new CafeItem();
    $items = $model->all();
    echo json_encode(['success'=>true,'count'=>count($items),'items'=>$items], JSON_PRETTY_PRINT);
} catch (Exception $e){
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
