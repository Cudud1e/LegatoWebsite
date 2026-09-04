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
$venue = trim($_POST['venue'] ?? '');
$guestCount = filter_var($_POST['guest_count'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$packageInterest = trim($_POST['package_interest'] ?? '');
$budgetRange = trim($_POST['budget_range'] ?? '') ?: null;
$inquiry = trim($_POST['message'] ?? '');
$services = array_values(array_filter($_POST['services'] ?? [], 'is_string'));
$allowedServices = ['Audio System & Sound Tech', 'Stage Lighting & Moving Heads', 'Professional EMCEE / Host', 'Full Event Coordination / Direction', 'Photo & Video Coverage'];
$services = array_values(array_intersect($services, $allowedServices));
$eventTypes = ['Wedding', 'Birthday/Debut', 'Corporate Event', 'School Gala', 'Private Party', 'Other'];
$packages = ['VIP 1: Elite Starter', 'VIP 2: Prestige', 'VIP 3: Grand Luxe', 'Custom Build', 'Unsure / Need Guidance'];
$validDate = DateTime::createFromFormat('Y-m-d', $eventDate);

if ($name === '' || !$email || $phone === '' || !in_array($eventType, $eventTypes, true) || !$validDate || $validDate->format('Y-m-d') !== $eventDate || $venue === '' || $guestCount === false || !in_array($packageInterest, $packages, true) || $inquiry === '') {
    $_SESSION['inquiry_error'] = 'Please complete all required booking details before sending your inquiry.';
    header('Location: contact.php');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('INSERT INTO inquiries (user_id, name, email, phone, event_type, target_event_date, venue, guest_count, package_interest, budget_range, requested_services, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $statement->execute([$user['id'] ?? null, $name, $email, $phone, $eventType, $eventDate, $venue, $guestCount, $packageInterest, $budgetRange, json_encode($services, JSON_THROW_ON_ERROR), $inquiry]);
    $inquiryId = (int) $pdo->lastInsertId();
    $_SESSION['last_inquiry_id'] = $inquiryId;
    sendAdminNotificationPlaceholder($inquiryId);
    sendClientReceiptPlaceholder((string) $email, $inquiryId);
    header('Location: inquiry_thank_you.php?inquiry_id=' . $inquiryId);
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
