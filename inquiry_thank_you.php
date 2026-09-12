<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/inquiry_access.php';

$referenceNo = trim((string) ($_GET['ref'] ?? ''));
$successMessage = $_SESSION['inquiry_success'] ?? 'Your inquiry has been sent successfully!';
unset($_SESSION['inquiry_success']);

$user = $_SESSION['user'] ?? [];
$isLoggedIn = isset($user['id']);
$sessionInquiry = $_SESSION['latest_inquiry'] ?? null;
$inquiry = null;

$accessScope = inquiryAccessScope($referenceNo, $user, $_SESSION['last_inquiry_reference'] ?? null);

if ($referenceNo !== '' && $accessScope !== null) {
    try {
        $pdo = getDatabaseConnection();
        if (isset($accessScope['user_id'])) {
            $statement = $pdo->prepare('SELECT reference_no, name, target_event_date, event_start_time, package_interest, payment_method, payment_reference, receipt_path, downpayment_amount, remaining_balance, deposit_status, requested_services, special_requests, venue, guest_count, status, COALESCE(NULLIF(estimated_total, 0), total_amount, estimated_cost, 0) AS estimated_total, COALESCE(total_amount, estimated_cost, 0) AS estimated_cost FROM inquiries WHERE reference_no = ? AND user_id = ?');
            $statement->execute([$referenceNo, $accessScope['user_id']]);
        } elseif (isset($accessScope['guest'])) {
            $statement = $pdo->prepare('SELECT reference_no, name, target_event_date, event_start_time, package_interest, payment_method, payment_reference, receipt_path, downpayment_amount, remaining_balance, deposit_status, requested_services, special_requests, venue, guest_count, status, COALESCE(NULLIF(estimated_total, 0), total_amount, estimated_cost, 0) AS estimated_total, COALESCE(total_amount, estimated_cost, 0) AS estimated_cost FROM inquiries WHERE reference_no = ? AND user_id IS NULL');
            $statement->execute([$referenceNo]);
        }
        $inquiry = $statement->fetch();
    } catch (PDOException $exception) {
        $inquiry = false;
    }
}

$isLatestInquiry = is_array($sessionInquiry) && isset($sessionInquiry['id']);

if (!$inquiry && $isLatestInquiry) {
    $inquiry = [
        'reference_no' => (string) ($sessionInquiry['reference_no'] ?? ('LGT-' . $sessionInquiry['id'])),
        'name' => (string) ($sessionInquiry['name'] ?? ''),
        'target_event_date' => (string) ($sessionInquiry['date'] ?? ''),
        'event_start_time' => '',
        'package_interest' => (string) ($sessionInquiry['package'] ?? ''),
        'payment_method' => (string) ($sessionInquiry['payment_method'] ?? 'Online Payment'),
        'payment_reference' => '',
        'receipt_path' => '',
        'downpayment_amount' => (float) ($sessionInquiry['downpayment_amount'] ?? 0),
        'remaining_balance' => (float) ($sessionInquiry['remaining_balance'] ?? 0),
        'deposit_status' => (string) ($sessionInquiry['deposit_status'] ?? 'Pending Review'),
        'requested_services' => '[]',
        'special_requests' => '',
        'venue' => '',
        'guest_count' => 0,
        'status' => (string) ($sessionInquiry['status'] ?? 'Pending Review'),
        'estimated_total' => (float) ($sessionInquiry['total'] ?? 0),
        'estimated_cost' => (float) ($sessionInquiry['total'] ?? 0),
    ];
}

$notFound = !$inquiry;
if ($notFound) {
    http_response_code(404);
}

$services = $inquiry ? (json_decode((string) $inquiry['requested_services'], true) ?: []) : [];
$estimatedTotal = (float) ($inquiry['estimated_total'] ?? $inquiry['estimated_cost'] ?? 0);

if ($inquiry) {
    if (!isset($inquiry['downpayment_amount']) || (float) $inquiry['downpayment_amount'] <= 0) {
        $inquiry['downpayment_amount'] = $estimatedTotal * 0.50;
    }
    if (!isset($inquiry['remaining_balance']) || (float) $inquiry['remaining_balance'] <= 0) {
        $inquiry['remaining_balance'] = $estimatedTotal - (float) $inquiry['downpayment_amount'];
    }
}

function escaped(mixed $value): string { 
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8'); 
}

$pageTitle = 'Inquiry Received | LEGATO Events & Productions';
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
                    <a href="login.php" class="nav-login">Log In</a>
                <?php endif; ?>
                <button class="menu-button" id="menuButton" aria-label="Open menu">&#9776;</button>
            </div>
        </div>
    </header>

    <main>
        <?php if ($notFound): ?>
            <section class="page-hero thank-you-hero">
                <div class="container">
                    <p class="section-label">INQUIRY NOT FOUND</p>
                    <h1>We could not find <em>that inquiry.</em></h1>
                    <p>The reference may be missing, expired, or may not belong to the current account.</p>
                    <a class="btn btn-gold" href="index.php">Return to Home</a>
                </div>
            </section>
        <?php else: ?>
            <section class="page-hero thank-you-hero">
                <div class="container">
                    <p class="section-label">LEGATO CLIENT PORTAL</p>
                    <h1>Your Inquiry Has Been <em>Received.</em></h1>
                    <p>Thank you for considering LEGATO Events &amp; Productions. Our team will review your requirements and send tailored availability and a custom proposal within 24 hours.</p>
                </div>
            </section>

            <section class="inner-section inquiry-confirmation">
                <div class="container">
                    <div class="inquiry-summary">
                        <p class="section-label">INQUIRY SUMMARY</p>
                        <div class="summary-grid">
                            <div>
                                <span>Reference Number</span>
                                <strong>#<?php echo escaped((string) $inquiry['reference_no']); ?></strong>
                            </div>
                            <div>
                                <span>Client Name</span>
                                <strong><?php echo escaped((string) ($inquiry['name'] ?: 'Not provided')); ?></strong>
                            </div>
                            <div>
                                <span>Event Date</span>
                                <strong><?php echo escaped($inquiry['target_event_date'] ?: 'Not provided'); ?></strong>
                            </div>
                            <div>
                                <span>Package / Services</span>
                                <strong>
                                    <?php echo escaped((string) $inquiry['package_interest']); ?>
                                    <?php if ($services): ?>
                                        <small><?php echo escaped(implode(', ', $services)); ?></small>
                                    <?php endif; ?>
                                </strong>
                            </div>
                            <div>
                                <span>Event Location</span>
                                <strong><?php echo escaped((string) $inquiry['venue']); ?></strong>
                            </div>
                            <div>
                                <span>Estimated Guests</span>
                                <strong><?php echo (int) $inquiry['guest_count']; ?></strong>
                            </div>
                            <div>
                                <span>Estimated Investment</span>
                                <strong>₱<?php echo number_format($estimatedTotal, 2); ?></strong>
                            </div>
                            <div>
                                <span>50% Down Payment Required</span>
                                <strong>₱<?php echo number_format((float) $inquiry['downpayment_amount'], 2); ?></strong>
                            </div>
                            <div>
                                <span>Current Status</span>
                                <strong class="status-badge status-pending-review"><?php echo escaped((string) ($inquiry['status'] ?: 'Pending Review')); ?></strong>
                            </div>
                            <div>
                                <span>Preferred Down Payment Method</span>
                                <strong><?php echo escaped((string) ($inquiry['payment_method'] ?: 'Not provided')); ?></strong>
                            </div>
                            <div>
                                <span>Payment Verification</span>
                                <strong><?php echo escaped((string) ($inquiry['deposit_status'] ?: 'Pending Review')); ?></strong>
                            </div>
                        </div>
                    </div>

                    <?php if (($inquiry['payment_method'] ?? '') === 'Online Payment'): ?>
                        <section class="payment-instructions">
                            <p class="section-label">ONLINE PAYMENT INSTRUCTIONS</p>
                            <h2>Your online payment is awaiting verification.</h2>
                            <p>We received your payment reference<?php echo !empty($inquiry['payment_reference']) ? ' <strong>' . escaped((string) $inquiry['payment_reference']) . '</strong>' : ''; ?>. Our team will verify the receipt, equipment availability, and technical manpower within 24 hours before locking in your date.</p>
                            <div class="payment-method-grid">
                                <article>
                                    <h3>GCash</h3>
                                    <p>Account Name: LEGATO Events &amp; Productions</p>
                                    <p>Account Number: To be provided on your approved invoice</p>
                                    <span>QR Code supplied with invoice</span>
                                </article>
                                <article>
                                    <h3>Maya</h3>
                                    <p>Account Name: LEGATO Events &amp; Productions</p>
                                    <p>Account Number: To be provided on your approved invoice</p>
                                    <span>QR Code supplied with invoice</span>
                                </article>
                                <article>
                                    <h3>Bank Transfer — BDO / PNB</h3>
                                    <p>Account Name: LEGATO Events &amp; Productions</p>
                                    <p>Account Number: To be provided on your approved invoice</p>
                                    <span>Bank details supplied with invoice</span>
                                </article>
                            </div>
                        </section>
                    <?php else: ?>
                        <section class="payment-instructions">
                            <p class="section-label">IN-PERSON PAYMENT &amp; OFFICE DETAILS</p>
                            <h2>Visit us when your inquiry is approved.</h2>
                            <div class="office-payment-card">
                                <p><strong>LEGATO Main Office — Dumaguete City, Negros Oriental</strong></p>
                                <p>Monday to Saturday, 9:00 AM to 6:00 PM</p>
                            </div>
                            <p>Our team will review your request within 24 hours. Once approved, you may visit our office within 3 business days to settle the 50% down payment and sign the production agreement.</p>
                        </section>
                    <?php endif; ?>

                    <div class="next-steps">
                        <p class="section-label">WHAT HAPPENS NEXT</p>
                        <div class="steps-grid">
                            <article>
                                <b>01</b>
                                <h2>Proposal Review</h2>
                                <p>Our production team verifies technical needs and venue logistics.</p>
                            </article>
                            <article>
                                <b>02</b>
                                <h2>Date Reservation</h2>
                                <p>We place a temporary 48-hour hold on your requested date.</p>
                            </article>
                            <article>
                                <b>03</b>
                                <h2>Consultation &amp; Deposit</h2>
                                <p>Once your invoice is approved, settle the 50% down payment using your selected method to confirm the reservation.</p>
                            </article>
                        </div>
                    </div>

                    <div class="thank-you-actions">
                        <a class="btn btn-gold" href="index.php">Return to Home</a>
                        <a class="btn btn-outline" href="profile.php">View My Dashboard / Inquiries</a>
                    </div>

                    <p class="form-message" role="status">
                        <?php echo escaped($successMessage); ?>
                    </p>

                    <p class="urgent-contact">Need immediate assistance? Contact our event director directly at <a href="tel:+639000000000">++63 917 123 4567</a> or <a href="mailto:info@legatoevents.com">info@legatoevents.com</a>.</p>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <footer id="contact" class="site-footer">
        <div class="footer-container">
            <div class="footer-column footer-brand-section">
                <a href="index.php" class="footer-logo inline-block transition-transform duration-200 hover:scale-105">
                    <img class="footer-custom-logo h-12 sm:h-14 w-auto object-contain" src="Assest/legato1.png" alt="LEGATO Events &amp; Productions">
                </a>
                <p class="footer-tagline">Where flawless production meets unforgettable celebration.</p>
            </div>
            <div class="footer-column">
                <p class="footer-title">Quick Links</p>
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="about.php">About Us</a>
                    <a href="packages.php">VIP Packages</a>
                    <a href="custom.php">Custom Services</a>
                    <a href="terms.php">Terms &amp; Conditions</a>
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="business_info.php">Business Info</a>
                </div>
            </div>
            <div class="footer-column">
                <p class="footer-title">Get In Touch</p>
                <div class="footer-links">
                    <span>Dumaguete City, Negros Oriental, Philippines 6200</span>
                    <a href="mailto:info@legatoevents.com">info@legatoevents.com</a>
                    <a href="tel:+639000000000">+63 917 123 4567</a>
                    <span>Monday to Saturday, 9:00 AM to 6:00 PM</span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <span>&copy; 2026 LEGATO Events &amp; Productions. All Rights Reserved.</span>
                <span>Dumaguete &middot; Negros Oriental</span>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>