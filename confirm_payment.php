<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/invoice_schema.php';
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['admin_user']['id'], $_SESSION['admin_user']['role']) || !in_array($_SESSION['admin_user']['role'], ['super_admin', 'coordinator', 'staff'], true)) { header('Location: admin/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) { $_SESSION['invoice_message'] = 'Your form session expired. Please try again.'; header('Location: admin_inquiries.php'); exit; }
$inquiryId = filter_var($_POST['inquiry_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$paymentType = $_POST['payment_type'] ?? ''; $paymentMode = $_POST['payment_mode'] ?? '';
$isDownpayment = $paymentType === '50_percent_paid'; $isFullVerification = $paymentType === 'full_payment_verified';
if (!$inquiryId || (!$isDownpayment && !$isFullVerification) || !in_array($paymentMode, ['Online Payment', 'In Person'], true) || ($isFullVerification && ($_POST['funds_verified'] ?? '') !== '1')) { $_SESSION['invoice_message'] = 'Choose a valid payment mode and complete the verification checklist.'; header('Location: admin_inquiries.php'); exit; }

try {
    $pdo = getDatabaseConnection(); ensureInvoiceSchema($pdo); $pdo->beginTransaction();
    $lookup = $pdo->prepare('SELECT id, name, email, package_interest, COALESCE(total_amount, estimated_total, estimated_cost, 0) AS total_amount FROM inquiries WHERE id = ? FOR UPDATE');
    $lookup->execute([$inquiryId]); $inquiry = $lookup->fetch();
    if (!$inquiry || (float) $inquiry['total_amount'] <= 0) throw new RuntimeException('A valid total package price is required before recording payment.');
    $total = round((float) $inquiry['total_amount'], 2); $paid = round($total * 0.50, 2); $remaining = round($total - $paid, 2);
    $existing = $pdo->prepare('SELECT id, invoice_number, public_token FROM invoices WHERE inquiry_id = ? FOR UPDATE'); $existing->execute([$inquiryId]); $invoice = $existing->fetch(); $token = $invoice['public_token'] ?? bin2hex(random_bytes(32));
    if ($isFullVerification && !$invoice) throw new RuntimeException('A 50% payment record must exist before full-payment verification.');
    if ($isFullVerification) {
        $invoiceId = (int) $invoice['id']; $invoiceNumber = $invoice['invoice_number'];
        $update = $pdo->prepare("UPDATE invoices SET amount_paid=?, remaining_balance=0.00, payment_method=?, status='Fully Paid', payment_date=NOW(), final_payment_date=NOW(), verified_by_admin=1, public_token=? WHERE id=?");
        $update->execute([$total, $paymentMode, $token, $invoiceId]);
        $pdo->prepare("UPDATE inquiries SET status='Fully Paid & Completed', deposit_status='Fully Paid', remaining_balance=0.00 WHERE id=?")->execute([$inquiryId]);
        $log = $pdo->prepare('INSERT INTO activity_logs (admin_id, inquiry_id, activity) VALUES (?, ?, ?)');
        $log->execute([(int) $_SESSION['admin_user']['id'], $inquiryId, 'Admin verified full payment for Inquiry #' . $inquiryId]);
    } elseif ($invoice) {
        $invoiceId = (int) $invoice['id']; $invoiceNumber = $invoice['invoice_number'];
        $update = $pdo->prepare("UPDATE invoices SET total_amount=?, down_payment_amount=?, amount_due=?, percentage=50, amount_paid=?, remaining_balance=?, payment_method=?, status='Paid', payment_date=NOW(), downpayment_date=NOW(), public_token=? WHERE id=?");
        $update->execute([$total, $paid, $paid, $paid, $remaining, $paymentMode, $token, $invoiceId]);
    } else {
        $invoiceNumber = 'LGT-REC-' . date('Ymd') . '-' . str_pad((string) $inquiryId, 5, '0', STR_PAD_LEFT);
        $insert = $pdo->prepare("INSERT INTO invoices (inquiry_id, invoice_number, client_name, client_email, package_type, total_amount, down_payment_amount, amount_due, percentage, due_date, public_token, amount_paid, remaining_balance, payment_method, status, payment_date, downpayment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 50, CURDATE(), ?, ?, ?, ?, 'Paid', NOW(), NOW())");
        $insert->execute([$inquiryId, $invoiceNumber, $inquiry['name'], $inquiry['email'], $inquiry['package_interest'], $total, $paid, $paid, $token, $paid, $remaining, $paymentMode]); $invoiceId = (int) $pdo->lastInsertId();
    }
    if ($isDownpayment) $pdo->prepare("UPDATE inquiries SET status='50% Paid - Booking Confirmed', deposit_status='50% Paid', downpayment_amount=?, remaining_balance=? WHERE id=?")->execute([$paid, $remaining, $inquiryId]);
    $pdo->commit();
    $_SESSION['invoice_message'] = $isFullVerification ? "Full payment verified. Receipt {$invoiceNumber} is available to the customer." : "50% payment recorded. Receipt {$invoiceNumber} is available to the customer.";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack(); error_log('LEGATO payment confirmation: ' . $exception->getMessage()); $_SESSION['invoice_message'] = 'Payment could not be recorded. Check the booking price and database setup.';
}
header('Location: admin_inquiries.php'); exit;
