USE casa_de_fernandes;

-- DROP TABLE IF EXISTS for safe resets
DROP TABLE IF EXISTS bookings;

-- CREATE TABLE bookings (structure matches PHP exactly)
CREATE TABLE bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(128) NOT NULL,
  phone VARCHAR(15) NOT NULL,
  email VARCHAR(120) NOT NULL,
  checkin DATE NOT NULL,
  checkout DATE NOT NULL,
  guests INT UNSIGNED NOT NULL,
  room_type ENUM('Standard Room','Apartment') NOT NULL,
  services VARCHAR(255),
  special VARCHAR(512),
  created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_name (name),
  INDEX idx_email (email),
  INDEX idx_checkin (checkin),
  INDEX idx_room_type (room_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT TEST ROW to verify table works
INSERT INTO bookings (name, phone, email, checkin, checkout, guests, room_type, services, special)
VALUES ('Test User', '9998887777', 'test@example.com', '2025-12-10', '2025-12-15', 2, 'Standard Room', 'Parking', 'None');





SELECT * FROM bookings;
SELECT * FROM bookings ORDER BY id DESC LIMIT 10;
