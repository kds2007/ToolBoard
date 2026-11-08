<?php
// API JSON para el sitio estático. Usa la misma base MySQL y las mismas reglas que las pantallas PHP.
require_once __DIR__ . '/config.php';
header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
header('Access-Control-Allow-Headers: Content-Type, X-TB-Token');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Vary: Origin'); header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }
ini_set('session.use_strict_mode', '1'); ini_set('session.use_cookies', '0');
$tok = $_SERVER['HTTP_X_TB_TOKEN'] ?? '';
if (preg_match('/^[A-Za-z0-9,-]{16,128}$/', $tok)) session_id($tok);
require_once __DIR__ . '/functions.php';
function out($d, $c = 200) { http_response_code($c); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }
function need_admin() { if (!is_admin()) throw new Exception('Solo administradores'); }
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$a = $in['a'] ?? '';
$s = fn($k) => trim((string)($in[$k] ?? ''));
try {
    if ($a === 'login') {
        ensure_admin();
        $u = q('SELECT * FROM usuarios WHERE usuario=? AND activo=1', [$s('usuario')])->fetch();
        if ($u && password_verify((string)($in['clave'] ?? ''), $u['password_hash'])) {
            if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) q('UPDATE usuarios SET password_hash=? WHERE id=?', [password_hash($in['clave'], PASSWORD_DEFAULT), $u['id']]);
            session_regenerate_id(true);
            $_SESSION['uid'] = $u['id']; $_SESSION['nombre'] = $u['nombre']; $_SESSION['rol'] = $u['rol'];
            out(['ok' => 1, 'token' => session_id(), 'user' => ['u' => $u['usuario'], 'nombre' => $u['nombre'], 'rol' => $u['rol']]]);
        }
        out(['error' => 'Usuario o contraseña incorrectos'], 401);
    }
    if ($a === 'register') {
        ensure_admin();
        $c1 = (string)($in['clave'] ?? '');
        if ($s('nombre') === '' || $s('apellido') === '' || $s('usuario') === '' || $c1 === '') throw new Exception('Completá todos los campos');
        if (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $s('usuario'))) throw new Exception('El usuario debe tener 3 a 40 caracteres (letras, números, . _ -)');
        if (strlen($c1) < 3) throw new Exception('La contraseña debe tener al menos 3 caracteres');
        if ($c1 !== (string)($in['clave2'] ?? '')) throw new Exception('Las contraseñas no coinciden');
        if (q('SELECT 1 FROM usuarios WHERE usuario=?', [$s('usuario')])->fetch()) throw new Exception('Ese nombre de usuario ya existe');
        q("INSERT INTO usuarios(usuario,nombre,password_hash,rol) VALUES(?,?,?,'usuario')", [$s('usuario'), mb_substr($s('nombre') . ' ' . $s('apellido'), 0, 80), password_hash($c1, PASSWORD_DEFAULT)]);
        out(['ok' => 1]);
    }
    if (empty($_SESSION['uid'])) out(['error' => 'Sesión expirada'], 401);
    switch ($a) {
    case 'logout': $_SESSION = []; session_destroy(); out(['ok' => 1]);
    case 'state':
        out(['data' => [
            'tools' => q("SELECT id,codigo,nombre,COALESCE(marca,'') marca,COALESCE(ubicacion,'') ubicacion,cantidad_total total,cantidad_disponible disp,estado FROM herramientas ORDER BY nombre")->fetchAll(),
            'staff' => q('SELECT id,nombre,apellido,documento doc,COALESCE(sector,\'\') sector,COALESCE(cargo,\'\') cargo,activo FROM personal ORDER BY apellido,nombre')->fetchAll(),
            'loans' => q("SELECT id,herramienta_id tool,personal_id staff,cantidad cant,cantidad_devuelta dev,fecha_prestamo desde,fecha_prevista prev,COALESCE(observaciones,'') obs,estado est FROM prestamos ORDER BY id")->fetchAll(),
            'maint' => q("SELECT id,herramienta_id tool,problema prob,responsable resp,fecha_inicio ini,COALESCE(fecha_fin,'') fin,estado est FROM mantenimientos ORDER BY id")->fetchAll(),
            'hist' => q("SELECT DATE_FORMAT(fecha,'%d/%m/%Y %H:%i') f,tipo,descripcion d FROM historial ORDER BY id DESC LIMIT 300")->fetchAll(),
        ]]);
    case 'tool':
        $id = (int)($in['id'] ?? 0); $t = filter_var($in['total'] ?? '', FILTER_VALIDATE_INT);
        if ($s('codigo') === '' || $s('nombre') === '' || $t === false || $t < 0) throw new Exception('Código, nombre y una cantidad válida son obligatorios');
        try {
            if ($id) {
                $h = q('SELECT * FROM herramientas WHERE id=?', [$id])->fetch(); if (!$h) throw new Exception('Herramienta inexistente');
                $d = $h['cantidad_disponible'] + $t - $h['cantidad_total']; if ($d < 0) throw new Exception('El total no puede ser menor que las unidades prestadas');
                q('UPDATE herramientas SET codigo=?,nombre=?,marca=?,ubicacion=?,cantidad_total=?,cantidad_disponible=? WHERE id=?', [$s('codigo'), $s('nombre'), $s('marca'), $s('ubicacion'), $t, $d, $id]);
                log_move('modificacion', 'Modificada: ' . $s('nombre'), $id);
            } else {
                q('INSERT INTO herramientas(codigo,nombre,marca,ubicacion,cantidad_total,cantidad_disponible) VALUES(?,?,?,?,?,?)', [$s('codigo'), $s('nombre'), $s('marca'), $s('ubicacion'), $t, $t]);
                log_move('alta', 'Alta: ' . $s('nombre'), db()->lastInsertId());
            }
        } catch (PDOException $x) { throw new Exception('Código duplicado o dato inválido'); }
        out(['ok' => 1, 'msg' => 'Herramienta guardada']);
    case 'baja':
        need_admin(); $id = (int)($in['id'] ?? 0);
        $h = q('SELECT nombre FROM herramientas WHERE id=?', [$id])->fetch(); if (!$h) throw new Exception('Herramienta inexistente');
        if (q("SELECT COUNT(*) FROM prestamos WHERE herramienta_id=? AND estado<>'devuelto'", [$id])->fetchColumn() > 0) throw new Exception('Tiene préstamos pendientes; registre la devolución primero');
        q("UPDATE herramientas SET estado='baja' WHERE id=?", [$id]); log_move('baja', 'Baja: ' . $h['nombre'], $id);
        out(['ok' => 1, 'msg' => 'Herramienta dada de baja']);
    case 'staff':
        need_admin();
        if ($s('nombre') === '' || $s('apellido') === '' || $s('doc') === '') throw new Exception('Nombre, apellido y documento son obligatorios');
        try { q('INSERT INTO personal(nombre,apellido,documento,sector,cargo) VALUES(?,?,?,?,?)', [$s('nombre'), $s('apellido'), $s('doc'), $s('sector'), $s('cargo')]); }
        catch (PDOException $x) { throw new Exception('Documento duplicado'); }
        log_move('alta', 'Alta de personal: ' . $s('nombre') . ' ' . $s('apellido')); out(['ok' => 1, 'msg' => 'Guardado']);
    case 'staff_toggle':
        need_admin(); q('UPDATE personal SET activo=1-activo WHERE id=?', [(int)($in['id'] ?? 0)]); log_move('modificacion', 'Cambio de estado de personal #' . (int)($in['id'] ?? 0)); out(['ok' => 1, 'msg' => 'Guardado']);
    case 'loan':
        $h = (int)($in['tool'] ?? 0); $w = (int)($in['staff'] ?? 0); $n = (int)($in['cant'] ?? 0); $f = $s('prev');
        if ($n < 1) throw new Exception('Cantidad inválida');
        if (!preg_match('/^\d{4}-\d\d-\d\d$/', $f) || $f < date('Y-m-d')) throw new Exception('Fecha prevista inválida');
        db()->beginTransaction();
        $t = q('SELECT * FROM herramientas WHERE id=? FOR UPDATE', [$h])->fetch();
        if (!$t || $t['estado'] !== 'activa') throw new Exception('La herramienta no está disponible para préstamo');
        if ($t['cantidad_disponible'] < $n) throw new Exception('Stock insuficiente');
        if (!q('SELECT 1 FROM personal WHERE id=? AND activo=1', [$w])->fetch()) throw new Exception('Trabajador inexistente o inactivo');
        q('UPDATE herramientas SET cantidad_disponible=cantidad_disponible-? WHERE id=?', [$n, $h]);
        q('INSERT INTO prestamos(herramienta_id,personal_id,cantidad,fecha_prestamo,fecha_prevista,observaciones) VALUES(?,?,?,CURDATE(),?,?)', [$h, $w, $n, $f, $s('obs')]);
        log_move('prestamo', "Préstamo de $n x {$t['nombre']}", $h); db()->commit(); out(['ok' => 1, 'msg' => 'Préstamo registrado']);
    case 'ret':
        $id = (int)($in['id'] ?? 0); $n = (int)($in['cant'] ?? 0); $d = (int)($in['dan'] ?? 0);
        db()->beginTransaction();
        $p = q('SELECT p.*,h.nombre FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id WHERE p.id=? FOR UPDATE', [$id])->fetch();
        if (!$p) throw new Exception('Préstamo inexistente');
        if ($n < 1 || $n > $p['cantidad'] - $p['cantidad_devuelta']) throw new Exception('Cantidad fuera del rango pendiente');
        if ($d < 0 || $d > $n) throw new Exception('Unidades dañadas inválidas');
        $dev = $p['cantidad_devuelta'] + $n;
        q('UPDATE prestamos SET cantidad_devuelta=?,estado=? WHERE id=?', [$dev, $dev >= $p['cantidad'] ? 'devuelto' : 'parcial', $id]);
        q('UPDATE herramientas SET cantidad_disponible=cantidad_disponible+? WHERE id=?', [$n - $d, $p['herramienta_id']]);
        q('INSERT INTO devoluciones(prestamo_id,cantidad,danada,observaciones) VALUES(?,?,?,?)', [$id, $n, $d, $s('obs')]);
        log_move('devolucion', "Devolución de $n x {$p['nombre']}" . ($d ? " ($d dañadas)" : ''), $p['herramienta_id']);
        if ($d > 0) {
            q('INSERT INTO mantenimientos(herramienta_id,problema,responsable,fecha_inicio) VALUES(?,?,?,CURDATE())', [$p['herramienta_id'], "Devuelta dañada ($d unid.) - préstamo #$id", $_SESSION['nombre'] ?? '']);
            log_move('incidencia', "Incidencia: $d unidad(es) dañada(s) de {$p['nombre']}", $p['herramienta_id']);
        }
        db()->commit(); out(['ok' => 1, 'msg' => 'Devolución registrada']);
    case 'maint':
        $h = (int)($in['tool'] ?? 0);
        if ($s('prob') === '' || $s('resp') === '') throw new Exception('Problema y responsable son obligatorios');
        db()->beginTransaction();
        if (!q("SELECT 1 FROM herramientas WHERE id=? AND estado<>'baja' FOR UPDATE", [$h])->fetch()) throw new Exception('Herramienta inválida');
        q('INSERT INTO mantenimientos(herramienta_id,problema,responsable,fecha_inicio) VALUES(?,?,?,CURDATE())', [$h, $s('prob'), $s('resp')]);
        q("UPDATE herramientas SET estado='mantenimiento' WHERE id=?", [$h]); log_move('mantenimiento', 'Mantenimiento iniciado: ' . $s('prob'), $h);
        db()->commit(); out(['ok' => 1, 'msg' => 'Guardado']);
    case 'mfin':
        db()->beginTransaction();
        $m = q("SELECT * FROM mantenimientos WHERE id=? AND estado='en_proceso' FOR UPDATE", [(int)($in['id'] ?? 0)])->fetch();
        if (!$m) throw new Exception('Mantenimiento inexistente o ya finalizado');
        q("UPDATE mantenimientos SET estado='finalizado',fecha_fin=CURDATE() WHERE id=?", [$m['id']]);
        if (!q("SELECT COUNT(*) FROM mantenimientos WHERE herramienta_id=? AND estado='en_proceso'", [$m['herramienta_id']])->fetchColumn())
            q("UPDATE herramientas SET estado='activa' WHERE id=? AND estado='mantenimiento'", [$m['herramienta_id']]);
        log_move('mantenimiento', "Mantenimiento #{$m['id']} finalizado", $m['herramienta_id']); db()->commit(); out(['ok' => 1, 'msg' => 'Guardado']);
    }
    out(['error' => 'Acción desconocida'], 400);
} catch (PDOException $x) { rollback(); out(['error' => 'Error de base de datos'], 500); }
catch (Exception $x) { rollback(); out(['error' => $x->getMessage()], 400); }
