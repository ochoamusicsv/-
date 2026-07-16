<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Repository\ReporteRepository;

$hoy = new DateTimeImmutable();
$stats = ['contr' => 0, 'propi' => 0, 'fdiar' => 0];
$resumen = ['recibos' => 0, 'total_general' => 0.0];
$dbError = null;

try {
    $pdo = pdo();
    $stats['contr'] = (int) $pdo->query('SELECT COUNT(*) FROM contr')->fetchColumn();
    $stats['propi'] = (int) $pdo->query('SELECT COUNT(*) FROM propi')->fetchColumn();
    $stats['fdiar'] = (int) $pdo->query('SELECT COUNT(*) FROM fdiar')->fetchColumn();
    $resumen = (new ReporteRepository($pdo))->resumenDia($hoy->format('Y-m-d'));
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$title = 'Panel principal';
require __DIR__ . '/partials/header.php';
?>

<?php if ($dbError !== null): ?>
    <div class="alert alert-warning">
        No se pudo conectar a la base de datos: <code><?= h($dbError) ?></code><br>
        Verifica la conexión y ejecuta los scripts de <code>db/</code>. Configura credenciales con
        <code>DB_HOST, DB_NAME, DB_USER, DB_PASS</code>.
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card text-bg-primary"><div class="card-body">
            <div class="h2 mb-0"><?= h((string) $stats['contr']) ?></div>Contribuyentes
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info"><div class="card-body">
            <div class="h2 mb-0"><?= h((string) $stats['propi']) ?></div>Propiedades/Empresas
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning"><div class="card-body">
            <div class="h2 mb-0"><?= h((string) $resumen['recibos']) ?></div>Recibos del día (sin cerrar)
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success"><div class="card-body">
            <div class="h2 mb-0">$<?= money($resumen['total_general']) ?></div>Recaudado hoy
        </div></div>
    </div>
</div>

<div class="card mt-4"><div class="card-body">
    <h2 class="h5">Flujo de trabajo</h2>
    <ol class="mb-0">
        <li>Registrar <a href="contribuyentes.php">Contribuyentes</a>.</li>
        <li>Registrar <a href="propiedades.php">Propiedad/Empresa</a> y su tributo (el monto se calcula por tarifa fija o variable según el activo).</li>
        <li>Aplicar cobros en <a href="facturacion.php">Facturación diaria</a>.</li>
        <li>Ejecutar el <a href="cierre.php">Cierre diario</a> para archivar al historial.</li>
        <li>Consultar <a href="reportes.php">Reportería</a> de mora y recaudación.</li>
    </ol>
</div></div>

<?php require __DIR__ . '/partials/footer.php'; ?>
