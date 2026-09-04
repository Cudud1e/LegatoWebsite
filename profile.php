<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

require_once __DIR__ . '/db.php';
$pdo = getDatabaseConnection();
$userId = (int) $_SESSION['user']['id'];
$profileMessage = '';
$profileError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $updatedName = trim($_POST['full_name'] ?? '');
    $updatedNickname = trim($_POST['nickname'] ?? '');
    $updatedPhone = trim($_POST['phone'] ?? '');
    $updatedLocation = trim($_POST['location'] ?? '');
    if ($updatedName === '' || $updatedNickname === '' || $updatedPhone === '' || $updatedLocation === '') {
        $profileError = 'Please complete all profile fields.';
    } else {
        $update = $pdo->prepare('UPDATE users SET full_name = ?, nickname = ?, phone = ?, location = ? WHERE id = ?');
        $update->execute([$updatedName, $updatedNickname, $updatedPhone, $updatedLocation, $userId]);
        $_SESSION['user']['full_name'] = $updatedName;
        $_SESSION['user']['nickname'] = $updatedNickname;
        $_SESSION['user']['phone'] = $updatedPhone;
        $_SESSION['user']['location'] = $updatedLocation;
        $profileMessage = 'Your profile was updated.';
    }
}

$accountStatement = $pdo->prepare('SELECT id, full_name, nickname, email, phone, location, created_at FROM users WHERE id = ?');
$accountStatement->execute([$userId]);
$account = $accountStatement->fetch();
if (!$account) {
    header('Location: logout.php');
    exit;
}

// One row per inquiry/service. The PHP loop below groups these rows into receipts.
$historyStatement = $pdo->prepare(
    'SELECT i.id, i.name, i.email, i.phone, i.event_type, i.target_event_date, i.venue,
            i.guest_count, i.package_interest, i.budget_range, i.requested_services,
            i.message, i.status, i.estimated_cost, i.distance_radius, i.deposit_status,
            i.created_at, bs.service_name, bs.unit_price
     FROM inquiries i
     LEFT JOIN booking_services bs ON bs.inquiry_id = i.id
     WHERE i.user_id = ?
     ORDER BY i.created_at DESC, bs.id ASC'
);
$historyStatement->execute([$userId]);
$historyRows = $historyStatement->fetchAll();
$inquiries = [];

foreach ($historyRows as $row) {
    $id = (int) $row['id'];
    if (!isset($inquiries[$id])) {
        $inquiries[$id] = [
            'id' => $id,
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'event_type' => $row['event_type'],
            'target_event_date' => $row['target_event_date'],
            'venue' => $row['venue'],
            'guest_count' => $row['guest_count'],
            'package_interest' => $row['package_interest'],
            'budget_range' => $row['budget_range'],
            'requested_services' => json_decode((string) $row['requested_services'], true) ?: [],
            'message' => $row['message'],
            'status' => $row['status'],
            'estimated_cost' => (float) $row['estimated_cost'],
            'distance_radius' => $row['distance_radius'] ?: 'Within standard service radius',
            'deposit_status' => $row['deposit_status'],
            'created_at' => $row['created_at'],
            'services' => [],
        ];
    }
    if ($row['service_name'] !== null) {
        $inquiries[$id]['services'][] = [
            'name' => $row['service_name'],
            'price' => (float) $row['unit_price'],
        ];
    }
}

$packagePrices = [
    'VIP 1: Elite Starter' => 49999,
    'VIP 2: Prestige' => 79999,
    'VIP 3: Grand Luxe' => 179999,
];
foreach ($inquiries as &$inquiry) {
    if (!$inquiry['services'] && $inquiry['requested_services']) {
        foreach ($inquiry['requested_services'] as $service) {
            $inquiry['services'][] = ['name' => (string) $service, 'price' => 0];
        }
    }
    if ($inquiry['estimated_cost'] <= 0) {
        $inquiry['estimated_cost'] = $packagePrices[$inquiry['package_interest']] ?? 0;
    }
}
unset($inquiry);

function escaped(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function money(float $amount): string { return '₱' . number_format($amount, 2); }
function statusClass(string $status): string { return strtolower(str_replace(' ', '-', $status)); }
$pageTitle = 'My Account | LEGATO Events & Productions';
require_once __DIR__ . '/includes/header.php';
?>
<main class="dashboard-page">
  <section class="page-hero dashboard-hero"><div class="container"><p class="section-label">LEGATO CLIENT PORTAL</p><h1>My <em>Account.</em></h1><p>Manage your profile, review inquiries, and keep track of your event proposals.</p></div></section>
  <section class="inner-section"><div class="container dashboard-grid">
    <aside class="profile-card"><p class="section-label">ACCOUNT DETAILS</p><h2><?php echo escaped($account['nickname']); ?></h2><dl><div><dt>Full Name</dt><dd><?php echo escaped($account['full_name']); ?></dd></div><div><dt>Email Address</dt><dd><?php echo escaped($account['email']); ?></dd></div><div><dt>Phone Number</dt><dd><?php echo escaped($account['phone']); ?></dd></div><div><dt>Location</dt><dd><?php echo escaped($account['location']); ?></dd></div><div><dt>Member Since</dt><dd><?php echo escaped(date('F j, Y', strtotime($account['created_at']))); ?></dd></div></dl><?php if ($profileMessage): ?><p class="form-message"><?php echo escaped($profileMessage); ?></p><?php endif; ?><?php if ($profileError): ?><p class="form-message"><?php echo escaped($profileError); ?></p><?php endif; ?><details id="edit-profile" class="edit-profile"><summary class="btn btn-outline full-width">Edit Profile</summary><form method="post" action="profile.php#edit-profile"><input type="hidden" name="action" value="update_profile"><label>Full Name<input name="full_name" required value="<?php echo escaped($account['full_name']); ?>"></label><label>Nickname<input name="nickname" required value="<?php echo escaped($account['nickname']); ?>"></label><label>Phone Number<input name="phone" required value="<?php echo escaped($account['phone']); ?>"></label><label>Location<input name="location" required value="<?php echo escaped($account['location']); ?>"></label><button class="btn btn-gold full-width" type="submit">Save Changes</button></form></details></aside>
    <div class="history-panel"><div class="section-heading"><p class="section-label">RECEIPT HISTORY</p><h2>Your inquiries and bookings</h2></div>
      <?php if (!$inquiries): ?><div class="empty-state"><p>No inquiries yet.</p><a class="btn btn-gold" href="booking.php">Start an Event Inquiry</a></div><?php endif; ?>
      <?php foreach ($inquiries as $inquiry): ?><article class="booking-card"><div class="booking-card-header"><div><span class="booking-reference">#LGT-<?php echo date('Y', strtotime($inquiry['created_at'])); ?>-<?php echo str_pad((string) $inquiry['id'], 3, '0', STR_PAD_LEFT); ?></span><h3><?php echo escaped($inquiry['package_interest']); ?></h3></div><span class="status-badge status-<?php echo escaped(statusClass($inquiry['status'])); ?>"><?php echo escaped($inquiry['status']); ?></span></div><div class="booking-meta"><span><?php echo escaped((string) ($inquiry['target_event_date'] ?: 'Date pending')); ?></span><span><?php echo escaped($inquiry['venue']); ?></span><strong><?php echo money($inquiry['estimated_cost']); ?></strong></div><details class="receipt-details"><summary>View Itemized Receipt</summary><div class="receipt-content"><div class="receipt-columns"><div><h4>Client Information</h4><p><?php echo escaped($inquiry['name']); ?><br><?php echo escaped($inquiry['email']); ?><br><?php echo escaped($inquiry['phone']); ?></p></div><div><h4>Event Details</h4><p><?php echo escaped((string) $inquiry['event_type']); ?><br><?php echo escaped((string) ($inquiry['target_event_date'] ?: 'Date pending')); ?><br><?php echo escaped($inquiry['venue']); ?><br><?php echo (int) $inquiry['guest_count']; ?> estimated guests<br><?php echo escaped($inquiry['distance_radius']); ?></p></div></div><h4>Itemized Breakdown</h4><ul class="receipt-items"><?php if ($inquiry['services']): ?><?php foreach ($inquiry['services'] as $service): ?><li><span><?php echo escaped($service['name']); ?></span><strong><?php echo $service['price'] > 0 ? money($service['price']) : 'Included / TBC'; ?></strong></li><?php endforeach; ?><?php else: ?><li><span><?php echo escaped($inquiry['package_interest']); ?></span><strong><?php echo money($inquiry['estimated_cost']); ?></strong></li><?php endif; ?></ul><div class="financial-summary"><span>Subtotal <strong><?php echo money($inquiry['estimated_cost']); ?></strong></span><span>Transport Fee <strong>Included / TBC</strong></span><span>Total Estimated Investment <strong><?php echo money($inquiry['estimated_cost']); ?></strong></span><span>Deposit Status <strong><?php echo escaped($inquiry['deposit_status']); ?></strong></span></div><div class="receipt-actions"><button type="button" class="btn btn-outline" onclick="window.print()">Download Receipt PDF</button><a class="btn btn-gold" href="contact.php">Contact Coordinator</a></div></div></details></article><?php endforeach; ?>
    </div>
  </div></section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
