<?php $title = 'Historial'; require_once __DIR__ . '/header.php';
$t = $_GET['tipo'] ?? '';
$rows = q('SELECT h.fecha,h.tipo,h.descripcion,u.nombre usuario FROM historial h LEFT JOIN usuarios u ON u.id=h.usuario_id' . ($t ? ' WHERE h.tipo=?' : '') . ' ORDER BY h.id DESC LIMIT 300', $t ? [$t] : [])->fetchAll();
?>
<form class="in" method="get"><select name="tipo"><option value="">Todos</option><?php foreach (['alta', 'modificacion', 'prestamo', 'devolucion', 'mantenimiento', 'incidencia', 'baja'] as $x): ?><option <?= $t === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select><button class="btn s">Filtrar</button></form>
<?php tabla($rows); require_once __DIR__ . '/footer.php'; ?>
