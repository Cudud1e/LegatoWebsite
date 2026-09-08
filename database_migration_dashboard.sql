USE legato_db;

CREATE TABLE booking_services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inquiry_id INT UNSIGNED NOT NULL,
  service_name VARCHAR(150) NOT NULL,
  unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_booking_services_inquiry FOREIGN KEY (inquiry_id) REFERENCES inquiries(id)
    ON DELETE CASCADE
);

CREATE INDEX idx_booking_services_inquiry ON booking_services (inquiry_id);
