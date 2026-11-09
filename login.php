<?php
require_once __DIR__ . '/functions.php';
ensure_admin();
if (!empty($_SESSION['uid'])) redirect('dashboard.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $u = q('SELECT * FROM usuarios WHERE usuario=? AND activo=1', [trim($_POST['usuario'] ?? '')])->fetch();
        if ($u && password_verify($_POST['clave'] ?? '', $u['password_hash'])) {
            if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) q('UPDATE usuarios SET password_hash=? WHERE id=?', [password_hash($_POST['clave'], PASSWORD_DEFAULT), $u['id']]);
            session_regenerate_id(true); $_SESSION['uid'] = $u['id']; $_SESSION['nombre'] = $u['nombre']; $_SESSION['rol'] = $u['rol'];
            redirect('dashboard.php');
        }
        flash('Usuario o contraseña incorrectos', 'err');
    } catch (Exception $x) { fail($x); }
}
$title = 'Iniciar sesión'; $f = flash();
?><!DOCTYPE html><html lang="es"><head><?php require_once __DIR__ . '/head.php'; ?></head><body>
<div class="auth"><div class="atop"><a class="back" href="index.php"><i>←</i> Volver</a><button class="btn g" data-theme-toggle type="button"></button></div>
<div class="acard"><a class="brand" href="index.php">Tool<span>Board</span></a><h1>Te damos la bienvenida</h1><p>Ingresá para gestionar las herramientas del taller.</p>
<?php if ($f): ?><div class="msg <?= e($f[1]) ?>"><?= e($f[0]) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<label>👤 Usuario <input name="usuario" required autofocus autocomplete="username"></label>
<label style="display: block; width: 100%;">🔒 Contraseña 
    <div style="display: flex; gap: 8px; width: 100%; margin-top: 4px; align-items: stretch;">
        <input type="password" id="pw" name="clave" required autocomplete="current-password" style="flex: 1; width: 100%; min-width: 0;">
        <button type="button" class="btn s" data-pass="pw" style="flex: 0 0 auto; width: auto; padding: 0 14px; margin: 0;">Mostrar</button>
    </div>
</label>
<button class="btn" style="margin-top: 12px;">Iniciar sesión</button></form>
<p class="alt">¿No tenés una cuenta? <a href="registro.php">Registrarse</a></p><a class="btn s" href="index.php" style="margin-top:8px">Volver al inicio</a></div></div>
<script src="script.js"></script></body></html>