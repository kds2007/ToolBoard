
<?php
require_once __DIR__ . '/auth.php';
require_login();
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'toggle') {
            $activo = q(
                'SELECT activo FROM categorias WHERE id=?',
                [$id]
            )->fetchColumn();

            if ($activo === false) {
                throw new Exception('Categoría inexistente');
            }

            if ($activo && q(
                "SELECT COUNT(*) FROM herramientas
                 WHERE categoria_id=? AND estado<>'baja'",
                [$id]
            )->fetchColumn() > 0) {
                throw new Exception(
                    'No se puede desactivar una categoría con herramientas asociadas.'
                );
            }

            q(
                'UPDATE categorias SET activo=1-activo WHERE id=?',
                [$id]
            );

            log_move('modificacion', "Cambio de estado de categoría #$id");

        } elseif ($accion === 'guardar') {
            if ($nombre === '') {
                throw new Exception('El nombre es obligatorio');
            }

            if ($id > 0) {
                q(
                    'UPDATE categorias SET nombre=? WHERE id=?',
                    [$nombre, $id]
                );

                log_move('modificacion', "Categoría: $nombre");
            } else {
                q(
                    'INSERT INTO categorias(nombre) VALUES(?)',
                    [$nombre]
                );

                log_move('alta', "Categoría: $nombre");
            }

        } else {
            throw new Exception('Acción inválida');
        }

        flash('Guardado');

    } catch (PDOException $x) {
        flash('Nombre de categoría duplicado', 'err');
    } catch (Exception $x) {
        fail($x);
    }

    redirect('categorias.php');
}

$title = 'Categorías';
require_once __DIR__ . '/header.php';
?>

<form class="in" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="accion" value="guardar">
    <input name="nombre" placeholder="Nueva categoría" required>
    <button class="btn">Agregar</button>
</form>

<div class="wrap">
<table>
<tr>
    <th>Categoría</th>
    <th>Estado</th>
    <th></th>
</tr>

<?php foreach (q('SELECT * FROM categorias ORDER BY nombre')->fetchAll() as $c): ?>
<tr>
<td>
    <form class="in" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <input name="nombre" value="<?= e($c['nombre']) ?>" required>
        <button class="btn s">Renombrar</button>
    </form>
</td>

<td>
    <span class="tag <?= $c['activo'] ? 'activa' : 'pendiente' ?>">
        <?= $c['activo'] ? 'activa' : 'inactiva' ?>
    </span>
</td>

<td>
    <form class="in" method="post"
          data-confirm="¿Cambiar el estado de la categoría?">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="toggle">
        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <button class="btn">
            <?= $c['activo'] ? 'Desactivar' : 'Activar' ?>
        </button>
    </form>
</td>
</tr>
<?php endforeach; ?>

</table>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
