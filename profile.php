<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
if (!isset($_SESSION['user']['id'])) { header('Location: login.php?redirect=profile.php'); exit; }
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/invoice_schema.php';
$userId = (int) $_SESSION['user']['id'];
$message = '';
$error = '';
try {
    $pdo = getDatabaseConnection();
    ensureInvoiceSchema($pdo);
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
    $history = $pdo->prepare("SELECT i.reference_no, i.event_type, i.target_event_date, i.event_start_time, i.venue, i.venue_type, i.guest_count, i.package_interest, i.budget_range, i.requested_services, i.special_requests, i.message, i.status, i.payment_reference, i.receipt_path, COALESCE(i.total_amount, i.estimated_cost, 0) AS estimated_cost, i.created_at, inv.id AS invoice_id, COALESCE(NULLIF(inv.amount_due, 0), inv.down_payment_amount, 0) AS invoice_amount_due, inv.amount_paid AS invoice_amount_paid, inv.remaining_balance AS invoice_remaining_balance, inv.payment_date AS invoice_payment_date, inv.downpayment_date AS invoice_downpayment_date, inv.final_payment_date AS invoice_final_payment_date, inv.verified_by_admin AS invoice_verified_by_admin, inv.due_date AS invoice_due_date, inv.status AS invoice_status, inv.public_token AS invoice_token FROM inquiries i LEFT JOIN invoices inv ON inv.inquiry_id = i.id AND LOWER(COALESCE(inv.status, '')) IN ('pending payment', 'unpaid', 'paid', 'down payment paid', '50% paid', 'fully paid') AND inv.percentage = 50 WHERE i.user_id = ? ORDER BY i.created_at DESC");
    $history->execute([$userId]);
    $inquiries = $history->fetchAll();
    $createInvoiceToken = $pdo->prepare('UPDATE invoices inv JOIN inquiries i ON i.id = inv.inquiry_id SET inv.public_token = ? WHERE inv.id = ? AND i.user_id = ? AND inv.public_token IS NULL');
    foreach ($inquiries as &$inquiry) {
        if (!empty($inquiry['invoice_id']) && empty($inquiry['invoice_token'])) {
            $token = bin2hex(random_bytes(32));
            $createInvoiceToken->execute([$token, $inquiry['invoice_id'], $userId]);
            if ($createInvoiceToken->rowCount() === 1) $inquiry['invoice_token'] = $token;
        }
    }
    unset($inquiry);
} catch (PDOException $exception) {
    $error = 'Your account information is temporarily unavailable. Please try again later.';
    $account = ['full_name' => '', 'nickname' => '', 'email' => '', 'phone' => '', 'location' => '', 'created_at' => 'now'];
    $inquiries = [];
}
$packagePrices = ['VIP 1: Elite Starter' => 49999, 'VIP 2: Prestige' => 79999, 'VIP 3: Grand Luxe' => 179999];
$isLoggedIn = isset($_SESSION['user']['id']);
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
        <script src="https://cdn.tailwindcss.com"></script>
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
                <?php else: ?>
                    <a href="login.php" class="nav-btn">Log In</a>
                <?php endif; ?>
                <button class="menu-button" id="menuButton" aria-label="Open menu">&#9776;</button>
            </div>
        </div>
    </header>
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
            <?php foreach ($inquiries as $inquiry): $services = json_decode((string) ($inquiry['requested_services'] ?? '[]'), true) ?: []; $estimatedCost = (float) ($inquiry['estimated_cost'] ?? 0) ?: ($packagePrices[$inquiry['package_interest'] ?? ''] ?? 0); $hasInvoice = !empty($inquiry['invoice_id']) && !empty($inquiry['invoice_token']); $invoiceState = strtolower((string) ($inquiry['invoice_status'] ?? '')); $isFullyPaid = $hasInvoice && $invoiceState === 'fully paid' && (int) ($inquiry['invoice_verified_by_admin'] ?? 0) === 1; $isFullPending = $hasInvoice && !$isFullyPaid && in_array($invoiceState, ['paid', 'down payment paid', '50% paid'], true); $isFiftyPaid = $hasInvoice && !$isFullyPaid && in_array($invoiceState, ['paid', 'down payment paid', '50% paid'], true); $hasPendingInvoice = $hasInvoice && !$isFiftyPaid && !$isFullyPaid; $invoiceUrl = $hasInvoice ? 'view_invoice.php?id=' . (int) $inquiry['invoice_id'] . '&token=' . rawurlencode((string) $inquiry['invoice_token']) : ''; $receiptUrl = $hasInvoice ? 'view_receipt.php?id=' . (int) $inquiry['invoice_id'] . '&token=' . rawurlencode((string) $inquiry['invoice_token']) : ''; ?>
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
                    <?php if ($isFullPending): ?>
                        <section class="mt-5 rounded-xl border border-[#F59E0B]/50 bg-[#121212] p-4 text-[#F5F2EB]">
                            <span class="inline-flex rounded-full bg-[#F59E0B]/10 px-2.5 py-1 font-mono text-[10px] font-semibold uppercase tracking-wider text-[#F59E0B]">Full Payment Verification Pending</span>
                            <p class="mt-3 text-sm text-[#9CA3AF]">Your final balance submission is awaiting the LEGATO team’s account-clearance verification.</p>
                        </section>
                    <?php elseif ($isFullyPaid): ?>
                        <section class="mt-5 rounded-xl border border-[#10B981]/50 bg-[#121212] p-4 text-[#F5F2EB] sm:p-5">
                            <span class="inline-flex rounded-full bg-[#10B981]/10 px-2.5 py-1 font-mono text-[10px] font-semibold uppercase tracking-wider text-[#D4AF37]">100% Fully Paid &amp; Cleared</span>
                            <p class="mt-3 text-sm text-[#9CA3AF]">Your event account has been settled and officially closed.</p>
                            <div class="mt-5 grid gap-3 border-y border-[#282828] py-4 text-sm sm:grid-cols-3"><div><span class="block font-mono text-[10px] uppercase tracking-wider text-[#6B7280]">Initial 50% downpayment</span><strong class="mt-1 block font-mono text-[#10B981]">Paid · <?php echo !empty($inquiry['invoice_downpayment_date']) ? escaped(date('M d, Y', strtotime((string) $inquiry['invoice_downpayment_date']))) : 'Verified'; ?></strong></div><div><span class="block font-mono text-[10px] uppercase tracking-wider text-[#6B7280]">Final 50% balance</span><strong class="mt-1 block font-mono text-[#10B981]">Paid &amp; Verified · <?php echo !empty($inquiry['invoice_final_payment_date']) ? escaped(date('M d, Y', strtotime((string) $inquiry['invoice_final_payment_date']))) : 'Verified'; ?></strong></div><div><span class="block font-mono text-[10px] uppercase tracking-wider text-[#6B7280]">Remaining balance</span><strong class="mt-1 block font-mono text-[#10B981]">&#8369;0.00</strong></div></div>
                            <div class="mt-4 flex justify-end"><a href="<?php echo escaped($receiptUrl); ?>&amp;type=full" target="_blank" rel="noopener noreferrer" class="rounded bg-[#D4AF37] px-4 py-2.5 text-center text-xs font-bold text-[#121212] transition hover:bg-white">Download Full Official Receipt</a></div>
                        </section>
                    <?php endif; ?>
                    <?php if ($isFiftyPaid): ?>
                        <section class="mt-5 rounded-xl border border-[#10B981]/50 bg-[#121212] p-4 text-[#F5F2EB] sm:p-5" aria-label="50 percent payment confirmed">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div><span class="inline-flex rounded-full bg-[#10B981]/10 px-2.5 py-1 font-mono text-[10px] font-semibold uppercase tracking-wider text-[#10B981]">50% Paid · Booking Confirmed</span><p class="mt-3 text-sm text-[#9CA3AF]">Your downpayment has been verified. Your booking is officially confirmed.</p></div>
                                <div class="sm:text-right"><span class="block font-mono text-[10px] uppercase tracking-wider text-[#6B7280]">Payment confirmed</span><strong class="mt-1 block font-mono text-sm text-[#10B981]"><?php echo !empty($inquiry['invoice_payment_date']) ? escaped(date('M d, Y', strtotime((string) $inquiry['invoice_payment_date']))) : 'Verified'; ?></strong></div>
                            </div>
                            <div class="mt-5 grid gap-3 border-y border-[#282828] py-4 text-sm sm:grid-cols-3"><div><span class="block font-mono text-[10px] uppercase tracking-wider text-[#6B7280]">Total package</span><strong class="mt-1 block font-mono text-[#F5F2EB]">&#8369;<?php echo number_format($estimatedCost, 2); ?></strong></div><div><span class="block font-mono text-[10px] uppercase tracking-wider text-[#6B7280]">Amount paid (50%)</span><strong class="mt-1 block font-mono text-[#10B981]">&#8369;<?php echo number_format((float) ($inquiry['invoice_amount_paid'] ?? 0), 2); ?></strong></div><div><span class="block font-mono text-[10px] uppercase tracking-wider text-[#6B7280]">Remaining balance</span><strong class="mt-1 block font-mono text-[#D4AF37]">&#8369;<?php echo number_format((float) ($inquiry['invoice_remaining_balance'] ?? 0), 2); ?></strong><span class="mt-1 block text-xs text-[#9CA3AF]">Due on event date</span></div></div>
                            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end"><a href="<?php echo escaped($receiptUrl); ?>" target="_blank" rel="noopener noreferrer" class="rounded border border-[#282828] bg-[#181818] px-4 py-2.5 text-center text-xs font-semibold text-[#F5F2EB] transition hover:border-[#10B981] hover:text-[#10B981]">View Payment Receipt ↗</a><a href="<?php echo escaped($receiptUrl); ?>" target="_blank" rel="noopener noreferrer" class="rounded bg-[#D4AF37] px-4 py-2.5 text-center text-xs font-bold text-[#121212] transition hover:bg-white">Download Official Receipt</a></div>
                        </section>
                    <?php endif; ?>
                    <?php if ($hasPendingInvoice): ?>
                        <section class="mt-5 rounded-xl border border-[#D4AF37]/30 bg-[#121212] p-4 text-[#F5F2EB] sm:p-5" aria-label="Pending 50 percent downpayment invoice">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <span class="inline-flex rounded-full bg-[#D4AF37]/10 px-2.5 py-1 font-mono text-[10px] font-semibold uppercase tracking-wider text-[#D4AF37]">50% Downpayment Invoice</span>
                                    <p class="mt-3 text-sm text-[#9CA3AF]">Your event reservation has a pending payment request.</p>
                                </div>
                                <div class="sm:text-right">
                                    <span class="block font-mono text-[10px] uppercase tracking-wider text-[#6B7280]">Amount due</span>
                                    <strong class="mt-1 block font-mono text-2xl text-[#D4AF37]">&#8369;<?php echo number_format((float) ($inquiry['invoice_amount_due'] ?? 0), 2); ?></strong>
                                    <span class="mt-1 block text-xs text-[#9CA3AF]">Due <?php echo !empty($inquiry['invoice_due_date']) ? escaped(date('M d, Y', strtotime((string) $inquiry['invoice_due_date']))) : 'upon confirmation'; ?></span>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
                                <a href="<?php echo escaped($invoiceUrl); ?>" target="_blank" rel="noopener noreferrer" class="rounded border border-[#282828] bg-[#181818] px-4 py-2.5 text-center text-xs font-semibold text-[#F5F2EB] transition hover:border-[#D4AF37] hover:text-[#D4AF37]">View Invoice ↗</a>
                                <a href="<?php echo escaped($invoiceUrl); ?>#payment" class="rounded bg-[#D4AF37] px-4 py-2.5 text-center text-xs font-bold text-[#121212] transition hover:bg-white">Proceed to Downpayment →</a>
                            </div>
                        </section>
                    <?php endif; ?>
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
                            <?php if (!empty($inquiry['receipt_path'])): ?>
                                <p class="receipt-actions"><a class="btn btn-outline" href="<?php echo escaped((string) $inquiry['receipt_path']); ?>" target="_blank" rel="noopener">View Uploaded Receipt</a></p>
                            <?php endif; ?>
                        </div>
                    </details>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
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
                <a href="packages.php">Pricing</a>
                <a href="custom.php">Custom Services</a>
                <a href="custom.php">Services</a>
                <a href="login.php">Log In</a>
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
