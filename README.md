# CSE391: Programming for the Internet

My coursework hub for **CSE391 at BRAC University (Fall 2026)**: a personal homepage that also hosts every web app I build for the course. Everything is plain Strict XHTML 1.0, CSS and JavaScript, with no frameworks and no build step.

**Live site:** https://sakibhossain0.github.io/cse391/

[![Live site](https://img.shields.io/badge/live_site-sakibhossain0.github.io%2Fcse391-5E81AC?style=flat-square&logo=githubpages&logoColor=white)](https://sakibhossain0.github.io/cse391/)
[![Deploy status](https://github.com/sakibhossain0/cse391/actions/workflows/pages/pages-build-deployment/badge.svg)](https://github.com/sakibhossain0/cse391/actions/workflows/pages/pages-build-deployment)
![XHTML 1.0 Strict](https://img.shields.io/badge/XHTML_1.0-Strict-E34F26?style=flat-square&logo=html5&logoColor=white)
![CSS: W3C valid](https://img.shields.io/badge/CSS-W3C_valid-1572B6?style=flat-square&logo=css&logoColor=white)
![JavaScript: no frameworks](https://img.shields.io/badge/JavaScript-no_frameworks-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![Last commit](https://img.shields.io/github/last-commit/sakibhossain0/cse391?style=flat-square)

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="docs/screenshots/home-dark.webp">
  <img src="docs/screenshots/home-light.webp" alt="The home page: my name, a short introduction and my photo">
</picture>

## Contents

- [Assignments at a glance](#assignments-at-a-glance)
- [Assignment 1: Personal homepage](#assignment-1-personal-homepage)
- [Assignment 2: JavaScript apps](#assignment-2-javascript-apps)
- [Project structure](#project-structure)
- [Run it locally](#run-it-locally)
- [Standards and validation](#standards-and-validation)
- [Accessibility](#accessibility)
- [Deployment](#deployment)
- [Credits and references](#credits-and-references)
- [About me](#about-me)

## Assignments at a glance

| # | Assignment | What it is | Try it | Status |
|---|---|---|---|---|
| 1 | Personal homepage | A four-page personal site with dark mode, a contact form and CSS animations | [Home page](https://sakibhossain0.github.io/cse391/) | Done |
| 2 | JavaScript programming | A fortune generator, a stopwatch and a to-do list | [Fortune](https://sakibhossain0.github.io/cse391/#fortune) · [Stopwatch](https://sakibhossain0.github.io/cse391/stopwatch.html) · [To-do list](https://sakibhossain0.github.io/cse391/todo.html) | Done |
| | Labs and later assignments | Added here as I finish them | | Coming up |

## Assignment 1: Personal homepage

The homepage introduces me and links to everything else on the site.

| Page | What's on it |
|---|---|
| [Home](https://sakibhossain0.github.io/cse391/) (`index.html`) | About me, personal info, education, technical skills, a favourite quote, categorized links, a contact form and the fortune generator |
| [Projects](https://sakibhossain0.github.io/cse391/projects.html) (`projects.html`) | Notes on my projects, a live list of my GitHub repositories and my CSE391 work so far |
| [Hobbies](https://sakibhossain0.github.io/cse391/hobbies.html) (`hobbies.html`) | Books, writing, films and series, music, sports, clubs and things I build |
| [Routine](https://sakibhossain0.github.io/cse391/routine.html) (`routine.html`) | My Fall 2026 classes, the week at a glance and the exam schedule |

**Highlights**

- **Dark and light mode in pure CSS.** A hidden checkbox and the `~` sibling selector swap every colour. JavaScript only remembers your choice, and first-time visitors get their system theme.
- **Contact form.** Messages are sent through [FormSubmit](https://formsubmit.co/). If direct sending isn't available (for example when the page is opened from a local file), your email app opens with the message filled in.
- **Tables** on the routine page use merged rows and columns and striped rows, and scroll sideways on phones. Today's column is highlighted, and a line counts down to the next exam.
- **Live GitHub list** on the projects page, loaded from the GitHub REST API.
- **Footer** on every page shows the page's address and when it was last modified, filled in by JavaScript.
- **Animations:** a drifting aurora background, a spinning ring around my photo, floating tags, a typing cursor and scroll reveals. They all switch off if your system asks for reduced motion.

## Assignment 2: JavaScript apps

Three small apps in plain JavaScript that practise arrays, DOM manipulation, events, timers and `localStorage`.

### Question 1: Fortune generator

[Try it](https://sakibhossain0.github.io/cse391/#fortune) · `index.html` (the `#fortune` block) · [`js/fortune.js`](js/fortune.js)

- An array of 12 fortunes. Each time the home page loads, `Math.random()` picks one, and a reload never shows the same one twice in a row.
- The fortune sits in a centred box just above the footer of the home page.
- Four colour buttons sit beside the box. Each one applies a complete look: text colour, background colour, border colour, and a slightly different font size and font family.
- The colours were checked with the WCAG contrast formula. Text is at least 5.8:1 on every box, and the borders are at least 3.1:1 against both the light and the dark page.

![The fortune generator: a yellow box with a green border and red text, with four colour buttons beside it](docs/screenshots/fortune.png)

![The same fortune in each of the four looks: green, yellow, blue and orange](docs/screenshots/fortune-looks.png)

### Question 2: Stopwatch

[Try it](https://sakibhossain0.github.io/cse391/stopwatch.html) · [`stopwatch.html`](stopwatch.html) · [`js/stopwatch.js`](js/stopwatch.js)

- Counts in steps of 3 seconds (3, 6, 9 and so on), using `setInterval` every 3000 ms.
- Stops by itself at 30 seconds and stays there until you press Reset.
- **Start** begins the count and **Stop** pauses it. Pressing **Start** again carries on from the last time shown, and **Reset** goes back to 0.
- Buttons that can't be used right now are disabled, keyboard focus moves to the next sensible button, and screen readers hear the time as it changes.

![The stopwatch at 00:09, with the first three of its ten 3-second steps lit up](docs/screenshots/stopwatch.png)

### Question 3: To-do list

[Try it](https://sakibhossain0.github.io/cse391/todo.html) · [`todo.html`](todo.html) · [`js/todo.js`](js/todo.js)

- Type a task and press **Add** (or Enter). An empty task shows an error instead of being added.
- Tasks appear in a list, each with a checkbox to mark it as completed and a **Delete** button.
- Every change is saved in `localStorage` as JSON, so the list is still there after a refresh or a browser restart. Two open tabs stay in sync through the `storage` event.
- Task text goes in with `textContent`, so typed HTML shows up as plain text. Corrupted saved data is ignored instead of breaking the page.

![The to-do list with four tasks, one ticked off and struck through](docs/screenshots/todo.png)

## Project structure

```text
cse391/
├── index.html            Home page, including the fortune generator (A2 Q1)
├── projects.html         Projects and a live list of my GitHub repositories
├── hobbies.html          Books, writing, films, music, sports and clubs
├── routine.html          Fall 2026 class routine and exam schedule
├── stopwatch.html        Stopwatch (A2 Q2)
├── todo.html             To-do list (A2 Q3)
├── css/
│   ├── style.css         Shared styles: Nord colour tokens, dark mode, layout, animations
│   └── apps.css          Styles for the Assignment 2 apps
├── js/
│   ├── theme-init.js     Applies the saved theme before the page is drawn
│   ├── script.js         Shared features: theme toggle, menu, footer info, contact form,
│   │                     GitHub list and routine helpers
│   ├── fortune.js        Fortune generator (A2 Q1)
│   ├── stopwatch.js      Stopwatch (A2 Q2)
│   └── todo.js           To-do list (A2 Q3)
├── images/               My photo and the favicon
├── docs/
│   ├── AI_Declaration.txt    AI usage declaration for Assignment 1
│   └── screenshots/      Images used in this README
├── favicon.ico
└── .nojekyll             Tells GitHub Pages to serve the files as they are
```

## Run it locally

There's nothing to install or build. Clone the repo and serve the folder:

```bash
git clone https://github.com/sakibhossain0/cse391.git
cd cse391
python -m http.server 8000
```

Then open `http://localhost:8000` in your browser. You can also open `index.html` straight from the folder. Everything works that way except direct sending from the contact form, which opens your email app instead.

## Standards and validation

- **Markup:** every page is Strict XHTML 1.0. That standard doesn't allow HTML5-only attributes such as `placeholder` and `aria-*`, so JavaScript adds them when the page loads and the markup stays valid.
- **Validation** (last checked on 2 November 2026):
  - [W3C Markup Validator](https://validator.w3.org/): all 6 pages pass with 0 errors and 0 warnings.
  - [W3C CSS Validator](https://jigsaw.w3.org/css-validator/): both stylesheets pass with 0 errors.
  - Each page's footer has badges that link to both validators.
- **JavaScript:** ES5 syntax (`var` and plain functions) with no libraries. Each script is wrapped in its own function so nothing leaks into the global scope. The larger scripts are split into numbered, commented sections and check that their page elements exist before running.

## Accessibility

- A skip link, visible keyboard focus and labelled form fields
- Screen-reader announcements for the stopwatch status, the to-do count and form errors
- App colours checked against WCAG contrast (text meets level AA)
- Animations switch off when your system asks for reduced motion
- A responsive layout for phones, tablets and desktops, in light and dark mode

## Deployment

GitHub Pages serves the site from the root of the `main` branch. Every push to `main` republishes it within about a minute, through GitHub's built-in `pages-build-deployment` workflow.

## Credits and references

**Libraries and services**

- [Font Awesome 6](https://fontawesome.com/) icons and [Google Fonts](https://fonts.google.com/) (Manrope, JetBrains Mono and Lora)
- The [Nord](https://www.nordtheme.com/docs/colors-and-palettes) colour palette
- [FormSubmit](https://formsubmit.co/) for the contact form
- The [GitHub REST API](https://docs.github.com/en/rest) for the live repository list
- [Shields.io](https://shields.io/) badges, and the BRAC University logo from [Wikimedia Commons](https://commons.wikimedia.org/)

**Documentation used for Assignment 2**

- MDN: [Math.random()](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/random), [HTMLElement.style](https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/style), [Window.sessionStorage](https://developer.mozilla.org/en-US/docs/Web/API/Window/sessionStorage)
- MDN: [setInterval()](https://developer.mozilla.org/en-US/docs/Web/API/Window/setInterval), [clearInterval()](https://developer.mozilla.org/en-US/docs/Web/API/Window/clearInterval)
- MDN: [Window.localStorage](https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage), [JSON](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON), [Document.createElement()](https://developer.mozilla.org/en-US/docs/Web/API/Document/createElement), [Window: storage event](https://developer.mozilla.org/en-US/docs/Web/API/Window/storage_event)
- MDN: [EventTarget.addEventListener()](https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener), [Event.preventDefault()](https://developer.mozilla.org/en-US/docs/Web/API/Event/preventDefault)
- W3C: [WCAG 2.1 contrast (minimum)](https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html)

## About me

**Md. Sakib Hossain**, a 10th-semester Computer Science and Engineering student at BRAC University, working toward a career in AI and cybersecurity.

- GitHub: [sakibhossain0](https://github.com/sakibhossain0)
- LinkedIn: [Sakib Hossain](https://www.linkedin.com/in/sakib-hossain-7829b12a5)
- Email: md.sakib.hossain2@g.bracu.ac.bd

---

This repository is my coursework for CSE391. If you're taking the course too, feel free to look around for ideas, but please don't submit it as your own.
