<?php
require_once __DIR__ . '/functions.php';
// Si ya está logueado, redirigir al dashboard
if (!empty($_SESSION['uid'])) redirect('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $u = q('SELECT * FROM usuarios WHERE usuario=? AND activo=1', [trim($_POST['usuario'] ?? '')])->fetch();
        if ($u && password_verify($_POST['clave'] ?? '', $u['password_hash'])) {
            if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
                q('UPDATE usuarios SET password_hash=? WHERE id=?', [password_hash($_POST['clave'], PASSWORD_DEFAULT), $u['id']]);
            }
            session_regenerate_id(true); 
            $_SESSION['uid'] = $u['id']; 
            $_SESSION['nombre'] = $u['nombre']; 
            $_SESSION['rol'] = $u['rol'];
            redirect('dashboard.php');
        }
        flash('Usuario o contraseña incorrectos', 'err');
    } catch (Exception $x) { fail($x); }
}
$title = 'Iniciar sesión'; $f = flash();
?>
<!-- HTML de login.php -->