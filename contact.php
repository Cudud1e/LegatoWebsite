<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/csrf.php';
$sessionUser = $_SESSION['user'] ?? [];
$isBookingPage = $isBookingPage ?? false;
$message = '';
$error = $_SESSION['inquiry_error'] ?? '';
unset($_SESSION['inquiry_error']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/process_inquiry.php';
    exit;
}
$name = trim($_POST['name'] ?? ($sessionUser['full_name'] ?? $_SESSION['user_full_name'] ?? ''));
$emailInput = trim($_POST['email'] ?? ($sessionUser['email'] ?? $_SESSION['user_email'] ?? ''));
$phone = trim($_POST['phone'] ?? ($sessionUser['phone'] ?? $_SESSION['user_phone'] ?? ''));
$eventType = trim($_POST['event_type'] ?? '');
$eventDate = trim($_POST['target_event_date'] ?? '');
$eventStartTime = trim($_POST['event_start_time'] ?? '');
$setupAccessTime = trim($_POST['setup_access_time'] ?? '');
$venue = trim($_POST['venue'] ?? ($sessionUser['location'] ?? $_SESSION['user_location'] ?? ''));
$venueType = trim($_POST['venue_type'] ?? '');
$guestCount = (int) ($_POST['guest_count'] ?? 0);
$packageInterest = trim($_POST['package_interest'] ?? '');
$selectedPackage = trim($_GET['package'] ?? '');
$packageMap = ['VIP1' => 'VIP 1: Elite Starter', 'VIP2' => 'VIP 2: Prestige', 'VIP3' => 'VIP 3: Grand Luxe', 'Custom Build' => 'Custom Build'];
if ($packageInterest === '' && isset($packageMap[$selectedPackage])) {
    $packageInterest = $packageMap[$selectedPackage];
}
$budgetRange = trim($_POST['budget_range'] ?? '');
$inquiry = trim($_POST['message'] ?? '');
$specialRequests = trim($_POST['special_requests'] ?? '');
$customServices = trim((string) ($_POST['custom_services'] ?? $_POST['services'] ?? $_GET['custom_services'] ?? $_GET['services'] ?? ''));
$customDisplayedTotal = (float) ($_POST['total_amount'] ?? $_POST['total'] ?? $_GET['total_amount'] ?? $_GET['total'] ?? 0);
$customSelections = [];
if ($customServices !== '') {
  $decodedCustomServices = json_decode($customServices, true);
  if (is_array($decodedCustomServices)) {
    foreach ($decodedCustomServices as $service => $tier) {
      if (is_string($service) && is_string($tier) && $service !== '' && $tier !== '') {
        $customSelections[$service] = $tier;
      }
    }
  }
}
$allowedServices = ['Audio System & Sound Tech', 'Stage Lighting & Moving Heads', 'LED Video Wall', 'Professional EMCEE / Host', 'Full Event Coordination', 'Photo & Video Coverage', 'Stage Setup', 'Ambient Uplighting', 'LED DJ Booth', 'Standby Generator Power'];
$services = array_values(array_intersect(array_values(array_filter($_POST['services'] ?? [], 'is_string')), $allowedServices));
$eventTypes = ['Wedding', 'Birthday/Debut', 'Corporate Event', 'School Gala', 'Private Party', 'Other'];
$venueTypes = ['Indoor Ballroom / Hotel', 'Outdoor Garden', 'Outdoor Beach', 'Private Residence'];
$packages = ['VIP 1: Elite Starter', 'VIP 2: Prestige', 'VIP 3: Grand Luxe', 'Custom Build', 'Unsure / Need Guidance'];
function escaped(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function selected(string $actual, string $expected): string { return $actual === $expected ? ' selected' : ''; }
function checked(string $value, array $values): string { return in_array($value, $values, true) ? ' checked' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $isBookingPage ? 'Book an Event' : 'Contact'; ?> | LEGATO Events &amp; Productions</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
    </head>
    <body class="inner-page">
        <header class="navbar">
            <div class="container nav-content">
                <a href="index.php" class="brand">
                    <img class="custom-logo h-16 sm:h-20 lg:h-24 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events & Productions">
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
                    <div id="authNav" class="auth-nav">
                    </div>
                    <button class="menu-button" id="menuButton" aria-label="Open menu">☰</button>
                </div>
            </div>
        </header>
        <main>
            <section class="page-hero">
                <div class="container">
                    <p class="section-label">LET'S CREATE SOMETHING BEAUTIFUL</p>
                    <h1>Bring us your <em>occasion.</em>
                </h1>
                <p>Tell us what you are planning and our team will help shape the right production experience.</p>
            </div>
        </section>
        <section class="inner-section">
            <div class="container contact-grid">
                <div class="contact-intro">
                    <p class="section-label">START A CONVERSATION</p>
                    <h2>We are ready when you are.</h2>
                    <p>Share the details below for a more accurate first quote and package recommendation.</p>
                    <div class="contact-details">
                        <div>
                            <span class="section-label">RESPONSE TIME</span>
                            <span>We typically respond with tailored availability and proposals within 24 hours.</span>
                        </div>
                        <div>
                            <span class="section-label">OFFICE / BASE</span>
                            <span>Dumaguete City, Negros Oriental</span>
                        </div>
                        <div>
                            <span class="section-label">DIRECT CONTACT</span>
                            <a href="tel:+639000000000">Call / Viber: +63 9XX XXX XXXX</a>
                            <a href="https://wa.me/639000000000">WhatsApp</a>
                            <a href="https://m.me/legatoevents">Messenger</a>
                        </div>
                        <div>
                            <span class="section-label">EMAIL</span>
                            <a href="mailto:info@legatoevents.com?subject=LEGATO%20Event%20Inquiry">info@legatoevents.com</a>
                        </div>
                    </div>
                </div>
                <form class="contact-form" action="process_inquiry.php" method="POST" id="inquiryForm">
                    <input type="hidden" name="csrf_token" value="<?php echo escaped(csrfToken()); ?>">
                    <fieldset class="form-section">
                        <legend>
                            <span>01</span> Contact Information</legend>
                            <div class="form-fields">
                                <label for="name">Your name<input id="name" name="full_name" type="text" required value="<?php echo escaped($name); ?>">
                                </label>
                                <label for="email">Email address<input id="email" name="email" type="email" required value="<?php echo escaped($emailInput); ?>">
                                </label>
                                <label for="phone">Phone Number / Viber<input id="phone" name="phone" type="tel" required value="<?php echo escaped($phone); ?>">
                                </label>
                            </div>
                        </fieldset>
                        <fieldset class="form-section">
                            <legend>
                                <span>02</span> Event Logistics &amp; Venue</legend>
                                <div class="form-fields">
                                    <label for="eventType">Event type<select id="eventType" name="event_type" required>
                                        <option value="">Select event type</option>
                                        <?php foreach ($eventTypes as $option): ?>
                                            <option value="<?php echo escaped($option); ?>"<?php echo selected($eventType, $option); ?>>
                                            <?php echo escaped($option); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label for="eventDate">Target event date<input id="eventDate" name="target_event_date" type="date" min="<?php echo date('Y-m-d'); ?>" required value="<?php echo escaped($eventDate); ?>">
                            </label>
                            <label for="eventStartTime">Preferred event start time<input id="eventStartTime" name="event_start_time" type="time" required value="<?php echo escaped($eventStartTime); ?>">
                            </label>
                            <label for="setupAccessTime">Venue ingress / setup access<input id="setupAccessTime" name="setup_access_time" type="time" required value="<?php echo escaped($setupAccessTime); ?>">
                            </label>
                            <label for="venue">Venue / location<input id="venue" name="venue" type="text" required value="<?php echo escaped($venue); ?>" placeholder="City or town in Negros Oriental">
                            </label>
                            <label for="venueType">Venue type<select id="venueType" name="venue_type" required>
                                <option value="">Select venue type</option>
                                <?php foreach ($venueTypes as $option): ?>
                                    <option value="<?php echo escaped($option); ?>"<?php echo selected($venueType, $option); ?>>
                                    <?php echo escaped($option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label for="guestCount">Estimated guest count<input id="guestCount" name="guest_count" type="number" min="1" required value="<?php echo $guestCount ?: ''; ?>">
                    </label>
                </div>
            </fieldset>
            <fieldset class="form-section">
                <legend>
                    <span>03</span> Package &amp; Production Preferences</legend>
                    <div class="form-fields">
                        <label for="packageInterest">Package interest<select id="packageInterest" name="package_interest" required>
                            <option value="">Select a starting preference</option>
                            <?php foreach ($packages as $option): ?>
                                <option value="<?php echo escaped($option); ?>"<?php echo selected($packageInterest, $option); ?>>
                                <?php echo escaped($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label for="budgetRange">Estimated budget range <small>(optional)</small>
                <select id="budgetRange" name="budget_range">
                    <option value="">Prefer not to say</option>
                    <option value="Under ₱50k"<?php echo selected($budgetRange, 'Under ₱50k'); ?>>Under ₱50k</option>
                    <option value="₱50k–₱100k"<?php echo selected($budgetRange, '₱50k–₱100k'); ?>>₱50k–₱100k</option>
                    <option value="₱100k+"<?php echo selected($budgetRange, '₱100k+'); ?>>₱100k+</option>
                </select>
            </label>
        </div>
        <input type="hidden" name="custom_services" value="<?php echo escaped($customServices); ?>">
        <div class="booking-estimate">Estimated investment: <strong>₱<?php echo number_format($customDisplayedTotal, 2); ?>
        </strong>
    </div>
    <?php if ($customSelections): ?>
        <div class="choice-group">
            <span class="choice-label">Selected custom tiers</span>
            <div class="choice-grid">
                <?php foreach ($customSelections as $service => $tier): ?>
                    <label class="choice-card">
                        <input type="checkbox" checked disabled>
                        <span>
                            <?php echo escaped($service); ?> <strong>
                            <?php echo escaped($tier); ?>
                        </strong>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
<div class="choice-group">
    <span class="choice-label">Additional services you may need <small>(optional)</small>
</span>
<div class="choice-grid">
    <?php foreach ($allowedServices as $service): ?>
        <label class="choice-card">
            <input type="checkbox" name="services[]" value="<?php echo escaped($service); ?>"<?php echo checked($service, $services); ?>>
            <span>
                <?php echo escaped($service); ?>
            </span>
        </label>
    <?php endforeach; ?>
</div>
</div>
<label for="message">Tell us about your event<textarea id="message" name="notes" rows="4" required>
    <?php echo escaped($inquiry); ?>
</textarea>
</label>
<label for="specialRequests">Special notes / production requests <small>(optional)</small>
<textarea id="specialRequests" name="special_requests" rows="4" placeholder="Artist requests, program flow, or custom sound requirements">
    <?php echo escaped($specialRequests); ?>
</textarea>
</label>
</fieldset>
<button type="submit" name="submit_inquiry" class="btn btn-gold form-submit">Send Inquiry</button>
<p class="form-message" role="status">
    <?php echo escaped($error ?: $message); ?>
</p>
</form>
</div>
</section>
</main>
<footer id="contact" class="site-footer">
    <div class="footer-container">
        <div class="footer-column footer-brand-section">
            <a href="index.php" class="brand">
                <img class="footer-custom-logo h-12 sm:h-14 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events & Productions">
            </a>
            <p class="footer-tagline">Where flawless production meets unforgettable celebration.</p>
        </div>
        <div class="footer-column">
            <p class="footer-title">Quick Links</p>
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
        <div class="footer-column">
            <p class="footer-title">Get In Touch</p>
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
            <span>&copy; 2026 LEGATO Events &amp; Productions. All Rights Reserved.</span>
            <span>Dumaguete &middot; Negros Oriental</span>
        </div>
    </div>
</footer>
<script src="script.js">
</script>
</body>
</html>
