<?php
require_once __DIR__ . '/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($u) { header("Location: $u"); exit; }
function flash($m = null, $t = 'ok') {
    if ($m !== null) { $_SESSION['f'] = [$m, $t]; return; }
    $f = $_SESSION['f'] ?? null; unset($_SESSION['f']); return $f;
}
function csrf_field() { $_SESSION['t'] ??= bin2hex(random_bytes(16)); return '<input type="hidden" name="t" value="' . $_SESSION['t'] . '">'; }
function csrf_check() { if (!hash_equals($_SESSION['t'] ?? '', $_POST['t'] ?? '')) { http_response_code(400); exit('Token inválido'); } }
function q($sql, $p = []) { $s = db()->prepare($sql); $s->execute($p); return $s; }
function fail($x) { flash($x instanceof PDOException ? 'Error de base de datos' : $x->getMessage(), 'err'); }
function rollback() { if (db()->inTransaction()) db()->rollBack(); }
function log_move($tipo, $desc, $hid = null) {
    q('INSERT INTO historial(tipo,descripcion,herramienta_id,usuario_id) VALUES(?,?,?,?)', [$tipo, $desc, $hid, $_SESSION['uid'] ?? null]);
}
function tabla($r) {
    if (!$r) { echo '<p>Sin datos.</p>'; return; }
    echo '<div class="wrap"><table><tr>';
    foreach (array_keys($r[0]) as $k) echo '<th>' . e($k) . '</th>';
    echo '</tr>';
    foreach ($r as $x) { echo '<tr>'; foreach ($x as $v) echo '<td>' . e($v) . '</td>'; echo '</tr>'; }
    echo '</table></div>';
}
function is_admin() { return ($_SESSION['rol'] ?? '') === 'admin'; }
// Inicialización idempotente: agrega la columna "rol" si falta y crea admin/123 solo si no existe.
function ensure_admin(): void {
    static $done = false; if ($done) return; $done = true;
    try {
        if (!q("SHOW COLUMNS FROM usuarios LIKE 'rol'")->fetch()) db()->exec("ALTER TABLE usuarios ADD rol ENUM('admin','usuario') NOT NULL DEFAULT 'usuario'");
        if (!q("SELECT 1 FROM usuarios WHERE usuario='admin'")->fetch())
            q("INSERT INTO usuarios(usuario,nombre,password_hash,rol) VALUES('admin','Administrador',?,'admin')", [password_hash('123', PASSWORD_DEFAULT)]);
        else q("UPDATE usuarios SET rol='admin' WHERE usuario='admin' AND rol<>'admin'");
    } catch (Throwable $x) { /* la BD aún no está importada: el login mostrará el error */ }
}
