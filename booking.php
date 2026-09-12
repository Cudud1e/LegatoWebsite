<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/booking_guard.php';
requireBookingLogin();
$isBookingPage = true;
$package = trim($_GET['package'] ?? '');
if ($package !== '') {
	$_GET['package'] = $package;
}
require __DIR__ . '/contact.php';
exit;
