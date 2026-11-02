/* ============================================================
   Md. Sakib Hossain - CSE391 Assignment 2, Question 1
   Fortune generator (home page). Every time the page loads, a
   random fortune from the array below is shown in the box just
   above the footer. The four colour buttons beside the box change
   its text colour, background, border, font size and font family.

   References:
   - MDN, Math.random():
     https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/random
   - MDN, HTMLElement.style:
     https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/style
   - MDN, Window.sessionStorage:
     https://developer.mozilla.org/en-US/docs/Web/API/Window/sessionStorage
   ============================================================ */

(function () {
  'use strict';

  function byId(id) {
    return document.getElementById(id);
  }

  var box = byId('fortune-box');
  var text = byId('fortune-text');

  /* stop quietly if this script is loaded on a page without the box */
  if (!box || !text) {
    return;
  }


  /* ---------- 1. The fortunes (at least 10) ---------- */

  var fortunes = [
    'A mystery you have been chasing will solve itself before the last chapter.',
    'Patience is also a move. Sometimes the best play on the board is to wait.',
    'The long run always looks longer from the starting line.',
    'Today\'s confusing error message is tomorrow\'s five-minute fix.',
    'Guard your passwords the way a detective guards a clue.',
    'The poem you keep putting off is ready to be written today.',
    'A stranger\'s question will teach you something no textbook could.',
    'Small steps every day will carry you further than one giant leap.',
    'Someone is quietly grateful for the help you gave them last week.',
    'An old friend will bring you good news before the month ends.',
    'Your curiosity will open a door that hard work alone could not.',
    'Not happy with this fortune? Fortune favours those who refresh.'
  ];


  /* ---------- 2. Pick a random fortune on page load ----------
     Math.random() gives a number from 0 up to (not including) 1.
     Multiplying by the array length and rounding down gives a
     valid index from 0 to fortunes.length - 1. */

  var LAST_KEY = 'sakib-last-fortune';
  var lastIndex = -1;

  /* remember the previous fortune (for this browser tab) so a
     reload never shows the same one twice in a row */
  try {
    var saved = window.sessionStorage.getItem(LAST_KEY);
    lastIndex = saved === null ? -1 : Number(saved);
  } catch (e) {
    lastIndex = -1;
  }

  var index = Math.floor(Math.random() * fortunes.length);
  while (index === lastIndex && fortunes.length > 1) {
    index = Math.floor(Math.random() * fortunes.length);
  }

  try {
    window.sessionStorage.setItem(LAST_KEY, String(index));
  } catch (e) {
    /* storage blocked: we just can't avoid repeats */
  }

  text.textContent = fortunes[index];


  /* ---------- 3. The four looks, one per button ----------
     Each look sets all of the things the brief lists: the font
     colour of the text, the background and border colours of the
     box, and a slightly different font size and font family.
     Colours were checked with the WCAG contrast formula:
     - text on the box: at least 5.8:1 in every look
     - borders are mid-tones, so they stand out (3.1:1 or more) on
       both the light page (#ECEFF4) and the dark page (#2E3440) */

  var looks = [
    {
      button: 'look-green',
      color: '#1E3312',
      background: '#A3BE8C',
      border: '#4E8F2A',
      fontSize: '1.22rem',
      fontFamily: 'Georgia, "Times New Roman", serif'
    },
    {
      button: 'look-yellow',
      color: '#8C1C1C',
      background: '#EBCB8B',
      border: '#4D932B',
      fontSize: '1.12rem',
      fontFamily: '"Trebuchet MS", "Segoe UI", sans-serif'
    },
    {
      button: 'look-blue',
      color: '#0E2236',
      background: '#88C0D0',
      border: '#3786D5',
      fontSize: '1.28rem',
      fontFamily: '"Palatino Linotype", "Book Antiqua", Palatino, serif'
    },
    {
      button: 'look-orange',
      color: '#2B0F06',
      background: '#D08770',
      border: '#5E81AC',
      fontSize: '1.06rem',
      fontFamily: '"JetBrains Mono", Consolas, monospace'
    }
  ];


  /* ---------- 4. Restyle the box when a button is clicked ---------- */

  function applyLook(look) {
    /* font colour, size and family of the fortune text */
    text.style.color = look.color;
    text.style.fontSize = look.fontSize;
    text.style.fontFamily = look.fontFamily;

    /* background and border colour of the box */
    box.style.backgroundColor = look.background;
    box.style.borderColor = look.border;

    /* show which button is selected */
    looks.forEach(function (other) {
      var button = byId(other.button);
      if (button) {
        button.classList.toggle('is-active', other === look);
        button.setAttribute('aria-pressed', other === look ? 'true' : 'false');
      }
    });
  }

  looks.forEach(function (look) {
    var button = byId(look.button);
    if (!button) {
      return;
    }

    /* paint each button as a tiny preview of its own look */
    button.style.backgroundColor = look.background;
    button.style.borderColor = look.border;

    button.setAttribute('aria-pressed', 'false');
    button.addEventListener('click', function () {
      applyLook(look);
    });
  });
}());
