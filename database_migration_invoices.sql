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
