<?php
require_once __DIR__ . '/auth.php'; require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $h = (int)($_POST['herramienta_id'] ?? 0); $w = (int)($_POST['personal_id'] ?? 0); $n = (int)($_POST['cantidad'] ?? 0);
    $f = $_POST['fecha_prevista'] ?? ''; $obs = trim($_POST['observaciones'] ?? '');
    try {
        if ($n < 1) throw new Exception('Cantidad inválida');
        if (!preg_match('/^\d{4}-\d\d-\d\d$/', $f) || $f < date('Y-m-d')) throw new Exception('Fecha prevista inválida');
        db()->beginTransaction();
        $t = q('SELECT * FROM herramientas WHERE id=? FOR UPDATE', [$h])->fetch();
        if (!$t || $t['estado'] !== 'activa') throw new Exception('La herramienta no está disponible para préstamo');
        if ($t['cantidad_disponible'] < $n) throw new Exception('Stock insuficiente');
        if (!q('SELECT 1 FROM personal WHERE id=? AND activo=1', [$w])->fetch()) throw new Exception('Trabajador inexistente o inactivo');
        q('UPDATE herramientas SET cantidad_disponible=cantidad_disponible-? WHERE id=?', [$n, $h]);
        q('INSERT INTO prestamos(herramienta_id,personal_id,cantidad,fecha_prestamo,fecha_prevista,observaciones) VALUES(?,?,?,CURDATE(),?,?)', [$h, $w, $n, $f, $obs]);
        log_move('prestamo', "Préstamo de $n x {$t['nombre']}", $h);
        db()->commit(); flash('Préstamo registrado'); redirect('prestamos.php');
    } catch (Exception $x) { rollback(); fail($x); }
}
$title = 'Registrar préstamo'; require_once __DIR__ . '/header.php';
?>
<form class="f" method="post"><?= csrf_field() ?>
<label>Herramienta <select name="herramienta_id" required><?php foreach (q("SELECT id,nombre,cantidad_disponible d FROM herramientas WHERE estado='activa' AND cantidad_disponible>0 ORDER BY nombre")->fetchAll() as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['nombre']) ?> (disp. <?= $r['d'] ?>)</option><?php endforeach; ?></select></label>
<label>Trabajador <select name="personal_id" required><?php foreach (q('SELECT id,nombre,apellido FROM personal WHERE activo=1 ORDER BY apellido')->fetchAll() as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['nombre'] . ' ' . $r['apellido']) ?></option><?php endforeach; ?></select></label>
<label>Cantidad <input type="number" name="cantidad" min="1" value="1" required></label>
<label>Fecha prevista de devolución <input type="date" name="fecha_prevista" min="<?= date('Y-m-d') ?>" required></label>
<label>Observaciones <textarea name="observaciones"></textarea></label>
<button class="btn">Confirmar préstamo</button></form>
<?php require_once __DIR__ . '/footer.php'; ?>
