<?php
require_once __DIR__ . '/auth.php';
require_login();
$title = 'Historial';
$t = $_GET['tipo'] ?? '';
$tipos = ['alta', 'modificacion', 'prestamo', 'devolucion', 'mantenimiento', 'incidencia', 'baja'];
$rows = q('SELECT h.fecha,h.tipo,h.descripcion,u.nombre usuario FROM historial h LEFT JOIN usuarios u ON u.id=h.usuario_id' . ($t ? ' WHERE h.tipo=?' : '') . ' ORDER BY h.id DESC LIMIT 300', $t ? [$t] : [])->fetchAll();
require_once __DIR__ . '/header.php';
?>
<form class="in" method="get"><select name="tipo"><option value="">Todos</option><?php foreach ($tipos as $x): ?><option value="<?= e($x) ?>" <?= $t === $x ? 'selected' : '' ?>><?= e($x) ?></option><?php endforeach; ?></select><button class="btn s">Filtrar</button></form>
<?php tabla($rows); require_once __DIR__ . '/footer.php'; ?>
