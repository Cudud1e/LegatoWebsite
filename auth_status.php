<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
header('Content-Type: application/json; charset=utf-8');
$user = $_SESSION['user'] ?? null;
$userName = trim((string) ($user['full_name'] ?? $user['nickname'] ?? 'Client'));
$firstName = explode(' ', $userName)[0] ?: 'Client';
echo json_encode([
    'loggedIn' => is_array($user) && isset($user['id']),
    'nickname' => $user['nickname'] ?? null,
    'firstName' => $firstName,
], JSON_THROW_ON_ERROR);
