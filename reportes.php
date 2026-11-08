<?php $title = 'Reportes'; require_once __DIR__ . '/header.php';
$R = [
 'inventario' => ['Inventario completo', "SELECT codigo,nombre,cantidad_total total,cantidad_disponible disponible,estado FROM herramientas WHERE estado<>'baja' ORDER BY nombre"],
 'disponibles' => ['Herramientas disponibles', "SELECT codigo,nombre,cantidad_disponible disponible,ubicacion FROM herramientas WHERE estado='activa' AND cantidad_disponible>0 ORDER BY nombre"],
 'prestadas' => ['Herramientas prestadas', "SELECT h.nombre,CONCAT(w.nombre,' ',w.apellido) trabajador,p.cantidad-p.cantidad_devuelta pendiente,p.fecha_prevista FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id JOIN personal w ON w.id=p.personal_id WHERE p.estado<>'devuelto'"],
 'atrasados' => ['Préstamos atrasados', "SELECT h.nombre,CONCAT(w.nombre,' ',w.apellido) trabajador,p.cantidad-p.cantidad_devuelta pendiente,p.fecha_prevista FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id JOIN personal w ON w.id=p.personal_id WHERE p.estado<>'devuelto' AND p.fecha_prevista<CURDATE()"],
 'mantenimientos' => ['Mantenimientos', "SELECT h.nombre,m.problema,m.responsable,m.fecha_inicio,m.fecha_fin,m.estado FROM mantenimientos m JOIN herramientas h ON h.id=m.herramienta_id ORDER BY m.id DESC"],
 'historial' => ['Historial de movimientos', "SELECT fecha,tipo,descripcion FROM historial ORDER BY id DESC LIMIT 500"],
];
$r = isset($R[$_GET['r'] ?? '']) ? $_GET['r'] : 'inventario';
?>
<form class="in noprint" method="get"><select name="r"><?php foreach ($R as $k => $v): ?><option value="<?= $k ?>" <?= $k === $r ? 'selected' : '' ?>><?= e($v[0]) ?></option><?php endforeach; ?></select>
<button class="btn s">Generar</button><button type="button" class="btn" onclick="window.print()">Imprimir</button></form>
<h3><?= e($R[$r][0]) ?> — <?= date('d/m/Y') ?></h3><?php tabla(q($R[$r][1])->fetchAll()); require_once __DIR__ . '/footer.php'; ?>
