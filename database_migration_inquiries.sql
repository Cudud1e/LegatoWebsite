USE legato_db;

ALTER TABLE inquiries
  ADD COLUMN IF NOT EXISTS phone VARCHAR(30) NOT NULL DEFAULT '' AFTER email,
  ADD COLUMN IF NOT EXISTS event_type VARCHAR(80) NOT NULL DEFAULT '' AFTER phone,
  ADD COLUMN IF NOT EXISTS target_event_date DATE NULL AFTER event_type,
  ADD COLUMN IF NOT EXISTS event_start_time TIME NULL AFTER target_event_date,
  ADD COLUMN IF NOT EXISTS setup_access_time TIME NULL AFTER event_start_time,
  ADD COLUMN IF NOT EXISTS venue VARCHAR(255) NOT NULL DEFAULT '' AFTER target_event_date,
  ADD COLUMN IF NOT EXISTS venue_type VARCHAR(80) NOT NULL DEFAULT '' AFTER venue,
  ADD COLUMN IF NOT EXISTS guest_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER venue,
  ADD COLUMN IF NOT EXISTS package_interest VARCHAR(100) NOT NULL DEFAULT '' AFTER guest_count,
  ADD COLUMN IF NOT EXISTS budget_range VARCHAR(50) NULL AFTER package_interest,
  ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NOT NULL DEFAULT 'Online Payment' AFTER budget_range,
  ADD COLUMN IF NOT EXISTS estimated_total DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER payment_method,
  ADD COLUMN IF NOT EXISTS status VARCHAR(30) NOT NULL DEFAULT 'Pending Review' AFTER estimated_total,
  ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100) NULL AFTER payment_method,
  ADD COLUMN IF NOT EXISTS receipt_path VARCHAR(255) NULL AFTER payment_reference,
  ADD COLUMN IF NOT EXISTS downpayment_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER receipt_path,
  ADD COLUMN IF NOT EXISTS remaining_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER downpayment_amount,
  ADD COLUMN IF NOT EXISTS requested_services TEXT NULL AFTER payment_method,
  ADD COLUMN IF NOT EXISTS special_requests TEXT NULL AFTER requested_services;
