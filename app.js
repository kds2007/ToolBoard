(function () {
'use strict';
var SK = 'tb-session', TK = 'tb-token', db = { tools: [], staff: [], loans: [], maint: [], hist: [] }, edit = null, flashMsg = null, R = {};
function e(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
function today() { return new Date().toISOString().slice(0, 10); }
function sess() { try { return JSON.parse(sessionStorage.getItem(SK)); } catch (x) { return null; } }
function adm() { var s = sess(); return !!s && s.rol === 'admin'; }
function tool(id) { return db.tools.find(function (x) { return x.id === id; }); }
function unload() { document.querySelectorAll('.loading').forEach(function (b) { b.classList.remove('loading'); }); }
async function api(a, d) {
  if (!window.TB_API) throw 'Falta configurar la URL del servidor en config.js (TB_API).';
  var h = { 'Content-Type': 'application/json' }, t = sessionStorage.getItem(TK); if (t) h['X-TB-Token'] = t;
  var r, j;
  try { r = await fetch(window.TB_API, { method: 'POST', headers: h, body: JSON.stringify(Object.assign({ a: a }, d || {})) }); } catch (x) { throw 'No se pudo conectar con el servidor.'; }
  try { j = await r.json(); } catch (x) { throw 'Respuesta inválida del servidor.'; }
  if (r.status === 401 && a !== 'login') { sessionStorage.removeItem(SK); sessionStorage.removeItem(TK); if (document.getElementById('app')) location.replace('login.html'); }
  if (!r.ok || j.error) throw j.error || 'Error del servidor';
  return j;
}
async function load() { db = (await api('state')).data; }
function err(x) { return typeof x === 'string' ? x : 'Error inesperado'; }
function showMsg(t, k) { var m = document.getElementById('msg'); if (m) m.innerHTML = '<div class="msg ' + k + '">' + e(t) + '</div>'; setTimeout(unload, 60); }
function tbl(h, rows) {
  if (!rows.length) return '<p>Sin datos.</p>';
  return '<div class="wrap"><table><tr>' + h.map(function (x) { return '<th>' + x + '</th>'; }).join('') + '</tr>' + rows.map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + c + '</td>'; }).join('') + '</tr>'; }).join('') + '</table></div>';
}
function tag(s) { return '<span class="tag ' + e(s) + '">' + e(s) + '</span>'; }
function fld(n, l, v, a) { return '<label>' + l + ' <input name="' + n + '" value="' + e(v) + '" ' + (a || '') + '></label>'; }
function opts(a, f) { return a.map(function (x) { return '<option value="' + x.id + '">' + e(f(x)) + '</option>'; }).join(''); }
var TT = { dashboard: 'Inicio', inventario: 'Inventario', prestamos: 'Préstamos', devoluciones: 'Devoluciones', personal: 'Personal', mantenimiento: 'Mantenimiento', historial: 'Historial' };
function who(l) { var p = db.staff.find(function (x) { return x.id === l.staff; }); return p ? p.nombre + ' ' + p.apellido : ''; }
R.dashboard = function () {
  var T = db.tools, L = db.loans, t = today(), pend = L.filter(function (l) { return l.est !== 'devuelto'; }), atr = pend.filter(function (l) { return l.prev < t; });
  function sum(a, f) { return a.reduce(function (s, x) { return s + f(x); }, 0); }
  var C = [['Total de herramientas', sum(T.filter(function (x) { return x.estado !== 'baja'; }), function (x) { return x.total; })],
    ['Disponibles', sum(T.filter(function (x) { return x.estado === 'activa'; }), function (x) { return x.disp; })],
    ['Prestadas', sum(L, function (l) { return l.cant - l.dev; })], ['En mantenimiento', T.filter(function (x) { return x.estado === 'mantenimiento'; }).length],
    ['Fuera de servicio', T.filter(function (x) { return x.estado === 'fuera_servicio'; }).length], ['Préstamos pendientes', pend.length], ['Devoluciones atrasadas', atr.length]];
  return (atr.length ? '<div class="msg err">Hay ' + atr.length + ' devolución(es) atrasada(s). <a href="#devoluciones">Ver</a></div>' : '') +
    '<div class="cards">' + C.map(function (c) { return '<div class="card"><b>' + c[1] + '</b>' + c[0] + '</div>'; }).join('') + '</div><h3>Últimos movimientos</h3>' +
    tbl(['Fecha', 'Tipo', 'Descripción'], db.hist.slice(0, 8).map(function (h) { return [e(h.f), e(h.tipo), e(h.d)]; }));
};
R.inventario = function () {
  var x = (edit && tool(edit)) || {};
  return '<form class="f" data-f="tool"><input type="hidden" name="id" value="' + (x.id || '') + '">' + fld('codigo', 'Código', x.codigo, 'required') + fld('nombre', 'Nombre', x.nombre, 'required') +
    fld('marca', 'Marca', x.marca) + fld('ubicacion', 'Ubicación', x.ubicacion) + fld('total', 'Cantidad total', x.total, 'type="number" min="0" required') +
    '<button class="btn">' + (x.id ? 'Guardar cambios' : 'Agregar herramienta') + '</button></form>' +
    tbl(['Código', 'Nombre', 'Marca', 'Ubicación', 'Total', 'Disp.', 'Estado', ''], db.tools.filter(function (t) { return t.estado !== 'baja'; }).map(function (t) {
      return [e(t.codigo), e(t.nombre), e(t.marca), e(t.ubicacion), t.total, t.disp, tag(t.estado), '<button class="btn s" data-act="edit" data-id="' + t.id + '">Editar</button>' + (adm() ? ' <button class="btn" data-act="baja" data-id="' + t.id + '">Baja</button>' : '')];
    }));
};
R.personal = function () {
  return '<form class="f" data-f="staff">' + fld('nombre', 'Nombre', '', 'required') + fld('apellido', 'Apellido', '', 'required') + fld('doc', 'Documento/código', '', 'required') + fld('sector', 'Sector', '') + fld('cargo', 'Cargo', '') + '<button class="btn">Agregar</button></form>' +
    tbl(['Nombre', 'Documento', 'Sector', 'Cargo', 'Estado', ''], db.staff.map(function (p) {
      return [e(p.nombre + ' ' + p.apellido), e(p.doc), e(p.sector), e(p.cargo), tag(p.activo ? 'activa' : 'pendiente').replace('>pendiente<', '>inactivo<').replace('>activa<', '>activo<'), '<button class="btn s" data-act="staff" data-id="' + p.id + '">' + (p.activo ? 'Desactivar' : 'Activar') + '</button>'];
    }));
};
R.prestamos = function () {
  var ok = db.tools.filter(function (t) { return t.estado === 'activa' && t.disp > 0; });
  return '<form class="f" data-f="loan"><label>Herramienta <select name="tool" required>' + opts(ok, function (t) { return t.nombre + ' (disp. ' + t.disp + ')'; }) + '</select></label>' +
    '<label>Trabajador <select name="staff" required>' + opts(db.staff.filter(function (p) { return p.activo; }), function (p) { return p.nombre + ' ' + p.apellido; }) + '</select></label>' +
    fld('cant', 'Cantidad', 1, 'type="number" min="1" required') + fld('prev', 'Fecha prevista de devolución', '', 'type="date" min="' + today() + '" required') + fld('obs', 'Observaciones', '') + '<button class="btn">Confirmar préstamo</button></form>' +
    tbl(['#', 'Herramienta', 'Trabajador', 'Cant.', 'Devuelta', 'Prevista', 'Estado'], db.loans.slice().reverse().map(function (l) { return [l.id, e(tool(l.tool).nombre), e(who(l)), l.cant, l.dev, e(l.prev), tag(l.est)]; }));
};
R.devoluciones = function () {
  return tbl(['Herramienta', 'Trabajador', 'Pendiente', 'Prevista', 'Registrar devolución (cantidad · dañadas)'], db.loans.filter(function (l) { return l.est !== 'devuelto'; }).map(function (l) {
    var p = l.cant - l.dev;
    return [e(tool(l.tool).nombre), e(who(l)), p, e(l.prev) + (l.prev < today() ? ' <span class="tag pendiente">atrasado</span>' : ''),
      '<form class="in" data-f="ret"><input type="hidden" name="id" value="' + l.id + '"><input name="cant" type="number" min="1" max="' + p + '" value="' + p + '" style="width:70px"><input name="dan" type="number" min="0" value="0" style="width:70px"><button class="btn">Devolver</button></form>'];
  }));
};
R.mantenimiento = function () {
  return '<form class="f" data-f="maint"><label>Herramienta <select name="tool">' + opts(db.tools.filter(function (t) { return t.estado !== 'baja'; }), function (t) { return t.nombre; }) + '</select></label>' +
    fld('prob', 'Problema', '', 'required') + fld('resp', 'Responsable', sess().nombre, 'required') + '<button class="btn">Registrar mantenimiento</button></form>' +
    tbl(['#', 'Herramienta', 'Problema', 'Responsable', 'Inicio', 'Fin', 'Estado', ''], db.maint.slice().reverse().map(function (m) {
      return [m.id, e(tool(m.tool).nombre), e(m.prob), e(m.resp), e(m.ini), e(m.fin), tag(m.est), m.est === 'en_proceso' ? '<button class="btn s" data-act="mfin" data-id="' + m.id + '">Finalizar</button>' : ''];
    }));
};
R.historial = function () {
  return '<button class="btn s noprint" onclick="window.print()">Imprimir</button>' + tbl(['Fecha', 'Tipo', 'Descripción'], db.hist.slice(0, 300).map(function (h) { return [e(h.f), e(h.tipo), e(h.d)]; }));
};
function shell(r, body) {
  var M = Object.keys(TT).filter(function (k) { return adm() || k !== 'personal'; }), u = sess();
  return '<aside class="side noprint" id="side"><a class="brand" href="index.html">Tool<span>Board</span></a><nav>' + M.map(function (k) { return '<a href="#' + k + '" class="' + (k === r ? 'on' : '') + '">' + TT[k] + '</a>'; }).join('') + '<a href="#" data-act="logout">Cerrar sesión</a></nav></aside>' +
    '<main><div class="top noprint"><a class="back" href="' + (r === 'dashboard' ? 'index.html' : '#dashboard') + '"><i>←</i> Volver</a><span class="sp"></span><span class="who">' + e(u.nombre) + ' · ' + (adm() ? 'Administrador' : 'Usuario') + '</span>' +
    '<button class="btn g" data-theme-toggle type="button"></button><button class="burger" id="burger" type="button" aria-label="Menú">☰</button></div><h2>' + TT[r] + '</h2>' +
    (flashMsg ? '<div class="msg ' + flashMsg[1] + '">' + e(flashMsg[0]) + '</div>' : '') + body + '</main>';
}
function render() {
  var r = (location.hash || '#dashboard').slice(1); if (!R[r] || (r === 'personal' && !adm())) r = 'dashboard';
  document.getElementById('app').innerHTML = shell(r, R[r]()); flashMsg = null;
  document.querySelectorAll('[data-theme-toggle]').forEach(function (b) { b.textContent = document.documentElement.dataset.theme === 'dark' ? '☀ Claro' : '☾ Oscuro'; });
  setTimeout(unload, 60);
}
async function main() {
  var s = sess(), app = document.getElementById('app'), lf = document.getElementById('loginForm'), rf = document.getElementById('regForm');
  if (document.querySelector('.acts') && s) { var a = document.querySelectorAll('.acts a'); a[0].textContent = 'Ir al panel'; a[0].href = 'panel.html'; a[0].className = 'btn'; if (a[1]) a[1].remove(); }
  if (lf) {
    var fm = sessionStorage.getItem('tb-flash'); if (fm) { sessionStorage.removeItem('tb-flash'); showMsg(fm, 'ok'); }
    lf.addEventListener('submit', async function (ev) {
      ev.preventDefault(); var f = new FormData(lf);
      try { var j = await api('login', { usuario: String(f.get('usuario')).trim(), clave: String(f.get('clave')) }); sessionStorage.setItem(TK, j.token); sessionStorage.setItem(SK, JSON.stringify(j.user)); location.href = 'panel.html'; }
      catch (x) { showMsg(err(x), 'err'); }
    });
  }
  if (rf) rf.addEventListener('submit', async function (ev) {
    ev.preventDefault(); var f = new FormData(rf), v = {}; ['nombre', 'apellido', 'usuario', 'clave', 'clave2'].forEach(function (k) { v[k] = String(f.get(k) || '').trim(); });
    if (!v.nombre || !v.apellido || !v.usuario || !v.clave) return showMsg('Completá todos los campos', 'err');
    if (!/^[A-Za-z0-9_.-]{3,40}$/.test(v.usuario)) return showMsg('El usuario debe tener 3 a 40 caracteres (letras, números, . _ -)', 'err');
    if (v.clave !== v.clave2) return showMsg('Las contraseñas no coinciden', 'err');
    try { await api('register', v); sessionStorage.setItem('tb-flash', 'Cuenta creada. Ya podés iniciar sesión.'); location.href = 'login.html'; } catch (x) { showMsg(err(x), 'err'); }
  });
  if (app) {
    if (!s) return location.replace('login.html');
    try { await load(); } catch (x) { app.innerHTML = '<main><div class="msg err">' + e(err(x)) + '</div><a class="btn" href="login.html">Ir al login</a></main>'; return; }
    async function run(fn) { try { var j = await fn(); await load(); flashMsg = [(j && j.msg) || 'Guardado', 'ok']; } catch (x) { flashMsg = [err(x), 'err']; } render(); window.scrollTo(0, 0); }
    document.addEventListener('submit', function (ev) {
      var n = ev.target.getAttribute('data-f'); if (!n) return; ev.preventDefault();
      var d = Object.fromEntries(new FormData(ev.target)); run(function () { edit = null; return api(n, d); });
    });
    document.addEventListener('click', function (ev) {
      var b = ev.target.closest('[data-act]'); if (!b) return; ev.preventDefault();
      var a = b.dataset.act, id = +b.dataset.id;
      if (a === 'logout') { api('logout').catch(function () {}).then(function () { sessionStorage.removeItem(SK); sessionStorage.removeItem(TK); location.href = 'index.html'; }); return; }
      if (a === 'edit') { edit = id; render(); window.scrollTo(0, 0); }
      else if (a === 'baja') { if (confirm('¿Dar de baja esta herramienta?')) run(function () { return api('baja', { id: id }); }); }
      else if (a === 'staff') run(function () { return api('staff_toggle', { id: id }); });
      else if (a === 'mfin') run(function () { return api('mfin', { id: id }); });
    });
    window.addEventListener('hashchange', render); render();
  }
}
main();
})();
