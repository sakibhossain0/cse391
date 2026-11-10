<?php
/* ============================================================
   CSE391 Assignment 3 - Car Workshop Appointment System
   index.php: the User Panel. A client types in their details,
   picks a date, sees how many places each mechanic has free on
   that date, and books one.
   With JavaScript (assets/js/booking.js), every step talks to the
   server in the background: changing the date reloads the free
   places, and the form is sent to book.php without leaving the
   page. Without JavaScript, the same form works with page loads.
   ============================================================ */
require __DIR__ . '/includes/bootstrap.php';

/* messages and typed values left by book.php after a normal (non-JavaScript) post */
$flash = flash_take();
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
$confirmed = isset($_SESSION['confirmed']) ? $_SESSION['confirmed'] : null;
unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['confirmed']);

/* without JavaScript, the "Show free places" button posts the form back to this page */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    list($old) = validate_booking($_POST);
}

/* the day whose free places are listed: the client's choice if it's valid, otherwise today */
$date = isset($old['appointment_date']) && date_error($old['appointment_date']) === ''
    ? $old['appointment_date'] : today();
$mechanics = mechanics_on($date);
$selected = isset($old['mechanic_id']) ? (int) $old['mechanic_id'] : 0;

/* what the client typed last time in a field, or '' */
function old_value(array $old, $key)
{
    return isset($old[$key]) ? $old[$key] : '';
}

/* ' has-error' for a field that failed the server-side check */
function error_class(array $errors, $key)
{
    return isset($errors[$key]) ? ' has-error' : '';
}

function error_text(array $errors, $key)
{
    return isset($errors[$key]) ? e($errors[$key]) : '';
}

$pageTitle = 'Book an appointment';
$active = 'book';
$base = '';
$scripts = ['assets/js/booking.js'];
require __DIR__ . '/includes/header.php';
?>

<section class="intro">
  <div>
    <p class="eyebrow">Car workshop &middot; online booking</p>
    <h1>Book your mechanic online</h1>
    <p class="lead">Choose the senior mechanic you want, see how many places they have free on your day, and get your appointment approved straight away, without visiting the workshop first.</p>
  </div>
  <ul class="rules" aria-label="Booking rules">
    <li><i class="fa-solid fa-user-gear" aria-hidden="true"></i><span><strong><?= count($mechanics) ?></strong> senior mechanics</span></li>
    <li><i class="fa-solid fa-car-side" aria-hidden="true"></i><span><strong>4 cars</strong> per mechanic each day</span></li>
    <li><i class="fa-solid fa-calendar-day" aria-hidden="true"></i><span><strong>1 appointment</strong> per client each day</span></li>
  </ul>
</section>

<!-- results and messages appear here; screen readers announce them -->
<div id="message" class="message-area" role="status" aria-live="polite">
<?php if ($confirmed): ?>
  <div class="alert alert-success confirm">
    <h2><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Appointment approved</h2>
    <dl class="confirm-details">
      <dt>Booking number</dt><dd><?= e($confirmed['ref']) ?></dd>
      <dt>Name</dt><dd><?= e($confirmed['name']) ?></dd>
      <dt>Date</dt><dd><?= e($confirmed['dateLabel']) ?></dd>
      <dt>Mechanic</dt><dd><?= e($confirmed['mechanic']) ?></dd>
      <dt>Car</dt><dd><?= e($confirmed['car']) ?></dd>
    </dl>
    <p>Please bring your car to the workshop on that day, and keep your booking number.</p>
  </div>
<?php elseif ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>"><p><?= e($flash['message']) ?></p></div>
<?php endif; ?>
</div>

<form id="booking-form" class="booking" action="book.php" method="post" novalidate
      data-min="<?= e(today()) ?>" data-max="<?= e(last_bookable_day()) ?>" data-days="<?= BOOKING_DAYS_AHEAD ?>">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <div class="booking-grid">
    <section class="panel" aria-labelledby="step-details">
      <h2 id="step-details"><span class="step">1</span> Your details</h2>

      <div class="field<?= error_class($errors, 'client_name') ?>">
        <label for="client_name">Full name</label>
        <input type="text" id="client_name" name="client_name" maxlength="80" autocomplete="name" required
               value="<?= e(old_value($old, 'client_name')) ?>" aria-describedby="hint-client_name err-client_name">
        <p class="hint" id="hint-client_name">Letters only, for example Md. Sakib Hossain.</p>
        <p class="field-error" id="err-client_name"><?= error_text($errors, 'client_name') ?></p>
      </div>

      <div class="field<?= error_class($errors, 'address') ?>">
        <label for="address">Address</label>
        <textarea id="address" name="address" rows="2" maxlength="200" autocomplete="street-address" required
                  aria-describedby="hint-address err-address"><?= e(old_value($old, 'address')) ?></textarea>
        <p class="hint" id="hint-address">House, road and area, for example House 12, Road 5, Dhanmondi, Dhaka.</p>
        <p class="field-error" id="err-address"><?= error_text($errors, 'address') ?></p>
      </div>

      <div class="field<?= error_class($errors, 'phone') ?>">
        <label for="phone">Phone number</label>
        <input type="tel" id="phone" name="phone" inputmode="numeric" pattern="[0-9]{7,15}" maxlength="15" autocomplete="tel" required
               value="<?= e(old_value($old, 'phone')) ?>" aria-describedby="hint-phone err-phone">
        <p class="hint" id="hint-phone">Digits only, no spaces or dashes, for example 01712345678.</p>
        <p class="field-error" id="err-phone"><?= error_text($errors, 'phone') ?></p>
      </div>

      <h2 id="step-car"><span class="step">2</span> Your car</h2>

      <div class="field<?= error_class($errors, 'car_license') ?>">
        <label for="car_license">Car license (registration) number</label>
        <input type="text" id="car_license" name="car_license" maxlength="30" autocapitalize="characters" required
               pattern="[A-Za-z0-9][A-Za-z0-9 \-]{2,29}"
               value="<?= e(old_value($old, 'car_license')) ?>" aria-describedby="hint-car_license err-car_license">
        <p class="hint" id="hint-car_license">As on the number plate, for example DHAKA METRO GA 11-2345.</p>
        <p class="field-error" id="err-car_license"><?= error_text($errors, 'car_license') ?></p>
      </div>

      <div class="field<?= error_class($errors, 'car_engine') ?>">
        <label for="car_engine">Car engine number</label>
        <input type="text" id="car_engine" name="car_engine" inputmode="numeric" pattern="[0-9]{4,20}" maxlength="20" required
               value="<?= e(old_value($old, 'car_engine')) ?>" aria-describedby="hint-car_engine err-car_engine">
        <p class="hint" id="hint-car_engine">Digits only, from your car's registration papers.</p>
        <p class="field-error" id="err-car_engine"><?= error_text($errors, 'car_engine') ?></p>
      </div>
    </section>

    <section class="panel" aria-labelledby="step-when">
      <h2 id="step-when"><span class="step">3</span> Date and mechanic</h2>

      <div class="field<?= error_class($errors, 'appointment_date') ?>">
        <label for="appointment_date">Appointment date</label>
        <input type="date" id="appointment_date" name="appointment_date" required
               min="<?= e(today()) ?>" max="<?= e(last_bookable_day()) ?>" value="<?= e($date) ?>"
               aria-describedby="hint-appointment_date err-appointment_date">
        <p class="hint" id="hint-appointment_date">From today up to <?= BOOKING_DAYS_AHEAD ?> days ahead. The free places below change with the date.</p>
        <p class="field-error" id="err-appointment_date"><?= error_text($errors, 'appointment_date') ?></p>
      </div>

      <fieldset class="field mechanic-field<?= error_class($errors, 'mechanic_id') ?>" aria-describedby="err-mechanic_id">
        <legend>Choose your mechanic <span class="legend-note">free places on <span id="list-date"><?= e(nice_date($date)) ?></span></span></legend>
        <div id="mechanic-list" class="mechanic-list">
          <?php foreach ($mechanics as $m): $full = $m['free'] === 0; ?>
          <label class="mechanic<?= $full ? ' is-full' : '' ?>">
            <input type="radio" name="mechanic_id" value="<?= $m['id'] ?>"<?= $selected === $m['id'] ? ' checked' : '' ?>>
            <span class="mechanic-body">
              <span class="mechanic-name"><?= e($m['name']) ?></span>
              <span class="mechanic-meta"><?= e($m['speciality']) ?> &middot; <?= $m['experience'] ?> years</span>
              <span class="mechanic-free"><?= $full ? 'Fully booked' : '<strong>' . $m['free'] . '</strong> of ' . $m['limit'] . ' places free' ?></span>
              <span class="meter" aria-hidden="true"><span style="width: <?= round($m['free'] / max(1, $m['limit']) * 100) ?>%"></span></span>
            </span>
          </label>
          <?php endforeach; ?>
        </div>
        <p class="field-error" id="err-mechanic_id"><?= error_text($errors, 'mechanic_id') ?></p>
      </fieldset>

      <noscript>
        <p><button type="submit" class="btn btn-ghost" formaction="index.php" name="check" value="1">Show free places for this date</button></p>
      </noscript>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="book-btn"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book appointment</button>
        <a class="btn btn-ghost" href="help.php"><i class="fa-solid fa-circle-question" aria-hidden="true"></i> How booking works</a>
      </div>
    </section>
  </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
