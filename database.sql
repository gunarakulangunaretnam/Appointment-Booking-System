-- Appointment Booking System
-- Import this file in phpMyAdmin (XAMPP)

CREATE DATABASE IF NOT EXISTS appointment_booking
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE appointment_booking;

DROP TABLE IF EXISTS appointments;

CREATE TABLE appointments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(150) NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  notes VARCHAR(500) DEFAULT NULL,
  status ENUM('booked', 'cancelled') NOT NULL DEFAULT 'booked',
  booked_slot_key VARCHAR(32) GENERATED ALWAYS AS (
    IF(status = 'booked', CONCAT(appointment_date, ' ', appointment_time), NULL)
  ) STORED,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_booked_slot (booked_slot_key),
  KEY idx_status_date (status, appointment_date),
  KEY idx_date_time (appointment_date, appointment_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO appointments (title, appointment_date, appointment_time, notes, status) VALUES
('Team Review Meeting', '2026-10-15', '14:00:00', 'Quarterly progress review', 'booked'),
('Client Consultation', '2026-10-16', '10:00:00', 'Discuss project scope', 'booked'),
('Dental Checkup', '2026-10-17', '09:30:00', NULL, 'booked'),
('Project Planning', '2026-10-20', '15:00:00', 'Sprint planning session', 'booked'),
('Follow-up Call', '2026-10-14', '11:00:00', 'Cancelled by client', 'cancelled');
