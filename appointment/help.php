<?php
/* ============================================================
   help.php: the help page the brief asks for. It explains how
   to book, what to type in each field, the workshop's rules,
   what each message means, and how the admin panel works.
   ============================================================ */
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Help';
$active = 'help';
$base = '';
require __DIR__ . '/includes/header.php';
?>

<section class="intro intro-compact">
  <div>
    <p class="eyebrow">Help</p>
    <h1>How online booking works</h1>
    <p class="lead">Everything you need to book your car in with the mechanic you want: the steps, what to type, the workshop's rules and what each message means.</p>
  </div>
</section>

<div class="help-grid">
  <nav class="panel help-toc" aria-label="Help topics">
    <h2>Topics</h2>
    <ol>
      <li><a href="#steps">Booking in 4 steps</a></li>
      <li><a href="#fields">What to type in each field</a></li>
      <li><a href="#rules">The workshop's rules</a></li>
      <li><a href="#messages">What the messages mean</a></li>
      <li><a href="#faq">Common questions</a></li>
      <li><a href="#admin-help">For the admin</a></li>
    </ol>
  </nav>

  <div class="help-content">
    <section class="panel" id="steps">
      <h2><i class="fa-solid fa-list-ol" aria-hidden="true"></i> Booking in 4 steps</h2>
      <ol class="steps">
        <li><strong>Fill in your details:</strong> your name, address and phone number.</li>
        <li><strong>Add your car:</strong> the license (registration) number from the number plate, and the engine number from the registration papers.</li>
        <li><strong>Pick a date.</strong> As soon as you choose it, the list of mechanics updates to show how many places each one has free that day.</li>
        <li><strong>Choose your mechanic and press "Book appointment".</strong> The server checks your booking straight away and either approves it, with a booking number, or tells you what to change.</li>
      </ol>
      <p><a class="btn btn-primary" href="index.php"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book now</a></p>
    </section>

    <section class="panel" id="fields">
      <h2><i class="fa-solid fa-keyboard" aria-hidden="true"></i> What to type in each field</h2>
      <div class="table-scroll">
        <table class="help-table">
          <thead>
            <tr><th scope="col">Field</th><th scope="col">What's allowed</th><th scope="col">Example</th></tr>
          </thead>
          <tbody>
            <tr><th scope="row">Full name</th><td>2 to 80 letters. Spaces, dots, hyphens and apostrophes are fine.</td><td>Md. Sakib Hossain</td></tr>
            <tr><th scope="row">Address</th><td>5 to 200 characters.</td><td>House 12, Road 5, Dhanmondi, Dhaka</td></tr>
            <tr><th scope="row">Phone number</th><td>Digits only (7 to 15), with no spaces, dashes or +.</td><td>01712345678</td></tr>
            <tr><th scope="row">Car license number</th><td>3 to 30 letters, numbers, spaces or hyphens.</td><td>DHAKA METRO GA 11-2345</td></tr>
            <tr><th scope="row">Car engine number</th><td>Digits only (4 to 20).</td><td>482910</td></tr>
            <tr><th scope="row">Appointment date</th><td>A real date from today up to <?= BOOKING_DAYS_AHEAD ?> days ahead.</td><td><?= e(nice_date(today())) ?></td></tr>
            <tr><th scope="row">Mechanic</th><td>Choose one mechanic from the list.</td><td>Abdul Karim</td></tr>
          </tbody>
        </table>
      </div>
      <p class="muted">Every field is required. The form checks your entries as you go, and the server checks them again when you book.</p>
    </section>

    <section class="panel" id="rules">
      <h2><i class="fa-solid fa-scale-balanced" aria-hidden="true"></i> The workshop's rules</h2>
      <ul class="check-list">
        <li><strong>Each mechanic works on at most 4 cars a day.</strong> The list shows how many of those 4 places are still free on the date you pick.</li>
        <li><strong>One appointment per client per day.</strong> The same phone number, or the same car, can only be booked once on a given date, with any mechanic.</li>
        <li><strong>Book from today up to <?= BOOKING_DAYS_AHEAD ?> days ahead.</strong> Dates in the past can't be booked.</li>
      </ul>
    </section>

    <section class="panel" id="messages">
      <h2><i class="fa-regular fa-comment-dots" aria-hidden="true"></i> What the messages mean</h2>
      <dl class="message-list">
        <dt><span class="badge badge-ok">Appointment approved</span></dt>
        <dd>Your booking is saved. Note your booking number (for example WS-00012) and bring your car on that day.</dd>
        <dt><span class="badge badge-warn">You already have an appointment on ...</span></dt>
        <dd>This phone number or car is already booked for that date. Pick another date. Only the workshop can change an existing booking.</dd>
        <dt><span class="badge badge-warn">... is fully booked on ...</span></dt>
        <dd>All 4 of that mechanic's places are taken for the day. Choose another mechanic, or another date to keep the same mechanic. You see this under the list as soon as you pick a full mechanic, and again if you press Book anyway.</dd>
        <dt><span class="badge badge-warn">Another booking was being saved at the same moment</span></dt>
        <dd>Two bookings reached the server at exactly the same time. Yours wasn't saved, so just press "Book appointment" again.</dd>
        <dt><span class="badge badge-error">Please fix the highlighted fields</span></dt>
        <dd>Something in the form isn't in the right format. The message under each red field says what to change.</dd>
      </dl>
    </section>

    <section class="panel" id="faq">
      <h2><i class="fa-solid fa-circle-question" aria-hidden="true"></i> Common questions</h2>
      <details>
        <summary>Can I choose a mechanic who is fully booked?</summary>
        <p>You can select them, but a warning appears under the list straight away, and if you press Book the system won't approve it: it tells you they're full that day. Try another mechanic, or a date when they have a free place.</p>
      </details>
      <details>
        <summary>How do I change or cancel my appointment?</summary>
        <p>Only the workshop's admin can change or cancel a booking. Give the workshop your booking number (for example WS-00012), and the admin can move it to another date or another available mechanic, or cancel it.</p>
      </details>
      <details>
        <summary>Can I book two cars on the same day?</summary>
        <p>No. A phone number, and a car, can each be booked only once on any date. Please book the second car for another day.</p>
      </details>
      <details>
        <summary>Why do the free places change while I'm on the page?</summary>
        <p>Other clients are booking too. The list is reloaded from the server every time you change the date, and again after you book, so you always see the current numbers.</p>
      </details>
      <details>
        <summary>Is my information safe?</summary>
        <p>Your details are only used for your appointment. Only the workshop's admin can see the appointment list, after logging in.</p>
      </details>
    </section>

    <section class="panel" id="admin-help">
      <h2><i class="fa-solid fa-user-gear" aria-hidden="true"></i> For the admin</h2>
      <ol class="steps">
        <li>Open <a href="admin/index.php">Admin</a> and log in.</li>
        <li>The table lists every upcoming appointment: client name, phone, car registration number, appointment date and mechanic. Use the filters to show one day, one mechanic, past appointments, or to search by name, phone or car number.</li>
        <li>Press <strong>Change</strong> to move an appointment to another date and/or another mechanic. Only mechanics with a free place on the chosen date can be picked.</li>
        <li>Press <strong>Cancel</strong> to delete an appointment. Its place becomes free again straight away.</li>
      </ol>
    </section>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
