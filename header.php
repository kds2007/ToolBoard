
<?php
require_once __DIR__ . '/auth.php';
require_login();

$cur = basename($_SERVER['SCRIPT_NAME']);

$grp = [
    'agregar_herramienta.php' => 'inventario.php',
    'editar_herramienta.php' => 'inventario.php',
    'registrar_prestamo.php' => 'prestamos.php'
];

$act = $grp[$cur] ?? $cur;

$back = [
    'dashboard.php' => 'index.php'
] + $grp;

$back = $back[$cur] ?? 'dashboard.php';

$menu = [
    'dashboard.php' => 'Inicio',
    'inventario.php' => 'Inventario',
    'categorias.php' => 'Categorías',
    'prestamos.php' => 'Préstamos',
    'devoluciones.php' => 'Devoluciones',
    'personal.php' => 'Personal',
    'mantenimiento.php' => 'Mantenimiento',
    'historial.php' => 'Historial',
    'reportes.php' => 'Reportes',
    'logout.php' => 'Cerrar sesión'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
</head>
<body class="app">

<aside class="side noprint" id="side">
    <a class="brand" href="index.php">Tool<span>Board</span></a>

    <nav>
        <?php foreach ($menu as $f => $n): ?>
            <?php if (!is_admin() && in_array($f, ['categorias.php', 'personal.php'])) continue; ?>

            <a href="<?= e($f) ?>"
               class="<?= $f === $act ? 'on' : '' ?>"
               <?= $f === $act ? 'aria-current="page"' : '' ?>>
                <?= e($n) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<main>
    <div class="top noprint">
        <a class="back" href="<?= e($back) ?>">
            <i>←</i> Volver
        </a>

        <span class="sp"></span>

        <span class="who">
            <?= e($_SESSION['nombre'] ?? '') ?> ·
            <?= is_admin() ? 'Administrador' : 'Usuario' ?>
        </span>

        <button class="btn g" data-theme-toggle type="button">
            ☀ Claro
        </button>

        <button
            class="burger"
            id="burger"
            type="button"
            aria-label="Abrir menú"
            aria-controls="side"
            aria-expanded="false">
            ☰
        </button>
    </div>

    <h2><?= e($title ?? '') ?></h2>

    <?php if ($f = flash()): ?>
        <div class="msg <?= e($f[1]) ?>">
            <?= e($f[0]) ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
