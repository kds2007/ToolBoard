<?php
// Credenciales: copie config.example.php a config.local.php y edítelo (no se sube a Git).
if (is_file(__DIR__ . '/config.local.php')) require_once __DIR__ . '/config.local.php';
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_NAME') || define('DB_NAME', 'toolboard');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');
