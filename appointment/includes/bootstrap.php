<?php
/* ============================================================
   CSE391 Assignment 3 - Car Workshop Appointment System
   bootstrap.php: every page loads this first. It reads the
   settings, starts the session, and makes the database and the
   helper functions available.
   ============================================================ */

/* the other include files refuse to run unless this is set,
   so nobody can open them directly in the browser */
define('APP_RUNNING', true);

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Setup needed: copy includes/config.sample.php to includes/config.php and add your database details.');
}
require $configFile;

/* show PHP errors on the page only while developing */
ini_set('display_errors', APP_DEBUG ? '1' : '0');
error_reporting(E_ALL);

/* a few standard safety headers: no showing these pages inside another
   site's frame (clickjacking), no guessing file types, and no full
   addresses sent to other sites */
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

/* the session remembers flash messages, the CSRF token and the admin
   login. Its cookie has its own name (so other PHP apps on the same
   host don't share it), can't be read by JavaScript (httponly), and
   isn't sent with forms posted from other websites (SameSite=Lax).
   Strict mode makes PHP refuse session ids it didn't create itself. */
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
ini_set('session.use_strict_mode', '1');
session_name('workshop_sid');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $https,
]);
session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';

/* if something unexpected goes wrong (for example the database is down),
   log the real error and show the visitor a short, friendly message */
set_exception_handler(function ($error) {
    error_log('Workshop app: ' . $error);
    $message = 'Sorry, something went wrong on the server. Please try again in a minute.';
    if (APP_DEBUG) {
        $message .= ' (' . $error->getMessage() . ')';
    }
    if (wants_json()) {
        json_response(['ok' => false, 'code' => 'server', 'message' => $message], 500);
    }
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Server error</title></head><body><p>'
        . e($message) . '</p></body></html>';
});
