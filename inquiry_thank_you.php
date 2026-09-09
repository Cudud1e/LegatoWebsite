<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/inquiry_access.php';
$referenceNo = trim((string) ($_GET['ref'] ?? ''));
$successMessage = $_SESSION['inquiry_success'] ?? 'Your inquiry has been sent successfully!';
unset($_SESSION['inquiry_success']);
$user = $_SESSION['user'] ?? [];
$inquiry = null;
$accessScope = inquiryAccessScope($referenceNo, $user, $_SESSION['last_inquiry_reference'] ?? null);
if ($referenceNo !== '' && $accessScope !== null) {
    try {
        $pdo = getDatabaseConnection();
        if (isset($accessScope['user_id'])) {
            $statement = $pdo->prepare('SELECT reference_no, target_event_date, event_start_time, package_interest, requested_services, special_requests, venue, guest_count, status, COALESCE(total_amount, estimated_cost, 0) AS estimated_cost FROM inquiries WHERE reference_no = ? AND user_id = ?');
            $statement->execute([$referenceNo, $accessScope['user_id']]);
        } elseif (isset($accessScope['guest'])) {
            $statement = $pdo->prepare('SELECT reference_no, target_event_date, event_start_time, package_interest, requested_services, special_requests, venue, guest_count, status, COALESCE(total_amount, estimated_cost, 0) AS estimated_cost FROM inquiries WHERE reference_no = ? AND user_id IS NULL');
            $statement->execute([$referenceNo]);
        }
        $inquiry = $statement->fetch();
    } catch (PDOException $exception) {
        $inquiry = false;
    }
}
$notFound = !$inquiry;
if ($notFound) {
    http_response_code(404);
}
$services = $inquiry ? (json_decode((string) $inquiry['requested_services'], true) ?: []) : [];
function escaped(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
$pageTitle = 'Inquiry Received | LEGATO Events & Productions';
require_once __DIR__ . '/includes/header.php';
?>
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
            <h1>Your Inquiry Has Been <em>Received.</em>
        </h1>
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
                    <strong>#<?php echo escaped((string) $inquiry['reference_no']); ?>
                    </strong>
                </div>
                <div>
                    <span>Event Date</span>
                    <strong>
                        <?php echo escaped($inquiry['target_event_date'] ?: 'Not provided'); ?>
                    </strong>
                </div>
                <div>
                    <span>Package / Services</span>
                    <strong>
                        <?php echo escaped((string) $inquiry['package_interest']); ?>
                        <?php if ($services): ?>
                            <small>
                                <?php echo escaped(implode(', ', $services)); ?>
                            </small>
                        <?php endif; ?>
                    </strong>
                </div>
                <div>
                    <span>Event Location</span>
                    <strong>
                        <?php echo escaped((string) $inquiry['venue']); ?>
                    </strong>
                </div>
                <div>
                    <span>Estimated Guests</span>
                    <strong>
                        <?php echo (int) $inquiry['guest_count']; ?>
                    </strong>
                </div>
                <div>
                    <span>Estimated Investment</span>
                    <strong>₱<?php echo number_format((float) $inquiry['estimated_cost'], 2); ?>
                    </strong>
                </div>
            </div>
        </div>
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
                    <p>Review your tailored quote and confirm booking with an initial deposit.</p>
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
        <p class="urgent-contact">Need immediate assistance? Contact our event director directly at <a href="tel:+639000000000">+63 9XX XXX XXXX</a> or <a href="mailto:info@legatoevents.com">info@legatoevents.com</a>.</p>
    </div>
</section>
    <?php endif; ?>
</main>
