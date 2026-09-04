<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
$user = $_SESSION['user'] ?? null;
echo json_encode([
    'loggedIn' => is_array($user) && isset($user['id']),
    'nickname' => $user['nickname'] ?? null,
], JSON_THROW_ON_ERROR);
