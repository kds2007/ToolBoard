<?php
require_once __DIR__ . '/auth.php'; require_login(); require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $id = (int)($_POST['id'] ?? 0); $n = trim($_POST['nombre'] ?? '');
    try {
        if ($_POST['accion'] === 'toggle') {
            $on = (int)q('SELECT activo FROM categorias WHERE id=?', [$id])->fetchColumn();
            if ($on && q("SELECT COUNT(*) FROM herramientas WHERE categoria_id=? AND estado<>'baja'", [$id])->fetchColumn() > 0)
                throw new Exception('La categoría tiene herramientas asociadas; reasígnelas antes de desactivarla');
            q('UPDATE categorias SET activo=1-activo WHERE id=?', [$id]); log_move('modificacion', "Cambio de estado de categoría #$id");
        } else {
            if ($n === '') throw new Exception('Nombre obligatorio');
            if ($id) q('UPDATE categorias SET nombre=? WHERE id=?', [$n, $id]); else q('INSERT INTO categorias(nombre) VALUES(?)', [$n]);
            log_move($id ? 'modificacion' : 'alta', "Categoría: $n");
        }
        flash('Guardado');
    } catch (PDOException $x) { flash('Nombre duplicado', 'err'); } catch (Exception $x) { fail($x); }
    redirect('categorias.php');
}
$title = 'Categorías'; require_once __DIR__ . '/header.php';
?>
<form class="in" method="post"><?= csrf_field() ?><input type="hidden" name="accion" value="guardar"><input name="nombre" placeholder="Nueva categoría" required><button class="btn">Agregar</button></form>
<div class="wrap"><table><tr><th>Categoría</th><th>Estado</th><th></th></tr>
<?php foreach (q('SELECT * FROM categorias ORDER BY nombre')->fetchAll() as $c): ?><tr>
<td><form class="in" method="post"><?= csrf_field() ?><input type="hidden" name="accion" value="guardar"><input type="hidden" name="id" value="<?= $c['id'] ?>"><input name="nombre" value="<?= e($c['nombre']) ?>" required><button class="btn s">Renombrar</button></form></td>
<td><span class="tag <?= $c['activo'] ? 'activa' : 'pendiente' ?>"><?= $c['activo'] ? 'activa' : 'inactiva' ?></span></td>
<td><form class="in" method="post" data-confirm="¿Cambiar el estado de la categoría?"><?= csrf_field() ?><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn"><?= $c['activo'] ? 'Desactivar' : 'Activar' ?></button></form></td></tr><?php endforeach; ?></table></div>
<?php require_once __DIR__ . '/footer.php'; ?>
