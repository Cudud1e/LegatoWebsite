<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}
$user = $_SESSION['user'] ?? [];
$name = trim($_POST['name'] ?? ($user['full_name'] ?? ''));
$email = filter_var(trim($_POST['email'] ?? ($user['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$phone = trim($_POST['phone'] ?? ($user['phone'] ?? ''));
$eventType = trim($_POST['event_type'] ?? '');
$eventDate = trim($_POST['target_event_date'] ?? '');
$eventStartTime = trim($_POST['event_start_time'] ?? '');
$setupAccessTime = trim($_POST['setup_access_time'] ?? '');
$venue = trim($_POST['venue'] ?? '');
$venueType = trim($_POST['venue_type'] ?? '');
$guestCount = filter_var($_POST['guest_count'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$packageInterest = trim($_POST['package_interest'] ?? '');
$budgetRange = trim($_POST['budget_range'] ?? '') ?: null;
$inquiry = trim($_POST['message'] ?? '');
$specialRequests = trim($_POST['special_requests'] ?? '');
$customSelectionJson = trim((string) ($_POST['custom_services'] ?? $_POST['services'] ?? ''));
$services = array_values(array_filter($_POST['services'] ?? [], 'is_string'));
$allowedServices = ['Audio System & Sound Tech', 'Stage Lighting & Moving Heads', 'LED Video Wall', 'Professional EMCEE / Host', 'Full Event Coordination', 'Photo & Video Coverage', 'Stage Setup', 'Ambient Uplighting', 'LED DJ Booth', 'Standby Generator Power'];
$services = array_values(array_intersect($services, $allowedServices));
$eventTypes = ['Wedding', 'Birthday/Debut', 'Corporate Event', 'School Gala', 'Private Party', 'Other'];
$venueTypes = ['Indoor Ballroom / Hotel', 'Outdoor Garden', 'Outdoor Beach', 'Private Residence'];
$budgetRanges = ['Under ₱50k', '₱50k–₱100k', '₱100k+'];
$packages = ['VIP 1: Elite Starter', 'VIP 2: Prestige', 'VIP 3: Grand Luxe', 'Custom Build', 'Unsure / Need Guidance'];
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
$validDate = DateTime::createFromFormat('Y-m-d', $eventDate);
$validStartTime = preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $eventStartTime) === 1;
$validSetupTime = preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $setupAccessTime) === 1;
if ($name === '' || !$email || $phone === '' || !in_array($eventType, $eventTypes, true) || !$validDate || $validDate->format('Y-m-d') !== $eventDate || !$validStartTime || !$validSetupTime || $venue === '' || !in_array($venueType, $venueTypes, true) || $guestCount === false || !in_array($packageInterest, $packages, true) || ($packageInterest === 'Custom Build' && $totalAmount <= 0) || ($budgetRange !== null && !in_array($budgetRange, $budgetRanges, true)) || $inquiry === '') {
    $_SESSION['inquiry_error'] = 'Please complete all required booking details before sending your inquiry.';
    header('Location: contact.php');
    exit;
}
try {
    $pdo = getDatabaseConnection();
    $referenceNo = 'LGT-' . date('Y') . '-' . random_int(1000, 9999);
    $referenceCheck = $pdo->prepare('SELECT COUNT(*) FROM inquiries WHERE reference_no = ?');
    while (true) {
        $referenceCheck->execute([$referenceNo]);
        if ((int) $referenceCheck->fetchColumn() === 0) {
            break;
        }
        $referenceNo = 'LGT-' . date('Y') . '-' . random_int(1000, 9999);
    }
    $statement = $pdo->prepare('INSERT INTO inquiries (reference_no, user_id, name, email, phone, event_type, target_event_date, event_start_time, setup_access_time, venue, venue_type, guest_count, package_interest, budget_range, requested_services, special_requests, message, total_amount, estimated_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $statement->execute([$referenceNo, $user['id'] ?? null, $name, $email, $phone, $eventType, $eventDate, $eventStartTime, $setupAccessTime, $venue, $venueType, $guestCount, $packageInterest, $budgetRange, json_encode($serviceSummary, JSON_THROW_ON_ERROR), $specialRequests ?: null, $inquiry, $totalAmount, $totalAmount]);
    $inquiryId = (int) $pdo->lastInsertId();
    $_SESSION['last_inquiry_id'] = $inquiryId;
    $_SESSION['last_inquiry_reference'] = $referenceNo;
    sendAdminNotificationPlaceholder($inquiryId);
    sendClientReceiptPlaceholder((string) $email, $inquiryId);
    header('Location: inquiry_thank_you.php?ref=' . rawurlencode($referenceNo));
    exit;
} catch (PDOException | JsonException $exception) {
    $_SESSION['inquiry_error'] = 'We could not save your inquiry. Please check the database setup and try again.';
    header('Location: contact.php');
    exit;
}
function sendAdminNotificationPlaceholder(int $inquiryId): void
{
    // Connect this to PHPMailer or another mail provider in production.
}
function sendClientReceiptPlaceholder(string $email, int $inquiryId): void
{
    // Send a confirmation receipt after SMTP credentials are configured.
}
