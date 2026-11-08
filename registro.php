<?php
require_once __DIR__ . '/functions.php';
ensure_admin();
if (!empty($_SESSION['uid'])) redirect('dashboard.php');
$v = ['nombre' => '', 'apellido' => '', 'usuario' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($v as $k => $_) $v[$k] = trim($_POST[$k] ?? '');
    $c1 = $_POST['clave'] ?? ''; $c2 = $_POST['clave2'] ?? '';
    try {
        if ($v['nombre'] === '' || $v['apellido'] === '' || $v['usuario'] === '' || $c1 === '') throw new Exception('Completá todos los campos');
        if (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $v['usuario'])) throw new Exception('El usuario debe tener 3 a 40 caracteres (letras, números, . _ -)');
        if (strlen($c1) < 3) throw new Exception('La contraseña debe tener al menos 3 caracteres');
        if ($c1 !== $c2) throw new Exception('Las contraseñas no coinciden');
        if (q('SELECT 1 FROM usuarios WHERE usuario=?', [$v['usuario']])->fetch()) throw new Exception('Ese nombre de usuario ya existe');
        // El rol siempre es "usuario": nunca se toma del formulario.
        q("INSERT INTO usuarios(usuario,nombre,password_hash,rol) VALUES(?,?,?,'usuario')", [$v['usuario'], mb_substr($v['nombre'] . ' ' . $v['apellido'], 0, 80), password_hash($c1, PASSWORD_DEFAULT)]);
        flash('Cuenta creada. Ya podés iniciar sesión.'); redirect('login.php');
    } catch (PDOException $x) { flash('Ese nombre de usuario ya existe o hubo un error de base de datos', 'err'); }
    catch (Exception $x) { fail($x); }
}
$title = 'Registro'; $f = flash();
?><!DOCTYPE html><html lang="es"><head><?php require_once __DIR__ . '/head.php'; ?></head><body>
<div class="auth"><div class="atop"><a class="back" href="login.php"><i>←</i> Volver</a><button class="btn g" data-theme-toggle type="button"></button></div>
<div class="acard"><a class="brand" href="index.php">Tool<span>Board</span></a><h1>Crear cuenta</h1><p>Las cuentas nuevas se crean con rol de usuario.</p>
<?php if ($f): ?><div class="msg <?= e($f[1]) ?>"><?= e($f[0]) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<label>Nombre <input name="nombre" required value="<?= e($v['nombre']) ?>"></label><label>Apellido <input name="apellido" required value="<?= e($v['apellido']) ?>"></label>
<label>Usuario <input name="usuario" required minlength="3" maxlength="40" pattern="[A-Za-z0-9_.\-]+" value="<?= e($v['usuario']) ?>" autocomplete="username"></label>
<label>Contraseña <input type="password" id="clave" name="clave" required minlength="3" autocomplete="new-password"></label>
<label>Confirmar contraseña <input type="password" id="clave2" name="clave2" required autocomplete="new-password"></label>
<button class="btn">Registrarse</button></form><p class="alt">¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a></p></div></div>
<script src="script.js"></script></body></html>
