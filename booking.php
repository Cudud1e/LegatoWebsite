<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/booking_guard.php';
require_once __DIR__ . '/includes/csrf.php';

requireBookingLogin();

$user = $_SESSION['user'] ?? [];
$selectedPackage = trim((string) ($_GET['package'] ?? 'VIP1'));
$packageMap = [
    'VIP1' => 'VIP 1: Elite Starter',
    'VIP2' => 'VIP 2: Prestige',
    'VIP3' => 'VIP 3: Grand Luxe',
    'Custom Build' => 'Custom Build'
];
$packageInterest = $packageMap[$selectedPackage] ?? 'VIP 1: Elite Starter';

$customServices = trim((string) ($_GET['services'] ?? ''));
$customSelections = json_decode($customServices, true);
$customSelections = is_array($customSelections) ? $customSelections : [];

$tierPrices = [
    'Audio System' => ['Basic' => 8000, 'Standard' => 15000, 'Premium' => 25000],
    'Professional EMCEE / Host' => ['Basic' => 5000, 'Standard' => 8000, 'Premium' => 12000],
    'Stage & DÃ©cor Support' => ['Basic' => 3000, 'Standard' => 7000, 'Premium' => 12000],
    'Visual & Multimedia Support' => ['Basic' => 2000, 'Standard' => 5000, 'Premium' => 8000],
    'Photography & Videography' => ['Basic' => 10000, 'Standard' => 15000, 'Premium' => 20000],
    'Full Event Management' => ['Basic' => 8000, 'Standard' => 12000, 'Premium' => 15000]
];

$customTotal = 0.0;
foreach ($customSelections as $service => $tier) {
    if (isset($tierPrices[$service][$tier])) {
        $customTotal += $tierPrices[$service][$tier];
    }
}

$error = $_SESSION['inquiry_error'] ?? '';
$fieldErrors = $_SESSION['inquiry_field_errors'] ?? [];
$oldInput = $_SESSION['inquiry_old'] ?? [];
unset($_SESSION['inquiry_error'], $_SESSION['inquiry_field_errors'], $_SESSION['inquiry_old']);

function escaped(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
function old(string $key, string $default = ''): string {
    global $oldInput;
    return htmlspecialchars((string) ($oldInput[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}
function fieldError(string $key): string {
    global $fieldErrors;
    return isset($fieldErrors[$key]) ? '<small class="field-error">' . htmlspecialchars((string) $fieldErrors[$key], ENT_QUOTES, 'UTF-8') . '</small>' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Event | LEGATO Events &amp; Productions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .booking-flow { max-width: 980px; margin: 0 auto; padding: 70px 0; }
        .booking-flow h1 { margin: 10px 0; font-size: clamp(36px, 6vw, 58px); }
        .booking-flow > p { max-width: 700px; color: var(--muted); line-height: 1.8; }
        .booking-card { margin-top: 36px; padding: 30px; border: 1px solid var(--border); background: #1A1A1A; }
        .booking-section { margin: 0 0 35px; padding: 0 0 30px; border: 0; border-bottom: 1px solid var(--border); }
        .booking-section:last-of-type { border-bottom: 0; padding-bottom: 0; }
        .booking-section legend { margin-bottom: 18px; color: var(--gold); font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .booking-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .booking-card label { display: grid; gap: 8px; color: #bbb; font-size: 12px; }
        .booking-card input, .booking-card select, .booking-card textarea { width: 100%; border: 1px solid #3a3a3a; background: #121212; color: var(--ivory); padding: 12px; font: inherit; }
        .booking-card textarea { min-height: 110px; resize: vertical; }
        .package-options, .payment-options, .payment-method-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .package-option, .payment-option, .payment-method-grid article { border: 1px solid #363636; background: #121212; padding: 18px; cursor: pointer; }
        .package-option:has(input:checked), .payment-option:has(input:checked) { border-color: var(--gold); background: rgba(212, 175, 55, .08); }
        .package-option input, .payment-option input { position: absolute; opacity: 0; pointer-events: none; }
        .package-option strong, .payment-option strong { display: block; color: var(--ivory); }
        .package-option span, .payment-option span, .payment-note { display: block; margin-top: 7px; color: var(--muted); font-size: 12px; line-height: 1.6; }
        .package-option b { display: block; margin-top: 9px; color: var(--gold); }
        .payment-panel { display: none; margin-top: 18px; padding: 20px; border: 1px solid rgba(212, 175, 55, .35); background: #121212; }
        .payment-panel.active { display: block; }
        .payment-method-grid { margin: 16px 0; }
        .payment-method-grid article { cursor: default; }
        .payment-method-grid h3 { margin: 0 0 10px; color: var(--gold); font-size: 14px; }
        .payment-method-grid p { margin: 5px 0; color: var(--muted); font-size: 12px; line-height: 1.6; }
        .payment-ledger { display: grid; gap: 9px; margin: 20px 0; padding: 18px; border: 1px dashed var(--gold); background: rgba(212, 175, 55, .04); }
        .payment-ledger div { display: flex; justify-content: space-between; gap: 15px; color: var(--muted); font-size: 13px; }
        .payment-ledger strong { color: var(--gold); }
        .form-error { margin: 22px 0; padding: 14px; border: 1px solid #9e4040; background: #281717; color: #f5c4c4; }
        .field-error { color: #f5a5a5; font-size: 11px; line-height: 1.4; }
        .booking-card .input-error { border-color: #d86969; }
        @media(max-width:760px) {
            .booking-grid, .package-options, .payment-options, .payment-method-grid { grid-template-columns: 1fr; }
            .booking-flow { padding: 45px 0; }
            .booking-card { padding: 22px; }
        }
    </style>
</head>
<body class="inner-page">
    <header class="navbar">
        <div class="container nav-content">
            <a href="index.php" class="brand">
                <img class="custom-logo h-16 sm:h-20 lg:h-24 w-auto object-contain transition-transform duration-200 hover:scale-105" src="Assest/legato1.png" alt="LEGATO Events &amp; Productions">
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
                <a href="profile.php" class="nav-login">My Account</a>
                <a href="logout.php" class="nav-login">Log Out</a>
                <button class="menu-button" id="menuButton" aria-label="Open menu">&#9776;</button>
            </div>
        </div>
    </header>

    <main>
        <section class="container booking-flow">
            <p class="section-label">RESERVE YOUR EVENT</p>
            <h1>Secure your <em>production date.</em></h1>
            <p>Submit your event details and payment route. Online transfers require a receipt for verification; in-person reservations can be settled at our Dumaguete office.</p>
            
            <?php if ($error): ?>
                <p class="form-error"><?php echo escaped((string) $error); ?></p>
            <?php endif; ?>

            <form id="bookingForm" action="process_inquiry.php" method="POST" enctype="multipart/form-data" class="booking-card">
                <input type="hidden" name="csrf_token" value="<?php echo escaped(csrfToken()); ?>">
                
                <fieldset class="booking-section">
                    <legend>01 · Contact &amp; Event Details</legend>
                    <div class="booking-grid">
                        <label>Full Name
                            <input class="<?php echo isset($fieldErrors['full_name']) ? 'input-error' : ''; ?>" name="full_name" required value="<?php echo old('full_name', (string) ($user['full_name'] ?? '')); ?>"><?php echo fieldError('full_name'); ?>
                        </label>
                        <label>Email Address
                            <input class="<?php echo isset($fieldErrors['email']) ? 'input-error' : ''; ?>" name="email" type="email" required value="<?php echo old('email', (string) ($user['email'] ?? '')); ?>"><?php echo fieldError('email'); ?>
                        </label>
                        <label>Phone Number
                            <input class="<?php echo isset($fieldErrors['phone']) ? 'input-error' : ''; ?>" name="phone" type="tel" required value="<?php echo old('phone', (string) ($user['phone'] ?? '')); ?>"><?php echo fieldError('phone'); ?>
                        </label>
                        <label>Target Event Date
                            <input class="<?php echo isset($fieldErrors['event_date']) ? 'input-error' : ''; ?>" name="event_date" type="date" min="<?php echo date('Y-m-d'); ?>" required value="<?php echo old('event_date'); ?>"><?php echo fieldError('event_date'); ?>
                        </label>
                        <label>Event Type
                            <select name="event_type" required>
                                <option value="">Select event type</option>
                                <?php foreach (['Wedding', 'Birthday/Debut', 'Corporate Event', 'School Gala', 'Private Party', 'Other'] as $type): ?><option<?php echo old('event_type') === $type ? ' selected' : ''; ?>><?php echo escaped($type); ?></option><?php endforeach; ?>
                            </select><?php echo fieldError('event_type'); ?>
                        </label>
                        <label>Expected Guests
                            <input name="guest_count" type="number" min="1" required value="<?php echo old('guest_count'); ?>">
                        </label>
                        <label>Event Start Time
                            <input class="<?php echo isset($fieldErrors['event_start_time']) ? 'input-error' : ''; ?>" name="event_start_time" type="time" required value="<?php echo old('event_start_time'); ?>"><?php echo fieldError('event_start_time'); ?>
                        </label>
                        <label>Venue Setup Access
                            <input class="<?php echo isset($fieldErrors['setup_access_time']) ? 'input-error' : ''; ?>" name="setup_access_time" type="time" required value="<?php echo old('setup_access_time'); ?>"><?php echo fieldError('setup_access_time'); ?>
                        </label>
                        <label>Venue / Location
                            <input class="<?php echo isset($fieldErrors['venue']) ? 'input-error' : ''; ?>" name="venue" required value="<?php echo old('venue', (string) ($user['location'] ?? '')); ?>"><?php echo fieldError('venue'); ?>
                        </label>
                        <label>Venue Type
                            <select name="venue_type" required>
                                <option value="">Select venue type</option>
                                <?php foreach (['Indoor Ballroom / Hotel', 'Outdoor Garden', 'Outdoor Beach', 'Private Residence'] as $type): ?><option<?php echo old('venue_type') === $type ? ' selected' : ''; ?>><?php echo escaped($type); ?></option><?php endforeach; ?>
                            </select><?php echo fieldError('venue_type'); ?>
                        </label>
                    </div>
                    <?php echo fieldError('package_type'); ?>
                </fieldset>

                <fieldset class="booking-section">
                    <legend>02 · Select Production Package</legend>
                    <div class="package-options">
                        <label class="package-option">
                            <input type="radio" name="package_type" value="VIP 1: Elite Starter" data-total="49999"<?php echo old('package_type', $packageInterest) === 'VIP 1: Elite Starter' ? ' checked' : ''; ?>>
                            <strong>VIP 1 · Elite Starter</strong>
                            <span>Up to 50 guests</span>
                            <b>₱49,999.00</b>
                        </label>
                        <label class="package-option">
                            <input type="radio" name="package_type" value="VIP 2: Prestige" data-total="79999"<?php echo old('package_type', $packageInterest) === 'VIP 2: Prestige' ? ' checked' : ''; ?>>
                            <strong>VIP 2 · Prestige</strong>
                            <span>50–150 guests</span>
                            <b>₱79,999.00</b>
                        </label>
                        <label class="package-option">
                            <input type="radio" name="package_type" value="VIP 3: Grand Luxe" data-total="179999"<?php echo old('package_type', $packageInterest) === 'VIP 3: Grand Luxe' ? ' checked' : ''; ?>>
                            <strong>VIP 3 · Grand Luxe</strong>
                            <span>150+ guests</span>
                            <b>₱179,999.00</b>
                        </label>
                        <label class="package-option">
                            <input type="radio" name="package_type" value="Custom Build" data-total="<?php echo $customTotal; ?>"<?php echo old('package_type', $packageInterest) === 'Custom Build' ? ' checked' : ''; ?>>
                            <strong>Custom Build</strong>
                            <span><?php echo $customSelections ? escaped(implode(', ', array_keys($customSelections))) : 'Choose your services and tiers'; ?></span>
                            <b><?php echo $customSelections ? '₱' . number_format($customTotal, 2) : 'Configure services'; ?></b>
                        </label>
                    </div>
                    <?php echo fieldError('payment_method'); ?>
                    <input id="estimated_total" type="hidden" name="estimated_total" value="0">
                    <input type="hidden" name="custom_services" value="<?php echo escaped($customServices); ?>">
                </fieldset>

                <fieldset class="booking-section">
                    <legend>03 · Payment &amp; Reservation</legend>
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="Online Payment"<?php echo old('payment_method', 'Online Payment') === 'Online Payment' ? ' checked' : ''; ?>>
                            <strong>Online Payment</strong>
                            <span>GCash, Maya, or Bank Transfer. Receipt required.</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="In Person"<?php echo old('payment_method') === 'In Person' ? ' checked' : ''; ?>>
                            <strong>In-Person Payment</strong>
                            <span>Settle at the LEGATO Main Office in Dumaguete City.</span>
                        </label>
                    </div>

                    <div class="payment-ledger">
                        <div><span>Package total</span><strong id="package-total">₱0.00</strong></div>
                        <div><span>Required 50% down payment</span><strong id="down-payment">₱0.00</strong></div>
                        <div><span>Remaining balance</span><strong id="remaining-balance">₱0.00</strong></div>
                    </div>

                    <div id="online-panel" class="payment-panel active">
                        <p class="payment-note">Use payment details provided by LEGATO. Upload proof of transfer so we can verify the reservation.</p>
                        <div class="payment-method-grid">
                            <article>
                                <h3>GCash</h3>
                                <p>Account Name: LEGATO Events &amp; Productions</p>
                                <p>Account Number: 0917-123-4567 </p>
                            </article>
                            <article>
                                <h3>Maya</h3>
                                <p>Account Name: LEGATO Events &amp; Productions</p>
                                <p>Account Number: 0917-123-4567 </p>
                            </article>
                            <article>
                                <h3>Bank Transfer · BDO / PNB</h3>
                                <p>Account Name: LEGATO Events &amp; Productions</p>
                                <p>BDO Account Number: 0012-3456-7890</p>
                                <p>PNB Account Number: 3105-1002-4821</p>
                            </article>
                        </div>
                        <div class="booking-grid">
                            <label>Transaction Reference Number
                                <input name="payment_reference" id="payment_reference" required value="<?php echo old('payment_reference'); ?>">
                            </label>
                            <label>Proof of Payment (JPG, PNG, or PDF)
                                <input name="receipt_file" id="receipt_file" type="file" accept=".jpg,.jpeg,.png,.pdf" required>
                                <?php echo fieldError('receipt_file'); ?>
                            </label>
                        </div>
                    </div>

                    <div id="inperson-panel" class="payment-panel">
                        <p><strong>LEGATO Main Office — Dumaguete City, Negros Oriental</strong></p>
                        <p class="payment-note">Monday to Saturday, 9:00 AM to 6:00 PM. We will hold your requested date while your inquiry is reviewed.</p>
                    </div>

                    <label style="margin-top:20px;">Event Notes
                        <textarea class="<?php echo isset($fieldErrors['notes']) ? 'input-error' : ''; ?>" name="notes" required placeholder="Tell us about your program, technical requirements, or special requests."><?php echo old('notes'); ?></textarea><?php echo fieldError('notes'); ?>
                    </label>
                </fieldset>

                <button type="submit" name="submit_inquiry" class="btn btn-gold full-width">Submit Inquiry &amp; Reservation Request</button>
            </form>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>

    <script src="script.js"></script>
    <script>
        const packageInputs = [...document.querySelectorAll('input[name="package_type"]')];
        const paymentInputs = [...document.querySelectorAll('input[name="payment_method"]')];
        const totalInput = document.getElementById('estimated_total');
        const totalEl = document.getElementById('package-total');
        const downEl = document.getElementById('down-payment');
        const balanceEl = document.getElementById('remaining-balance');
        const onlinePanel = document.getElementById('online-panel');
        const inpersonPanel = document.getElementById('inperson-panel');
        const paymentReference = document.getElementById('payment_reference');
        const receiptFile = document.getElementById('receipt_file');
        const hasCustomServices = <?php echo $customSelections ? 'true' : 'false'; ?>;

        function peso(v) {
            return '₱' + v.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function refreshPayment() {
            const p = packageInputs.find(i => i.checked);
            if (p?.value === 'Custom Build' && !hasCustomServices) {
                window.location.href = 'custom.php';
                return;
            }
            const total = Number(p?.dataset.total || 0);
            const online = paymentInputs.find(i => i.checked)?.value === 'Online Payment';
            
            totalInput.value = total.toFixed(2);
            totalEl.textContent = peso(total);
            downEl.textContent = peso(online ? total * .5 : 0);
            balanceEl.textContent = peso(online ? total * .5 : total);
            
            onlinePanel.classList.toggle('active', online);
            inpersonPanel.classList.toggle('active', !online);
            paymentReference.required = online;
            receiptFile.required = online;
        }

        packageInputs.forEach(i => i.addEventListener('change', refreshPayment));
        paymentInputs.forEach(i => i.addEventListener('change', refreshPayment));
        refreshPayment();
    </script>
</body>
</html>
