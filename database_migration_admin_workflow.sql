USE legato_db;

ALTER TABLE inquiries
  MODIFY status ENUM('Pending Review', 'Pending Verification', 'In-Person Pending', 'Confirmed', 'Rejected', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending Review';

ALTER TABLE inquiries
  ADD COLUMN IF NOT EXISTS is_archived TINYINT(1) NULL DEFAULT 0 AFTER status;

UPDATE inquiries SET is_archived = 1
WHERE status IN ('Confirmed', 'Rejected', 'Completed', 'Cancelled');

ALTER TABLE invoices
  ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER down_payment_amount,
  ADD COLUMN IF NOT EXISTS remaining_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER amount_paid,
  MODIFY status VARCHAR(30) NOT NULL DEFAULT 'Unpaid';

UPDATE invoices
SET remaining_balance = GREATEST(total_amount - amount_paid, 0)
WHERE remaining_balance = 0 AND amount_paid < total_amount;

ALTER TABLE admin_tasks
  MODIFY status ENUM('Open', 'In Progress', 'Done', 'Completed') NOT NULL DEFAULT 'Open';
UPDATE admin_tasks SET status = 'Completed' WHERE status = 'Done';
ALTER TABLE admin_tasks
  MODIFY status ENUM('Open', 'In Progress', 'Completed') NOT NULL DEFAULT 'Open';
