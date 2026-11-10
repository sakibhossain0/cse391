<?php
/* ============================================================
   availability.php: answers the page's background requests
   (fetch) with JSON. Given a date, it returns every active
   mechanic with how many places are free that day.
   It only tells free places, never who has booked: whether a
   client already has an appointment is checked by book.php when
   they press Book, so nobody can look up someone else's bookings.
   ============================================================ */
require __DIR__ . '/includes/bootstrap.php';

/* booking.js and admin.js send a POST; a plain GET link works too */
$source = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$date = field($source, 'appointment_date');

$problem = date_error($date);
if ($problem !== '') {
    json_response(['ok' => false, 'message' => $problem]);
}

/* only the admin may leave an appointment out of the counts (when moving it) */
$ignoreId = is_admin() ? (int) field($source, 'ignore') : 0;

json_response([
    'ok' => true,
    'date' => $date,
    'dateLabel' => nice_date($date),
    'mechanics' => mechanics_on($date, $ignoreId),
]);
