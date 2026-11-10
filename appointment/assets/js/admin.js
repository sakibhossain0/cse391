/* ============================================================
   CSE391 Assignment 3 - Car Workshop Appointment System
   admin.js: two small helpers for the admin panel.
     1. Change page: when the admin picks a new date, the mechanic
        list is reloaded from the server with each one's free places,
        and fully booked mechanics can't be chosen
     2. Cancel buttons ask "are you sure?" first
   ============================================================ */

(function () {
  'use strict';

  /* ---------- 1. Change page: free places for the chosen date ---------- */

  var form = document.getElementById('edit-form');

  if (form) {
    var dateInput = document.getElementById('appointment_date');
    var select = document.getElementById('mechanic_id');
    var listDate = document.getElementById('list-date');
    var dateError = document.getElementById('err-date');
    var requestNo = 0;

    /* rebuilds the options, keeping the admin's choice if that mechanic is still free */
    var drawOptions = function (mechanics) {
      var chosen = select.value;
      select.innerHTML = '';

      var placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = 'Choose a mechanic';
      select.appendChild(placeholder);

      mechanics.forEach(function (m) {
        var full = m.free === 0;
        var option = document.createElement('option');
        option.value = String(m.id);
        option.textContent = m.name + ': ' + (full ? 'fully booked' : m.free + ' of ' + m.limit + ' places free');
        option.disabled = full;
        option.selected = !full && String(m.id) === chosen;
        select.appendChild(option);
      });
      /* the old choice is full on the new date: fall back to "Choose a mechanic" */
      if (select.selectedIndex === -1 || select.options[select.selectedIndex].disabled) {
        select.selectedIndex = 0;
      }
    };

    dateInput.addEventListener('change', function () {
      if (dateInput.value === '') {
        return;
      }
      var mine = ++requestNo;
      var data = new FormData();
      data.append('appointment_date', dateInput.value);
      data.append('ignore', form.dataset.id);   // don't count the appointment being moved
      select.disabled = true;

      fetch('../availability.php', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
        body: data
      }).then(function (response) {
        return response.json();
      }).then(function (answer) {
        if (mine !== requestNo) {
          return;
        }
        select.disabled = false;
        if (!answer.ok) {
          dateError.textContent = answer.message;
          return;
        }
        dateError.textContent = '';
        listDate.textContent = answer.dateLabel;
        drawOptions(answer.mechanics);
      }).catch(function () {
        select.disabled = false;
        dateError.textContent = 'Couldn\'t reach the server to load the free places. Please try again.';
      });
    });
  }


  /* ---------- 2. Ask before cancelling ---------- */

  document.querySelectorAll('form.js-confirm').forEach(function (cancelForm) {
    cancelForm.addEventListener('submit', function (event) {
      if (!window.confirm(cancelForm.dataset.confirm)) {
        event.preventDefault();
      }
    });
  });
}());
