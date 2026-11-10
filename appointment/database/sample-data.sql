-- ============================================================
-- sample-data.sql (optional): a few demo appointments, so the
-- admin list isn't empty and the "fully booked" rule is quick
-- to try. Import it AFTER schema.sql. The dates are worked out
-- from the day you import it, so they are always upcoming.
--
-- After importing: Abdul Karim has 3 of his 4 places taken
-- tomorrow, so booking him tomorrow works once and is then
-- refused as fully booked.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+06:00';   -- so CURDATE() is today in Bangladesh, even on a UTC server

INSERT INTO appointments
  (client_name, address, phone, car_license, car_engine, appointment_date, mechanic_id)
VALUES
  ('Tanvir Rahman',   'House 12, Road 5, Dhanmondi, Dhaka',  '01711000001', 'DHAKA METRO GA 11-2345', '482910', CURDATE() + INTERVAL 1 DAY, 1),
  ('Nusrat Jahan',    'Flat 3B, Mirpur 10, Dhaka',           '01811000002', 'DHAKA METRO KHA 12-8811', '573021', CURDATE() + INTERVAL 1 DAY, 1),
  ('Arif Chowdhury',  'Sector 7, Uttara, Dhaka',             '01911000003', 'DHAKA METRO GHA 13-4402', '660914', CURDATE() + INTERVAL 1 DAY, 1),
  ('Farzana Akter',   'College Road, Brahmanbaria',          '01511000004', 'BRAHMANBARIA HA 11-0057', '719330', CURDATE() + INTERVAL 1 DAY, 3),
  ('Mahmudul Hasan',  'Agrabad, Chattogram',                 '01611000005', 'CHATTA METRO GA 14-6620', '804477', CURDATE() + INTERVAL 2 DAY, 2),
  ('Sadia Islam',     'Zindabazar, Sylhet',                  '01311000006', 'SYLHET HA 12-3398',       '915562', CURDATE() + INTERVAL 3 DAY, 5);
