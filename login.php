<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/csrf.php';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';
$redirectPath = parse_url($redirect, PHP_URL_PATH) ?: 'index.php';
if (!in_array(basename($redirectPath), ['index.php', 'contact.php', 'custom.php', 'booking.php'], true)) {
    $redirect = 'index.php';
} else {
    $redirect = basename($redirectPath);
    $redirectQuery = parse_url($_GET['redirect'] ?? $_POST['redirect'] ?? '', PHP_URL_QUERY);
    if ($redirectQuery) {
        $redirect .= '?' . $redirectQuery;
    }
}
$createAccountMode = ($_POST['mode'] ?? 'login') === 'register';
$message = '';
$error = '';
$fullName = trim($_POST['full_name'] ?? '');
$nickname = trim($_POST['nickname'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$location = trim($_POST['location'] ?? '');
$emailInput = trim($_POST['email'] ?? '');
$loginMessage = trim($_GET['message'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please try again.';
    }
    $email = filter_var($emailInput, FILTER_VALIDATE_EMAIL);
    $password = (string) ($_POST['password'] ?? '');
    if ($error) {
        // Keep the existing form error for an invalid or expired submission.
    } elseif (!$email || strlen($password) < 8) {
        $error = 'Enter a valid email and a password with at least 8 characters.';
    } elseif ($createAccountMode && ($fullName === '' || $nickname === '' || $phone === '' || $location === '')) {
        $error = 'Please complete your full name, nickname, phone number, and location.';
    } elseif ($createAccountMode && $password !== (string) ($_POST['confirm_password'] ?? '')) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $pdo = getDatabaseConnection();
            if ($createAccountMode) {
                $statement = $pdo->prepare('INSERT INTO users (email, password_hash, full_name, nickname, phone, location) VALUES (:email, :password_hash, :full_name, :nickname, :phone, :location)');
                $statement->bindValue(':email', $email, PDO::PARAM_STR);
                $statement->bindValue(':password_hash', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                $statement->bindValue(':full_name', $fullName, PDO::PARAM_STR);
                $statement->bindValue(':nickname', $nickname, PDO::PARAM_STR);
                $statement->bindValue(':phone', $phone, PDO::PARAM_STR);
                $statement->bindValue(':location', $location, PDO::PARAM_STR);
                $statement->execute();
                $message = 'Your account was created. You can now log in.';
                $createAccountMode = false;
            } else {
                $adminStatement = $pdo->prepare('SELECT id, email, password_hash, full_name, role FROM admin_users WHERE email = :email AND is_active = 1');
                $adminStatement->bindValue(':email', $email, PDO::PARAM_STR);
                $adminStatement->execute();
                $admin = $adminStatement->fetch();
                if ($admin && password_verify($password, $admin['password_hash'])) {
                    session_regenerate_id(true);
                    unset($_SESSION['user']);
                    unset($admin['password_hash']);
                    $_SESSION['admin_user'] = $admin;
                    header('Location: admin/dashboard.php');
                    exit;
                }
                $statement = $pdo->prepare('SELECT id, email, password_hash, nickname FROM users WHERE email = :email');
                $statement->bindValue(':email', $email, PDO::PARAM_STR);
                $statement->execute();
                $user = $statement->fetch();
                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $error = 'The email or password is incorrect.';
                } else {
                    session_regenerate_id(true);
                    unset($_SESSION['admin_user']);
                    $profile = $pdo->prepare('SELECT full_name, phone, location FROM users WHERE id = :user_id');
                    $profile->bindValue(':user_id', (int) $user['id'], PDO::PARAM_INT);
                    $profile->execute();
                    $profileData = $profile->fetch() ?: [];
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'role' => 'client',
                        'email' => $user['email'],
                        'nickname' => $user['nickname'],
                        'full_name' => $profileData['full_name'] ?? '',
                        'phone' => $profileData['phone'] ?? '',
                        'location' => $profileData['location'] ?? '',
                    ];
                    header('Location: ' . $redirect);
                    exit;
                }
            }
        } catch (PDOException $exception) {
            $error = 'Database is not connected yet, or the profile columns have not been added. Run database_migration_profile.sql.';
        }
    }
}
function escaped(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Log In | LEGATO Events & Productions</title>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="style.css" />
    </head>
    <body class="inner-page">
        <header class="navbar">
            <div class="container nav-content">
                <a href="index.php" class="brand">
                    <img class="custom-logo h-16 sm:h-20 lg:h-24 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events & Productions" />
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
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="profile.php" class="nav-login">My Account</a>
                        <a href="logout.php" class="nav-login">Log Out</a>
                    <?php else: ?>
                    <a class="nav-login active" href="login.php">Log In</a>
                <?php endif; ?>
                <button class="menu-button" id="menuButton" aria-label="Open menu">☰</button>
            </div>
        </div>
    </header>
    <main>
        <section class="auth-section">
            <div class="auth-card">
                <p class="section-label">LEGATO CLIENT PORTAL</p>
                <h1 id="authTitle">
                    <?php echo $createAccountMode ? 'Create your account.' : 'Welcome back.'; ?>
                </h1>
                <p id="authDescription">
                    <?php echo $createAccountMode ? 'Save your event inquiry details and stay connected with the LEGATO team.' : 'Log in to manage your event inquiry and booking details.'; ?>
                </p>
                <?php if ($loginMessage): ?>
                    <p class="form-message">
                        <?php echo escaped($loginMessage); ?>
                    </p>
                <?php endif; ?>
                <form id="authForm" method="post" action="login.php">
                    <input type="hidden" name="csrf_token" value="<?php echo escaped(csrfToken()); ?>">
                    <input type="hidden" name="mode" id="authMode" value="<?php echo $createAccountMode ? 'register' : 'login'; ?>">
                    <input type="hidden" name="redirect" value="<?php echo escaped($redirect); ?>">
                    <label class="registration-field" for="fullName"<?php echo $createAccountMode ? '' : ' hidden'; ?>>Full name<input id="fullName" name="full_name" type="text" value="<?php echo escaped($fullName); ?>" <?php echo $createAccountMode ? 'required' : ''; ?> />
                    </label>
                    <label class="registration-field" for="nickname"<?php echo $createAccountMode ? '' : ' hidden'; ?>>Nickname<input id="nickname" name="nickname" type="text" value="<?php echo escaped($nickname); ?>" <?php echo $createAccountMode ? 'required' : ''; ?> />
                    </label>
                    <label for="authEmail">Email address<input id="authEmail" name="email" type="email" required value="<?php echo escaped($emailInput); ?>" />
                    </label>
                    <label class="registration-field" for="phone"<?php echo $createAccountMode ? '' : ' hidden'; ?>>Phone number<input id="phone" name="phone" type="tel" value="<?php echo escaped($phone); ?>" <?php echo $createAccountMode ? 'required' : ''; ?> />
                    </label>
                    <label class="registration-field" for="location"<?php echo $createAccountMode ? '' : ' hidden'; ?>>Home or event location<input id="location" name="location" type="text" value="<?php echo escaped($location); ?>" <?php echo $createAccountMode ? 'required' : ''; ?> />
                    </label>
                    <label for="authPassword">Password<input id="authPassword" name="password" type="password" required />
                    </label>
                    <label class="confirm-field" for="confirmPassword"<?php echo $createAccountMode ? '' : ' hidden'; ?>>Confirm password<input id="confirmPassword" name="confirm_password" type="password" <?php echo $createAccountMode ? 'required' : ''; ?> />
                    </label>
                    <button class="btn btn-gold full-width" type="submit" id="authSubmit">
                        <?php echo $createAccountMode ? 'Create Account' : 'Log In'; ?>
                    </button>
                </form>
                <p class="auth-switch">
                    <span id="switchPrompt">
                        <?php echo $createAccountMode ? 'Already have an account?' : 'New to LEGATO?'; ?>
                    </span> <button type="button" id="switchAuth">
                    <?php echo $createAccountMode ? 'Log in' : 'Create an account'; ?>
                </button>
            </p>
            <p class="form-message" id="formMessage" role="status">
                <?php echo escaped($error ?: $message); ?>
            </p>
        </div>
    </section>
</main>
<footer>
    <div class="container footer-grid">
        <div>
            <a href="index.php" class="brand">
                <img class="footer-custom-logo h-12 sm:h-14 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events & Productions" />
            </a>
            <p class="footer-tagline">Where flawless production meets unforgettable celebration.</p>
        </div>
        <div>
            <p class="footer-title">Explore</p>
            <div class="footer-links">
                <a href="about.php">About Us</a>
                <a href="packages.php">VIP Packages</a>
                <a href="custom.php">Custom Services</a>
                <a href="login.php">Log In</a>
                <a href="terms.php">Terms &amp; Conditions</a>
                <a href="privacy.php">Privacy Policy</a>
                <a href="business_info.php">Business Info</a>
            </div>
        </div>
        <div>
            <p class="footer-title">Contact</p>
            <div class="footer-links">
                <span>Dumaguete City, Philippines</span>
                <a href="mailto:info@legatoevents.com">info@legatoevents.com</a>
                <a href="tel:+639000000000">+63 9XX XXX XXXX</a>
                <span>Monday to Saturday, 9:00 AM to 6:00 PM</span>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <span>© 2026 LEGATO Events & Productions. All Rights Reserved.</span>
            <span>Dumaguete · Negros Oriental</span>
        </div>
    </div>
</footer>
<script src="script.js">
</script>
<script src="auth.js">
</script>
</body>
</html>
