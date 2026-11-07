<?php
require_once __DIR__ . '/auth.php'; require_login(); require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        if ($_POST['accion'] === 'toggle') { q('UPDATE personal SET activo=1-activo WHERE id=?', [(int)$_POST['id']]); log_move('modificacion', 'Cambio de estado de personal #' . (int)$_POST['id']); }
        else {
            $d = []; foreach (['nombre', 'apellido', 'documento', 'sector', 'cargo'] as $k) $d[$k] = trim($_POST[$k] ?? '');
            if ($d['nombre'] === '' || $d['apellido'] === '' || $d['documento'] === '') throw new Exception('Nombre, apellido y documento son obligatorios');
            q('INSERT INTO personal(nombre,apellido,documento,sector,cargo) VALUES(?,?,?,?,?)', array_values($d));
            log_move('alta', "Alta de personal: {$d['nombre']} {$d['apellido']}");
        }
        flash('Guardado');
    } catch (PDOException $x) { flash('Documento duplicado', 'err'); } catch (Exception $x) { fail($x); }
    redirect('personal.php');
}
$title = 'Personal'; require_once __DIR__ . '/header.php';
?>
<form class="in" method="post"><?= csrf_field() ?><input type="hidden" name="accion" value="alta">
<input name="nombre" placeholder="Nombre" required><input name="apellido" placeholder="Apellido" required><input name="documento" placeholder="Documento/código" required><input name="sector" placeholder="Sector"><input name="cargo" placeholder="Cargo"><button class="btn">Agregar</button></form>
<div class="wrap"><table><tr><th>Nombre</th><th>Documento</th><th>Sector</th><th>Cargo</th><th>Estado</th><th></th></tr>
<?php foreach (q('SELECT * FROM personal ORDER BY apellido,nombre')->fetchAll() as $p): ?><tr><td><?= e($p['nombre'] . ' ' . $p['apellido']) ?></td><td><?= e($p['documento']) ?></td><td><?= e($p['sector']) ?></td><td><?= e($p['cargo']) ?></td>
<td><span class="tag <?= $p['activo'] ? 'activa' : 'pendiente' ?>"><?= $p['activo'] ? 'activo' : 'inactivo' ?></span></td>
<td><form class="in" method="post" data-confirm="¿Cambiar el estado?"><?= csrf_field() ?><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="btn s"><?= $p['activo'] ? 'Desactivar' : 'Activar' ?></button></form></td></tr><?php endforeach; ?></table></div>
<?php require_once __DIR__ . '/footer.php'; ?>
