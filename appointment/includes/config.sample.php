<?php
/* ============================================================
   config.sample.php: the settings template.
   Copy this file to config.php (in this same folder) and put
   your own details in it. config.php is listed in .gitignore,
   so your database password never goes to GitHub.
   ============================================================ */
defined('APP_RUNNING') or exit('No direct access.');

/* ---------- Database ----------
   XAMPP: host localhost, user root, empty password.
   Free web host: copy the MySQL host name, database name, user
   name and password from its control panel. */
define('DB_HOST', 'localhost');
define('DB_NAME', 'cse391_workshop');
define('DB_USER', 'root');
define('DB_PASS', '');

/* ---------- Admin login ----------
   The password is stored only as a hash, never as plain text, and
   this file comes with none, so every copy of the site gets its own
   password. Choose one, make its hash with PHP (XAMPP: C:\xampp\php\php.exe):
       php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
   and paste the result between the quotes below. Until then nobody
   can log in to the admin panel. */
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '');

/* ---------- Booking rules ---------- */
define('BOOKING_DAYS_AHEAD', 60);      // clients can book from today up to 60 days ahead
date_default_timezone_set('Asia/Dhaka');

/* true while developing (PHP errors are shown on the page),
   false on the live site (errors only go to the server log) */
define('APP_DEBUG', false);
