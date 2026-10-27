/* ============================================================
   Md. Sakib Hossain - personal homepage (CSE391, Assignment 1)
   Shared JavaScript for all four pages. Each feature first checks
   that its elements exist, so one file serves every page.
   ============================================================ */

(function () {
  'use strict';

  var root = document.documentElement;
  var THEME_KEY = 'sakib-theme';
  var CONTACT_EMAIL = 'md.sakib.hossain2@gmail.com';
  var GITHUB_USER = 'sakibhossain0';

  function byId(id) {
    return document.getElementById(id);
  }

  function setText(id, text) {
    var el = byId(id);
    if (el) {
      el.textContent = text;
    }
  }


  /* ---------- 1. Dark / light toggle ----------
     The switch itself is pure CSS (#theme-switch:checked ~ #page).
     JavaScript only remembers the choice between pages. */

  var themeSwitch = byId('theme-switch');

  function saveTheme(value) {
    try {
      window.localStorage.setItem(THEME_KEY, value);
    } catch (e) {
      /* private mode: the toggle still works, it just isn't remembered */
    }
  }

  if (themeSwitch) {
    themeSwitch.checked = root.classList.contains('preload-dark');
    root.classList.remove('preload-dark');

    themeSwitch.addEventListener('change', function () {
      var dark = themeSwitch.checked;
      root.classList.toggle('dark', dark);
      saveTheme(dark ? 'dark' : 'light');
    });
  }

  /* turn colour transitions on only after the first paint */
  window.setTimeout(function () {
    root.classList.add('ready');
  }, 60);


  /* ---------- 2. Mobile menu ---------- */

  var navBtn = byId('nav-btn');
  var siteNav = byId('site-nav');

  function setMenu(open) {
    siteNav.classList.toggle('open', open);
    navBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    navBtn.title = open ? 'Close menu' : 'Open menu';
    navBtn.innerHTML = open ? '<i class="fa-solid fa-xmark"></i>' : '<i class="fa-solid fa-bars"></i>';
  }

  if (navBtn && siteNav) {
    navBtn.setAttribute('aria-controls', 'site-nav');
    navBtn.setAttribute('aria-expanded', 'false');

    navBtn.addEventListener('click', function () {
      setMenu(!siteNav.classList.contains('open'));
    });

    siteNav.addEventListener('click', function (e) {
      if (e.target.tagName === 'A') {
        setMenu(false);
      }
    });
  }


  /* ---------- 3. External links open in a new tab ----------
     Strict XHTML does not allow target="_blank", so links marked
     rel="external" get it here instead. */

  var anchors = document.getElementsByTagName('a');
  for (var i = 0; i < anchors.length; i++) {
    var rel = ' ' + (anchors[i].getAttribute('rel') || '') + ' ';
    if (rel.indexOf(' external ') !== -1) {
      anchors[i].target = '_blank';
      anchors[i].rel = 'external noopener';
    }
  }


  /* ---------- 4. Footer: page location + last modified ---------- */

  setText('page-location', window.location.href);

  var modified = new Date(document.lastModified);
  if (isNaN(modified.getTime())) {
    setText('last-modified', document.lastModified);
  } else {
    setText('last-modified', modified.toLocaleString('en-GB', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    }));
  }

  setText('year', String(new Date().getFullYear()));


  /* ---------- 5. Reveal sections while scrolling ---------- */

  var reveals = document.querySelectorAll('.reveal');

  if ('IntersectionObserver' in window) {
    var revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    Array.prototype.forEach.call(reveals, function (el) {
      revealObserver.observe(el);
    });
  } else {
    Array.prototype.forEach.call(reveals, function (el) {
      el.classList.add('in');
    });
  }


  /* ---------- 6. Highlight the current section in the anchor bar ---------- */

  var pageNav = document.querySelector('.page-nav');

  if (pageNav && 'IntersectionObserver' in window) {
    var navLinks = pageNav.querySelectorAll('a[href^="#"]');
    var navStrip = pageNav.querySelector('.wrap');
    var linkFor = {};

    var markActive = function (id) {
      Array.prototype.forEach.call(navLinks, function (link) {
        link.classList.remove('active');
      });
      var link = linkFor[id];
      if (link) {
        link.classList.add('active');
        navStrip.scrollLeft = link.offsetLeft - navStrip.clientWidth / 2 + link.offsetWidth / 2;
      }
    };

    var spyObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          markActive(entry.target.id);
        }
      });
    }, { rootMargin: '-40% 0px -55% 0px' });

    Array.prototype.forEach.call(navLinks, function (link) {
      var target = byId(link.getAttribute('href').slice(1));
      if (target) {
        linkFor[target.id] = link;
        spyObserver.observe(target);
      }
    });
  }


  /* ---------- 7. Back-to-top button ---------- */

  var toTop = byId('to-top');

  if (toTop) {
    var onScroll = function () {
      toTop.classList.toggle('show', window.pageYOffset > 640);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }


  /* ---------- 8. Contact form (bonus) ----------
     Online, the message is posted to FormSubmit, which emails it to me.
     Opened straight from the zip (file://) FormSubmit refuses to work,
     so the form falls back to the visitor's own email app. */

  var form = byId('contact-form');

  if (form) {
    var status = byId('form-status');
    var sendBtn = byId('cf-send');
    var inputs = {
      name: byId('cf-name'),
      email: byId('cf-email'),
      subject: byId('cf-subject'),
      message: byId('cf-message')
    };

    var showStatus = function (text, kind) {
      status.textContent = text;
      status.className = 'form-status ' + (kind || '');
    };

    var setError = function (input, text) {
      var box = input.parentNode;
      var msg = byId('err-' + input.id.replace('cf-', ''));
      box.classList.toggle('has-error', !!text);
      if (msg) {
        msg.textContent = text || '';
      }
      if (text) {
        input.setAttribute('aria-invalid', 'true');
      } else {
        input.removeAttribute('aria-invalid');
      }
    };

    var validate = function () {
      var firstBad = null;
      var name = inputs.name.value.trim();
      var email = inputs.email.value.trim();
      var message = inputs.message.value.trim();

      if (name.length < 2) {
        setError(inputs.name, 'Please tell me your name.');
        firstBad = firstBad || inputs.name;
      } else {
        setError(inputs.name, '');
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
        setError(inputs.email, 'That email address does not look right.');
        firstBad = firstBad || inputs.email;
      } else {
        setError(inputs.email, '');
      }

      if (message.length < 10) {
        setError(inputs.message, 'Write at least a sentence (10+ characters).');
        firstBad = firstBad || inputs.message;
      } else {
        setError(inputs.message, '');
      }

      if (firstBad) {
        firstBad.focus();
        return false;
      }
      return true;
    };

    Object.keys(inputs).forEach(function (key) {
      inputs[key].addEventListener('input', function () {
        if (inputs[key].parentNode.classList.contains('has-error')) {
          setError(inputs[key], '');
        }
      });
    });

    var setSending = function (sending) {
      sendBtn.disabled = sending;
      sendBtn.innerHTML = sending ?
        '<i class="fa-solid fa-spinner fa-spin"></i> Sending...' :
        '<i class="fa-solid fa-paper-plane"></i> Send message';
    };

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      if (!validate()) {
        showStatus('Please fix the highlighted fields first.', 'bad');
        return;
      }

      /* bots fill the hidden honeypot field; people never see it */
      if (byId('cf-honey').value !== '') {
        showStatus('Thanks! Your message has been sent.', 'ok');
        form.reset();
        return;
      }

      var subject = inputs.subject.value.trim() || 'Message from your homepage';
      var hiddenSubject = byId('cf-subject-hidden');
      if (hiddenSubject) {
        hiddenSubject.value = 'Homepage: ' + subject;
      }

      /* hand the message to the visitor's own email app so it is never lost */
      var openMailApp = function () {
        var body = 'From: ' + inputs.name.value.trim() + ' <' + inputs.email.value.trim() + '>\n\n' +
          inputs.message.value.trim();
        window.location.href = 'mailto:' + CONTACT_EMAIL +
          '?subject=' + encodeURIComponent(subject) +
          '&body=' + encodeURIComponent(body);
      };

      if (window.location.protocol === 'file:') {
        openMailApp();
        showStatus('Your email app should now open with the message filled in. ' +
          '(The direct-send version works once this site is online.)', 'ok');
        return;
      }

      if (!window.fetch || !window.FormData) {
        form.submit();
        return;
      }

      setSending(true);
      showStatus('', '');

      fetch(form.action.replace('formsubmit.co/', 'formsubmit.co/ajax/'), {
        method: 'POST',
        headers: { Accept: 'application/json' },
        body: new FormData(form)
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          setSending(false);
          if (data.success === true || data.success === 'true') {
            showStatus('Thanks, ' + inputs.name.value.trim().split(' ')[0] +
              '! Your message is on its way to my inbox.', 'ok');
            form.reset();
          } else {
            /* e.g. FormSubmit still waiting for activation: don't show its
               internal message to visitors, just switch to the email app */
            openMailApp();
            showStatus('Direct sending is not available right now, so your email app should open ' +
              'with the message filled in. You can also write to ' + CONTACT_EMAIL + '.', 'bad');
          }
        })
        .catch(function () {
          setSending(false);
          openMailApp();
          showStatus('Could not reach the mail service, so your email app should open with the ' +
            'message filled in. You can also write to ' + CONTACT_EMAIL + '.', 'bad');
        });
    });
  }


  /* ---------- 9. Projects page: live repositories from GitHub ---------- */

  var repoBox = byId('repo-list');

  function repoCard(repo) {
    var card = document.createElement('div');
    card.className = 'card lift repo-card';

    var h3 = document.createElement('h3');
    var a = document.createElement('a');
    a.href = repo.html_url;
    a.target = '_blank';
    a.rel = 'external noopener';
    a.textContent = repo.name;
    h3.appendChild(a);
    card.appendChild(h3);

    var p = document.createElement('p');
    p.className = 'muted';
    if (repo.name.toLowerCase() === GITHUB_USER) {
      p.textContent = 'The README that shows up on my GitHub profile: tech stack, featured projects and contact links.';
    } else {
      p.textContent = repo.description || 'No description yet.';
    }
    card.appendChild(p);

    var meta = document.createElement('ul');
    meta.className = 'repo-meta';
    var bits = [];
    if (repo.language) {
      bits.push(['fa-solid fa-code', repo.language]);
    }
    bits.push(['fa-regular fa-star', String(repo.stargazers_count)]);
    bits.push(['fa-solid fa-clock-rotate-left', 'Updated ' +
      new Date(repo.pushed_at || repo.updated_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })]);

    bits.forEach(function (bit) {
      var li = document.createElement('li');
      var icon = document.createElement('i');
      icon.className = bit[0];
      li.appendChild(icon);
      li.appendChild(document.createTextNode(bit[1]));
      meta.appendChild(li);
    });
    card.appendChild(meta);
    return card;
  }

  if (repoBox && window.fetch) {
    fetch('https://api.github.com/users/' + GITHUB_USER + '/repos?sort=pushed&per_page=30')
      .then(function (response) {
        if (!response.ok) {
          throw new Error('GitHub answered ' + response.status);
        }
        return response.json();
      })
      .then(function (repos) {
        var own = repos.filter(function (repo) {
          return !repo.fork;
        });
        repoBox.innerHTML = '';
        if (!own.length) {
          repoBox.innerHTML = '<p class="muted">No public repositories yet.</p>';
          return;
        }
        own.forEach(function (repo) {
          repoBox.appendChild(repoCard(repo));
        });
        setText('repo-count', own.length + (own.length === 1 ? ' public repository' : ' public repositories'));
      })
      .catch(function () {
        repoBox.innerHTML = '<p class="muted">Could not load the live list right now (GitHub may be rate-limiting). ' +
          'You can browse everything on <a href="https://github.com/' + GITHUB_USER +
          '" target="_blank" rel="external noopener">github.com/' + GITHUB_USER + '</a>.</p>';
      });
  }


  /* ---------- 10. Routine page: today's column + next exam ---------- */

  var todayHead = document.querySelector('table.week .day-' + new Date().getDay());
  if (todayHead) {
    todayHead.classList.add('is-today');
    todayHead.title = 'Today';
  }

  var nextExamBox = byId('next-exam');

  if (nextExamBox) {
    /* [year, month (0 = Jan), day, hour, minute, course, exam] */
    var exams = [
      [2026, 10, 22, 11, 0, 'CSE391', 'midterm'],
      [2026, 10, 23, 16, 30, 'CSE341', 'midterm'],
      [2026, 10, 24, 16, 30, 'SOC101', 'midterm'],
      [2026, 10, 25, 16, 30, 'CSE470', 'midterm'],
      [2027, 0, 8, 11, 0, 'CSE391', 'final'],
      [2027, 0, 9, 16, 30, 'CSE341', 'final'],
      [2027, 0, 10, 16, 30, 'SOC101', 'final'],
      [2027, 0, 11, 16, 30, 'CSE470', 'final']
    ];
    var now = new Date();
    var found = false;

    for (var k = 0; k < exams.length; k++) {
      var ex = exams[k];
      var when = new Date(ex[0], ex[1], ex[2], ex[3], ex[4]);
      if (when > now) {
        var days = Math.ceil((when - now) / 86400000);
        var dateText = when.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });
        var timeText = when.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        nextExamBox.textContent = 'Next exam: ' + ex[5] + ' ' + ex[6] + ' on ' + dateText + ' at ' + timeText +
          (days <= 1 ? ' (tomorrow or today!)' : ' (' + days + ' days to go)');
        var row = byId('exam-' + (k + 1));
        if (row) {
          row.classList.add('next-up');
        }
        found = true;
        break;
      }
    }

    if (!found) {
      nextExamBox.textContent = 'All Fall 2026 exams are done. Time for a long break.';
    }
  }
}());
