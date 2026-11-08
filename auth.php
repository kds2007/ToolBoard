<?php
require_once __DIR__ . '/functions.php';
function require_login() { if (empty($_SESSION['uid'])) redirect('login.php'); }
function require_admin() { require_login(); if (!is_admin()) { flash('Acceso solo para administradores', 'err'); redirect('dashboard.php'); } }
