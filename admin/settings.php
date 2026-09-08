<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION['admin_user']['id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/../db.php';
$pdo = getDatabaseConnection();
$admin = $_SESSION['admin_user'];
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $statement = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = ?'); $statement->execute([(int) $admin['id']]); $record = $statement->fetch();
        if (!$record || !password_verify($current, $record['password_hash']) || strlen($new) < 8) { $error = 'Verify the current password and use at least 8 characters for the new password.'; }
        else { $update = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?'); $update->execute([password_hash($new, PASSWORD_DEFAULT), (int) $admin['id']]); $message = 'Your password was updated.'; }
    }
    if ($action === 'team' && $admin['role'] === 'super_admin') {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL); $fullName = trim($_POST['full_name'] ?? ''); $role = trim($_POST['role'] ?? 'staff'); $password = (string) ($_POST['password'] ?? '');
        if (!$email || $fullName === '' || !in_array($role, ['super_admin', 'coordinator', 'staff'], true) || strlen($password) < 8) { $error = 'Complete the team member details and use a password with at least 8 characters.'; }
        else { try { $statement = $pdo->prepare('INSERT INTO admin_users (email, password_hash, full_name, role) VALUES (?, ?, ?, ?)'); $statement->execute([$email, password_hash($password, PASSWORD_DEFAULT), $fullName, $role]); $message = 'Team member created.'; } catch (PDOException $exception) { $error = 'That admin email is already in use.'; } }
    }
}
$team = $pdo->query('SELECT id, email, full_name, role, is_active FROM admin_users ORDER BY full_name')->fetchAll();
function escaped(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin Settings | LEGATO</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet"><link rel="stylesheet" href="../style.css"></head><body class="inner-page"><header class="navbar"><div class="container nav-content"><a class="brand" href="dashboard.php"><span class="brand-mark">L</span><strong>LEGATO <small>OPERATIONS</small></strong></a><nav class="nav-links"><a href="dashboard.php">Dashboard</a><a class="active" href="settings.php">Settings</a><a href="logout.php">Log Out</a></nav></div></header><main><section class="page-hero"><div class="container"><p class="section-label">LEGATO OPERATIONS</p><h1>Account <em>Settings.</em></h1><p>Manage your operations credentials and team access.</p></div></section><section class="inner-section"><div class="container settings-grid"><section class="admin-panel"><p class="section-label">YOUR ACCESS</p><h2>Change password</h2><form class="settings-form" method="post"><input type="hidden" name="action" value="password"><label>Current password<input name="current_password" type="password" required></label><label>New password<input name="new_password" type="password" minlength="8" required></label><button class="btn btn-gold" type="submit">Update Password</button></form></section><?php if ($admin['role'] === 'super_admin'): ?><section class="admin-panel"><p class="section-label">SUPER ADMIN</p><h2>Invite team member</h2><form class="settings-form" method="post"><input type="hidden" name="action" value="team"><label>Full name<input name="full_name" required></label><label>Email<input name="email" type="email" required></label><label>Role<select name="role"><option value="staff">Staff</option><option value="coordinator">Coordinator</option><option value="super_admin">Super Admin</option></select></label><label>Temporary password<input name="password" type="password" minlength="8" required></label><button class="btn btn-gold" type="submit">Create Account</button></form><div class="team-list"><?php foreach ($team as $member): ?><div><strong><?php echo escaped($member['full_name']); ?></strong><span><?php echo escaped($member['email']); ?> · <?php echo escaped($member['role']); ?></span></div><?php endforeach; ?></div></section><?php endif; ?><?php if ($message): ?><p class="form-message"><?php echo escaped($message); ?></p><?php endif; ?><?php if ($error): ?><p class="form-message error-message"><?php echo escaped($error); ?></p><?php endif; ?></div></section></main></body></html>
