
<?php

require_once __DIR__ . '/functions.php';

function require_login() {
    if (empty($_SESSION['uid'])) {
        redirect('login.php');
    }
}

function require_admin() {
    require_login();

    if (!is_admin()) {
        flash('Acceso solo para administradores', 'err');
        redirect('dashboard.php');
    }
}

function login_user($user) {
    if (empty($user) || empty($user['id']) || empty($user['rol'])) {
        return false;
    }

    session_regenerate_id(true);

    $_SESSION['uid'] = (int)$user['id'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['usuario'] = $user['usuario'];

    return true;
}

function logout_user() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    redirect('login.php');
}
