<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Repository\ReporteRepository;
use App\Service\CierreService;

$cierreSvc = new CierreService(pdo());
$reporte = new ReporteRepository(pdo());

if (is_post()) {
    try {
        $fechaPost = (string) ($_POST['fecha'] ?? '');
        if ($fechaPost === '') {
            throw new RuntimeException('Debe indicar la fecha del cierre.');
        }
        $res = $cierreSvc->cerrarDia(new DateTimeImmutable($fechaPost), (string) ($_POST['usuario'] ?? 'sistema'));
        flash('success', sprintf(
            'Cierre %s: %d recibos, monto $%s, multa $%s, interés $%s, total $%s.',
            $res['fecha'], $res['recibos'], money($res['total_monto']),
            money($res['total_multa']), money($res['total_inter']), money($res['total_general'])
        ));
    } catch (Throwable $e) {
        flash('danger', 'Error: ' . $e->getMessage());
    }
    redirect('cierre.php');
}

$fecha = (string) ($_GET['fecha'] ?? date('Y-m-d'));
$resumen = $reporte->resumenDia($fecha);
$cierres = $reporte->recaudacionPorCierre(date('Y-m-01', strtotime($fecha)), date('Y-m-d'));

$title = 'Cierre diario';
require __DIR__ . '/partials/header.php';
?>

<div class="row">
    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <h2 class="h5">Ejecutar cierre</h2>
            <form method="get" class="mb-3">
                <label class="form-label">Fecha</label>
                <div class="input-group">
                    <input type="date" class="form-control" name="fecha" value="<?= h($fecha) ?>">
                    <button class="btn btn-outline-secondary">Ver resumen</button>
                </div>
            </form>
            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between">Recibos<span><?= (int) $resumen['recibos'] ?></span></li>
                <li class="list-group-item d-flex justify-content-between">Monto<span>$<?= money($resumen['total_monto']) ?></span></li>
                <li class="list-group-item d-flex justify-content-between">Multa<span>$<?= money($resumen['total_multa']) ?></span></li>
                <li class="list-group-item d-flex justify-content-between">Interés<span>$<?= money($resumen['total_inter']) ?></span></li>
                <li class="list-group-item d-flex justify-content-between fw-bold">Total general<span>$<?= money($resumen['total_general']) ?></span></li>
            </ul>
            <form method="post" onsubmit="return confirm('¿Cerrar el día <?= h($fecha) ?>? Las facturas pasarán al historial.')">
                <input type="hidden" name="fecha" value="<?= h($fecha) ?>">
                <input type="hidden" name="usuario" value="cajero">
                <button class="btn btn-danger" <?= $resumen['recibos'] === 0 ? 'disabled' : '' ?>>Cerrar día <?= h($fecha) ?></button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-7">
        <h2 class="h5">Cierres del mes</h2>
        <div class="table-responsive">
            <table class="table table-sm table-striped bg-white">
                <thead><tr><th>Fecha</th><th class="text-end">Recibos</th><th class="text-end">Monto</th>
                    <th class="text-end">Multa</th><th class="text-end">Interés</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                <?php foreach ($cierres as $c): ?>
                    <tr>
                        <td><?= h($c['fecha_cierre']) ?></td>
                        <td class="text-end"><?= (int) $c['recibos'] ?></td>
                        <td class="text-end"><?= money($c['total_monto']) ?></td>
                        <td class="text-end"><?= money($c['total_multa']) ?></td>
                        <td class="text-end"><?= money($c['total_inter']) ?></td>
                        <td class="text-end fw-bold">$<?= money($c['total_general']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($cierres === []): ?>
                    <tr><td colspan="6" class="text-center text-muted">Sin cierres en el periodo.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
