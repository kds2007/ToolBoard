<?php $title = 'Préstamos'; require_once __DIR__ . '/header.php'; ?>
<a class="btn" href="registrar_prestamo.php">Registrar préstamo</a>
<?php tabla(q("SELECT p.id,h.nombre herramienta,CONCAT(w.nombre,' ',w.apellido) trabajador,p.cantidad,p.cantidad_devuelta devuelta,p.fecha_prestamo,p.fecha_prevista,p.estado
 FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id JOIN personal w ON w.id=p.personal_id ORDER BY p.id DESC LIMIT 200")->fetchAll()); ?>
<?php require_once __DIR__ . '/footer.php'; ?>
