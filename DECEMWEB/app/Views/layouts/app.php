<?php $u = Auth::usuario(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? e($titulo) . ' · ' : '' ?>DECEM</title>
    <link rel="stylesheet" href="<?= asset('css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="sidebar-logo">DECEM</span>
            <small>Recaudación</small>
        </div>
        <nav class="sidebar-nav">
            <?php
            $rutaActual = $GLOBALS['CURRENT_PATH'] ?? '';
            $items = [
                ['/dashboard', 'Panel'],
                ['/usuarios',  'Usuarios'],
            ];
            foreach ($items as [$ruta, $label]):
                $activo = str_starts_with($rutaActual, $ruta) ? ' active' : '';
            ?>
                <a class="nav-link<?= $activo ?>" href="<?= url($ruta) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <div class="content">
        <header class="topbar">
            <h1 class="topbar-title"><?= isset($titulo) ? e($titulo) : 'DECEM' ?></h1>
            <div class="topbar-user dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <?= e($u['nombre'] ?: $u['usuario']) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form method="POST" action="<?= url('/logout') ?>" class="px-2">
                            <?= Csrf::campo() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="main">
            <?php require ROOT_PATH . '/app/Views/partials/flash.php'; ?>
            <?= $contenido ?>
        </main>
    </div>
</div>
<script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
