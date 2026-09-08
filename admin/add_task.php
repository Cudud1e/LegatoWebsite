<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION['admin_user']['id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/../db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }
$pdo = getDatabaseConnection();
$action = $_POST['action'] ?? 'add';
if ($action === 'add') {
    $inquiryId = filter_var($_POST['inquiry_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $title = trim($_POST['title'] ?? '');
    $assignedAdminId = filter_var($_POST['assigned_admin_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $dueDate = trim($_POST['due_date'] ?? '') ?: null;
    if (!$inquiryId || $title === '') {
        $_SESSION['admin_message'] = 'Choose a booking and enter a task name.';
    } else {
        $statement = $pdo->prepare('INSERT INTO admin_tasks (inquiry_id, title, assigned_admin_id, due_date) VALUES (?, ?, ?, ?)');
        $statement->execute([$inquiryId, $title, $assignedAdminId, $dueDate]);
        $_SESSION['admin_message'] = 'Production task added.';
    }
} elseif ($action === 'toggle') {
    $taskId = filter_var($_POST['task_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $status = ($_POST['completed'] ?? '') === '1' ? 'Done' : 'Open';
    if ($taskId) {
        $statement = $pdo->prepare('UPDATE admin_tasks SET status = ? WHERE id = ?');
        $statement->execute([$status, $taskId]);
    }
} elseif ($action === 'delete') {
    $taskId = filter_var($_POST['task_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($taskId) {
        $statement = $pdo->prepare('DELETE FROM admin_tasks WHERE id = ?');
        $statement->execute([$taskId]);
        $_SESSION['admin_message'] = 'Task deleted.';
    }
}
header('Location: dashboard.php');
exit;
