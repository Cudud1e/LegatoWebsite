<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validation.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: booking.php');
    exit;
}
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $_SESSION['inquiry_error'] = 'Your form session expired. Please try again.';
    header('Location: booking.php');
    exit;
}
$user = $_SESSION['user'] ?? [];
$name = trim($_POST['name'] ?? $_POST['full_name'] ?? ($user['full_name'] ?? ''));
$email = filter_var(trim($_POST['email'] ?? ($user['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$phone = trim($_POST['phone'] ?? ($user['phone'] ?? ''));
$eventType = trim($_POST['event_type'] ?? '');
$eventDate = trim($_POST['target_event_date'] ?? $_POST['event_date'] ?? '');
$eventStartTime = trim($_POST['event_start_time'] ?? '');
$setupAccessTime = trim($_POST['setup_access_time'] ?? '');
$venue = trim($_POST['venue'] ?? '');
$venueType = trim($_POST['venue_type'] ?? '');
$guestCount = filter_var($_POST['guest_count'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$packageInterest = trim($_POST['package_interest'] ?? $_POST['package_type'] ?? '');
$budgetRange = trim($_POST['budget_range'] ?? '') ?: null;
$paymentMethod = trim($_POST['payment_method'] ?? '');
$paymentReference = trim($_POST['payment_reference'] ?? '');
$inquiry = trim($_POST['message'] ?? $_POST['notes'] ?? '');
$specialRequests = trim($_POST['special_requests'] ?? '');
$customSelectionJson = trim((string) ($_POST['custom_services'] ?? $_POST['services'] ?? ''));
$services = array_values(array_filter($_POST['services'] ?? [], 'is_string'));
$allowedServices = ['Audio System & Sound Tech', 'Stage Lighting & Moving Heads', 'LED Video Wall', 'Professional EMCEE / Host', 'Full Event Coordination', 'Photo & Video Coverage', 'Stage Setup', 'Ambient Uplighting', 'LED DJ Booth', 'Standby Generator Power'];
$services = array_values(array_intersect($services, $allowedServices));
$eventTypes = ['Wedding', 'Birthday/Debut', 'Corporate Event', 'School Gala', 'Private Party', 'Other'];
$venueTypes = ['Indoor Ballroom / Hotel', 'Outdoor Garden', 'Outdoor Beach', 'Private Residence'];
$budgetRanges = ['Under ₱50k', '₱50k–₱100k', '₱100k+'];
$packages = ['VIP 1: Elite Starter', 'VIP 2: Prestige', 'VIP 3: Grand Luxe', 'Custom Build', 'Unsure / Need Guidance'];
$paymentMethods = ['Online Payment', 'In Person'];
$tierPrices = [
    'Audio System' => ['Basic' => 8000, 'Standard' => 15000, 'Premium' => 25000],
    'Professional EMCEE / Host' => ['Basic' => 5000, 'Standard' => 8000, 'Premium' => 12000],
    'Stage & Décor Support' => ['Basic' => 3000, 'Standard' => 7000, 'Premium' => 12000],
    'Visual & Multimedia Support' => ['Basic' => 2000, 'Standard' => 5000, 'Premium' => 8000],
    'Photography & Videography' => ['Basic' => 10000, 'Standard' => 15000, 'Premium' => 20000],
    'Full Event Management' => ['Basic' => 8000, 'Standard' => 12000, 'Premium' => 15000],
];
$customSelection = [];
if ($customSelectionJson !== '') {
    $decodedSelection = json_decode($customSelectionJson, true);
    if (is_array($decodedSelection)) {
        foreach ($decodedSelection as $service => $tier) {
            if (isset($tierPrices[$service][$tier])) {
                $customSelection[$service] = $tier;
            }
        }
    }
}
$totalAmount = ['VIP 1: Elite Starter' => 49999, 'VIP 2: Prestige' => 79999, 'VIP 3: Grand Luxe' => 179999][$packageInterest] ?? 0;
if ($packageInterest === 'Custom Build') {
    foreach ($customSelection as $service => $tier) {
        $totalAmount += $tierPrices[$service][$tier];
    }
}
$serviceSummary = $services;
foreach ($customSelection as $service => $tier) {
    $serviceSummary[] = $service . ' - ' . $tier;
}
$validStartTime = preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $eventStartTime) === 1;
$validSetupTime = preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $setupAccessTime) === 1;
if ($name === '' || strlen($name) > 120 || !$email || !isValidPhoneNumber($phone) || !in_array($eventType, $eventTypes, true) || !isValidDateOnOrAfterToday($eventDate) || !$validStartTime || !$validSetupTime || $venue === '' || strlen($venue) > 255 || !in_array($venueType, $venueTypes, true) || $guestCount === false || $guestCount > 100000 || !in_array($packageInterest, $packages, true) || !in_array($paymentMethod, $paymentMethods, true) || ($packageInterest === 'Custom Build' && $totalAmount <= 0) || ($budgetRange !== null && !in_array($budgetRange, $budgetRanges, true)) || $inquiry === '' || strlen($inquiry) > 5000 || strlen($specialRequests) > 5000) {
    $_SESSION['inquiry_error'] = 'Please complete all required booking details before sending your inquiry.';
    header('Location: booking.php');
    exit;
}
$receiptPath = null;
if ($paymentMethod === 'Online Payment') {
    if ($paymentReference === '' || strlen($paymentReference) > 100) {
        $_SESSION['inquiry_error'] = 'Enter the transaction reference number for your online payment.';
        header('Location: booking.php');
        exit;
    }
    $upload = $_FILES['receipt_file'] ?? null;
    if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $_SESSION['inquiry_error'] = 'Attach a proof-of-payment receipt for your online payment.';
        header('Location: booking.php');
        exit;
    }
    if (($upload['size'] ?? 0) < 1 || $upload['size'] > 5 * 1024 * 1024) {
        $_SESSION['inquiry_error'] = 'Your receipt must be between 1 byte and 5 MB.';
        header('Location: booking.php');
        exit;
    }
    $extension = strtolower(pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION));
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file((string) $upload['tmp_name']);
    $allowedUploads = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
    if (!isset($allowedUploads[$extension]) || $allowedUploads[$extension] !== $mimeType) {
        $_SESSION['inquiry_error'] = 'Upload a valid JPG, PNG, or PDF payment receipt.';
        header('Location: booking.php');
        exit;
    }
    $receiptDirectory = __DIR__ . '/uploads/receipts';
    if (!is_dir($receiptDirectory) && !mkdir($receiptDirectory, 0755, true) && !is_dir($receiptDirectory)) {
        $_SESSION['inquiry_error'] = 'The receipt storage directory is unavailable. Please try again.';
        header('Location: booking.php');
        exit;
    }
    $receiptFilename = 'receipt_' . bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file((string) $upload['tmp_name'], $receiptDirectory . '/' . $receiptFilename)) {
        $_SESSION['inquiry_error'] = 'We could not save your receipt. Please try again.';
        header('Location: booking.php');
        exit;
    }
    $receiptPath = 'uploads/receipts/' . $receiptFilename;
}
$downpaymentAmount = $paymentMethod === 'Online Payment' ? $totalAmount * 0.50 : 0.00;
$remainingBalance = $totalAmount - $downpaymentAmount;
$depositStatus = $paymentMethod === 'Online Payment' ? 'Pending Payment Verification' : 'Pending In-Person Payment';
$insertSql = '';
try {
    $pdo = getDatabaseConnection();
    ensureInquirySchema($pdo);
    $referenceNo = 'LGT-' . date('Y') . '-' . random_int(1000, 9999);
    $referenceCheck = $pdo->prepare('SELECT COUNT(*) FROM inquiries WHERE reference_no = ?');
    while (true) {
        $referenceCheck->execute([$referenceNo]);
        if ((int) $referenceCheck->fetchColumn() === 0) {
            break;
        }
        $referenceNo = 'LGT-' . date('Y') . '-' . random_int(1000, 9999);
    }
    $insertSql = 'INSERT INTO inquiries (reference_no, user_id, name, email, phone, event_type, target_event_date, event_start_time, setup_access_time, venue, venue_type, guest_count, package_interest, budget_range, payment_method, payment_reference, receipt_path, downpayment_amount, remaining_balance, deposit_status, requested_services, special_requests, message, estimated_total, total_amount, estimated_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $statement = $pdo->prepare($insertSql);
    $statement->execute([$referenceNo, $user['id'] ?? null, $name, $email, $phone, $eventType, $eventDate, $eventStartTime, $setupAccessTime, $venue, $venueType, $guestCount, $packageInterest, $budgetRange, $paymentMethod, $paymentReference ?: null, $receiptPath, $downpaymentAmount, $remainingBalance, $depositStatus, json_encode($serviceSummary, JSON_THROW_ON_ERROR), $specialRequests ?: null, $inquiry, $totalAmount, $totalAmount, $totalAmount]);
    $inquiryId = (int) $pdo->lastInsertId();
    $_SESSION['last_inquiry_id'] = $inquiryId;
    $_SESSION['last_inquiry_reference'] = $referenceNo;
    $_SESSION['inquiry_success'] = 'Your inquiry has been sent successfully!';
    $_SESSION['latest_inquiry'] = [
        'id' => $inquiryId,
        'reference_no' => $referenceNo,
        'name' => $name,
        'date' => $eventDate,
        'package' => $packageInterest,
        'total' => $totalAmount,
        'payment_method' => $paymentMethod,
        'downpayment_amount' => $downpaymentAmount,
        'remaining_balance' => $remainingBalance,
        'deposit_status' => $depositStatus,
        'status' => 'Pending Review',
    ];
    sendAdminNotificationPlaceholder($inquiryId);
    sendClientReceiptPlaceholder((string) $email, $inquiryId);
    header('Location: inquiry_thank_you.php?ref=' . rawurlencode($referenceNo));
    exit;
} catch (PDOException | JsonException $exception) {
    error_log('Inquiry database error: ' . $exception->getMessage());
    $attemptedQuery = $insertSql !== '' ? $insertSql : 'Schema check or inquiry reference lookup';
    die("<div style='color:red; font-family:sans-serif; padding:20px;'>\n        <h3>Database Insertion Failed</h3>\n        <p><strong>Error:</strong> " . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>\n        <p><strong>Attempted Query:</strong> <code>" . htmlspecialchars($attemptedQuery, ENT_QUOTES, 'UTF-8') . "</code></p>\n    </div>");
}
function ensureInquirySchema(PDO $pdo): void
{
    $columns = [
        'payment_method' => "VARCHAR(50) NOT NULL DEFAULT 'Online Payment'",
        'estimated_total' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'status' => "VARCHAR(30) NOT NULL DEFAULT 'Pending Review'",
        'payment_reference' => 'VARCHAR(100) NULL',
        'receipt_path' => 'VARCHAR(255) NULL',
        'downpayment_amount' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'remaining_balance' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'deposit_status' => "VARCHAR(80) NOT NULL DEFAULT 'Not Required Yet'",
    ];

    foreach ($columns as $column => $definition) {
        $result = $pdo->query("SHOW COLUMNS FROM inquiries LIKE '{$column}'");
        if ($result->fetch() === false) {
            $pdo->exec("ALTER TABLE inquiries ADD COLUMN {$column} {$definition}");
        }
    }
}
function sendAdminNotificationPlaceholder(int $inquiryId): void
{
    // Connect this to PHPMailer or another mail provider in production.
}
function sendClientReceiptPlaceholder(string $email, int $inquiryId): void
{
    // Send a confirmation receipt after SMTP credentials are configured.
}
