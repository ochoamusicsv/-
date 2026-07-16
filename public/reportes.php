<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Repository\ReporteRepository;

$reporte = new ReporteRepository(pdo());

$anio = (int) ($_GET['anio'] ?? date('Y'));
$hastaMes = (int) ($_GET['mes'] ?? date('n'));
$desde = (string) ($_GET['desde'] ?? date('Y-01-01'));
$hasta = (string) ($_GET['hasta'] ?? date('Y-m-d'));

$mora = $reporte->clientesEnMora($anio, $hastaMes);
$recaudacion = $reporte->recaudacionPorCierre($desde, $hasta);
$totalRecaudado = array_sum(array_map(static fn ($r) => (float) $r['total_general'], $recaudacion));
$totalDeuda = array_sum(array_map(static fn ($r) => (float) $r['deuda_estimada'], $mora));

$title = 'Reportería';
require __DIR__ . '/partials/header.php';
?>

<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Clientes en mora</h2>
    <form class="row g-2 mb-3">
        <div class="col-auto"><label class="form-label">Año</label>
            <input type="number" class="form-control" name="anio" value="<?= $anio ?>"></div>
        <div class="col-auto"><label class="form-label">Hasta el mes</label>
            <input type="number" min="1" max="12" class="form-control" name="mes" value="<?= $hastaMes ?>"></div>
        <div class="col-auto align-self-end"><button class="btn btn-primary">Filtrar mora</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-sm table-striped bg-white">
            <thead><tr><th>Cuenta</th><th>Nombre</th><th>Teléfono</th><th>Cód.Cat.</th><th>Tributo</th>
                <th class="text-end">Monto</th><th class="text-end">Meses pend.</th><th class="text-end">Deuda estimada</th></tr></thead>
            <tbody>
            <?php foreach ($mora as $m): ?>
                <tr>
                    <td><?= h($m['cuent']) ?></td>
                    <td><?= h(trim(($m['nombr'] ?? '') . ' ' . ($m['apell'] ?? ''))) ?></td>
                    <td><?= h($m['telef']) ?></td>
                    <td><?= h($m['codca']) ?></td>
                    <td><?= h($m['tributo_desc']) ?></td>
                    <td class="text-end"><?= money($m['monto']) ?></td>
                    <td class="text-end"><?= (int) $m['meses_pendientes'] ?></td>
                    <td class="text-end fw-bold text-danger">$<?= money($m['deuda_estimada']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($mora === []): ?>
                <tr><td colspan="8" class="text-center text-muted">Sin clientes en mora.</td></tr>
            <?php else: ?>
                <tr class="fw-bold"><td colspan="7" class="text-end">Deuda total estimada</td><td class="text-end text-danger">$<?= money($totalDeuda) ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div></div>

<div class="card"><div class="card-body">
    <h2 class="h5">Recaudación por cierre</h2>
    <form class="row g-2 mb-3">
        <div class="col-auto"><label class="form-label">Desde</label>
            <input type="date" class="form-control" name="desde" value="<?= h($desde) ?>"></div>
        <div class="col-auto"><label class="form-label">Hasta</label>
            <input type="date" class="form-control" name="hasta" value="<?= h($hasta) ?>"></div>
        <div class="col-auto align-self-end"><button class="btn btn-primary">Filtrar recaudación</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-sm table-striped bg-white">
            <thead><tr><th>Fecha cierre</th><th class="text-end">Recibos</th><th class="text-end">Monto</th>
                <th class="text-end">Multa</th><th class="text-end">Interés</th><th class="text-end">Total</th></tr></thead>
            <tbody>
            <?php foreach ($recaudacion as $r): ?>
                <tr>
                    <td><?= h($r['fecha_cierre']) ?></td>
                    <td class="text-end"><?= (int) $r['recibos'] ?></td>
                    <td class="text-end"><?= money($r['total_monto']) ?></td>
                    <td class="text-end"><?= money($r['total_multa']) ?></td>
                    <td class="text-end"><?= money($r['total_inter']) ?></td>
                    <td class="text-end fw-bold text-success">$<?= money($r['total_general']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($recaudacion === []): ?>
                <tr><td colspan="6" class="text-center text-muted">Sin recaudación en el periodo.</td></tr>
            <?php else: ?>
                <tr class="fw-bold"><td colspan="5" class="text-end">Total recaudado</td><td class="text-end text-success">$<?= money($totalRecaudado) ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div></div>

<?php require __DIR__ . '/partials/footer.php'; ?>
