<?php $title = 'Inicio'; require_once __DIR__ . '/header.php';
$c = fn($s) => (int)q($s)->fetchColumn();
$cards = [
 'Total de herramientas' => $c("SELECT COALESCE(SUM(cantidad_total),0) FROM herramientas WHERE estado<>'baja'"),
 'Disponibles' => $c("SELECT COALESCE(SUM(cantidad_disponible),0) FROM herramientas WHERE estado='activa'"),
 'Prestadas' => $c("SELECT COALESCE(SUM(cantidad-cantidad_devuelta),0) FROM prestamos"),
 'En mantenimiento' => $c("SELECT COUNT(*) FROM herramientas WHERE estado='mantenimiento'"),
 'Fuera de servicio' => $c("SELECT COUNT(*) FROM herramientas WHERE estado='fuera_servicio'"),
 'Préstamos pendientes' => $c("SELECT COUNT(*) FROM prestamos WHERE estado<>'devuelto'"),
 'Devoluciones atrasadas' => $c("SELECT COUNT(*) FROM prestamos WHERE estado<>'devuelto' AND fecha_prevista<CURDATE()"),
];
$bajo = $c("SELECT COUNT(*) FROM herramientas WHERE estado='activa' AND cantidad_disponible=0");
?>
<?php if ($cards['Devoluciones atrasadas']): ?><div class="msg err">Hay <?= $cards['Devoluciones atrasadas'] ?> devolución(es) atrasada(s). <a href="devoluciones.php">Ver</a></div><?php endif; ?>
<?php if ($bajo): ?><div class="msg err"><?= $bajo ?> herramienta(s) sin unidades disponibles.</div><?php endif; ?>
<div class="cards"><?php foreach ($cards as $n => $v): ?><div class="card"><b><?= $v ?></b><?= e($n) ?></div><?php endforeach; ?></div>
<h3>Últimos movimientos</h3>
<?php tabla(q('SELECT h.fecha,h.tipo,h.descripcion FROM historial h ORDER BY h.id DESC LIMIT 8')->fetchAll()); ?>
<?php require_once __DIR__ . '/footer.php'; ?>
