USE legato_db;

ALTER TABLE inquiries
  ADD COLUMN phone VARCHAR(30) NOT NULL DEFAULT '' AFTER email,
  ADD COLUMN event_type VARCHAR(80) NOT NULL DEFAULT '' AFTER phone,
  ADD COLUMN target_event_date DATE NULL AFTER event_type,
  ADD COLUMN event_start_time TIME NULL AFTER target_event_date,
  ADD COLUMN setup_access_time TIME NULL AFTER event_start_time,
  ADD COLUMN venue VARCHAR(255) NOT NULL DEFAULT '' AFTER target_event_date,
  ADD COLUMN venue_type VARCHAR(80) NOT NULL DEFAULT '' AFTER venue,
  ADD COLUMN guest_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER venue,
  ADD COLUMN package_interest VARCHAR(100) NOT NULL DEFAULT '' AFTER guest_count,
  ADD COLUMN budget_range VARCHAR(50) NULL AFTER package_interest,
  ADD COLUMN requested_services TEXT NULL AFTER budget_range,
  ADD COLUMN special_requests TEXT NULL AFTER requested_services;