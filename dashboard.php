<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';

$sessionUser = $_SESSION['user'] ?? [];
$isLoggedIn = isset($sessionUser['id']);
if (!$isLoggedIn) {
    header('Location: login.php?redirect=dashboard.php');
    exit;
}
$userEmail = htmlspecialchars((string) ($sessionUser['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$userNickname = htmlspecialchars((string) ($sessionUser['nickname'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | LEGATO Events &amp; Productions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="inner-page">
    <header class="navbar">
        <div class="container nav-content">
            <a href="index.php" class="brand">
                <img class="custom-logo h-16 sm:h-20 lg:h-24 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events &amp; Productions" />
            </a>
            <nav class="nav-links" id="navLinks">
                <a href="index.php">Home</a>
                <a href="about.php">About Us</a>
                <a href="packages.php">VIP Packages</a>
                <a href="custom.php">Custom Services</a>
                <a href="business_info.php">Policies &amp; Info</a>
            </nav>
            <div class="nav-actions">
                <a href="booking.php" class="btn btn-gold nav-book">Book An Event</a>
                <?php if ($isLoggedIn): ?>
                    <a href="profile.php" class="nav-login">My Account</a>
                    <a href="logout.php" class="nav-login">Log Out</a>
                <?php else: ?>
                    <a href="login.php?redirect=booking.php&amp;message=Please+log+in+or+create+an+account+to+finalize+your+event+booking." class="nav-login">Log In</a>
                <?php endif; ?>
                <button class="menu-button" id="menuButton" aria-label="Open menu">&#9776;</button>
            </div>
        </div>
    </header>
    <main class="dashboard-page">
        <section class="page-hero dashboard-hero profile-header">
            <div class="container">
                <p class="section-label">LEGATO CLIENT PORTAL</p>
                <h1>Welcome back, <em><?php echo $userNickname ?: $userEmail; ?>.</em></h1>
                <p>Review your profile and keep track of your event inquiries in one place.</p>
                <a href="profile.php" class="btn btn-gold">Open My Account</a>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
