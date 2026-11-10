<?php
/* ============================================================
   book.php: receives the booking form (POST) and decides.
     - booking.js sends it with fetch and gets a JSON answer
     - without JavaScript the browser posts here normally and is
       sent back to index.php with a message (Post/Redirect/Get)
   The order of the checks:
     1. the CSRF token is right (the form really came from our page)
     2. every field passes validate_booking()
     3. create_booking(): no other appointment for this client
        that day, and the mechanic still has a free place
   A refused booking is still a normal answer (HTTP 200): the JSON's
   "ok" and "code" say what happened, and "message" is shown to the
   client. Error codes are kept for real faults (a bad token, a crash).
   ============================================================ */
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$ajax = wants_json();

/* sends the visitor back to the form with a message (non-JavaScript only) */
function back_to_form($type, $message, array $old = [], array $errors = [])
{
    flash_set($type, $message);
    $_SESSION['old'] = $old;
    $_SESSION['errors'] = $errors;
    redirect('index.php');
}

/* 1. the CSRF token */
if (!csrf_valid(field($_POST, 'csrf'))) {
    $message = 'Your session has expired. Please reload the page and try again.';
    if ($ajax) {
        json_response(['ok' => false, 'code' => 'csrf', 'message' => $message], 400);
    }
    back_to_form('error', $message);
}

/* 2. the fields */
list($booking, $errors) = validate_booking($_POST);
if ($errors) {
    $message = 'Please fix the highlighted fields and try again.';
    if ($ajax) {
        json_response(['ok' => false, 'code' => 'invalid', 'message' => $message, 'errors' => $errors]);
    }
    back_to_form('error', $message, $booking, $errors);
}

/* 3. the booking rules (in the database) */
$result = create_booking($booking);

if (!$result['ok']) {
    if ($ajax) {
        /* send the fresh free places too, so the list on the page is up to date */
        $result['mechanics'] = mechanics_on($booking['appointment_date']);
        json_response($result);
    }
    back_to_form('error', $result['message'], $booking);
}

$details = [
    'ref' => booking_ref($result['id']),
    'name' => $booking['client_name'],
    'date' => $booking['appointment_date'],
    'dateLabel' => nice_date($booking['appointment_date']),
    'mechanic' => $result['mechanic'],
    'car' => $booking['car_license'],
];

if ($ajax) {
    json_response([
        'ok' => true,
        'message' => 'Appointment approved.',
        'booking' => $details,
        'mechanics' => mechanics_on($booking['appointment_date']),
    ]);
}

$_SESSION['confirmed'] = $details;
redirect('index.php');
