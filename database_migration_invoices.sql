USE legato_db;

CREATE TABLE IF NOT EXISTS invoices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inquiry_id INT UNSIGNED NOT NULL,
  invoice_number VARCHAR(50) NOT NULL UNIQUE,
  client_name VARCHAR(120) NOT NULL,
  client_email VARCHAR(255) NOT NULL,
  package_type VARCHAR(100) NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  down_payment_amount DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Unpaid',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_invoices_inquiry FOREIGN KEY (inquiry_id)
    REFERENCES inquiries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE invoices
  ADD COLUMN IF NOT EXISTS amount_due DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER down_payment_amount,
  ADD COLUMN IF NOT EXISTS percentage TINYINT UNSIGNED NOT NULL DEFAULT 50 AFTER amount_due,
  ADD COLUMN IF NOT EXISTS due_date DATE NULL AFTER percentage,
  ADD COLUMN IF NOT EXISTS public_token CHAR(64) NULL AFTER due_date,
  ADD COLUMN IF NOT EXISTS payment_date TIMESTAMP NULL DEFAULT NULL AFTER remaining_balance,
  ADD COLUMN IF NOT EXISTS downpayment_date TIMESTAMP NULL DEFAULT NULL AFTER payment_date,
  ADD COLUMN IF NOT EXISTS final_payment_date TIMESTAMP NULL DEFAULT NULL AFTER downpayment_date,
  ADD COLUMN IF NOT EXISTS verified_by_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER final_payment_date;

ALTER TABLE inquiries
  MODIFY status ENUM('Pending Review', 'Pending Verification', 'In-Person Pending', 'Invoice Sent', '50% Paid - Booking Confirmed', 'Full Payment Verification Pending', 'Fully Paid & Completed', 'Confirmed', 'Rejected', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending Review';

CREATE TABLE IF NOT EXISTS activity_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NULL,
  inquiry_id INT UNSIGNED NULL,
  activity VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_inquiry (inquiry_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
