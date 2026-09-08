USE legato_db;

ALTER TABLE inquiries
  ADD COLUMN IF NOT EXISTS reference_no VARCHAR(30) NULL AFTER id,
  ADD COLUMN IF NOT EXISTS event_start_time TIME NULL AFTER target_event_date,
  ADD COLUMN IF NOT EXISTS setup_access_time TIME NULL AFTER event_start_time,
  ADD COLUMN IF NOT EXISTS venue_type VARCHAR(80) NOT NULL DEFAULT '' AFTER venue,
  ADD COLUMN IF NOT EXISTS special_requests TEXT NULL AFTER requested_services,
  ADD COLUMN IF NOT EXISTS status ENUM('Pending Review', 'Confirmed', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending Review' AFTER message,
  ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0 AFTER status,
  ADD COLUMN IF NOT EXISTS estimated_cost DECIMAL(10, 2) NOT NULL DEFAULT 0 AFTER status,
  ADD COLUMN IF NOT EXISTS deposit_status VARCHAR(80) NOT NULL DEFAULT 'Not Required Yet' AFTER estimated_cost,
  ADD COLUMN IF NOT EXISTS assigned_admin_id INT UNSIGNED NULL AFTER deposit_status;
UPDATE inquiries
SET reference_no = CONCAT('LGT-', YEAR(created_at), '-', LPAD(id, 4, '0'))
WHERE reference_no IS NULL OR reference_no = '';

ALTER TABLE inquiries
  MODIFY status ENUM('Pending Review', 'Confirmed', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending Review',
  MODIFY reference_no VARCHAR(30) NOT NULL UNIQUE;
CREATE TABLE IF NOT EXISTS admin_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  role ENUM('super_admin', 'coordinator', 'staff') NOT NULL DEFAULT 'staff',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admin_tasks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inquiry_id INT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  assigned_admin_id INT UNSIGNED NULL,
  status ENUM('Open', 'In Progress', 'Done') NOT NULL DEFAULT 'Open',
  due_date DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_admin_tasks_inquiry FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE,
  CONSTRAINT fk_admin_tasks_admin FOREIGN KEY (assigned_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
);

INSERT INTO admin_users (email, password_hash, full_name, role)
VALUES ('admin@legatoevents.com', '$2y$10$XJ5iaVPBCGrwaMabLtRDQ.Nl9JFyFeO2gI.lTSbngP28R3YejyUuK', 'LEGATO Super Admin', 'super_admin')
ON DUPLICATE KEY UPDATE email = email;