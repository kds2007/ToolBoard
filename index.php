<?php
require_once __DIR__ . '/functions.php';
ensure_admin();
$logged = !empty($_SESSION['uid']); $title = 'Control de herramientas industriales';
$F = [['📦', 'Inventario', 'Alta, edición, búsqueda, filtros por categoría y estado, y baja lógica de herramientas.'], ['🗂', 'Categorías', 'Organización del inventario; no se desactivan si tienen herramientas asociadas.'],
 ['👷', 'Personal', 'Registro de trabajadores; los inactivos no pueden recibir préstamos.'], ['🤝', 'Préstamos', 'Validan estado y stock real antes de confirmar, con fecha prevista de devolución.'],
 ['↩️', 'Devoluciones', 'Totales o parciales; si vuelven dañadas se abre una incidencia de mantenimiento.'], ['🛠', 'Mantenimiento', 'Una herramienta en mantenimiento no puede prestarse hasta finalizarlo.'],
 ['🕑', 'Historial', 'Registro de altas, modificaciones, préstamos, devoluciones, mantenimientos y bajas.'], ['🖨', 'Reportes', 'Inventario, disponibles, prestadas, atrasadas, mantenimientos e historial, con impresión.']];
?><!DOCTYPE html><html lang="es"><head><?php require_once __DIR__ . '/head.php'; ?></head><body>
<header class="nav"><a class="brand" href="index.php">Tool<span>Board</span></a>
<nav class="links"><a href="#que-es">Proyecto</a><a href="#como-funciona">Cómo funciona</a><a href="#funciones">Funciones</a><a href="#beneficios">Beneficios</a><a href="#destinatarios">Destinatarios</a></nav>
<div class="acts"><button class="btn g" data-theme-toggle type="button"></button>
<?php if ($logged): ?><a class="btn" href="dashboard.php">Ir al panel</a><?php else: ?><a class="btn g" href="login.php">Iniciar sesión</a><a class="btn" href="registro.php">Registrarse</a><?php endif; ?></div></header>
<section class="sec hero"><div><h1>Cada herramienta del taller, <span>bajo control</span>.</h1>
<p>ToolBoard es un sistema web para registrar, prestar, devolver y mantener las herramientas de un taller industrial, sabiendo siempre qué hay, quién lo tiene y cuándo debe volver.</p>
<p><a class="btn" href="<?= $logged ? 'dashboard.php' : 'login.php' ?>">Acceder al sistema</a></p></div>
<div class="mock" aria-hidden="true"><small>Vista ilustrativa del panel</small><div class="mc"><div><b>24</b>Disponibles</div><div><b>6</b>Prestadas</div><div><b>2</b>Atrasadas</div></div>
<div class="bar"><i style="width:75%"></i></div><div class="bar"><i style="width:40%"></i></div><div class="bar"><i style="width:60%"></i></div></div></section>
<section class="sec" id="que-es"><h2>¿Qué es ToolBoard?</h2><p>Una aplicación de administración y control de inventario industrial, hecha con PHP, MySQL/MariaDB y JavaScript, que centraliza herramientas, personal, préstamos, devoluciones y mantenimiento.</p>
<div class="grid3"><div class="card"><div class="ico">🎯</div><h3>Objetivo</h3>Facilitar el registro, seguimiento, préstamo, devolución y mantenimiento de las herramientas.</div>
<div class="card"><div class="ico">❓</div><h3>Problema que resuelve</h3>Saber qué herramientas existen, cuántas hay disponibles, quién las tiene, cuándo vuelven y cuáles están dañadas.</div></div></section>
<section class="sec" id="como-funciona"><h2>¿Cómo funciona?</h2><div class="grid3">
<div class="card step"><b>1</b><h3>Registrar</h3>Se cargan herramientas, categorías y personal.</div><div class="card step"><b>2</b><h3>Prestar</h3>Se elige herramienta, trabajador, cantidad y fecha prevista.</div>
<div class="card step"><b>3</b><h3>Devolver</h3>Se registra la devolución y el stock se actualiza.</div><div class="card step"><b>4</b><h3>Consultar</h3>Panel, historial y reportes muestran el estado real.</div></div></section>
<section class="sec" id="funciones"><h2>Funcionalidades</h2><div class="grid3"><?php foreach ($F as $x): ?><div class="card"><div class="ico"><?= $x[0] ?></div><h3><?= e($x[1]) ?></h3><?= e($x[2]) ?></div><?php endforeach; ?></div></section>
<section class="sec" id="beneficios"><h2>Beneficios</h2><div class="grid3">
<div class="card"><div class="ico">🔒</div><h3>Stock consistente</h3>Las operaciones usan transacciones y no permiten cantidades negativas.</div>
<div class="card"><div class="ico">🔎</div><h3>Trazabilidad</h3>Cada movimiento importante queda en el historial.</div>
<div class="card"><div class="ico">⚠️</div><h3>Alertas</h3>El panel marca devoluciones atrasadas y herramientas sin stock.</div>
<div class="card"><div class="ico">🛡</div><h3>Acceso por roles</h3>Cuentas de administrador y de usuario; contraseñas con hash.</div>
<div class="card"><div class="ico">🌗</div><h3>Modo claro y oscuro</h3>Interfaz adaptable a escritorio, tablet y celular.</div></div></section>
<section class="sec" id="destinatarios"><h2>¿Para quién es?</h2><p>Para quienes administran las herramientas de un taller industrial: encargados del depósito, supervisores que consultan el estado y administradores del sistema.</p>
<p><?php if (!$logged): ?><a class="btn" href="registro.php">Crear cuenta</a> <?php endif; ?><a class="btn s" href="login.php">Iniciar sesión</a></p></section>
<footer class="foot"><div><b>ToolBoard</b><br>Sistema de gestión y control de herramientas de un taller industrial.<br>© 2026</div>
<div><a href="#que-es">Proyecto</a><a href="#funciones">Funciones</a><a href="login.php">Iniciar sesión</a><a href="registro.php">Registrarse</a></div></footer>
<script src="script.js"></script></body></html>
