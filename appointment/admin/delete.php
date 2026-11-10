<?php
/* ============================================================
   admin/delete.php: cancels (deletes) one appointment. It only
   accepts a POST from the admin's own page, with a valid CSRF
   token, and then goes back to the list with a message.
   ============================================================ */
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid(field($_POST, 'csrf'))) {
    flash_set('error', 'The appointment was not cancelled. Please use the Cancel button in the list.');
    redirect('index.php');
}

$id = (int) field($_POST, 'id');
$appointment = find_appointment($id);

if ($appointment && delete_booking($id)) {
    flash_set('success', 'Cancelled ' . booking_ref($id) . ' for ' . $appointment['client_name']
        . ' on ' . nice_date($appointment['appointment_date']) . '. The place is free again.');
} else {
    flash_set('error', 'That appointment doesn\'t exist any more.');
}

redirect('index.php');
