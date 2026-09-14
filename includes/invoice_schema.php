<?php
declare(strict_types=1);

function ensureInvoiceSchema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        inquiry_id INT UNSIGNED NOT NULL UNIQUE,
        invoice_number VARCHAR(50) NOT NULL UNIQUE,
        client_name VARCHAR(120) NOT NULL,
        client_email VARCHAR(255) NOT NULL,
        package_type VARCHAR(100) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        down_payment_amount DECIMAL(10,2) NOT NULL,
        amount_due DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        percentage TINYINT UNSIGNED NOT NULL DEFAULT 50,
        due_date DATE NULL,
        public_token CHAR(64) NULL UNIQUE,
        amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        remaining_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        payment_date TIMESTAMP NULL DEFAULT NULL,
        downpayment_date TIMESTAMP NULL DEFAULT NULL,
        final_payment_date TIMESTAMP NULL DEFAULT NULL,
        verified_by_admin TINYINT(1) NOT NULL DEFAULT 0,
        payment_method VARCHAR(50) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'Pending Payment',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_invoices_inquiry FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $columns = [
        'amount_due' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'percentage' => 'TINYINT UNSIGNED NOT NULL DEFAULT 50',
        'due_date' => 'DATE NULL',
        'public_token' => 'CHAR(64) NULL',
        'amount_paid' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'remaining_balance' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'payment_date' => 'TIMESTAMP NULL DEFAULT NULL',
        'downpayment_date' => 'TIMESTAMP NULL DEFAULT NULL',
        'final_payment_date' => 'TIMESTAMP NULL DEFAULT NULL',
        'verified_by_admin' => 'TINYINT(1) NOT NULL DEFAULT 0',
    ];
    foreach ($columns as $name => $definition) {
        $exists = $pdo->query("SHOW COLUMNS FROM invoices LIKE " . $pdo->quote($name));
        if ($exists->fetch() === false) $pdo->exec("ALTER TABLE invoices ADD COLUMN `{$name}` {$definition}");
    }
    $statusColumn = $pdo->query("SHOW COLUMNS FROM inquiries LIKE 'status'")->fetch();
    if ($statusColumn && str_contains((string) $statusColumn['Type'], 'enum') && !str_contains((string) $statusColumn['Type'], "'Fully Paid & Completed'")) {
        $pdo->exec("ALTER TABLE inquiries MODIFY status ENUM('Pending Review','Pending Verification','In-Person Pending','Invoice Sent','50% Paid - Booking Confirmed','Full Payment Verification Pending','Fully Paid & Completed','Confirmed','Rejected','Completed','Cancelled') NOT NULL DEFAULT 'Pending Review'");
    }
    $pdo->exec('CREATE TABLE IF NOT EXISTS activity_logs (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, admin_id INT UNSIGNED NULL, inquiry_id INT UNSIGNED NULL, activity VARCHAR(255) NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_activity_inquiry (inquiry_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
}
