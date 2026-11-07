<?php
require_once __DIR__ . '/auth.php'; require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        db()->beginTransaction();
        if ($_POST['accion'] === 'fin') {
            $m = q('SELECT * FROM mantenimientos WHERE id=? AND estado="en_proceso" FOR UPDATE', [(int)$_POST['id']])->fetch();
            if (!$m) throw new Exception('Mantenimiento inexistente o ya finalizado');
            q('UPDATE mantenimientos SET estado="finalizado",fecha_fin=CURDATE() WHERE id=?', [$m['id']]);
            if (!q('SELECT COUNT(*) FROM mantenimientos WHERE herramienta_id=? AND estado="en_proceso"', [$m['herramienta_id']])->fetchColumn())
                q('UPDATE herramientas SET estado="activa" WHERE id=? AND estado="mantenimiento"', [$m['herramienta_id']]);
            log_move('mantenimiento', "Mantenimiento #{$m['id']} finalizado", $m['herramienta_id']);
        } else {
            $h = (int)$_POST['herramienta_id']; $pr = trim($_POST['problema'] ?? ''); $rs = trim($_POST['responsable'] ?? '');
            if ($pr === '' || $rs === '') throw new Exception('Problema y responsable son obligatorios');
            if (!q("SELECT 1 FROM herramientas WHERE id=? AND estado<>'baja' FOR UPDATE", [$h])->fetch()) throw new Exception('Herramienta inválida');
            q('INSERT INTO mantenimientos(herramienta_id,problema,responsable,fecha_inicio,observaciones) VALUES(?,?,?,CURDATE(),?)', [$h, $pr, $rs, trim($_POST['observaciones'] ?? '')]);
            q('UPDATE herramientas SET estado="mantenimiento" WHERE id=?', [$h]);
            log_move('mantenimiento', "Mantenimiento iniciado: $pr", $h);
        }
        db()->commit(); flash('Guardado');
    } catch (Exception $x) { rollback(); fail($x); }
    redirect('mantenimiento.php');
}
$title = 'Mantenimiento'; require_once __DIR__ . '/header.php';
?>
<form class="f" method="post"><?= csrf_field() ?><input type="hidden" name="accion" value="nuevo">
<label>Herramienta <select name="herramienta_id"><?php foreach (q("SELECT id,nombre FROM herramientas WHERE estado<>'baja' ORDER BY nombre")->fetchAll() as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['nombre']) ?></option><?php endforeach; ?></select></label>
<label>Problema <input name="problema" required></label><label>Responsable <input name="responsable" required value="<?= e($_SESSION['nombre'] ?? '') ?>"></label>
<label>Observaciones <textarea name="observaciones"></textarea></label><button class="btn">Registrar mantenimiento</button></form>
<div class="wrap"><table><tr><th>#</th><th>Herramienta</th><th>Problema</th><th>Responsable</th><th>Inicio</th><th>Fin</th><th>Estado</th><th></th></tr>
<?php foreach (q('SELECT m.*,h.nombre FROM mantenimientos m JOIN herramientas h ON h.id=m.herramienta_id ORDER BY m.id DESC LIMIT 100')->fetchAll() as $m): ?><tr>
<td><?= $m['id'] ?></td><td><?= e($m['nombre']) ?></td><td><?= e($m['problema']) ?></td><td><?= e($m['responsable']) ?></td><td><?= e($m['fecha_inicio']) ?></td><td><?= e($m['fecha_fin']) ?></td><td><span class="tag <?= e($m['estado']) ?>"><?= e($m['estado']) ?></span></td>
<td><?php if ($m['estado'] === 'en_proceso'): ?><form class="in" method="post" data-confirm="¿Finalizar mantenimiento?"><?= csrf_field() ?><input type="hidden" name="accion" value="fin"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="btn s">Finalizar</button></form><?php endif; ?></td></tr><?php endforeach; ?></table></div>
<?php require_once __DIR__ . '/footer.php'; ?>
