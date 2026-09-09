<?php
declare(strict_types=1);
function bookingLoginUrl(string $destination): string
{
    $allowedPath = parse_url($destination, PHP_URL_PATH) ?: 'contact.php';
    if (!in_array(basename($allowedPath), ['contact.php', 'custom.php', 'booking.php'], true)) {
        $allowedPath = 'contact.php';
    }
    $query = parse_url($destination, PHP_URL_QUERY);
    $target = $allowedPath . ($query ? '?' . $query : '');
    return 'login.php?' . http_build_query([
        'redirect' => $target,
        'message' => 'Please log in or create an account to finalize your event booking.',
    ]);
}
function requireBookingLogin(): void
{
    if (!isset($_SESSION['user']['id'])) {
        header('Location: ' . bookingLoginUrl($_SERVER['REQUEST_URI'] ?? 'contact.php'));
        exit;
    }
}
