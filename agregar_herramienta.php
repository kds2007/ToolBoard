<?php
require_once __DIR__ . '/auth.php'; require_login();
$id = (int)($_GET['id'] ?? 0);
$h = $id ? q('SELECT * FROM herramientas WHERE id=?', [$id])->fetch() : null;
if ($id && !$h) redirect('inventario.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $d = []; foreach (['codigo', 'nombre', 'marca', 'ubicacion', 'descripcion'] as $k) $d[$k] = trim($_POST[$k] ?? '');
    $tot = filter_var($_POST['cantidad_total'] ?? '', FILTER_VALIDATE_INT);
    $est = in_array($_POST['estado'] ?? '', ['activa', 'mantenimiento', 'fuera_servicio']) ? $_POST['estado'] : 'activa';
    $cat = (int)($_POST['categoria_id'] ?? 0) ?: null;
    try {
        if ($d['codigo'] === '' || $d['nombre'] === '' || $tot === false || $tot < 0) throw new Exception('Código, nombre y una cantidad válida son obligatorios');
        if ($h) {
            $disp = $h['cantidad_disponible'] + $tot - $h['cantidad_total'];
            if ($disp < 0) throw new Exception('El total no puede ser menor que las unidades prestadas');
            q('UPDATE herramientas SET codigo=?,nombre=?,categoria_id=?,marca=?,ubicacion=?,descripcion=?,cantidad_total=?,cantidad_disponible=?,estado=? WHERE id=?',
              [$d['codigo'], $d['nombre'], $cat, $d['marca'], $d['ubicacion'], $d['descripcion'], $tot, $disp, $est, $id]);
            log_move('modificacion', "Modificada: {$d['nombre']}", $id);
        } else {
            q('INSERT INTO herramientas(codigo,nombre,categoria_id,marca,ubicacion,descripcion,cantidad_total,cantidad_disponible,estado) VALUES(?,?,?,?,?,?,?,?,?)',
              [$d['codigo'], $d['nombre'], $cat, $d['marca'], $d['ubicacion'], $d['descripcion'], $tot, $tot, $est]);
            log_move('alta', "Alta: {$d['nombre']}", db()->lastInsertId());
        }
        flash('Herramienta guardada'); redirect('inventario.php');
    } catch (PDOException $x) { flash('Código duplicado o dato inválido', 'err'); }
    catch (Exception $x) { fail($x); }
}
$v = fn($k) => e($_POST[$k] ?? ($h[$k] ?? ''));
$title = $h ? 'Editar herramienta' : 'Nueva herramienta'; require_once __DIR__ . '/header.php';
$cats = q('SELECT id,nombre FROM categorias WHERE activo=1 ORDER BY nombre')->fetchAll();
?>
<form class="f" method="post"><?= csrf_field() ?>
<label>Código <input name="codigo" required value="<?= $v('codigo') ?>"></label>
<label>Nombre <input name="nombre" required value="<?= $v('nombre') ?>"></label>
<label>Categoría <select name="categoria_id"><option value="">—</option><?php foreach ($cats as $k): ?><option value="<?= $k['id'] ?>" <?= ($_POST['categoria_id'] ?? $h['categoria_id'] ?? '') == $k['id'] ? 'selected' : '' ?>><?= e($k['nombre']) ?></option><?php endforeach; ?></select></label>
<label>Marca <input name="marca" value="<?= $v('marca') ?>"></label>
<label>Ubicación <input name="ubicacion" value="<?= $v('ubicacion') ?>"></label>
<label>Cantidad total <input type="number" min="0" name="cantidad_total" required value="<?= $v('cantidad_total') ?>"></label>
<label>Estado <select name="estado"><?php foreach (['activa', 'mantenimiento', 'fuera_servicio'] as $x): ?><option <?= ($_POST['estado'] ?? $h['estado'] ?? 'activa') === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></label>
<label>Descripción <textarea name="descripcion"><?= $v('descripcion') ?></textarea></label>
<button class="btn">Guardar</button></form>
<?php require_once __DIR__ . '/footer.php'; ?>
