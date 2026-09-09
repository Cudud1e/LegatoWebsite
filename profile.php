<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
if (!isset($_SESSION['user']['id'])) { header('Location: login.php?redirect=profile.php'); exit; }
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validation.php';
$userId = (int) $_SESSION['user']['id'];
$message = '';
$error = '';
try {
    $pdo = getDatabaseConnection();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $error = 'Your form session expired. Please try again.';
        }
        $fullName = trim($_POST['full_name'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $location = trim($_POST['location'] ?? '');
        if ($error === '') {
            $error = profileValidationError($fullName, $nickname, $phone, $location) ?? '';
        }
        if ($error === '') {
            $update = $pdo->prepare('UPDATE users SET full_name = ?, nickname = ?, phone = ?, location = ? WHERE id = ?');
            $update->execute([$fullName, $nickname, $phone, $location, $userId]);
            $_SESSION['user']['full_name'] = $fullName;
            $_SESSION['user']['nickname'] = $nickname;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['location'] = $location;
            $message = 'Your profile was updated.';
        }
    }
    $accountStatement = $pdo->prepare('SELECT id, full_name, nickname, email, phone, location, created_at FROM users WHERE id = ?');
    $accountStatement->execute([$userId]);
    $account = $accountStatement->fetch();
    if (!$account) { header('Location: logout.php'); exit; }
    $history = $pdo->prepare('SELECT reference_no, event_type, target_event_date, event_start_time, venue, venue_type, guest_count, package_interest, budget_range, requested_services, special_requests, message, status, COALESCE(total_amount, estimated_cost, 0) AS estimated_cost, created_at FROM inquiries WHERE user_id = ? ORDER BY created_at DESC');
    $history->execute([$userId]);
    $inquiries = $history->fetchAll();
} catch (PDOException $exception) {
    $error = 'Your account information is temporarily unavailable. Please try again later.';
    $account = ['full_name' => '', 'nickname' => '', 'email' => '', 'phone' => '', 'location' => '', 'created_at' => 'now'];
    $inquiries = [];
}
$packagePrices = ['VIP 1: Elite Starter' => 49999, 'VIP 2: Prestige' => 79999, 'VIP 3: Grand Luxe' => 179999];
function escaped(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function money(float $value): string { return '₱' . number_format($value, 2); }
function statusClass(string $value): string { return strtolower(str_replace(' ', '-', $value)); }
$pageTitle = 'My Account | LEGATO Events & Productions';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo escaped($pageTitle); ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
    </head>
    <body class="inner-page">
    <main class="dashboard-page">
        <section class="page-hero dashboard-hero profile-header">
            <div class="container">
                <p class="section-label">LEGATO CLIENT PORTAL</p>
                <h1>My <em>Account.</em></h1>
                <p>Manage your client profile, inquiries, and event investment details.</p>
            </div>
        </section>
<section class="inner-section">
    <div class="container dashboard-grid">
        <aside class="profile-card">
            <p class="section-label">ACCOUNT DETAILS</p>
            <h2>
                <?php echo escaped($account['nickname']); ?>
            </h2>
            <dl>
                <div>
                    <dt>Full Name</dt>
                    <dd>
                        <?php echo escaped($account['full_name']); ?>
                    </dd>
                </div>
                <div>
                    <dt>Email Address</dt>
                    <dd>
                        <?php echo escaped($account['email']); ?>
                    </dd>
                </div>
                <div>
                    <dt>Phone Number</dt>
                    <dd>
                        <?php echo escaped($account['phone']); ?>
                    </dd>
                </div>
                <div>
                    <dt>Location</dt>
                    <dd>
                        <?php echo escaped($account['location']); ?>
                    </dd>
                </div>
                <div>
                    <dt>Member Since</dt>
                    <dd>
                        <?php echo escaped(date('F j, Y', strtotime($account['created_at']))); ?>
                    </dd>
                </div>
            </dl>
            <?php if ($message): ?>
                <p class="form-message">
                    <?php echo escaped($message); ?>
                </p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="form-message error-message">
                    <?php echo escaped($error); ?>
                </p>
            <?php endif; ?>
            <details id="edit-profile" class="edit-profile">
                <summary class="btn btn-outline full-width">Edit Profile</summary>
                <form method="post" action="profile.php#edit-profile">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="csrf_token" value="<?php echo escaped(csrfToken()); ?>">
                    <label>Full Name<input name="full_name" required value="<?php echo escaped($account['full_name']); ?>">
                    </label>
                    <label>Nickname<input name="nickname" required value="<?php echo escaped($account['nickname']); ?>">
                    </label>
                    <label>Phone Number<input name="phone" required value="<?php echo escaped($account['phone']); ?>">
                    </label>
                    <label>Location<input name="location" required value="<?php echo escaped($account['location']); ?>">
                    </label>
                    <button class="btn btn-gold full-width" type="submit">Save Changes</button>
                </form>
            </details>
        </aside>
        <div class="history-panel">
            <div class="section-heading">
                <p class="section-label">RECEIPT HISTORY</p>
                <h2>Your inquiries</h2>
            </div>
            <?php if (!$inquiries): ?>
                <div class="empty-state">
                    <p>No inquiries yet.</p>
                    <a class="btn btn-gold" href="booking.php">Start an Event Inquiry</a>
                </div>
            <?php endif; ?>
            <?php foreach ($inquiries as $inquiry): $services = json_decode((string) $inquiry['requested_services'], true) ?: []; $estimatedCost = (float) $inquiry['estimated_cost'] ?: ($packagePrices[$inquiry['package_interest']] ?? 0); ?>
                <article class="booking-card">
                    <div class="booking-card-header">
                        <div>
                            <span class="booking-reference">#<?php echo escaped($inquiry['reference_no']); ?>
                            </span>
                            <h3>
                                <?php echo escaped($inquiry['package_interest']); ?>
                            </h3>
                        </div>
                        <span class="status-badge status-<?php echo escaped(statusClass($inquiry['status'])); ?>">
                            <?php echo escaped($inquiry['status']); ?>
                        </span>
                    </div>
                    <div class="booking-meta">
                        <span>
                            <?php echo escaped((string) $inquiry['target_event_date']); ?>
                        </span>
                        <span>
                            <?php echo escaped($inquiry['venue']); ?>
                        </span>
                        <strong>
                            <?php echo money($estimatedCost); ?>
                        </strong>
                    </div>
                    <details class="receipt-details">
                        <summary>View Itemized Receipt</summary>
                        <div class="receipt-content">
                            <div class="receipt-columns">
                                <div>
                                    <h4>Event Profile</h4>
                                    <p>
                                        <?php echo escaped($inquiry['event_type']); ?>
                                        <br>
                                        <?php echo escaped((string) $inquiry['target_event_date']); ?> at <?php echo escaped((string) $inquiry['event_start_time']); ?>
                                        <br>
                                        <?php echo escaped($inquiry['venue']); ?>
                                        <br>
                                        <?php echo escaped($inquiry['venue_type']); ?>
                                        <br>
                                        <?php echo (int) $inquiry['guest_count']; ?> estimated guests</p>
                                    </div>
                                    <div>
                                        <h4>Program Notes</h4>
                                        <p>
                                            <?php echo nl2br(escaped((string) ($inquiry['special_requests'] ?: $inquiry['message']))); ?>
                                        </p>
                                    </div>
                                </div>
                                <h4>Itemized Services</h4>
                                <ul class="receipt-items">
                                    <li>
                                        <span>
                                            <?php echo escaped($inquiry['package_interest']); ?>
                                        </span>
                                        <strong>
                                            <?php echo money($estimatedCost); ?>
                                        </strong>
                                    </li>
                                    <?php foreach ($services as $service): ?>
                                        <li>
                                            <span>
                                                <?php echo escaped((string) $service); ?>
                                            </span>
                                            <strong>Included / TBC</strong>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="financial-summary">
                                    <span>Total Estimated Investment <strong>
                                        <?php echo money($estimatedCost); ?>
                                    </strong>
                                </span>
                            </div>
                        </div>
                    </details>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
</main>
</body>
</html>
