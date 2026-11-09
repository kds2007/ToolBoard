<?php
require_once __DIR__ . '/auth.php'; 
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('devoluciones.php');
}

csrf_check();

$id  = (int)($_POST['prestamo_id'] ?? 0); 
$n   = (int)($_POST['cantidad'] ?? 0); 
$d   = (int)($_POST['danada'] ?? 0); 
$obs = trim($_POST['observaciones'] ?? '');

try {
    db()->beginTransaction();
    
    // Obtener datos del préstamo y bloquear la fila para evitar concurrencia
    $p = q('SELECT p.*, h.nombre FROM prestamos p JOIN herramientas h ON h.id = p.herramienta_id WHERE p.id = ? FOR UPDATE', [$id])->fetch();
    if (!$p) {
        throw new Exception('Préstamo inexistente');
    }

    $pend = $p['cantidad'] - $p['cantidad_devuelta'];
    
    // Validaciones de rangos
    if ($n < 1 || $n > $pend) {
        throw new Exception('Cantidad fuera del rango pendiente');
    }
    if ($d < 0 || $d > $n) {
        throw new Exception('Unidades dañadas inválidas');
    }

    $dev = $p['cantidad_devuelta'] + $n;
    $nuevo_estado = ($dev >= $p['cantidad']) ? 'devuelto' : 'parcial';

    // 1. Actualizar el estado y la cantidad devuelta del préstamo
    q('UPDATE prestamos SET cantidad_devuelta = ?, estado = ? WHERE id = ?', [$dev, $nuevo_estado, $id]);

    // 2. Actualizar el stock disponible (solo se reincorporan las unidades sanas: $n - $d)
    q('UPDATE herramientas SET cantidad_disponible = cantidad_disponible + ? WHERE id = ?', [$n - $d, $p['herramienta_id']]);

    // 3. Registrar la devolución en historial
    q('INSERT INTO devoluciones (prestamo_id, cantidad, danada, observaciones) VALUES (?, ?, ?, ?)', [$id, $n, $d, $obs]);

    log_move('devolucion', "Devolución de $n x {$p['nombre']}" . ($d ? " ($d dañadas)" : ''), $p['herramienta_id']);

    // 4. Si hay unidades dañadas, abrir mantenimiento y registrar incidencia
    if ($d > 0) {
        q("INSERT INTO mantenimientos (herramienta_id, problema, responsable, fecha_inicio, observaciones) VALUES (?, ?, ?, CURDATE(), ?)",
          [$p['herramienta_id'], "Devuelta dañada ($d unid.) - préstamo #$id", $_SESSION['nombre'] ?? '', $obs]);
          
        log_move('incidencia', "Incidencia: $d unidad(es) dañada(s) de {$p['nombre']}", $p['herramienta_id']);
    }

    db()->commit(); 
    flash('Devolución registrada correctamente');

} catch (Exception $x) { 
    if (db()->inTransaction()) {
        db()->rollBack();
    }
    fail($x); 
}

redirect('devoluciones.php');