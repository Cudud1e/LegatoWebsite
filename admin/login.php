<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../db.php';
if (isset($_SESSION['admin_user']['id'])) { header('Location: dashboard.php'); exit; }
$error = '';
$email = trim($_POST['email'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') { $error = 'Enter your admin email and password.'; }
    else { try { $pdo = getDatabaseConnection(); $statement = $pdo->prepare('SELECT id, email, password_hash, full_name, role FROM admin_users WHERE email = ? AND is_active = 1'); $statement->execute([$email]); $admin = $statement->fetch(); if (!$admin || !password_verify($password, $admin['password_hash'])) { $error = 'The admin email or password is incorrect.'; } else { session_regenerate_id(true); unset($admin['password_hash']); $_SESSION['admin_user'] = $admin; header('Location: dashboard.php'); exit; } } catch (PDOException $exception) { $error = 'Admin tables are not available. Run database_migration_system.sql first.'; } }
}
function escaped(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin Login | LEGATO</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet"><link rel="stylesheet" href="../style.css"></head><body class="inner-page"><main class="auth-section"><form class="auth-card" method="post"><p class="section-label">LEGATO OPERATIONS</p><h1>Admin Sign In.</h1><p>Access inquiries, production tasks, and team settings.</p><label for="email">Admin email<input id="email" name="email" type="email" required value="<?php echo escaped($email); ?>"></label><label for="password">Password<input id="password" name="password" type="password" required></label><button class="btn btn-gold full-width" type="submit">Sign In</button><?php if ($error): ?><p class="form-message error-message"><?php echo escaped($error); ?></p><?php endif; ?></form></main></body></html>
