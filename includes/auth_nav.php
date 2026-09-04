<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (isset($_SESSION['user']['id'])):
?>
<a href="profile.php" class="nav-login">My Account</a>
<a href="logout.php" class="nav-login">Log Out</a>
<?php else: ?>
<a href="login.php" class="nav-login">Log In</a>
<?php endif; ?>
