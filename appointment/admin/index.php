<?php
/* ============================================================
   admin/index.php: the Admin Panel. Until the admin logs in it
   shows a login form. After that it lists the appointments with
   the client name, phone, car registration number, appointment
   date and mechanic name, with filters and Change / Cancel buttons.
   ============================================================ */
require dirname(__DIR__) . '/includes/bootstrap.php';

$base = '../';
$active = 'admin';

/* ---------- 1. Not logged in: the login form ---------- */

if (!is_admin()) {
    $loginError = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
        if (!csrf_valid(field($_POST, 'csrf'))) {
            $loginError = 'Your session expired. Please try again.';
        } elseif (admin_login(field($_POST, 'username'), $password)) {
            redirect('index.php');
        } else {
            usleep(500000);   // half a second's pause slows down anyone guessing passwords
            $loginError = 'Wrong username or password.';
        }
    }

    $flash = flash_take();
    $pageTitle = 'Admin login';
    require dirname(__DIR__) . '/includes/header.php';
    ?>
    <section class="login-wrap">
      <form class="panel login" action="index.php" method="post">
        <h1><i class="fa-solid fa-lock" aria-hidden="true"></i> Admin login</h1>
        <p class="muted">The appointment list is only for the workshop's admin.</p>
        <?php if (ADMIN_PASSWORD_HASH === ''): ?>
          <div class="alert alert-warn"><p>No admin password has been set up yet. Put a password hash in <code>includes/config.php</code> (the steps are in that file).</p></div>
        <?php endif; ?>
        <?php if ($flash): ?>
          <div class="alert alert-<?= e($flash['type']) ?>"><p><?= e($flash['message']) ?></p></div>
        <?php endif; ?>
        <?php if ($loginError !== ''): ?>
          <div class="alert alert-error" role="alert"><p><?= e($loginError) ?></p></div>
        <?php endif; ?>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="field">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" autocomplete="username" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Log in</button>
      </form>
    </section>
    <?php
    require dirname(__DIR__) . '/includes/footer.php';
    exit;
}


/* ---------- 2. Logged in: the appointment list ---------- */

$filter = [
    'date' => field($_GET, 'date'),
    'mechanic' => (int) field($_GET, 'mechanic'),
    'q' => field($_GET, 'q'),
    'past' => field($_GET, 'past') === '1',
];
$filterNote = '';
if ($filter['date'] !== '' && !is_real_date($filter['date'])) {
    $filterNote = 'That date isn\'t a real date, so it was ignored.';
    $filter['date'] = '';
}

$appointments = find_appointments($filter);
$mechanicList = all_mechanics();

/* "3 appointments from today onwards", so it's clear which days are listed */
$countText = count($appointments) . ' appointment' . (count($appointments) === 1 ? '' : 's');
if ($filter['date'] !== '') {
    $countText .= ' on ' . nice_date($filter['date']) . '.';
} elseif ($filter['past']) {
    $countText .= ', including past days.';
} else {
    $countText .= ' from today onwards. Tick "Include past days" to see older ones.';
}

/* the "free places" overview is for the filtered day, or today */
$overviewDay = $filter['date'] !== '' ? $filter['date'] : today();
$overview = mechanics_on($overviewDay);

$flash = flash_take();
$pageTitle = 'Appointments';
$scripts = ['assets/js/admin.js'];
require dirname(__DIR__) . '/includes/header.php';
?>

<section class="admin-head">
  <div>
    <p class="eyebrow">Admin panel</p>
    <h1>Appointments</h1>
  </div>
  <form action="logout.php" method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <button type="submit" class="btn btn-ghost"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Log out</button>
  </form>
</section>

<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>" role="status"><p><?= e($flash['message']) ?></p></div>
<?php endif; ?>
<?php if ($filterNote !== ''): ?>
  <div class="alert alert-warn" role="status"><p><?= e($filterNote) ?></p></div>
<?php endif; ?>

<section class="panel" aria-labelledby="overview-title">
  <h2 id="overview-title">Free places on <?= e(nice_date($overviewDay)) ?></h2>
  <ul class="overview">
    <?php foreach ($overview as $m): ?>
    <li class="<?= $m['free'] === 0 ? 'is-full' : '' ?>">
      <span class="overview-name"><?= e($m['name']) ?></span>
      <span class="overview-count"><strong><?= $m['free'] ?></strong> of <?= $m['limit'] ?> free</span>
      <span class="meter" aria-hidden="true"><span style="width: <?= round($m['free'] / max(1, $m['limit']) * 100) ?>%"></span></span>
    </li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="panel" aria-labelledby="list-title">
  <h2 id="list-title">Appointment list</h2>

  <form class="filters" action="index.php" method="get">
    <div class="field">
      <label for="f-date">Date</label>
      <input type="date" id="f-date" name="date" value="<?= e($filter['date']) ?>">
    </div>
    <div class="field">
      <label for="f-mechanic">Mechanic</label>
      <select id="f-mechanic" name="mechanic">
        <option value="0">All mechanics</option>
        <?php foreach ($mechanicList as $m): ?>
          <option value="<?= (int) $m['id'] ?>"<?= $filter['mechanic'] === (int) $m['id'] ? ' selected' : '' ?>><?= e($m['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="f-q">Search</label>
      <input type="search" id="f-q" name="q" value="<?= e($filter['q']) ?>" placeholder="Name, phone or car number">
    </div>
    <label class="check"><input type="checkbox" name="past" value="1"<?= $filter['past'] ? ' checked' : '' ?>> Include past days</label>
    <div class="filter-actions">
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter" aria-hidden="true"></i> Filter</button>
      <a class="btn btn-ghost" href="index.php">Reset</a>
    </div>
  </form>

  <p class="result-count"><?= e($countText) ?></p>

  <?php if (!$appointments): ?>
    <p class="empty"><i class="fa-regular fa-calendar" aria-hidden="true"></i> No appointments match these filters.</p>
  <?php else: ?>
  <div class="table-scroll">
    <table class="appointments">
      <thead>
        <tr>
          <th scope="col">Booking no.</th>
          <th scope="col">Client name</th>
          <th scope="col">Phone</th>
          <th scope="col">Car registration no.</th>
          <th scope="col">Appointment date</th>
          <th scope="col">Mechanic</th>
          <th scope="col"><span class="sr-only">Actions</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($appointments as $a): ?>
        <tr<?= $a['appointment_date'] < today() ? ' class="is-past"' : '' ?>>
          <td data-label="Booking no."><span class="mono nowrap"><?= e(booking_ref($a['id'])) ?></span></td>
          <td data-label="Client name"><?= e($a['client_name']) ?></td>
          <td data-label="Phone"><span class="mono nowrap"><?= e($a['phone']) ?></span></td>
          <td data-label="Car registration no."><span class="mono"><?= e($a['car_license']) ?></span></td>
          <td data-label="Appointment date"><span class="nowrap"><?= e(date('j M Y', strtotime($a['appointment_date']))) ?></span></td>
          <td data-label="Mechanic"><?= e($a['mechanic_name']) ?></td>
          <td class="actions">
            <a class="btn btn-small" href="edit.php?id=<?= (int) $a['id'] ?>"><i class="fa-solid fa-pen" aria-hidden="true"></i> Change</a>
            <form action="delete.php" method="post" class="js-confirm" data-confirm="Cancel <?= e(booking_ref($a['id'])) ?> for <?= e($a['client_name']) ?>? This can't be undone.">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
              <button type="submit" class="btn btn-small btn-danger"><i class="fa-solid fa-xmark" aria-hidden="true"></i> Cancel</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>

<aside class="panel help-note">
  <h2><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Admin help</h2>
  <p><strong>Change</strong> moves an appointment to another date and/or mechanic. Only mechanics with a free place that day can be chosen, and a client still can't have two appointments on one day. <strong>Cancel</strong> deletes the appointment and frees its place. More in the <a href="../help.php#admin-help">help page</a>.</p>
</aside>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
