/* ============================================================
   CSE391 Assignment 3 - Car Workshop Appointment System
   booking.js: makes the booking form talk to the server at every
   step of the appointment, without reloading the page.
     1. Checking the fields as the client types (same rules as PHP)
     2. Messages shown above the form
     3. Free places: reloaded from availability.php whenever the
        date changes, with a warning when a full mechanic is chosen
     4. Booking: the form is sent to book.php with fetch(), and the
        server's answer (approved or refused) is shown
   ============================================================ */

(function () {
  'use strict';

  var form = document.getElementById('booking-form');
  if (!form) {
    return;
  }

  var dateInput = document.getElementById('appointment_date');
  var list = document.getElementById('mechanic-list');
  var listDate = document.getElementById('list-date');
  var messageArea = document.getElementById('message');
  var bookButton = document.getElementById('book-btn');

  var mechanics = [];        // the free places last received from the server
  var currentDateLabel = listDate.textContent;
  var requestNo = 0;         // stops an older, slower answer from overwriting a newer one


  /* ---------- 1. Checking the fields ----------
     The same rules as validate_booking() in includes/functions.php.
     The browser checks first for quick feedback; the server always
     checks again, because a browser can be bypassed. */

  /* the tidied value of a field, the same way PHP tidies it */
  function valueOf(name) {
    if (name === 'mechanic_id') {
      var chosen = form.querySelector('input[name="mechanic_id"]:checked');
      return chosen ? chosen.value : '';
    }
    var value = form.elements[name].value.trim().replace(/\s+/g, ' ');
    return name === 'car_license' ? value.toUpperCase() : value;
  }

  function isRealDate(value) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
      return false;
    }
    var date = new Date(value + 'T00:00:00Z');
    return !isNaN(date.getTime()) && date.toISOString().slice(0, 10) === value;
  }

  /* each rule returns an error message, or '' when the value is fine */
  var rules = {
    client_name: function (v) {
      if (v === '') { return 'Please enter your name.'; }
      if (!/^\p{L}[\p{L}\p{M} .'-]{1,79}$/u.test(v)) { return 'Use 2 to 80 letters. Spaces, dots, hyphens and apostrophes are fine.'; }
      return '';
    },
    address: function (v) {
      var length = Array.from(v).length;   // counts characters like PHP's mb_strlen (an emoji is 1, not 2)
      if (v === '') { return 'Please enter your address.'; }
      if (length < 5 || length > 200) { return 'The address should be 5 to 200 characters long.'; }
      return '';
    },
    phone: function (v) {
      if (v === '') { return 'Please enter your phone number.'; }
      if (!/^[0-9]{7,15}$/.test(v)) { return 'Use digits only (7 to 15 of them), like 01712345678.'; }
      return '';
    },
    car_license: function (v) {
      if (v === '') { return 'Please enter the car\'s license (registration) number.'; }
      if (!/^[A-Z0-9][A-Z0-9 -]{2,29}$/.test(v)) { return 'Use 3 to 30 letters, numbers, spaces or hyphens, like DHAKA METRO GA 11-2345.'; }
      return '';
    },
    car_engine: function (v) {
      if (v === '') { return 'Please enter the car\'s engine number.'; }
      if (!/^[0-9]{4,20}$/.test(v)) { return 'Use digits only (4 to 20 of them).'; }
      return '';
    },
    appointment_date: function (v) {
      if (v === '') { return 'Please choose an appointment date.'; }
      if (!isRealDate(v)) { return 'Please enter a real date, like ' + form.dataset.min + '.'; }
      if (v < form.dataset.min) { return 'The appointment date can\'t be in the past.'; }
      if (v > form.dataset.max) { return 'Appointments can be booked up to ' + form.dataset.days + ' days ahead.'; }
      return '';
    },
    mechanic_id: function (v) {
      return v === '' ? 'Please choose a mechanic from the list.' : '';
    }
  };

  /* shows (or clears) the message under one field */
  function showError(name, text) {
    var box = document.getElementById('err-' + name);
    if (!box) {
      return;
    }
    box.textContent = text;
    box.closest('.field').classList.toggle('has-error', text !== '');
    if (name !== 'mechanic_id') {
      form.elements[name].setAttribute('aria-invalid', text !== '' ? 'true' : 'false');
    }
  }

  function checkField(name) {
    var problem = rules[name](valueOf(name));
    showError(name, problem);
    return problem === '';
  }

  /* checks every field; returns the first wrong one (to focus it) or null */
  function checkAll() {
    var firstBad = null;
    Object.keys(rules).forEach(function (name) {
      if (!checkField(name) && !firstBad) {
        firstBad = name === 'mechanic_id' ? list.querySelector('input') : form.elements[name];
      }
    });
    return firstBad;
  }

  /* check a field when the client leaves it, and clear its error as soon as it's fixed */
  ['client_name', 'address', 'phone', 'car_license', 'car_engine'].forEach(function (name) {
    var input = form.elements[name];
    input.addEventListener('blur', function () {
      if (input.value !== '') {
        checkField(name);
      }
    });
    input.addEventListener('input', function () {
      if (input.closest('.field').classList.contains('has-error')) {
        checkField(name);
      }
    });
  });


  /* ---------- 2. Messages above the form ---------- */

  /* replaces the message area's content with a new alert.
     kind names the message, so it can be cleared later on its own. */
  function showMessage(type, text, kind) {
    var alert = document.createElement('div');
    alert.className = 'alert alert-' + type;
    var p = document.createElement('p');
    p.textContent = text;
    alert.appendChild(p);
    messageArea.innerHTML = '';
    messageArea.appendChild(alert);
    messageArea.dataset.kind = kind || '';
  }

  function clearMessage(kind) {
    if (messageArea.dataset.kind === kind) {
      messageArea.innerHTML = '';
      messageArea.dataset.kind = '';
    }
  }

  /* the green "Appointment approved" card with the booking details */
  function showConfirmation(booking) {
    var card = document.createElement('div');
    card.className = 'alert alert-success confirm';
    card.tabIndex = -1;

    var title = document.createElement('h2');
    title.innerHTML = '<i class="fa-solid fa-circle-check" aria-hidden="true"></i> ';
    title.appendChild(document.createTextNode('Appointment approved'));
    card.appendChild(title);

    var details = document.createElement('dl');
    details.className = 'confirm-details';
    [['Booking number', booking.ref], ['Name', booking.name], ['Date', booking.dateLabel],
      ['Mechanic', booking.mechanic], ['Car', booking.car]].forEach(function (pair) {
      var dt = document.createElement('dt');
      dt.textContent = pair[0];
      var dd = document.createElement('dd');
      dd.textContent = pair[1];
      details.appendChild(dt);
      details.appendChild(dd);
    });
    card.appendChild(details);

    var note = document.createElement('p');
    note.textContent = 'Please bring your car to the workshop on that day, and keep your booking number.';
    card.appendChild(note);

    messageArea.innerHTML = '';
    messageArea.appendChild(card);
    messageArea.dataset.kind = 'approved';
    card.focus();
  }

  function scrollToMessage() {
    messageArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }


  /* ---------- 3. Free places ---------- */

  /* POSTs form data to a PHP file and returns its JSON answer. If the
     answer isn't JSON (for example a web host's own error page), it
     becomes a normal "server error" answer instead of a crash. */
  function post(url, data) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: data
    }).then(function (response) {
      return response.json().catch(function () {
        return { ok: false, code: 'server', message: 'Sorry, something went wrong on the server. Please try again in a minute.' };
      });
    });
  }

  /* draws the mechanic cards from the server's list, keeping the client's choice */
  function drawMechanics(fresh) {
    var chosen = valueOf('mechanic_id');
    mechanics = fresh;
    list.innerHTML = '';

    fresh.forEach(function (m) {
      var full = m.free === 0;
      var label = document.createElement('label');
      label.className = full ? 'mechanic is-full' : 'mechanic';

      var radio = document.createElement('input');
      radio.type = 'radio';
      radio.name = 'mechanic_id';
      radio.value = String(m.id);
      radio.checked = String(m.id) === chosen;

      var body = document.createElement('span');
      body.className = 'mechanic-body';

      var name = document.createElement('span');
      name.className = 'mechanic-name';
      name.textContent = m.name;

      var meta = document.createElement('span');
      meta.className = 'mechanic-meta';
      meta.textContent = m.speciality + ' · ' + m.experience + ' years';

      var free = document.createElement('span');
      free.className = 'mechanic-free';
      if (full) {
        free.textContent = 'Fully booked';
      } else {
        var count = document.createElement('strong');
        count.textContent = String(m.free);
        free.appendChild(count);
        free.appendChild(document.createTextNode(' of ' + m.limit + ' places free'));
      }

      var meter = document.createElement('span');
      meter.className = 'meter';
      meter.setAttribute('aria-hidden', 'true');
      var bar = document.createElement('span');
      bar.style.width = Math.round(m.free / Math.max(1, m.limit) * 100) + '%';
      meter.appendChild(bar);

      body.appendChild(name);
      body.appendChild(meta);
      body.appendChild(free);
      body.appendChild(meter);
      label.appendChild(radio);
      label.appendChild(body);
      list.appendChild(label);
    });

    warnIfFull();
  }

  /* the mechanic currently chosen, from the last list the server sent */
  function chosenMechanic() {
    var id = Number(valueOf('mechanic_id'));
    for (var i = 0; i < mechanics.length; i++) {
      if (mechanics[i].id === id) {
        return mechanics[i];
      }
    }
    return null;
  }

  /* when the chosen mechanic is fully booked, say so straight away, right
     under the list where the client is looking. They can still press Book:
     the server then refuses it and tells them, as the brief asks. */
  function warnIfFull() {
    var m = chosenMechanic();
    if (!m) {
      return;   // nobody chosen yet: leave any "Please choose a mechanic" message alone
    }
    if (m.free === 0) {
      showError('mechanic_id', m.name + ' is fully booked on ' + currentDateLabel
        + '. Please choose another mechanic, or another date to keep ' + m.name + '.');
    } else {
      showError('mechanic_id', '');
      clearMessage('full');   // an earlier "fully booked" refusal no longer applies
    }
  }

  /* asks the server for the free places on the chosen date */
  function refreshAvailability() {
    if (!checkField('appointment_date')) {
      return;
    }

    var data = new FormData();
    data.append('appointment_date', dateInput.value);

    var mine = ++requestNo;
    list.classList.add('is-loading');

    post('availability.php', data).then(function (answer) {
      if (mine !== requestNo) {
        return;
      }
      list.classList.remove('is-loading');
      if (!answer.ok) {
        showError('appointment_date', answer.message);
        return;
      }
      currentDateLabel = answer.dateLabel;
      listDate.textContent = answer.dateLabel;
      clearMessage('duplicate');   // an "already booked that day" refusal was about the old date
      drawMechanics(answer.mechanics);
    }).catch(function () {
      list.classList.remove('is-loading');
      showMessage('error', 'Couldn\'t reach the server to load the free places. Check your connection and try again.', 'network');
    });
  }

  dateInput.addEventListener('change', refreshAvailability);

  /* choosing a mechanic: clear the "please choose" error, or warn at once if they're full */
  list.addEventListener('change', warnIfFull);


  /* ---------- 4. Booking ---------- */

  function setBusy(busy) {
    bookButton.disabled = busy;
    bookButton.innerHTML = busy
      ? '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Checking...'
      : '<i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book appointment';
  }

  /* after a successful booking: empty the form, but keep the date */
  function clearForm() {
    ['client_name', 'address', 'phone', 'car_license', 'car_engine'].forEach(function (name) {
      form.elements[name].value = '';
      showError(name, '');
    });
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();   // send it with fetch instead of reloading the page

    var firstBad = checkAll();
    if (firstBad) {
      showMessage('error', 'Please fix the highlighted fields and try again.', 'invalid');
      firstBad.focus();
      return;
    }

    setBusy(true);

    post('book.php', new FormData(form)).then(function (answer) {
      setBusy(false);

      if (answer.mechanics) {
        drawMechanics(answer.mechanics);   // the server sends the up-to-date free places
      }

      if (answer.ok) {
        list.querySelectorAll('input').forEach(function (radio) { radio.checked = false; });
        showError('mechanic_id', '');
        clearForm();
        showConfirmation(answer.booking);
        scrollToMessage();
        return;
      }

      /* refused: wrong fields, already booked that day, mechanic full,
         or a server problem. The booking rules are shown as warnings. */
      if (answer.errors) {
        Object.keys(answer.errors).forEach(function (name) {
          showError(name, answer.errors[name]);
        });
      }
      var ruleRefusal = ['duplicate', 'full', 'busy'].indexOf(answer.code) !== -1;
      showMessage(ruleRefusal ? 'warn' : 'error', answer.message, answer.code);
      scrollToMessage();
    }).catch(function () {
      setBusy(false);
      showMessage('error', 'Couldn\'t reach the server, so the appointment was NOT booked. Please try again.', 'network');
      scrollToMessage();
    });
  });

  /* ask the server once more when the page opens: after Back or a reload the
     browser may put back a different date than the one PHP listed */
  refreshAvailability();
}());
