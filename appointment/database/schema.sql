-- ============================================================
-- CSE391 Assignment 3 - Car Workshop Appointment System
-- schema.sql: creates the two tables and adds the 5 mechanics.
--
-- How to use it:
--   1. Create an empty database (XAMPP: phpMyAdmin > New > cse391_workshop;
--      a free host creates one for you in its control panel).
--   2. Open that database in phpMyAdmin > Import, and choose this file.
-- ============================================================

SET NAMES utf8mb4;

DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS mechanics;

-- ------------------------------------------------------------
-- mechanics: the senior mechanics a client can choose from.
-- daily_limit is the most cars a mechanic takes on one day (4).
-- ------------------------------------------------------------
CREATE TABLE mechanics (
  id               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  name             VARCHAR(80)      NOT NULL,
  speciality       VARCHAR(120)     NOT NULL DEFAULT '',
  experience_years TINYINT UNSIGNED NOT NULL DEFAULT 0,
  daily_limit      TINYINT UNSIGNED NOT NULL DEFAULT 4,
  is_active        TINYINT(1)       NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- appointments: one row per booking.
-- The two UNIQUE keys make the database itself refuse a second
-- appointment for the same phone number, or the same car, on the
-- same date ("one appointment per client per day").
-- ------------------------------------------------------------
CREATE TABLE appointments (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_name      VARCHAR(80)  NOT NULL,
  address          VARCHAR(200) NOT NULL,
  phone            VARCHAR(15)  NOT NULL,
  car_license      VARCHAR(30)  NOT NULL,
  car_engine       VARCHAR(20)  NOT NULL,
  appointment_date DATE         NOT NULL,
  mechanic_id      INT UNSIGNED NOT NULL,
  created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP    NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_phone_per_day (phone, appointment_date),
  UNIQUE KEY uq_car_per_day (car_license, appointment_date),
  KEY idx_mechanic_day (mechanic_id, appointment_date),
  CONSTRAINT fk_appointment_mechanic
    FOREIGN KEY (mechanic_id) REFERENCES mechanics (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- The workshop's 5 senior mechanics (4 cars each per day)
-- ------------------------------------------------------------
INSERT INTO mechanics (name, speciality, experience_years, daily_limit) VALUES
  ('Abdul Karim',    'Engine and gearbox',          14, 4),
  ('Rafiq Ahmed',    'Brakes and suspension',        9, 4),
  ('Shahidul Islam', 'Electrical and diagnostics',  11, 4),
  ('Nasir Uddin',    'AC and cooling system',        8, 4),
  ('Jamal Hossain',  'General service and tuning',  16, 4);
