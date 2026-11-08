<?php $title = 'Inventario'; require_once __DIR__ . '/header.php';
$s = trim($_GET['s'] ?? ''); $c = (int)($_GET['c'] ?? 0); $st = $_GET['e'] ?? '';
$w = ["h.estado<>'baja'"]; $p = [];
if ($s !== '') { $w[] = '(h.nombre LIKE ? OR h.codigo LIKE ? OR h.marca LIKE ?)'; array_push($p, "%$s%", "%$s%", "%$s%"); }
if ($c) { $w[] = 'h.categoria_id=?'; $p[] = $c; }
if ($st !== '') { $w[] = 'h.estado=?'; $p[] = $st; }
$rows = q('SELECT h.*,c.nombre cat FROM herramientas h LEFT JOIN categorias c ON c.id=h.categoria_id WHERE ' . implode(' AND ', $w) . ' ORDER BY h.nombre', $p)->fetchAll();
$cats = q('SELECT id,nombre FROM categorias WHERE activo=1 ORDER BY nombre')->fetchAll();
?>
<form class="in" method="get"><input name="s" placeholder="Buscar…" value="<?= e($s) ?>">
<select name="c"><option value="">Categoría</option><?php foreach ($cats as $k): ?><option value="<?= $k['id'] ?>" <?= $c == $k['id'] ? 'selected' : '' ?>><?= e($k['nombre']) ?></option><?php endforeach; ?></select>
<select name="e"><option value="">Estado</option><?php foreach (['activa', 'mantenimiento', 'fuera_servicio'] as $x): ?><option <?= $st === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select>
<button class="btn s">Filtrar</button> <a class="btn" href="agregar_herramienta.php">Nueva herramienta</a></form>
<div class="wrap"><table><tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Marca</th><th>Ubicación</th><th>Total</th><th>Disp.</th><th>Estado</th><th></th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['codigo']) ?></td><td><?= e($r['nombre']) ?></td><td><?= e($r['cat']) ?></td><td><?= e($r['marca']) ?></td><td><?= e($r['ubicacion']) ?></td>
<td><?= $r['cantidad_total'] ?></td><td><?= $r['cantidad_disponible'] ?></td><td><span class="tag <?= e($r['estado']) ?>"><?= e($r['estado']) ?></span></td>
<td><a class="btn s" href="editar_herramienta.php?id=<?= $r['id'] ?>">Editar</a>
<form class="in" style="display:inline" method="post" action="eliminar_herramienta.php" data-confirm="¿Dar de baja esta herramienta?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn">Baja</button></form></td></tr>
<?php endforeach; ?></table></div>
<?php require_once __DIR__ . '/footer.php'; ?>
