<?php
require_once __DIR__.'/../db.php';
require_once __DIR__.'/../middleware.php';

function getTaskProject($taskId){
    global $pdo;
    $stmt=$pdo->prepare("SELECT project_id, assigned_to FROM tasks WHERE id=?");
    $stmt->execute([$taskId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ------------------- ADD COMMENT -------------------
function addComment($taskId, $userId, $content) { 
    global $pdo;
    $level = userLevel($userId);
    $task = getTaskProject($taskId);
    if (!$task) throw new Exception('Task not found');

    $assigned = $task['assigned_to'];
    $projectId = $task['project_id'];

    if (in_array($level,['Admin','TeamLead','Senior'])){
        // full access → can comment on any task
    } elseif ($level==='Mid') {
        if (!isProjectMember($projectId,$userId)) throw new Exception('Mid cannot comment outside project');
        if ($assigned != $userId && userLevel($assigned)!=='Junior') 
            throw new Exception('Mid cannot comment on this task');
    } elseif ($level==='Junior'){
        if ($assigned != $userId) throw new Exception('Junior cannot comment on this task');
    }

    $stmt = $pdo->prepare("INSERT INTO comments(task_id,user_id,content) VALUES(?,?,?)");
    $stmt->execute([$taskId,$userId,$content]);
    return $pdo->lastInsertId();
}

// ------------------- EDIT COMMENT -------------------
function editComment($commentId, $userId, $newContent) { 
    global $pdo;
    $stmt=$pdo->prepare("SELECT user_id, task_id FROM comments WHERE id=?");
    $stmt->execute([$commentId]);
    $comment=$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$comment) throw new Exception('Comment not found');

    $owner = $comment['user_id'];
    $task = getTaskProject($comment['task_id']);
    $projectId = $task['project_id'];

    $level = userLevel($userId);
    $ownerLevel = userLevel($owner);

    $allowed=false;
    if (in_array($level,['Admin','TeamLead','Senior'])) $allowed=true;
    elseif ($level==='Mid') {
        if ($owner==$userId) $allowed=true;
        elseif ($ownerLevel==='Junior') $allowed=true;
    } elseif ($level==='Junior') {
        if ($owner==$userId) $allowed=true;
    }

    if (!$allowed) throw new Exception('No permission to edit comment');

    $stmt=$pdo->prepare("UPDATE comments SET content=?, updated_at=NOW(), edited=1 WHERE id=?");
    $stmt->execute([$newContent,$commentId]);
}

// ------------------- DELETE COMMENT -------------------
function deleteComment($commentId, $userId) { 
    global $pdo;
    $stmt=$pdo->prepare("SELECT user_id, task_id FROM comments WHERE id=?");
    $stmt->execute([$commentId]);
    $comment=$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$comment) throw new Exception('Comment not found');

    $owner = $comment['user_id'];
    $task = getTaskProject($comment['task_id']);
    $projectId = $task['project_id'];

    $level = userLevel($userId);
    $ownerLevel = userLevel($owner);

    $allowed=false;
    if (in_array($level,['Admin','TeamLead','Senior'])) $allowed=true;
    elseif ($level==='Mid') {
        if ($owner==$userId) $allowed=true;
        elseif ($ownerLevel==='Junior') $allowed=true;
    } elseif ($level==='Junior') {
        if ($owner==$userId) $allowed=true;
    }

    if (!$allowed) throw new Exception('No permission to delete comment');

    $stmt=$pdo->prepare("DELETE FROM comments WHERE id=?");
    $stmt->execute([$commentId]);
}
?>
