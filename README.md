# CSE391 homepage

My personal homepage for CSE391 (Programming for the Internet) at BRAC University, Fall 2026. It is also the hub for the web apps I build in the course.

**Live site:** https://sakibhossain0.github.io/cse391/

## Assignment 1: personal homepage

- Written in Strict XHTML 1.0, with one shared stylesheet (`css/style.css`) and a small script (`js/script.js`)
- Four pages: home, projects, hobbies and my class routine
- Dark and light mode, a contact form, and CSS animations

## Assignment 2: JavaScript apps

| Question | Where it is | Files |
|---|---|---|
| 1. Fortune generator | Bottom of the home page, just above the footer: https://sakibhossain0.github.io/cse391/#fortune | `index.html`, `js/fortune.js` |
| 2. Stopwatch | https://sakibhossain0.github.io/cse391/stopwatch.html | `stopwatch.html`, `js/stopwatch.js` |
| 3. To-do list | https://sakibhossain0.github.io/cse391/todo.html | `todo.html`, `js/todo.js` |

The styles for all three are in `css/apps.css`. The pages are still Strict XHTML 1.0, so attributes that only exist in HTML5 (`placeholder` and the `aria-*` accessibility attributes) are added from JavaScript.

### References

- MDN, Math.random(): https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/random
- MDN, HTMLElement.style: https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/style
- MDN, Window.sessionStorage: https://developer.mozilla.org/en-US/docs/Web/API/Window/sessionStorage
- MDN, setInterval(): https://developer.mozilla.org/en-US/docs/Web/API/Window/setInterval
- MDN, clearInterval(): https://developer.mozilla.org/en-US/docs/Web/API/Window/clearInterval
- MDN, Window.localStorage: https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage
- MDN, JSON.stringify() and JSON.parse(): https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON
- MDN, Document.createElement(): https://developer.mozilla.org/en-US/docs/Web/API/Document/createElement
- MDN, Window: storage event: https://developer.mozilla.org/en-US/docs/Web/API/Window/storage_event
- MDN, EventTarget.addEventListener(): https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
- MDN, Event.preventDefault(): https://developer.mozilla.org/en-US/docs/Web/API/Event/preventDefault
- W3C, WCAG 2.1 contrast (minimum), used to check the fortune box colours: https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html
- Nord colour palette: https://www.nordtheme.com/docs/colors-and-palettes

I'll keep linking the rest of my CSE391 work here as the semester goes on.
