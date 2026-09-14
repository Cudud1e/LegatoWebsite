<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/invoice_schema.php';
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['admin_user']['id'], $_SESSION['admin_user']['role']) || !in_array($_SESSION['admin_user']['role'], ['super_admin', 'coordinator', 'staff'], true)) { header('Location: admin/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) { $_SESSION['invoice_message'] = 'Your form session expired. Please try again.'; header('Location: admin_inquiries.php'); exit; }
$inquiryId = filter_var($_POST['inquiry_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$inquiryId) { $_SESSION['invoice_message'] = 'Invalid inquiry selected.'; header('Location: admin_inquiries.php'); exit; }

try {
    $pdo = getDatabaseConnection(); ensureInvoiceSchema($pdo); $pdo->beginTransaction();
    $lookup = $pdo->prepare('SELECT id, name, email, package_interest, payment_method, COALESCE(total_amount, estimated_total, estimated_cost, 0) AS total_amount FROM inquiries WHERE id = ? FOR UPDATE');
    $lookup->execute([$inquiryId]); $inquiry = $lookup->fetch();
    if (!$inquiry || (float) $inquiry['total_amount'] <= 0) throw new RuntimeException('A valid total package price is required before invoicing.');
    $total = round((float) $inquiry['total_amount'], 2); $amountDue = round($total * 0.50, 2); $dueDate = (new DateTimeImmutable('+3 days'))->format('Y-m-d');
    $existing = $pdo->prepare('SELECT id, invoice_number, public_token FROM invoices WHERE inquiry_id = ? FOR UPDATE'); $existing->execute([$inquiryId]); $invoice = $existing->fetch();
    $token = $invoice['public_token'] ?? bin2hex(random_bytes(32));
    if ($invoice) {
        $invoiceId = (int) $invoice['id']; $invoiceNumber = $invoice['invoice_number'];
        $update = $pdo->prepare("UPDATE invoices SET total_amount=?, down_payment_amount=?, amount_due=?, percentage=50, due_date=?, public_token=?, payment_method=?, status='Pending Payment' WHERE id=?");
        $update->execute([$total, $amountDue, $amountDue, $dueDate, $token, $inquiry['payment_method'] ?: 'Online Payment', $invoiceId]);
    } else {
        $invoiceNumber = 'LGT-INV-' . date('Ymd') . '-' . str_pad((string) $inquiryId, 5, '0', STR_PAD_LEFT);
        $insert = $pdo->prepare("INSERT INTO invoices (inquiry_id, invoice_number, client_name, client_email, package_type, total_amount, down_payment_amount, amount_due, percentage, due_date, public_token, amount_paid, remaining_balance, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 50, ?, ?, 0, ?, ?, 'Pending Payment')");
        $insert->execute([$inquiryId, $invoiceNumber, $inquiry['name'], $inquiry['email'], $inquiry['package_interest'], $total, $amountDue, $amountDue, $dueDate, $token, $total, $inquiry['payment_method'] ?: 'Online Payment']); $invoiceId = (int) $pdo->lastInsertId();
    }
    $pdo->prepare("UPDATE inquiries SET status='Invoice Sent', deposit_status='Invoice Sent' WHERE id=?")->execute([$inquiryId]); $pdo->commit();
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'; $host = preg_replace('/[^A-Za-z0-9.:-]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $link = "{$scheme}://{$host}/view_invoice.php?id={$invoiceId}&token={$token}";
    $subject = 'Your LEGATO 50% Downpayment Invoice'; $body = "Hello {$inquiry['name']},\n\nYour 50% downpayment invoice ({$invoiceNumber}) is ready. View it securely here: {$link}\nDue date: {$dueDate}.";
    $mailSent = filter_var($inquiry['email'], FILTER_VALIDATE_EMAIL) && @mail($inquiry['email'], $subject, $body, "Content-Type: text/plain; charset=UTF-8\r\n");
    $_SESSION['invoice_message'] = "Invoice {$invoiceNumber} generated. " . ($mailSent ? 'Customer notification email sent.' : "Email delivery is not configured; share this secure link: {$link}");
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack(); error_log('LEGATO invoice generation: ' . $exception->getMessage()); $_SESSION['invoice_message'] = 'Invoice could not be generated. Check the package price and database setup.';
}
header('Location: admin_inquiries.php'); exit;
