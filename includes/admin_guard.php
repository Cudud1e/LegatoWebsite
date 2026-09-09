<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['admin_user']['id'], $_SESSION['admin_user']['role']) || !in_array($_SESSION['admin_user']['role'], ['super_admin', 'coordinator', 'staff'], true)) {
    header('Location: login.php');
    exit;
}
