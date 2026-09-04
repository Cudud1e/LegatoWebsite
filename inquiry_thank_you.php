<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db.php';

$inquiryId = filter_var($_GET['inquiry_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$user = $_SESSION['user'] ?? [];
$inquiry = null;

if ($inquiryId) {
    try {
        $pdo = getDatabaseConnection();
        if (isset($user['id'])) {
            $statement = $pdo->prepare('SELECT id, target_event_date, package_interest, requested_services, venue FROM inquiries WHERE id = ? AND user_id = ?');
            $statement->execute([$inquiryId, $user['id']]);
        } elseif (($inquiryId === (int) ($_SESSION['last_inquiry_id'] ?? 0))) {
            $statement = $pdo->prepare('SELECT id, target_event_date, package_interest, requested_services, venue FROM inquiries WHERE id = ? AND user_id IS NULL');
            $statement->execute([$inquiryId]);
        }
        $inquiry = isset($statement) ? $statement->fetch() : false;
    } catch (PDOException $exception) {
        $inquiry = false;
    }
}

if (!$inquiry) {
    http_response_code(404);
    $inquiry = ['id' => $inquiryId ?: 'Unavailable', 'target_event_date' => null, 'package_interest' => 'Unavailable', 'requested_services' => '[]', 'venue' => 'Unavailable'];
}

$services = json_decode((string) $inquiry['requested_services'], true) ?: [];
function escaped(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
$pageTitle = 'Inquiry Received | LEGATO Events & Productions';
require_once __DIR__ . '/includes/header.php';
?>
<main>
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
          <div><span>Reference Number</span><strong>LEG-<?php echo escaped((string) $inquiry['id']); ?></strong></div>
          <div><span>Event Date</span><strong><?php echo escaped($inquiry['target_event_date'] ?: 'Not provided'); ?></strong></div>
          <div><span>Package / Services</span><strong><?php echo escaped((string) $inquiry['package_interest']); ?><?php if ($services): ?><small><?php echo escaped(implode(', ', $services)); ?></small><?php endif; ?></strong></div>
          <div><span>Event Location</span><strong><?php echo escaped((string) $inquiry['venue']); ?></strong></div>
        </div>
      </div>
      <div class="next-steps">
        <p class="section-label">WHAT HAPPENS NEXT</p>
        <div class="steps-grid">
          <article><b>01</b><h2>Proposal Review</h2><p>Our production team verifies technical needs and venue logistics.</p></article>
          <article><b>02</b><h2>Date Reservation</h2><p>We place a temporary 48-hour hold on your requested date.</p></article>
          <article><b>03</b><h2>Consultation &amp; Deposit</h2><p>Review your tailored quote and confirm booking with an initial deposit.</p></article>
        </div>
      </div>
      <div class="thank-you-actions"><a class="btn btn-gold" href="index.php">Return to Home</a><a class="btn btn-outline" href="profile.php">View My Dashboard / Inquiries</a></div>
      <p class="urgent-contact">Need immediate assistance? Contact our event director directly at <a href="tel:+639000000000">+63 9XX XXX XXXX</a> or <a href="mailto:info@legatoevents.com">info@legatoevents.com</a>.</p>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
