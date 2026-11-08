(function () {
  var d = document.documentElement, dirty = false;
  function setTheme(t) {
    d.dataset.theme = t;
    try { localStorage.setItem('tb-theme', t); } catch (e) {}
    document.querySelectorAll('[data-theme-toggle]').forEach(function (b) { b.textContent = t === 'dark' ? '☀ Claro' : '☾ Oscuro'; });
  }
  setTheme(d.dataset.theme || 'dark');
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-theme-toggle]');
    if (t) return setTheme(d.dataset.theme === 'dark' ? 'light' : 'dark');
    var p = e.target.closest('[data-pass]');
    if (p) { var i = document.getElementById(p.dataset.pass); i.type = i.type === 'password' ? 'text' : 'password'; p.textContent = i.type === 'password' ? 'Mostrar' : 'Ocultar'; }
    if (e.target.closest('#burger')) document.getElementById('side').classList.toggle('open');
  });
  document.addEventListener('input', function (e) { if (e.target.closest('form.f')) dirty = true; });
  window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  document.addEventListener('submit', function (ev) {
    var m = ev.target.getAttribute('data-confirm');
    if (m && !confirm(m)) return ev.preventDefault();
    dirty = false;
    var b = ev.target.querySelector('button:not([type=button])');
    if (b) setTimeout(function () { b.classList.add('loading'); }, 0);
  });
  var c2 = document.getElementById('clave2'), c1 = document.getElementById('clave');
  if (c1 && c2) { var chk = function () { c2.setCustomValidity(c2.value !== c1.value ? 'Las contraseñas no coinciden' : ''); }; c1.addEventListener('input', chk); c2.addEventListener('input', chk); }
})();
