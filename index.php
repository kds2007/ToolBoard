<?php
require_once __DIR__ . '/functions.php';
// NO llamar a ensure_admin() aquí, la landing es pública.
$logged = !empty($_SESSION['uid']); 
$title = 'Control de herramientas industriales';
$F = [
    ['📦', 'Inventario', 'Alta, edición, búsqueda, filtros por categoría y estado, y baja lógica de herramientas.'], 
    ['🗂', 'Categorías', 'Organización del inventario; no se desactivan si tienen herramientas asociadas.'],
    ['👷', 'Personal', 'Registro de trabajadores; los inactivos no pueden recibir préstamos.'], 
    ['🤝', 'Préstamos', 'Validan estado y stock real antes de confirmar, con fecha prevista de devolución.'],
    ['↩️', 'Devoluciones', 'Totales o parciales; si vuelven dañadas se abre una incidencia de mantenimiento.'], 
    ['🛠', 'Mantenimiento', 'Una herramienta en mantenimiento no puede prestarse hasta finalizarlo.'],
    ['🕑', 'Historial', 'Registro de altas, modificaciones, préstamos, devoluciones, mantenimientos y bajas.'], 
    ['🖨', 'Reportes', 'Inventario, disponibles, prestadas, atrasadas, mantenimientos e historial, con impresión.']
];
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
<!-- Resto del HTML de index.php igual -->
<script src="script.js"></script></body></html>