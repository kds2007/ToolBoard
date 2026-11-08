<?php
require_once __DIR__ . '/auth.php'; require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('devoluciones.php');
csrf_check();
$id = (int)($_POST['prestamo_id'] ?? 0); $n = (int)($_POST['cantidad'] ?? 0); $d = (int)($_POST['danada'] ?? 0); $obs = trim($_POST['observaciones'] ?? '');
try {
    db()->beginTransaction();
    $p = q('SELECT p.*,h.nombre FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id WHERE p.id=? FOR UPDATE', [$id])->fetch();
    if (!$p) throw new Exception('Préstamo inexistente');
    $pend = $p['cantidad'] - $p['cantidad_devuelta'];
    if ($n < 1 || $n > $pend) throw new Exception('Cantidad fuera del rango pendiente');
    if ($d < 0 || $d > $n) throw new Exception('Unidades dañadas inválidas');
    $dev = $p['cantidad_devuelta'] + $n;
    q('UPDATE prestamos SET cantidad_devuelta=?,estado=? WHERE id=?', [$dev, $dev >= $p['cantidad'] ? 'devuelto' : 'parcial', $id]);
    q('UPDATE herramientas SET cantidad_disponible=cantidad_disponible+? WHERE id=?', [$n - $d, $p['herramienta_id']]);
    q('INSERT INTO devoluciones(prestamo_id,cantidad,danada,observaciones) VALUES(?,?,?,?)', [$id, $n, $d, $obs]);
    log_move('devolucion', "Devolución de $n x {$p['nombre']}" . ($d ? " ($d dañadas)" : ''), $p['herramienta_id']);
    if ($d > 0) {
        q("INSERT INTO mantenimientos(herramienta_id,problema,responsable,fecha_inicio,observaciones) VALUES(?,?,?,CURDATE(),?)",
          [$p['herramienta_id'], "Devuelta dañada ($d unid.) - préstamo #$id", $_SESSION['nombre'] ?? '', $obs]);
        log_move('incidencia', "Incidencia: $d unidad(es) dañada(s) de {$p['nombre']}", $p['herramienta_id']);
    }
    db()->commit(); flash('Devolución registrada');
} catch (Exception $x) { rollback(); fail($x); }
redirect('devoluciones.php');
