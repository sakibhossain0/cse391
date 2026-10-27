/* Loaded in <head>, before the page paints.
   Adds the "js" hook class and applies a saved (or system) dark theme
   early, so dark-mode visitors never see a white flash. script.js then
   ticks the real CSS toggle checkbox and removes "preload-dark". */
(function () {
  var root = document.documentElement;
  var theme = null;

  root.className += ' js';

  try {
    theme = window.localStorage.getItem('sakib-theme');
  } catch (e) {
    theme = null;
  }

  var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

  if (theme === 'dark' || (!theme && prefersDark)) {
    root.className += ' dark preload-dark';
  }
}());
