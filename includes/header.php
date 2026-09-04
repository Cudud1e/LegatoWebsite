<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'LEGATO Events & Productions';
$currentPage = basename($_SERVER['PHP_SELF']);
$user = $_SESSION['user'] ?? null;
$isLoggedIn = is_array($user) && isset($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $currentPage === 'index.php' ? '' : 'inner-page'; ?>">
<header class="navbar">
  <div class="container nav-content">
    <a href="index.php" class="brand"><img class="custom-logo" src="Assest/legato1.png" alt="LEGATO Events & Productions"></a>
    <nav class="nav-links" id="navLinks">
      <a class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
      <a class="<?php echo $currentPage === 'about.php' ? 'active' : ''; ?>" href="about.php">About Us</a>
      <a class="<?php echo $currentPage === 'packages.php' ? 'active' : ''; ?>" href="packages.php">VIP Packages</a>
      <a class="<?php echo $currentPage === 'custom.php' ? 'active' : ''; ?>" href="custom.php">Custom Services</a>
      <a class="<?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a>
    </nav>
    <div class="nav-actions">
      <a href="booking.php" class="btn btn-gold nav-book">Book An Event</a>
      <?php if ($isLoggedIn): ?>
        <a href="profile.php" class="nav-login">My Account</a>
        <a href="logout.php" class="nav-login">Log Out</a>
      <?php else: ?>
        <a href="login.php?redirect=booking.php&message=Please+log+in+or+create+an+account+to+finalize+your+event+booking." class="nav-login">Log In</a>
      <?php endif; ?>
      <button class="menu-button" id="menuButton" aria-label="Open menu">☰</button>
    </div>
  </div>
</header>
