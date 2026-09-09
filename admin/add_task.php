<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $_SESSION['admin_message'] = 'Your form session expired. Please try again.';
    header('Location: dashboard.php');
    exit;
}
$pdo = getDatabaseConnection();
$taskMessage = '';
$action = $_POST['action'] ?? 'add';
if ($action === 'add') {
    $inquiryId = filter_var($_POST['inquiry_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $title = trim($_POST['title'] ?? '');
    $assignedAdminId = filter_var($_POST['assigned_admin_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $dueDate = trim($_POST['due_date'] ?? '') ?: null;
    $dateIsValid = $dueDate === null || isValidDateOnOrAfterToday($dueDate);
    if (!$inquiryId || $title === '' || strlen($title) > 180 || !$dateIsValid) {
        $_SESSION['admin_message'] = 'Choose a booking and enter a task name.';
    } else {
        $inquiryExists = $pdo->prepare('SELECT 1 FROM inquiries WHERE id = ?');
        $inquiryExists->execute([$inquiryId]);
        if (!$inquiryExists->fetchColumn()) {
            $taskMessage = 'The selected inquiry no longer exists.';
        } elseif ($assignedAdminId !== null) {
            $adminExists = $pdo->prepare('SELECT 1 FROM admin_users WHERE id = ? AND is_active = 1');
            $adminExists->execute([$assignedAdminId]);
            if (!$adminExists->fetchColumn()) {
                $taskMessage = 'Choose an active team member.';
            }
        }
        if ($taskMessage === '') {
            $statement = $pdo->prepare('INSERT INTO admin_tasks (inquiry_id, title, assigned_admin_id, due_date) VALUES (?, ?, ?, ?)');
            $statement->execute([$inquiryId, $title, $assignedAdminId, $dueDate]);
            $taskMessage = 'Production task added.';
        }
        $_SESSION['admin_message'] = $taskMessage;
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
