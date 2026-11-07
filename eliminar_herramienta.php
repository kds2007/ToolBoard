<?php
require_once __DIR__ . '/auth.php'; require_login(); require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('inventario.php');
csrf_check();
$id = (int)($_POST['id'] ?? 0);
try {
    $h = q('SELECT nombre FROM herramientas WHERE id=?', [$id])->fetch();
    if (!$h) throw new Exception('Herramienta inexistente');
    if (q("SELECT COUNT(*) FROM prestamos WHERE herramienta_id=? AND estado<>'devuelto'", [$id])->fetchColumn() > 0)
        throw new Exception('Tiene préstamos pendientes; registre la devolución primero');
    q("UPDATE herramientas SET estado='baja' WHERE id=?", [$id]);
    log_move('baja', "Baja: {$h['nombre']}", $id);
    flash('Herramienta dada de baja');
} catch (Exception $x) { fail($x); }
redirect('inventario.php');
