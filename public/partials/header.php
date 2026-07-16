<?php
/** @var string $title */
$title = $title ?? 'Sistema de Recaudación';
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');
$nav = [
    'index.php'         => 'Inicio',
    'contribuyentes.php'=> 'Contribuyentes',
    'propiedades.php'   => 'Propiedad/Empresa',
    'facturacion.php'   => 'Facturación diaria',
    'cierre.php'        => 'Cierre diario',
    'reportes.php'      => 'Reportería',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Recaudación Municipal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav">
                <?php foreach ($nav as $file => $label): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === $file ? 'active fw-bold' : '' ?>" href="<?= h($file) ?>"><?= h($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container-fluid py-4">
    <?php foreach (take_flash() as $f): ?>
        <div class="alert alert-<?= h($f['type']) ?> alert-dismissible fade show" role="alert">
            <?= h($f['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
    <h1 class="h3 mb-4"><?= h($title) ?></h1>
