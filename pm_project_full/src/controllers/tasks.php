<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../auth.php';

/**
 * Create task – only Project Team Lead or Admin
 */
function createTask($projectId, $title, $description, $creatorId)
{
    global $pdo;

    if (!isProjectTeamLead($projectId, $creatorId) && !isAdmin()) {
        throw new Exception('No permission to create task for this project');
    }

    $stmt = $pdo->prepare("INSERT INTO tasks (project_id, title, description, created_by, status)
                           VALUES (?, ?, ?, ?, 'Unassigned')");
    $stmt->execute([$projectId, $title, $description, $creatorId]);

    return $pdo->lastInsertId();
}

/**
 * Assign task
 */
function assignTask($taskId, $assignerId, $assigneeId)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();
    if (!$task) throw new Exception('Task not found');

    $projectId = $task['project_id'];
    $assignerLevel = userLevel($assignerId);
    $assigneeLevel = userLevel($assigneeId);

    // Check project membership
    if (!isProjectMember($projectId, $assigneeId)) {
        throw new Exception("Assignee is not part of this project");
    }

    // Permissions
    if (isProjectTeamLead($projectId, $assignerId) || isAdmin()) {
        // full control
    } elseif ($assignerLevel === 'Senior') {
        if ($assigneeId != $assignerId && !in_array($assigneeLevel, ['Mid', 'Junior'])) {
            throw new Exception('Senior can assign only to themselves, Mid, or Junior');
        }
    } elseif ($assignerLevel === 'Mid' || $assignerLevel === 'Junior') {
        throw new Exception("$assignerLevel cannot assign tasks");
    } else {
        throw new Exception('No permission to assign');
    }

    $stmt = $pdo->prepare("UPDATE tasks SET assigned_to = ?, status = ?, updated_at = NOW() WHERE id = ?");
    $newStatus = $assigneeId ? 'To Do' : $task['status'];
    $stmt->execute([$assigneeId, $newStatus, $taskId]);
}

/**
 * Change task status
 */
function changeTaskStatus($taskId, $userId, $newStatus)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();
    if (!$task) throw new Exception('Task not found');

    $projectId = $task['project_id'];
    $userLvl = userLevel($userId);

    if (!isProjectMember($projectId, $userId) && !isAdmin() && !isProjectTeamLead($projectId, $userId)) {
        throw new Exception("You are not a member of this project");
    }

    // Permissions
    if (isProjectTeamLead($projectId, $userId) || isAdmin()) {
        // full control
    } elseif ($userLvl === 'Senior') {
        // allowed for project members
    } elseif ($userLvl === 'Mid') {
        if ($task['assigned_to'] == $userId || ($task['assigned_to'] && userLevel($task['assigned_to']) === 'Junior')) {
            // ok
        } else {
            throw new Exception('Mid can only change status of own tasks or tasks assigned to Junior');
        }
    } elseif ($userLvl === 'Junior') {
        if ($task['assigned_to'] != $userId) {
            throw new Exception('Junior can only change status of their own tasks');
        }
    } else {
        throw new Exception('No permission');
    }

    $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newStatus, $taskId]);

    // Auto comment
    $stmt = $pdo->prepare("INSERT INTO comments (task_id, user_id, content) VALUES (?, ?, ?)");
    $user = currentUser();
    $uname = $user['name'] ?? 'System';
    $text = "$uname changed the status to $newStatus";
    $stmt->execute([$taskId, $user['id'], $text]);

    // Check project completion
    if ($newStatus === 'Done') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE project_id = ? AND status <> 'Done'");
        $stmt->execute([$projectId]);
        $remaining = $stmt->fetchColumn();
        if ($remaining == 0) {
            $stmt = $pdo->prepare("UPDATE projects SET status='Done' WHERE id = ?");
            $stmt->execute([$projectId]);
        }
    }
}

/**
 * Delete Task – ONLY Project Team Lead or Admin
 */
function deleteTask($id, $userId){
    global $pdo;

    // земи project_id
    $stmt = $pdo->prepare("SELECT project_id FROM tasks WHERE id=?");
    $stmt->execute([$id]);
    $projectId = $stmt->fetchColumn();
    if (!$projectId) throw new Exception("Task not found");

    $userLevel = userLevel($userId);
    $isTeamLead = isTaskProjectTeamLead($id, $userId);

    if ($userLevel !== 'Admin' && !$isTeamLead) {
        throw new Exception("No permission to delete task");
    }

    // Бришење коментари
    $stmt = $pdo->prepare("DELETE FROM comments WHERE task_id=?");
    $stmt->execute([$id]);

    // Бришење task
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id=?");
    $stmt->execute([$id]);
}


/**
 * Helper to check if user is Team Lead of a project associated with a task
 */
function isTaskProjectTeamLead($taskId, $userId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 1 FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE t.id=? AND p.team_lead_id=?
    ");
    $stmt->execute([$taskId, $userId]);

    return (bool) $stmt->fetchColumn();
}
