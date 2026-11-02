/* ============================================================
   Md. Sakib Hossain - CSE391 Assignment 2, Question 3
   To-do list. Type a task and add it, tick its checkbox when it
   is done, or delete it. Every change is saved to localStorage,
   so the list is still there after a refresh or a browser restart.

   References:
   - MDN, Window.localStorage:
     https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage
   - MDN, JSON.stringify() and JSON.parse():
     https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON
   - MDN, Document.createElement():
     https://developer.mozilla.org/en-US/docs/Web/API/Document/createElement
   - MDN, Window: storage event:
     https://developer.mozilla.org/en-US/docs/Web/API/Window/storage_event
   ============================================================ */

(function () {
  'use strict';

  var STORAGE_KEY = 'sakib-todo-tasks';

  function byId(id) {
    return document.getElementById(id);
  }

  var form = byId('todo-form');
  var input = byId('todo-input');
  var errorMsg = byId('todo-error');
  var list = byId('todo-list');
  var emptyMsg = byId('todo-empty');
  var summary = byId('todo-summary');

  /* stop quietly if this script is loaded on a page without the list */
  if (!form || !input || !errorMsg || !list || !emptyMsg || !summary) {
    return;
  }


  /* ---------- 1. Saving and loading with localStorage ----------
     localStorage can only hold text, so the array of tasks is
     turned into JSON text to save it and parsed back to load it.
     Each task is a small object, e.g. { text: 'Buy milk', done: false } */

  function loadTasks() {
    var saved = null;

    try {
      saved = JSON.parse(window.localStorage.getItem(STORAGE_KEY));
    } catch (e) {
      saved = null;   /* storage blocked, or the saved text is not valid JSON */
    }

    /* nothing saved yet: getItem() gives null, so start with no tasks */
    if (!Array.isArray(saved)) {
      return [];
    }

    /* keep only entries that really look like tasks */
    return saved.filter(function (task) {
      return task && typeof task.text === 'string' && task.text.trim() !== '';
    }).map(function (task) {
      return { text: task.text, done: task.done === true };
    });
  }

  function saveTasks() {
    try {
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify(tasks));
    } catch (e) {
      showStorageWarning();
    }
  }

  /* Some private windows block localStorage. The list still works,
     but it can't be saved, so tell the visitor once. */
  function showStorageWarning() {
    if (byId('storage-warning')) {
      return;
    }
    var warning = document.createElement('p');
    warning.id = 'storage-warning';
    warning.className = 'storage-warning';
    warning.textContent = 'Your browser is blocking localStorage here, so these tasks will be lost when you close the page.';
    form.parentNode.insertBefore(warning, form.nextSibling);
  }

  var tasks = loadTasks();


  /* ---------- 2. Showing the tasks ---------- */

  /* Builds one list item: checkbox + task text + delete button.
     The two event handlers keep a reference to their own task
     object, so they always change the right entry in the array. */
  function buildItem(task) {
    var li = document.createElement('li');
    li.className = task.done ? 'todo-item done' : 'todo-item';

    /* the label wraps the checkbox, so clicking the text ticks it too */
    var label = document.createElement('label');

    var checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.checked = task.done;
    checkbox.title = 'Mark as completed';

    /* textContent (not innerHTML), so typed HTML shows as plain text */
    var textSpan = document.createElement('span');
    textSpan.className = 'todo-text';
    textSpan.textContent = task.text;

    label.appendChild(checkbox);
    label.appendChild(textSpan);

    var deleteBtn = document.createElement('button');
    deleteBtn.type = 'button';
    deleteBtn.className = 'todo-delete';
    deleteBtn.title = 'Delete this task';
    /* innerHTML is safe here: this is fixed markup with no typed text in it */
    deleteBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i> <span class="label">Delete</span>';
    /* screen readers hear which task each Delete button removes */
    deleteBtn.setAttribute('aria-label', 'Delete task: ' + task.text);

    /* checkbox: mark the task as completed (or not) and save */
    checkbox.addEventListener('change', function () {
      task.done = checkbox.checked;
      li.classList.toggle('done', task.done);
      saveTasks();
      updateSummary();
    });

    /* delete button: remove the task from the array and the page, then save */
    deleteBtn.addEventListener('click', function () {
      /* keyboard focus moves to the next task's Delete button (or the
         text box if the list is now empty), instead of getting lost */
      var neighbour = li.nextElementSibling || li.previousElementSibling;

      var index = tasks.indexOf(task);
      if (index !== -1) {
        tasks.splice(index, 1);
      }
      list.removeChild(li);
      saveTasks();
      updateSummary();

      if (neighbour) {
        neighbour.querySelector('.todo-delete').focus();
      } else {
        input.focus();
      }
    });

    li.appendChild(label);
    li.appendChild(deleteBtn);
    return li;
  }

  /* the count line ("3 tasks, 1 completed") and the empty-list message */
  function updateSummary() {
    var doneCount = tasks.filter(function (task) {
      return task.done;
    }).length;

    if (tasks.length === 0) {
      summary.textContent = 'No tasks yet';
    } else {
      summary.textContent = tasks.length + (tasks.length === 1 ? ' task, ' : ' tasks, ') +
        doneCount + ' completed';
    }

    emptyMsg.style.display = tasks.length === 0 ? 'block' : 'none';
  }

  /* draw every saved task when the page loads */
  function renderAll() {
    list.innerHTML = '';
    tasks.forEach(function (task) {
      list.appendChild(buildItem(task));
    });
    updateSummary();
  }


  /* ---------- 3. Adding a task ---------- */

  function showError(message) {
    errorMsg.textContent = message;
    form.classList.toggle('has-error', message !== '');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();   /* stay on this page instead of sending the form */

    var text = input.value.trim();
    if (text === '') {
      showError('Type a task first.');
      input.focus();
      return;
    }

    var task = { text: text, done: false };
    tasks.push(task);
    list.appendChild(buildItem(task));
    saveTasks();
    updateSummary();

    showError('');
    input.value = '';
    input.focus();
  });

  /* hide the error as soon as the visitor starts typing */
  input.addEventListener('input', function () {
    if (form.classList.contains('has-error')) {
      showError('');
    }
  });

  /* Strict XHTML 1.0 has no placeholder or aria-* attributes, so they
     are added here. The ARIA ones make screen readers read out the
     error and the task count when they change. */
  input.setAttribute('placeholder', 'e.g. Finish the CSE391 lab report');
  input.setAttribute('aria-describedby', 'todo-error');
  errorMsg.setAttribute('role', 'alert');
  summary.setAttribute('aria-live', 'polite');


  /* ---------- 4. Keeping two open tabs in sync ----------
     The "storage" event fires in every OTHER tab of this site when
     localStorage changes. Reloading the list there stops one tab from
     overwriting tasks that were added in another. */

  window.addEventListener('storage', function (e) {
    if (e.key === STORAGE_KEY) {
      tasks = loadTasks();
      renderAll();
    }
  });

  renderAll();
}());
