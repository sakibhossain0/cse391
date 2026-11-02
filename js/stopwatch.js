/* ============================================================
   Md. Sakib Hossain - CSE391 Assignment 2, Question 2
   Stopwatch that counts in steps of 3 seconds (3, 6, 9 ...) and
   stops by itself at 30 seconds. Stop pauses it, Start carries on
   from the last recorded time, and Reset puts it back to 0.

   References:
   - MDN, setInterval():
     https://developer.mozilla.org/en-US/docs/Web/API/Window/setInterval
   - MDN, clearInterval():
     https://developer.mozilla.org/en-US/docs/Web/API/Window/clearInterval
   ============================================================ */

(function () {
  'use strict';

  var STEP = 3;     /* seconds added on every tick */
  var LIMIT = 30;   /* the stopwatch stops by itself at this time */

  var seconds = 0;      /* last recorded time, always a multiple of STEP */
  var timerId = null;   /* id from setInterval() while running, null while stopped */

  function byId(id) {
    return document.getElementById(id);
  }

  var watch = byId('stopwatch');
  var timeEl = byId('sw-time');
  var statusEl = byId('sw-status');
  var startBtn = byId('sw-start');
  var stopBtn = byId('sw-stop');
  var resetBtn = byId('sw-reset');

  /* stop quietly if this script is loaded on a page without the stopwatch */
  if (!watch || !timeEl || !statusEl || !startBtn || !stopBtn || !resetBtn) {
    return;
  }

  var steps = watch.querySelectorAll('.sw-steps li');


  /* ---------- 1. Drawing the stopwatch ---------- */

  /* 9 -> "00:09", 30 -> "00:30" */
  function formatTime(total) {
    var mins = Math.floor(total / 60);
    var secs = total % 60;
    return (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
  }

  /* Makes the page match the current state: the time, the status
     line, the ten step segments and which buttons can be pressed.
     Called after every change. */
  function render(message) {
    var running = timerId !== null;
    var finished = seconds >= LIMIT;

    timeEl.textContent = formatTime(seconds);
    statusEl.textContent = message;

    /* one segment lights up for every 3 seconds counted; while
       running, the segment it is counting towards blinks */
    for (var i = 0; i < steps.length; i++) {
      var stepTime = (i + 1) * STEP;
      if (stepTime <= seconds) {
        steps[i].className = 'done';
      } else if (running && stepTime === seconds + STEP) {
        steps[i].className = 'next';
      } else {
        steps[i].className = '';
      }
    }

    watch.classList.toggle('is-running', running);
    watch.classList.toggle('is-finished', finished);

    /* only the buttons that make sense right now can be pressed */
    startBtn.disabled = running || finished;
    stopBtn.disabled = !running;
    resetBtn.disabled = !running && seconds === 0;
  }


  /* ---------- 2. One tick = 3 more seconds ----------
     setInterval() calls tick() every 3000 ms (3 seconds), so the
     display only ever shows multiples of 3. */

  function tick() {
    seconds += STEP;

    if (seconds >= LIMIT) {
      /* Stop is about to be disabled; if the keyboard is on it, move to Reset */
      var stopHadFocus = document.activeElement === stopBtn;

      seconds = LIMIT;
      clearInterval(timerId);
      timerId = null;
      render('Stopped at 30 seconds. Press Reset to go again.');

      if (stopHadFocus) {
        resetBtn.focus();
      }
    } else {
      render('Running... ' + seconds + ' seconds so far.');
    }
  }


  /* ---------- 3. The three buttons ----------
     Each button disables itself when pressed, so each function ends
     by moving keyboard focus to the button you would press next. */

  /* Start: begin counting. After a Stop it carries on from the last
     recorded time, because only tick() and reset() change "seconds". */
  function start() {
    if (timerId !== null || seconds >= LIMIT) {
      return;   /* already running, or already at 30 seconds */
    }
    timerId = setInterval(tick, STEP * 1000);
    render(seconds === 0 ? 'Running...' : 'Carrying on from ' + seconds + ' seconds...');
    stopBtn.focus();
  }

  /* Stop: pause the timer and keep the time on the display */
  function stop() {
    if (timerId === null) {
      return;   /* not running */
    }
    clearInterval(timerId);
    timerId = null;
    if (seconds === 0) {
      render('Stopped before the first 3 seconds. Press Start to begin again.');
    } else {
      render('Paused at ' + seconds + ' seconds. Press Start to carry on.');
    }
    startBtn.focus();
  }

  /* Reset: stop the timer (if it is running) and go back to 0 */
  function reset() {
    clearInterval(timerId);
    timerId = null;
    seconds = 0;
    render('Back to 0. Press Start to begin.');
    startBtn.focus();
  }

  startBtn.addEventListener('click', start);
  stopBtn.addEventListener('click', stop);
  resetBtn.addEventListener('click', reset);

  /* For screen readers (added here because Strict XHTML 1.0 has no
     aria-* attributes): read the status line out when it changes,
     and skip the step bar, which only repeats the time visually. */
  statusEl.setAttribute('aria-live', 'polite');
  watch.querySelector('.sw-steps').setAttribute('aria-hidden', 'true');

  render('Ready. Press Start to begin.');
}());
