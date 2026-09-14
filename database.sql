CREATE DATABASE IF NOT EXISTS legato_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE legato_db;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  nickname VARCHAR(80) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  location VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE inquiries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference_no VARCHAR(30) NOT NULL UNIQUE,
  user_id INT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  event_type VARCHAR(80) NOT NULL,
  target_event_date DATE NOT NULL,
  event_start_time TIME NOT NULL,
  setup_access_time TIME NOT NULL,
  venue VARCHAR(255) NOT NULL,
  venue_type VARCHAR(80) NOT NULL,
  guest_count INT UNSIGNED NOT NULL,
  package_interest VARCHAR(100) NOT NULL,
  budget_range VARCHAR(50) NULL,
  payment_method VARCHAR(50) NOT NULL DEFAULT 'Online Payment',
  payment_reference VARCHAR(100) NULL,
  receipt_path VARCHAR(255) NULL,
  downpayment_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
  remaining_balance DECIMAL(10, 2) NOT NULL DEFAULT 0,
  estimated_total DECIMAL(10, 2) NOT NULL DEFAULT 0,
  requested_services TEXT NULL,
  special_requests TEXT NULL,
  message TEXT NOT NULL,
  status ENUM('Pending Review', 'Pending Verification', 'In-Person Pending', 'Confirmed', 'Rejected', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending Review',
  is_archived TINYINT(1) NULL DEFAULT 0,
  confirmed_at TIMESTAMP NULL DEFAULT NULL,
  total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
  estimated_cost DECIMAL(10, 2) NOT NULL DEFAULT 0,
  deposit_status VARCHAR(80) NOT NULL DEFAULT 'Not Required Yet',
  assigned_admin_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_inquiries_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
);

CREATE TABLE bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(255) NOT NULL,
  services TEXT NOT NULL,
  estimated_cost DECIMAL(10, 2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
);

CREATE TABLE admin_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  role ENUM('super_admin', 'coordinator', 'staff') NOT NULL DEFAULT 'staff',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE admin_tasks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inquiry_id INT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  assigned_admin_id INT UNSIGNED NULL,
  status ENUM('Open', 'In Progress', 'Completed') NOT NULL DEFAULT 'Open',
  due_date DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_admin_tasks_inquiry FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE,
  CONSTRAINT fk_admin_tasks_admin FOREIGN KEY (assigned_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
);

ALTER TABLE inquiries
  ADD CONSTRAINT fk_inquiries_admin FOREIGN KEY (assigned_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL;

-- Create the first admin account using the documented local setup steps in SETUP.md.
