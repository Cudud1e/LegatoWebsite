<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../db.php';
$isAjax = ($_POST['ajax'] ?? '') === '1' || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
function respond(bool $ok, string $message, bool $ajax): void {
    if ($ajax) { header('Content-Type: application/json; charset=utf-8'); http_response_code($ok ? 200 : 422); echo json_encode(['ok' => $ok, 'message' => $message]); exit; }
    $_SESSION['admin_message'] = $message; header('Location: dashboard.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    respond(false, 'Your form session expired. Please refresh and try again.', $isAjax);
}
$inquiryId = filter_var($_POST['inquiry_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$totalAmount = filter_var($_POST['total_amount'] ?? null, FILTER_VALIDATE_FLOAT);
$status = trim($_POST['status'] ?? '');
$statuses = ['Pending Review', 'Pending Verification', 'In-Person Pending', 'Confirmed', 'Rejected', 'Completed', 'Cancelled'];
if (!$inquiryId || $totalAmount === false || $totalAmount < 0 || !in_array($status, $statuses, true)) {
    respond(false, 'Enter a valid amount and status.', $isAjax);
}
try {
    $pdo = getDatabaseConnection();
    $archived = in_array($status, ['Confirmed', 'Rejected', 'Completed', 'Cancelled'], true) ? 1 : 0;
    $statement = $pdo->prepare("UPDATE inquiries SET total_amount = ?, estimated_cost = ?, status = ?, is_archived = ?, confirmed_at = CASE WHEN ? = 'Confirmed' AND confirmed_at IS NULL THEN NOW() ELSE confirmed_at END WHERE id = ?");
    $statement->execute([round((float) $totalAmount, 2), round((float) $totalAmount, 2), $status, $archived, $status, $inquiryId]);
    respond($statement->rowCount() > 0, $statement->rowCount() > 0 ? 'Inquiry status updated.' : 'The selected inquiry no longer exists.', $isAjax);
} catch (PDOException $exception) {
    error_log('LEGATO inquiry update: ' . $exception->getMessage());
    respond(false, 'The inquiry could not be updated. Please try again.', $isAjax);
}
