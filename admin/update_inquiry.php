<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }
$inquiryId = filter_var($_POST['inquiry_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$totalAmount = filter_var($_POST['total_amount'] ?? null, FILTER_VALIDATE_FLOAT);
$status = trim($_POST['status'] ?? '');
$statuses = ['Pending Review', 'Confirmed', 'Completed', 'Cancelled'];
if (!$inquiryId || $totalAmount === false || $totalAmount < 0 || !in_array($status, $statuses, true)) {
    $_SESSION['admin_message'] = 'Enter a valid amount and status.';
    header('Location: dashboard.php');
    exit;
}
$pdo = getDatabaseConnection();
$statement = $pdo->prepare('UPDATE inquiries SET total_amount = ?, estimated_cost = ?, status = ? WHERE id = ?');
$statement->execute([round((float) $totalAmount, 2), round((float) $totalAmount, 2), $status, $inquiryId]);
$_SESSION['admin_message'] = 'Inquiry price and status updated.';
header('Location: dashboard.php');
exit;
