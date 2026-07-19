<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? e($titulo) . ' · ' : '' ?>DECEM</title>
    <link rel="stylesheet" href="<?= asset('css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="auth-body">
    <main class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-brand">
                <span class="auth-logo">DECEM</span>
                <p class="auth-tagline">Sistema de Recaudación</p>
            </div>
            <div class="px-4 pb-4">
                <?php require ROOT_PATH . '/app/Views/partials/flash.php'; ?>
                <?= $contenido ?>
            </div>
        </div>
    </main>
    <script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
