<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../controllers/tasks.php';
require_once __DIR__.'/../db.php';

requireLogin();
$user = currentUser();

$data = json_decode(file_get_contents('php://input'), true);
$taskId = $data['taskId'] ?? null;

if(!$taskId){
    echo json_encode(['success'=>false,'error'=>'Task ID missing']);
    exit;
}

try {
    deleteTask($taskId, $user['id']); // користи твојата function deleteTask
    echo json_encode(['success'=>true]);
} catch(Exception $e){
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
