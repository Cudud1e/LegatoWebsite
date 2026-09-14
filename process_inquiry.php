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
function returnWithInquiryErrors(array $errors, string $summary = 'Please correct the highlighted booking details and try again.'): never
{
    $allowed = ['full_name', 'email', 'phone', 'event_date', 'event_type', 'guest_count', 'event_start_time', 'setup_access_time', 'venue', 'venue_type', 'package_type', 'payment_method', 'payment_reference', 'notes', 'custom_services'];
    $_SESSION['inquiry_old'] = [];
    foreach ($allowed as $key) if (isset($_POST[$key]) && is_scalar($_POST[$key])) $_SESSION['inquiry_old'][$key] = trim((string) $_POST[$key]);
    $_SESSION['inquiry_field_errors'] = $errors;
    $_SESSION['inquiry_error'] = $summary;
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
$fieldErrors = [];
if ($name === '' || strlen($name) > 120) $fieldErrors['full_name'] = 'Enter your full name (up to 120 characters).';
if (!$email) $fieldErrors['email'] = 'Enter a valid email address.';
if (!isValidPhoneNumber($phone)) $fieldErrors['phone'] = 'Enter a valid phone number.';
if (!in_array($eventType, $eventTypes, true)) $fieldErrors['event_type'] = 'Choose an event type.';
if (!isValidDateOnOrAfterToday($eventDate)) $fieldErrors['event_date'] = 'Choose a valid future event date.';
if (!$validStartTime) $fieldErrors['event_start_time'] = 'Choose a valid event start time.';
if (!$validSetupTime) $fieldErrors['setup_access_time'] = 'Choose a valid venue setup access time.';
if ($venue === '' || strlen($venue) > 255) $fieldErrors['venue'] = 'Enter the event venue or location.';
if (!in_array($venueType, $venueTypes, true)) $fieldErrors['venue_type'] = 'Choose a venue type.';
if ($guestCount === false || $guestCount > 100000) $fieldErrors['guest_count'] = 'Enter an expected guest count.';
if (!in_array($packageInterest, $packages, true) || ($packageInterest === 'Custom Build' && $totalAmount <= 0)) $fieldErrors['package_type'] = 'Choose a valid production package.';
if (!in_array($paymentMethod, $paymentMethods, true)) $fieldErrors['payment_method'] = 'Choose a payment method.';
if ($inquiry === '' || strlen($inquiry) > 5000 || strlen($specialRequests) > 5000) $fieldErrors['notes'] = 'Enter event notes of up to 5,000 characters.';
if ($fieldErrors) returnWithInquiryErrors($fieldErrors);
$receiptPath = null;
if ($paymentMethod === 'Online Payment') {
    if ($paymentReference === '' || strlen($paymentReference) > 100) {
        returnWithInquiryErrors(['payment_reference' => 'Enter the transaction reference number for your online payment.']);
    }
    $upload = $_FILES['receipt_file'] ?? null;
    if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        returnWithInquiryErrors(['receipt_file' => 'Attach a proof-of-payment receipt for your online payment.']);
    }
    if (($upload['size'] ?? 0) < 1 || $upload['size'] > 5 * 1024 * 1024) {
        returnWithInquiryErrors(['receipt_file' => 'Your receipt must be between 1 byte and 5 MB.']);
    }
    $extension = strtolower(pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION));
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file((string) $upload['tmp_name']);
    $allowedUploads = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
    if (!isset($allowedUploads[$extension]) || $allowedUploads[$extension] !== $mimeType) {
        returnWithInquiryErrors(['receipt_file' => 'Upload a valid JPG, PNG, or PDF payment receipt.']);
    }
    $receiptDirectory = __DIR__ . '/uploads/receipts';
    if (!is_dir($receiptDirectory) && !mkdir($receiptDirectory, 0755, true) && !is_dir($receiptDirectory)) {
        returnWithInquiryErrors(['receipt_file' => 'The receipt storage directory is unavailable. Please try again.']);
    }
    $receiptFilename = 'receipt_' . bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file((string) $upload['tmp_name'], $receiptDirectory . '/' . $receiptFilename)) {
        returnWithInquiryErrors(['receipt_file' => 'We could not save your receipt. Please try again.']);
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
