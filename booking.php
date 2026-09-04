<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/booking_guard.php';

requireBookingLogin();
$package = trim($_GET['package'] ?? '');
$destination = 'contact.php' . ($package !== '' ? '?' . http_build_query(['package' => $package]) : '');
header('Location: ' . $destination);
exit;
