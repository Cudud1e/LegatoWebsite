<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $_SESSION['admin_message'] = 'Your form session expired. Please try again.';
    header('Location: dashboard.php');
    exit;
}
$inquiryId = filter_var($_POST['inquiry_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$totalAmount = filter_var($_POST['total_amount'] ?? null, FILTER_VALIDATE_FLOAT);
$status = trim($_POST['status'] ?? '');
$statuses = ['Pending Review', 'Pending Verification', 'In-Person Pending', 'Confirmed', 'Rejected', 'Completed', 'Cancelled'];
if (!$inquiryId || $totalAmount === false || $totalAmount < 0 || !in_array($status, $statuses, true)) {
    $_SESSION['admin_message'] = 'Enter a valid amount and status.';
    header('Location: dashboard.php');
    exit;
}
try {
    $pdo = getDatabaseConnection();
    $archived = in_array($status, ['Confirmed', 'Rejected', 'Completed', 'Cancelled'], true) ? 1 : 0;
    $statement = $pdo->prepare('UPDATE inquiries SET total_amount = ?, estimated_cost = ?, status = ?, is_archived = ? WHERE id = ?');
    $statement->execute([round((float) $totalAmount, 2), round((float) $totalAmount, 2), $status, $archived, $inquiryId]);
    $_SESSION['admin_message'] = $statement->rowCount() ? 'Inquiry price and status updated.' : 'The selected inquiry no longer exists.';
} catch (PDOException $exception) {
    $_SESSION['admin_message'] = 'The inquiry could not be updated. Please try again.';
}
header('Location: dashboard.php');
exit;
