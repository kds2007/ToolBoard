
(function () {
  'use strict';

  var d = document.documentElement;
  var dirty = false;

  function setTheme(t) {
    t = t === 'light' ? 'light' : 'dark';
    d.dataset.theme = t;

    try {
      localStorage.setItem('tb-theme', t);
    } catch (e) {}

    document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
      b.textContent = t === 'dark' ? '☀ Claro' : '☾ Oscuro';
    });
  }

  var temaGuardado = 'dark';

  try {
    temaGuardado = localStorage.getItem('tb-theme') || 'dark';
  } catch (e) {}

  setTheme(temaGuardado);

  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-theme-toggle]');

    if (t) {
      setTheme(d.dataset.theme === 'dark' ? 'light' : 'dark');
      return;
    }

    var p = e.target.closest('[data-pass]');

    if (p) {
      var i = document.getElementById(p.dataset.pass);

      if (i) {
        i.type = i.type === 'password' ? 'text' : 'password';
        p.textContent = i.type === 'password' ? 'Mostrar' : 'Ocultar';
      }

      return;
    }

    if (e.target.closest('#burger')) {
      var side = document.getElementById('side');

      if (side) {
        side.classList.toggle('open');
      }
    }
  });

  document.addEventListener('input', function (e) {
    if (e.target.closest('form.f')) {
      dirty = true;
    }
  });

  document.addEventListener('change', function (e) {
    if (e.target.closest('form.f')) {
      dirty = true;
    }
  });

  window.addEventListener('beforeunload', function (e) {
    if (dirty) {
      e.preventDefault();
      e.returnValue = '';
    }
  });

  document.addEventListener('submit', function (ev) {
    var m = ev.target.getAttribute('data-confirm');

    if (m && !confirm(m)) {
      ev.preventDefault();
      return;
    }

    dirty = false;

    var b = ev.target.querySelector(
      'button:not([type="button"]):not([type="reset"])'
    );

    if (b) {
      b.classList.add('loading');
      b.disabled = true;
    }
  });

  var c1 = document.getElementById('clave');
  var c2 = document.getElementById('clave2');

  if (c1 && c2) {
    function comprobarClaves() {
      c2.setCustomValidity(
        c2.value !== c1.value
          ? 'Las contraseñas no coinciden'
          : ''
      );
    }

    c1.addEventListener('input', comprobarClaves);
    c2.addEventListener('input', comprobarClaves);
  }

})();
