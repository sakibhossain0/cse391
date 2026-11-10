<?php
/* ============================================================
   admin/edit.php: the admin changes one appointment's date
   and/or mechanic. The mechanic list only lets them pick a
   mechanic who has a free place on the chosen date (admin.js
   reloads it from the server when the date changes), and
   update_booking() checks the same rules again on the server.
   ============================================================ */
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$base = '../';
$active = 'admin';

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$id = (int) field($isPost ? $_POST : $_GET, 'id');
$appointment = find_appointment($id);

if (!$appointment) {
    flash_set('error', 'That appointment doesn\'t exist any more.');
    redirect('index.php');
}

/* start from the appointment's current values */
$date = $appointment['appointment_date'];
$mechanicId = (int) $appointment['mechanic_id'];
$error = '';

if ($isPost) {
    $date = field($_POST, 'appointment_date');
    $mechanicId = (int) field($_POST, 'mechanic_id');
    $dateProblem = date_error($date);

    if (!csrf_valid(field($_POST, 'csrf'))) {
        $error = 'Your session expired. Please try again.';
    } elseif ($dateProblem !== '') {
        $error = $dateProblem;
    } elseif ($mechanicId <= 0) {
        $error = 'Please choose a mechanic.';
    } else {
        $result = update_booking($id, $date, $mechanicId);
        if ($result['ok']) {
            flash_set('success', 'Saved: ' . booking_ref($id) . ' for ' . $appointment['client_name']
                . ' is now on ' . nice_date($date) . ' with ' . $result['mechanic'] . '.');
            redirect('index.php');
        }
        $error = $result['message'];
    }
}

/* free places for the date in the form, leaving this appointment out of the
   count (if that date isn't bookable any more, today's places are shown) */
$listDate = date_error($date) === '' ? $date : today();
$mechanics = mechanics_on($listDate, $id);

$pageTitle = 'Change appointment ' . booking_ref($id);
$scripts = ['assets/js/admin.js'];
require dirname(__DIR__) . '/includes/header.php';
?>

<section class="admin-head">
  <div>
    <p class="eyebrow"><a href="index.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Appointments</a></p>
    <h1>Change appointment <?= e(booking_ref($id)) ?></h1>
  </div>
</section>

<?php if ($error !== ''): ?>
  <div class="alert alert-error" role="alert"><p><?= e($error) ?></p></div>
<?php endif; ?>

<div class="edit-grid">
  <section class="panel" aria-labelledby="client-title">
    <h2 id="client-title">Client</h2>
    <dl class="details">
      <dt>Name</dt><dd><?= e($appointment['client_name']) ?></dd>
      <dt>Phone</dt><dd class="mono"><?= e($appointment['phone']) ?></dd>
      <dt>Address</dt><dd><?= e($appointment['address']) ?></dd>
      <dt>Car registration no.</dt><dd class="mono"><?= e($appointment['car_license']) ?></dd>
      <dt>Car engine no.</dt><dd class="mono"><?= e($appointment['car_engine']) ?></dd>
      <dt>Booked on</dt><dd><?= e(date('j M Y, g:i a', strtotime($appointment['created_at']))) ?></dd>
      <dt>Current appointment</dt><dd><?= e(nice_date($appointment['appointment_date'])) ?>, with <?= e($appointment['mechanic_name']) ?></dd>
    </dl>
  </section>

  <form class="panel" id="edit-form" action="edit.php" method="post" data-id="<?= $id ?>">
    <h2>New date and mechanic</h2>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="field">
      <label for="appointment_date">Appointment date</label>
      <input type="date" id="appointment_date" name="appointment_date" required
             min="<?= e(today()) ?>" max="<?= e(last_bookable_day()) ?>" value="<?= e($date) ?>">
      <p class="field-error" id="err-date" role="alert"></p>
    </div>

    <div class="field">
      <label for="mechanic_id">Mechanic</label>
      <select id="mechanic_id" name="mechanic_id" required aria-describedby="mechanic-note">
        <option value="">Choose a mechanic</option>
        <?php foreach ($mechanics as $m): $full = $m['free'] === 0; ?>
          <option value="<?= $m['id'] ?>"<?= $full ? ' disabled' : '' ?><?= !$full && $m['id'] === $mechanicId ? ' selected' : '' ?>>
            <?= e($m['name']) ?>: <?= $full ? 'fully booked' : $m['free'] . ' of ' . $m['limit'] . ' places free' ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="hint" id="mechanic-note">Free places on <span id="list-date"><?= e(nice_date($listDate)) ?></span>, not counting this appointment. Fully booked mechanics can't be chosen.</p>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save changes</button>
      <a class="btn btn-ghost" href="index.php">Back to the list</a>
    </div>
  </form>
</div>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
