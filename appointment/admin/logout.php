<?php
/* ============================================================
   admin/logout.php: logs the admin out (POST with a CSRF token,
   so another website can't log you out) and shows the login form.
   ============================================================ */
require dirname(__DIR__) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid(field($_POST, 'csrf'))) {
    unset($_SESSION['admin']);
    session_regenerate_id(true);
    flash_set('success', 'You have logged out.');
}

redirect('index.php');
