<?php
/* ============================================================
   functions.php: the shared helpers and all the booking rules.
     1. Small helpers (escaping, JSON, redirects, flash messages)
     2. Security (CSRF tokens, admin login)
     3. Dates
     4. Checking the booking form
     5. Mechanics and their free places
     6. Booking, changing and cancelling appointments
     7. The admin's appointment list
   ============================================================ */
defined('APP_RUNNING') or exit('No direct access.');


/* ---------- 1. Small helpers ---------- */

/* Escapes text before it goes into HTML, so anything a visitor
   typed shows as plain text and can never run as code (XSS). */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* True when the request came from our JavaScript (fetch) and wants a JSON answer. */
function wants_json()
{
    $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
    return strpos($accept, 'application/json') !== false;
}

/* Sends a JSON answer and stops the script. */
function json_response(array $data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data);
    exit;
}

/* Sends the browser to another page and stops. Used after a form post
   (Post/Redirect/Get), so refreshing the page can't send the form twice. */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/* A one-time message kept in the session until the next page shows it. */
function flash_set($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_take()
{
    $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
    unset($_SESSION['flash']);
    return $flash;
}

/* Reads one field from $_POST or $_GET as a trimmed string ('' if missing).
   The /u pattern also trims Unicode spaces (like a no-break space), the
   same way JavaScript's trim() does in booking.js. */
function field(array $source, $key)
{
    if (!isset($source[$key]) || !is_string($source[$key])) {
        return '';
    }
    return (string) preg_replace('/^\s+|\s+$/u', '', $source[$key]);
}

/* Booking number shown to the client: 7 becomes "WS-00007". */
function booking_ref($id)
{
    return 'WS-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
}


/* ---------- 2. Security ---------- */

/* CSRF token: a random secret saved in the session and sent with every
   form. A form posted from another website won't have it, so it's refused. */
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid($token)
{
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function is_admin()
{
    return !empty($_SESSION['admin']);
}

/* The admin pages call this first: anyone not logged in is sent to the login form. */
function require_admin()
{
    if (!is_admin()) {
        if (wants_json()) {
            json_response(['ok' => false, 'code' => 'login', 'message' => 'Please log in as admin first.'], 401);
        }
        redirect('index.php');
    }
}

/* Checks the admin's username and password. The password is compared with
   password_verify() against the stored hash, never as plain text. */
function admin_login($username, $password)
{
    $ok = ADMIN_PASSWORD_HASH !== ''
        && hash_equals(ADMIN_USERNAME, $username)
        && password_verify($password, ADMIN_PASSWORD_HASH);
    if ($ok) {
        session_regenerate_id(true);   // a fresh session id after logging in (stops session fixation)
        $_SESSION['csrf'] = bin2hex(random_bytes(32));   // and a fresh CSRF token
        $_SESSION['admin'] = true;
    }
    return $ok;
}


/* ---------- 3. Dates ---------- */

function today()
{
    return date('Y-m-d');
}

function last_bookable_day()
{
    return date('Y-m-d', strtotime('+' . BOOKING_DAYS_AHEAD . ' days'));
}

/* True if $date is a real calendar date written as YYYY-MM-DD
   (so 2026-02-30 or "next friday" are refused). */
function is_real_date($date)
{
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

/* Returns an error message, or '' if $date is a real date (YYYY-MM-DD)
   between today and the last day that can be booked. Dates in this
   format compare correctly as plain text, so < and > work on them. */
function date_error($date)
{
    if ($date === '') {
        return 'Please choose an appointment date.';
    }
    if (!is_real_date($date)) {
        return 'Please enter a real date, like ' . today() . '.';
    }
    if ($date < today()) {
        return 'The appointment date can\'t be in the past.';
    }
    if ($date > last_bookable_day()) {
        return 'Appointments can be booked up to ' . BOOKING_DAYS_AHEAD . ' days ahead.';
    }
    return '';
}

/* 2026-11-12 becomes "Thursday, 12 November 2026" */
function nice_date($date)
{
    return date('l, j F Y', strtotime($date));
}


/* ---------- 4. Checking the booking form ---------- */

/* Tidies and checks every field of the booking form.
   Returns [$clean, $errors]: $clean holds the tidied values,
   and $errors maps a field name to a message for each problem.
   booking.js runs the same checks in the browser first. */
function validate_booking(array $in)
{
    $clean = [
        'client_name' => (string) preg_replace('/\s+/u', ' ', field($in, 'client_name')),
        'address' => (string) preg_replace('/\s+/u', ' ', field($in, 'address')),
        'phone' => field($in, 'phone'),
        'car_license' => normalise_license(field($in, 'car_license')),
        'car_engine' => field($in, 'car_engine'),
        'appointment_date' => field($in, 'appointment_date'),
        'mechanic_id' => field($in, 'mechanic_id'),
    ];
    $errors = [];

    /* name: letters, spaces, dots, hyphens and apostrophes. \p{L} is a letter in
       any language and \p{M} a mark such as a Bangla vowel sign, so "সাকিব" works too */
    if ($clean['client_name'] === '') {
        $errors['client_name'] = 'Please enter your name.';
    } elseif (!preg_match("/^\\p{L}[\\p{L}\\p{M} .'-]{1,79}$/u", $clean['client_name'])) {
        $errors['client_name'] = 'Use 2 to 80 letters. Spaces, dots, hyphens and apostrophes are fine.';
    }

    if ($clean['address'] === '') {
        $errors['address'] = 'Please enter your address.';
    } elseif (mb_strlen($clean['address']) < 5 || mb_strlen($clean['address']) > 200) {
        $errors['address'] = 'The address should be 5 to 200 characters long.';
    }

    /* phone: numbers only, as the brief asks */
    if ($clean['phone'] === '') {
        $errors['phone'] = 'Please enter your phone number.';
    } elseif (!preg_match('/^[0-9]{7,15}$/', $clean['phone'])) {
        $errors['phone'] = 'Use digits only (7 to 15 of them), like 01712345678.';
    }

    if ($clean['car_license'] === '') {
        $errors['car_license'] = 'Please enter the car\'s license (registration) number.';
    } elseif (!preg_match('/^[A-Z0-9][A-Z0-9 -]{2,29}$/', $clean['car_license'])) {
        $errors['car_license'] = 'Use 3 to 30 letters, numbers, spaces or hyphens, like DHAKA METRO GA 11-2345.';
    }

    /* engine number: numbers only, as the brief asks */
    if ($clean['car_engine'] === '') {
        $errors['car_engine'] = 'Please enter the car\'s engine number.';
    } elseif (!preg_match('/^[0-9]{4,20}$/', $clean['car_engine'])) {
        $errors['car_engine'] = 'Use digits only (4 to 20 of them).';
    }

    $dateProblem = date_error($clean['appointment_date']);
    if ($dateProblem !== '') {
        $errors['appointment_date'] = $dateProblem;
    }

    /* the mechanic must be picked from the list; whether they exist and
       still have room is checked against the database when booking */
    if (!ctype_digit($clean['mechanic_id'])) {
        $errors['mechanic_id'] = 'Please choose a mechanic from the list.';
    }

    return [$clean, $errors];
}

/* "dhaka  metro ga 11-2345" becomes "DHAKA METRO GA 11-2345", so the same
   car is always stored the same way */
function normalise_license($license)
{
    return strtoupper((string) preg_replace('/\s+/', ' ', trim($license)));
}

/* The plate with its spaces and hyphens taken out, for comparing two plates:
   "DHAKA METRO GA 11-2345" and "DHAKA-METRO-GA 112345" are the same car. */
function plate_key($license)
{
    return str_replace([' ', '-'], '', normalise_license($license));
}


/* ---------- 5. Mechanics and their free places ---------- */

/* Every active mechanic, with how many cars they already have and how
   many places are still free on $date. $ignoreId leaves one appointment
   out of the count (used when the admin moves that appointment). */
function mechanics_on($date, $ignoreId = 0)
{
    $stmt = db()->prepare(
        'SELECT m.id, m.name, m.speciality, m.experience_years, m.daily_limit,
                COUNT(a.id) AS booked
           FROM mechanics m
           LEFT JOIN appointments a
                  ON a.mechanic_id = m.id
                 AND a.appointment_date = :day
                 AND a.id <> :ignore
          WHERE m.is_active = 1
          GROUP BY m.id, m.name, m.speciality, m.experience_years, m.daily_limit
          ORDER BY m.id'
    );
    $stmt->execute(['day' => $date, 'ignore' => (int) $ignoreId]);

    $mechanics = [];
    foreach ($stmt->fetchAll() as $row) {
        $limit = (int) $row['daily_limit'];
        $booked = (int) $row['booked'];
        $mechanics[] = [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'speciality' => $row['speciality'],
            'experience' => (int) $row['experience_years'],
            'limit' => $limit,
            'booked' => $booked,
            'free' => max(0, $limit - $booked),
        ];
    }
    return $mechanics;
}

/* Has this client (the same phone number OR the same car) already booked
   any mechanic on $date? Plates are compared without their spaces and
   hyphens (see plate_key). $ignoreId skips one appointment (the one being moved). */
function client_has_booking($phone, $license, $date, $ignoreId = 0)
{
    $stmt = db()->prepare(
        "SELECT COUNT(*) FROM appointments
          WHERE appointment_date = :day
            AND (phone = :phone OR REPLACE(REPLACE(car_license, ' ', ''), '-', '') = :plate)
            AND id <> :ignore"
    );
    $stmt->execute(['day' => $date, 'phone' => $phone, 'plate' => plate_key($license), 'ignore' => (int) $ignoreId]);
    return (int) $stmt->fetchColumn() > 0;
}


/* ---------- 6. Booking, changing and cancelling appointments ---------- */

/* An answer for a refused booking: a short code plus a message for the client. */
function refused($code, $message)
{
    return ['ok' => false, 'code' => $code, 'message' => $message];
}

/* True when MySQL gave up on a transaction because another one was saving
   at the same moment: error 1213 (deadlock) or 1205 (waited too long for a
   lock). Nothing was saved, so the client can simply try again. */
function is_busy_error(PDOException $error)
{
    $code = isset($error->errorInfo[1]) ? (int) $error->errorInfo[1] : 0;
    return $code === 1213 || $code === 1205;
}

/* Books an appointment after the two checks the brief asks for:
     1. the client has no other appointment on that date
     2. the chosen mechanic still has a free place that day
   Everything runs inside one transaction. The mechanic's row is locked
   (FOR UPDATE), so two clients booking the same mechanic at the same
   moment are handled one after the other and the limit of 4 always holds.
   Returns ['ok' => true, 'id' => ..., 'mechanic' => ...] or a refusal. */
function create_booking(array $b)
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        /* the lock must be the transaction's first query: MySQL's default
           mode (REPEATABLE READ) takes a snapshot of the data at the first
           ordinary SELECT, so the counts below are made after we hold the
           lock and include every booking saved before we got it */
        $stmt = $pdo->prepare('SELECT id, name, daily_limit FROM mechanics WHERE id = ? AND is_active = 1 FOR UPDATE');
        $stmt->execute([(int) $b['mechanic_id']]);
        $mechanic = $stmt->fetch();
        if (!$mechanic) {
            $pdo->rollBack();
            return refused('mechanic', 'Please choose a mechanic from the list.');
        }

        /* check 1: one appointment per client per day */
        if (client_has_booking($b['phone'], $b['car_license'], $b['appointment_date'])) {
            $pdo->rollBack();
            return refused('duplicate', 'You already have an appointment on ' . nice_date($b['appointment_date'])
                . '. Each client can book only one appointment per day.');
        }

        /* check 2: the mechanic must still have a free place on that day */
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE mechanic_id = ? AND appointment_date = ?');
        $stmt->execute([$mechanic['id'], $b['appointment_date']]);
        $booked = (int) $stmt->fetchColumn();
        if ($booked >= (int) $mechanic['daily_limit']) {
            $pdo->rollBack();
            return refused('full', $mechanic['name'] . ' is fully booked on ' . nice_date($b['appointment_date'])
                . ' (all ' . $mechanic['daily_limit'] . ' places are taken). Please choose another mechanic or another date.');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO appointments (client_name, address, phone, car_license, car_engine, appointment_date, mechanic_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $b['client_name'], $b['address'], $b['phone'], $b['car_license'],
            $b['car_engine'], $b['appointment_date'], $mechanic['id'],
        ]);
        $id = (int) $pdo->lastInsertId();
        $pdo->commit();

        return ['ok' => true, 'id' => $id, 'mechanic' => $mechanic['name']];
    } catch (PDOException $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        /* 23000 = a UNIQUE key refused the row: the same phone or car was
           booked for this date, with another mechanic, at the same moment
           (the lock above only queues bookings for the same mechanic) */
        if ((string) $error->getCode() === '23000') {
            return refused('duplicate', 'You already have an appointment on ' . nice_date($b['appointment_date'])
                . '. Each client can book only one appointment per day.');
        }
        if (is_busy_error($error)) {
            return refused('busy', 'Another booking was being saved at the same moment, so yours wasn\'t saved. Please press "Book appointment" again.');
        }
        throw $error;
    }
}

/* One appointment with its mechanic's name, or null if it doesn't exist. */
function find_appointment($id)
{
    $stmt = db()->prepare(
        'SELECT a.*, m.name AS mechanic_name
           FROM appointments a
           JOIN mechanics m ON m.id = a.mechanic_id
          WHERE a.id = ?'
    );
    $stmt->execute([(int) $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/* Admin: moves an appointment to another date and/or mechanic. It runs the
   same two checks as booking, leaving this appointment itself out of both
   counts, so saving it unchanged (or keeping the same mechanic) always works. */
function update_booking($id, $date, $mechanicId)
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare('SELECT id, phone, car_license FROM appointments WHERE id = ? FOR UPDATE');
        $stmt->execute([(int) $id]);
        $appointment = $stmt->fetch();
        if (!$appointment) {
            $pdo->rollBack();
            return refused('missing', 'That appointment doesn\'t exist any more.');
        }

        $stmt = $pdo->prepare('SELECT id, name, daily_limit FROM mechanics WHERE id = ? AND is_active = 1 FOR UPDATE');
        $stmt->execute([(int) $mechanicId]);
        $mechanic = $stmt->fetch();
        if (!$mechanic) {
            $pdo->rollBack();
            return refused('mechanic', 'Please choose a mechanic from the list.');
        }

        if (client_has_booking($appointment['phone'], $appointment['car_license'], $date, $appointment['id'])) {
            $pdo->rollBack();
            return refused('duplicate', 'This client already has another appointment on ' . nice_date($date) . '.');
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE mechanic_id = ? AND appointment_date = ? AND id <> ?');
        $stmt->execute([$mechanic['id'], $date, $appointment['id']]);
        if ((int) $stmt->fetchColumn() >= (int) $mechanic['daily_limit']) {
            $pdo->rollBack();
            return refused('full', $mechanic['name'] . ' is fully booked on ' . nice_date($date) . '. Please choose an available mechanic.');
        }

        $stmt = $pdo->prepare('UPDATE appointments SET appointment_date = ?, mechanic_id = ? WHERE id = ?');
        $stmt->execute([$date, $mechanic['id'], $appointment['id']]);
        $pdo->commit();

        return ['ok' => true, 'mechanic' => $mechanic['name']];
    } catch (PDOException $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ((string) $error->getCode() === '23000') {
            return refused('duplicate', 'This client already has another appointment on ' . nice_date($date) . '.');
        }
        if (is_busy_error($error)) {
            return refused('busy', 'Another change was being saved at the same moment, so this one wasn\'t saved. Please press "Save changes" again.');
        }
        throw $error;
    }
}

/* Admin: cancels (deletes) an appointment. Returns true if a row was deleted. */
function delete_booking($id)
{
    $stmt = db()->prepare('DELETE FROM appointments WHERE id = ?');
    $stmt->execute([(int) $id]);
    return $stmt->rowCount() > 0;
}


/* ---------- 7. The admin's appointment list ---------- */

/* The appointments the admin asked to see. $filter can hold:
   'date' (one day), 'mechanic' (an id), 'q' (search text for the
   name, phone or car number) and 'past' (true to include past days). */
function find_appointments(array $filter)
{
    $where = [];
    $params = [];

    if ($filter['date'] !== '') {
        $where[] = 'a.appointment_date = :day';
        $params['day'] = $filter['date'];
    } elseif (!$filter['past']) {
        $where[] = 'a.appointment_date >= :today';
        $params['today'] = today();
    }

    if ($filter['mechanic'] > 0) {
        $where[] = 'a.mechanic_id = :mechanic';
        $params['mechanic'] = $filter['mechanic'];
    }

    if ($filter['q'] !== '') {
        /* escape LIKE's wildcards so a search for "50%" means the text "50%" */
        $like = '%' . addcslashes($filter['q'], '\\%_') . '%';
        $where[] = '(a.client_name LIKE :q1 OR a.phone LIKE :q2 OR a.car_license LIKE :q3)';
        $params['q1'] = $like;
        $params['q2'] = $like;
        $params['q3'] = $like;
    }

    $sql = 'SELECT a.id, a.client_name, a.phone, a.car_license, a.appointment_date, m.name AS mechanic_name
              FROM appointments a
              JOIN mechanics m ON m.id = a.mechanic_id'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY a.appointment_date, m.name, a.id';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/* All active mechanics (id and name), for the admin's filter list. */
function all_mechanics()
{
    return db()->query('SELECT id, name FROM mechanics WHERE is_active = 1 ORDER BY id')->fetchAll();
}
