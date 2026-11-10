<?php
require_once __DIR__ . '/functions.php';
if (!empty($_SESSION['uid'])) redirect('dashboard.php');
$v = ['nombre' => '', 'apellido' => '', 'usuario' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($v as $k => $_) $v[$k] = trim($_POST[$k] ?? '');
    $c1 = $_POST['clave'] ?? ''; $c2 = $_POST['clave2'] ?? '';
    try {
        if ($v['nombre'] === '' || $v['apellido'] === '' || $v['usuario'] === '' || $c1 === '') throw new Exception('Completá todos los campos');
        if (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $v['usuario'])) throw new Exception('El usuario debe tener 3 a 40 caracteres');
        if (strlen($c1) < 3) throw new Exception('La contraseña debe tener al menos 3 caracteres');
        if ($c1 !== $c2) throw new Exception('Las contraseñas no coinciden');
        if (q('SELECT 1 FROM usuarios WHERE usuario=?', [$v['usuario']])->fetch()) throw new Exception('Ese nombre de usuario ya existe');
        
        q("INSERT INTO usuarios(usuario,nombre,password_hash,rol) VALUES(?,?,?,'usuario')", [$v['usuario'], mb_substr($v['nombre'] . ' ' . $v['apellido'], 0, 80), password_hash($c1, PASSWORD_DEFAULT)]);
        flash('Cuenta creada. Ya podés iniciar sesión.'); redirect('login.php');
    } catch (PDOException $x) { flash('Ese nombre de usuario ya existe o hubo un error de base de datos', 'err'); }
    catch (Exception $x) { fail($x); }
}
$title = 'Registro'; $f = flash();
?>
<!-- HTML de registro.php -->