<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';

if (!isset($_SESSION['admin_user']['id'], $_SESSION['admin_user']['role']) || !in_array($_SESSION['admin_user']['role'], ['super_admin', 'coordinator', 'staff'], true)) {
    header('Location: login.php');
    exit;
}
