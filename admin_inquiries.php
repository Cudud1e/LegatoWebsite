<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/csrf.php';

if (!isset($_SESSION['admin_user']['id'], $_SESSION['admin_user']['role'])
    || !in_array($_SESSION['admin_user']['role'], ['super_admin', 'coordinator', 'staff'], true)) {
    header('Location: admin/login.php');
    exit;
}

function escaped(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function createInvoiceTable(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        inquiry_id INT UNSIGNED NOT NULL,
        invoice_number VARCHAR(50) NOT NULL UNIQUE,
        client_name VARCHAR(120) NOT NULL,
        client_email VARCHAR(255) NOT NULL,
        package_type VARCHAR(100) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        down_payment_amount DECIMAL(10,2) NOT NULL,
        amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        remaining_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        payment_method VARCHAR(50) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'Unpaid',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_invoices_inquiry FOREIGN KEY (inquiry_id)
            REFERENCES inquiries(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // CREATE TABLE does not add fields to an invoice table created by an older release.
    $columns = [
        'amount_paid' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'remaining_balance' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
    ];
    foreach ($columns as $column => $definition) {
        $exists = $pdo->query("SHOW COLUMNS FROM invoices LIKE " . $pdo->quote($column));
        if ($exists->fetch() === false) {
            $pdo->exec("ALTER TABLE invoices ADD COLUMN `{$column}` {$definition}");
        }
    }
}

$message = '';
$error = '';

try {
    $pdo = getDatabaseConnection();
    createInvoiceTable($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('Your form session expired. Please refresh and try again.');
        }

        $action = $_POST['action'] ?? '';
        $inquiryId = filter_var($_POST['inquiry_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$inquiryId) {
            throw new RuntimeException('Invalid inquiry selected.');
        }

        if ($action === 'generate_invoice') {
            $inquiryStatement = $pdo->prepare('SELECT id, name, email, package_interest, payment_method, COALESCE(total_amount, estimated_cost, 0) AS total_amount FROM inquiries WHERE id = ?');
            $inquiryStatement->execute([$inquiryId]);
            $inquiry = $inquiryStatement->fetch();
            if (!$inquiry) {
                throw new RuntimeException('The selected inquiry no longer exists.');
            }

            $existing = $pdo->prepare('SELECT invoice_number FROM invoices WHERE inquiry_id = ?');
            $existing->execute([$inquiryId]);
            $existingInvoice = $existing->fetch();
            if ($existingInvoice) {
                $message = 'Invoice ' . $existingInvoice['invoice_number'] . ' already exists for this inquiry.';
            } else {
                $invoiceNumber = 'LGT-INV-' . date('Ymd') . '-' . str_pad((string) $inquiryId, 5, '0', STR_PAD_LEFT);
                $totalAmount = (float) $inquiry['total_amount'];
                $insert = $pdo->prepare('INSERT INTO invoices (inquiry_id, invoice_number, client_name, client_email, package_type, total_amount, down_payment_amount, amount_paid, remaining_balance, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?)');
                $insert->execute([$inquiryId, $invoiceNumber, $inquiry['name'], $inquiry['email'], $inquiry['package_interest'], $totalAmount, $totalAmount * 0.50, $totalAmount, $inquiry['payment_method'] ?: 'Online Payment']);
                $pdo->prepare("UPDATE inquiries SET deposit_status = 'Invoice Issued' WHERE id = ?")->execute([$inquiryId]);
                $message = 'Invoice ' . $invoiceNumber . ' was generated.';
            }
        } elseif ($action === 'confirm_payment') {
            $paid = $pdo->prepare("UPDATE invoices SET amount_paid = down_payment_amount, remaining_balance = total_amount - down_payment_amount, status = 'Down Payment Paid' WHERE inquiry_id = ? AND status = 'Unpaid'");
            $paid->execute([$inquiryId]);
            if ($paid->rowCount() === 0) {
                throw new RuntimeException('No unpaid invoice was found for this inquiry.');
            }
            $pdo->prepare("UPDATE inquiries SET deposit_status = '50% Down Payment Confirmed', status = 'Confirmed' WHERE id = ?")->execute([$inquiryId]);
            $message = '50% down payment confirmed and inquiry marked as Confirmed.';
        } elseif ($action === 'mark_paid_full') {
            $paid = $pdo->prepare("UPDATE invoices SET amount_paid = total_amount, remaining_balance = 0, status = 'Paid in Full' WHERE inquiry_id = ? AND status = 'Down Payment Paid'");
            $paid->execute([$inquiryId]);
            if ($paid->rowCount() === 0) { throw new RuntimeException('No invoice with an outstanding balance was found.'); }
            $pdo->prepare("UPDATE inquiries SET status = 'Completed' WHERE id = ?")->execute([$inquiryId]);
            $message = 'Remaining balance paid; invoice moved to paid-receipts history.';
        }
    }

    $inquiries = $pdo->query("SELECT i.id, i.reference_no, i.name, i.email, i.target_event_date, i.package_interest, i.payment_method, i.payment_reference, i.receipt_path, i.status AS inquiry_status, COALESCE(i.total_amount, i.estimated_cost, 0) AS total_amount, inv.invoice_number, inv.down_payment_amount, inv.amount_paid, inv.remaining_balance, inv.status AS invoice_status FROM inquiries i LEFT JOIN invoices inv ON inv.inquiry_id = i.id ORDER BY i.created_at DESC")->fetchAll();
} catch (PDOException | RuntimeException $exception) {
    $error = $exception->getMessage();
    $inquiries = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry &amp; Invoice Review | LEGATO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root { color-scheme: dark; } * { box-sizing: border-box; } body { margin: 0; background: #121212; color: #F5F2EB; font-family: Montserrat, sans-serif; } .topbar, main { width: min(1200px, calc(100% - 40px)); margin: auto; } .topbar { display: flex; justify-content: space-between; align-items: center; padding: 24px 0; border-bottom: 1px solid #292929; } .brand { color: #D4AF37; font-family: 'Playfair Display', serif; font-size: 24px; text-decoration: none; } .nav, .tabs { display: flex; gap: 12px; } .nav a { color: #c7c7c7; font-size: 13px; text-decoration: none; } main { padding: 44px 0 70px; } h1 { margin: 0 0 8px; font-family: 'Playfair Display', serif; font-size: clamp(32px, 5vw, 48px); } .intro, .muted { color: #a3a3a3; line-height: 1.7; } .notice { margin: 22px 0; padding: 14px 16px; border: 1px solid #5e4a16; background: #211d12; color: #F5F2EB; } .error { border-color: #7b3030; background: #261717; } .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(310px, 1fr)); gap: 20px; margin-top: 28px; } .card { padding: 24px; background: #1A1A1A; border: 1px solid #303030; } .ref, .label { color: #D4AF37; font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; } h2 { margin: 9px 0; font-family: 'Playfair Display', serif; font-size: 25px; } dl { display: grid; grid-template-columns: 1fr auto; gap: 12px; margin: 22px 0; } dt { color: #a3a3a3; font-size: 12px; } dd { margin: 0; text-align: right; font-size: 12px; } .badge { display: inline-block; padding: 6px 9px; border: 1px solid #D4AF37; color: #D4AF37; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; } .paid { border-color: #689b72; color: #9bd4a7; } form { margin-top: 16px; } button { width: 100%; padding: 12px; border: 1px solid #D4AF37; background: #D4AF37; color: #121212; cursor: pointer; font: 700 12px Montserrat, sans-serif; letter-spacing: .06em; text-transform: uppercase; } button.secondary, button.tab { background: transparent; color: #D4AF37; } button.tab { width: auto; } [hidden] { display: none !important; } .receipt-button { margin-top: 12px; } dialog { width: min(640px, calc(100% - 32px)); border: 1px solid #D4AF37; background: #1A1A1A; color: #F5F2EB; padding: 26px; } dialog::backdrop { background: rgb(0 0 0 / .75); } dialog img { max-width: 100%; max-height: 60vh; object-fit: contain; } dialog a { color: #D4AF37; } @media (max-width: 600px) { .topbar { align-items: flex-start; flex-direction: column; gap: 14px; } .nav { flex-wrap: wrap; } }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="admin/dashboard.php">LEGATO Admin</a>
        <nav class="nav"><a href="admin/dashboard.php">Dashboard</a><a href="admin/settings.php">Settings</a><a href="admin/logout.php">Log Out</a></nav>
    </header>
    <main>
        <p class="label">Inquiry-to-Invoice Workflow</p>
        <h1>Review inquiries. Issue invoices.</h1>
        <p class="intro">Issue the 50% invoice, verify the deposit, then record the remaining balance when the event is paid in full.</p>
        <?php if ($message !== ''): ?><p class="notice"><?php echo escaped($message); ?></p><?php endif; ?>
        <?php if ($error !== ''): ?><p class="notice error"><?php echo escaped($error); ?></p><?php endif; ?>
        <div class="tabs" role="tablist" aria-label="Invoice views">
            <button class="tab" type="button" data-invoice-tab="active">Active invoices</button>
            <button class="tab" type="button" data-invoice-tab="paid">Paid receipts / history</button>
        </div>
        <section id="active-invoices" class="grid">
            <?php foreach ($inquiries as $inquiry): ?>
                <?php if ($inquiry['invoice_status'] === 'Paid in Full'): continue; endif; ?>
                <article class="card">
                    <p class="ref">#<?php echo escaped((string) $inquiry['reference_no']); ?></p>
                    <h2><?php echo escaped((string) $inquiry['name']); ?></h2>
                    <p class="muted"><?php echo escaped((string) $inquiry['email']); ?></p>
                    <dl>
                        <dt>Event date</dt><dd><?php echo escaped((string) $inquiry['target_event_date']); ?></dd>
                        <dt>Package</dt><dd><?php echo escaped((string) $inquiry['package_interest']); ?></dd>
                        <dt>Total investment</dt><dd>₱<?php echo number_format((float) $inquiry['total_amount'], 2); ?></dd>
                        <dt>50% down payment</dt><dd>₱<?php echo number_format((float) $inquiry['total_amount'] * .5, 2); ?></dd>
                        <dt>Preferred method</dt><dd><?php echo escaped((string) ($inquiry['payment_method'] ?: 'Online Payment')); ?></dd>
                        <?php if ($inquiry['invoice_number']): ?>
                            <dt>Amount received</dt><dd>&#8369;<?php echo number_format((float) $inquiry['amount_paid'], 2); ?></dd>
                            <dt>Outstanding balance</dt><dd>&#8369;<?php echo number_format((float) $inquiry['remaining_balance'], 2); ?></dd>
                        <?php endif; ?>
                    </dl>
                    <?php if (!empty($inquiry['receipt_path']) || !empty($inquiry['payment_reference'])): ?>
                        <button class="secondary receipt-button" type="button" data-receipt-dialog="receipt-<?php echo (int) $inquiry['id']; ?>">Review payment proof</button>
                        <dialog id="receipt-<?php echo (int) $inquiry['id']; ?>">
                            <p class="label">Payment verification</p><h2><?php echo escaped((string) $inquiry['reference_no']); ?></h2>
                            <p class="muted">Transaction number: <?php echo escaped((string) ($inquiry['payment_reference'] ?: 'Not provided')); ?></p>
                            <?php if (!empty($inquiry['receipt_path'])): ?>
                                <p><a href="<?php echo escaped((string) $inquiry['receipt_path']); ?>" target="_blank" rel="noopener">Open original uploaded receipt</a></p>
                                <?php if (preg_match('/\\.(jpe?g|png)$/i', (string) $inquiry['receipt_path'])): ?><img src="<?php echo escaped((string) $inquiry['receipt_path']); ?>" alt="Uploaded payment receipt"><?php endif; ?>
                            <?php else: ?><p class="muted">No receipt file was uploaded.</p><?php endif; ?>
                            <form method="dialog"><button class="secondary" type="submit">Close</button></form>
                        </dialog>
                    <?php endif; ?>
                    <?php if ($inquiry['invoice_number']): ?>
                        <p class="label">Invoice <?php echo escaped((string) $inquiry['invoice_number']); ?></p>
                        <span class="badge<?php echo $inquiry['invoice_status'] === 'Paid in Full' ? ' paid' : ''; ?>"><?php echo escaped((string) $inquiry['invoice_status']); ?></span>
                        <?php if ($inquiry['invoice_status'] === 'Unpaid'): ?>
                            <form method="post"><input type="hidden" name="csrf_token" value="<?php echo escaped(csrfToken()); ?>"><input type="hidden" name="action" value="confirm_payment"><input type="hidden" name="inquiry_id" value="<?php echo (int) $inquiry['id']; ?>"><button class="secondary" type="submit">Confirm 50% Payment</button></form>
                        <?php elseif ($inquiry['invoice_status'] === 'Down Payment Paid'): ?>
                            <form method="post"><input type="hidden" name="csrf_token" value="<?php echo escaped(csrfToken()); ?>"><input type="hidden" name="action" value="mark_paid_full"><input type="hidden" name="inquiry_id" value="<?php echo (int) $inquiry['id']; ?>"><button class="secondary" type="submit">Mark Paid in Full</button></form>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="post"><input type="hidden" name="csrf_token" value="<?php echo escaped(csrfToken()); ?>"><input type="hidden" name="action" value="generate_invoice"><input type="hidden" name="inquiry_id" value="<?php echo (int) $inquiry['id']; ?>"><button type="submit">Generate 50% Invoice</button></form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
        <section id="paid-invoices" class="grid" hidden>
            <?php foreach ($inquiries as $inquiry): ?>
                <?php if ($inquiry['invoice_status'] !== 'Paid in Full'): continue; endif; ?>
                <article class="card"><p class="ref">Paid receipt · <?php echo escaped((string) $inquiry['invoice_number']); ?></p><h2><?php echo escaped((string) $inquiry['name']); ?></h2><dl><dt>Reference</dt><dd>#<?php echo escaped((string) $inquiry['reference_no']); ?></dd><dt>Paid in full</dt><dd>&#8369;<?php echo number_format((float) $inquiry['amount_paid'], 2); ?></dd><dt>Balance</dt><dd>&#8369;<?php echo number_format((float) $inquiry['remaining_balance'], 2); ?></dd></dl><span class="badge paid">Paid in Full</span></article>
            <?php endforeach; ?>
        </section>
    </main>
    <script>
        document.querySelectorAll('[data-receipt-dialog]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.receiptDialog).showModal()));
        document.querySelectorAll('[data-invoice-tab]').forEach((button) => button.addEventListener('click', () => { const paid = button.dataset.invoiceTab === 'paid'; document.getElementById('active-invoices').hidden = paid; document.getElementById('paid-invoices').hidden = !paid; }));
    </script>
</body>
</html>
