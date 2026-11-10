
<?php

require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function redirect($u) {
    $u = (string)$u;

    if ($u === '' || preg_match('/[\r\n]/', $u)) {
        $u = 'dashboard.php';
    }

    header('Location: ' . $u);
    exit;
}

function flash($m = null, $t = 'ok') {
    if ($m !== null) {
        $_SESSION['f'] = [$m, $t];
        return;
    }

    $f = $_SESSION['f'] ?? null;
    unset($_SESSION['f']);

    return $f;
}

function csrf_field() {
    if (empty($_SESSION['t'])) {
        $_SESSION['t'] = bin2hex(random_bytes(32));
    }

    return '<input type="hidden" name="t" value="' . e($_SESSION['t']) . '">';
}

function csrf_check() {
    $sessionToken = $_SESSION['t'] ?? '';
    $postToken = $_POST['t'] ?? '';

    if (
        !is_string($sessionToken) ||
        !is_string($postToken) ||
        $sessionToken === '' ||
        $postToken === '' ||
        !hash_equals($sessionToken, $postToken)
    ) {
        http_response_code(400);
        exit('Token inválido');
    }
}

function q($sql, $p = []) {
    $s = db()->prepare($sql);
    $s->execute($p);

    return $s;
}

function fail($x) {
    if ($x instanceof PDOException) {
        error_log($x->getMessage());
        flash('Error de base de datos', 'err');
    } elseif ($x instanceof Throwable) {
        error_log($x->getMessage());
        flash('Ocurrió un error inesperado', 'err');
    } else {
        flash('Ocurrió un error inesperado', 'err');
    }
}

function rollback() {
    if (db()->inTransaction()) {
        db()->rollBack();
    }
}

function log_move($tipo, $desc, $hid = null) {
    q(
        'INSERT INTO historial(tipo,descripcion,herramienta_id,usuario_id) VALUES(?,?,?,?)',
        [$tipo, $desc, $hid, $_SESSION['uid'] ?? null]
    );
}

function tabla($r) {
    if (!$r) {
        echo '<p>Sin datos.</p>';
        return;
    }

    echo '<div class="wrap"><table><tr>';

    foreach (array_keys($r[0]) as $k) {
        echo '<th>' . e($k) . '</th>';
    }

    echo '</tr>';

    foreach ($r as $x) {
        echo '<tr>';

        foreach ($x as $v) {
            echo '<td>' . e($v) . '</td>';
        }

        echo '</tr>';
    }

    echo '</table></div>';
}

function is_admin() {
    return ($_SESSION['rol'] ?? '') === 'admin';
}

function ensure_admin(): void {
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;

    try {
        q("SELECT id FROM usuarios WHERE usuario = 'admin' LIMIT 1")->fetch();
    } catch (Throwable $x) {
        error_log($x->getMessage());
    }
}
