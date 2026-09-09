<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$pageTitle = $pageTitle ?? 'LEGATO Events & Productions';
$currentPage = basename($_SERVER['PHP_SELF']);
$user = $_SESSION['user'] ?? null;
$isLoggedIn = is_array($user) && isset($user['id']);
$userName = trim((string) ($user['full_name'] ?? $user['nickname'] ?? 'Client'));
$firstName = explode(' ', $userName)[0] ?: 'Client';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            <?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>
        </title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
    </head>
    <body class="<?php echo $currentPage === 'index.php' ? '' : 'inner-page'; ?>">
        <header class="navbar py-4">
            <div class="container nav-content">
                <a href="index.php" class="brand navbar-brand">
                    <img class="custom-logo h-16 sm:h-20 lg:h-24 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events & Productions">
                </a>
                <nav class="nav-links" id="navLinks">
                    <a class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                    <a class="<?php echo $currentPage === 'about.php' ? 'active' : ''; ?>" href="about.php">About Us</a>
                    <a class="<?php echo $currentPage === 'packages.php' ? 'active' : ''; ?>" href="packages.php">VIP Packages</a>
                    <a class="<?php echo $currentPage === 'custom.php' ? 'active' : ''; ?>" href="custom.php">Custom Services</a>
                    <a class="<?php echo in_array($currentPage, ['terms.php', 'privacy.php', 'business_info.php'], true) ? 'active' : ''; ?>" href="business_info.php">Policies &amp; Info</a>
                </nav>
                <div class="nav-actions">
                    <a href="booking.php" class="btn btn-gold nav-book flex-shrink-0">Book An Event</a>
                    <?php if ($isLoggedIn): ?>
                        <details class="profile-menu">
                            <summary class="profile-menu-trigger">
                                <span class="user-avatar" aria-hidden="true">&#128100;</span>
                                <span>Welcome, <?php echo htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?></span>
                            </summary>
                            <div class="profile-menu-dropdown">
                                <a href="profile.php">Dashboard / My Bookings</a>
                                <a href="profile.php#account-settings">Account Settings</a>
                                <a class="profile-menu-logout" href="logout.php">Log Out</a>
                            </div>
                        </details>
                    <?php else: ?>
                        <a href="login.php?redirect=booking.php&message=Please+log+in+or+create+an+account+to+finalize+your+event+booking." class="nav-login">Log In</a>
                    <?php endif; ?>
                <button class="menu-button" id="menuButton" aria-label="Open menu">☰</button>
            </div>
        </div>
    </header>
