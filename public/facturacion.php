<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Billing\PenaltyCalculator;
use App\Repository\FacturaRepository;
use App\Repository\ParamRepository;
use App\Repository\PropiedadRepository;
use App\Repository\TarifaRepository;
use App\Repository\TributoRepository;
use App\Service\FacturaService;
use App\Service\MontoService;

$propRepo = new PropiedadRepository(pdo());
$facturaRepo = new FacturaRepository(pdo());
$montoSvc = new MontoService(new TributoRepository(pdo()), new TarifaRepository(pdo()));
$penalty = new PenaltyCalculator();
$facturaSvc = new FacturaService($facturaRepo, new ParamRepository(pdo()), $penalty);

if (is_post()) {
    try {
        $prop = $propRepo->find((int) $_POST['propiedad_id']);
        if ($prop === null) {
            throw new RuntimeException('Propiedad no encontrada.');
        }
        $montoMensual = $montoSvc->montoMensual($prop['tribu'], (float) $prop['activ']);
        $res = $facturaSvc->aplicarCobro(
            $prop['cuent'], $prop['codca'], $prop['tipoc'], $prop['tribu'],
            (string) $_POST['periodo'], $montoMensual,
            new DateTimeImmutable((string) $_POST['fecha_pago']),
            (string) ($_POST['usuario'] ?? 'sistema')
        );
        flash('success', sprintf(
            'Recibo %s aplicado. Monto $%s + Multa $%s + Interés $%s = Total $%s (%d días de mora).',
            $res['corre'], money($res['monto']), money($res['multa']),
            money($res['interes']), money($res['total']), $res['dias_mora']
        ));
    } catch (Throwable $e) {
        flash('danger', 'Error: ' . $e->getMessage());
    }
    redirect('facturacion.php');
}

$propiedades = $propRepo->all();
$hoy = date('Y-m-d');
$pendientes = $facturaRepo->pendientesDia($hoy);

$title = 'Facturación diaria';
require __DIR__ . '/partials/header.php';
?>

<div class="row">
    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <h2 class="h5">Aplicar cobro</h2>
            <form method="post" class="row g-2">
                <div class="col-12"><label class="form-label">Propiedad/Empresa*</label>
                    <select class="form-select" name="propiedad_id" required>
                        <option value="">Seleccione…</option>
                        <?php foreach ($propiedades as $p): ?>
                            <option value="<?= (int) $p['id'] ?>">
                                <?= h($p['codca'] . ' · ' . ($p['nombr'] ?? '') . ' · ' . $p['tributo_desc'] . ' · $' . money($p['monto'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-6"><label class="form-label">Periodo (YYYY-MM)*</label>
                    <input class="form-control" name="periodo" required pattern="\d{4}-\d{2}" value="<?= h(date('Y-m')) ?>"></div>
                <div class="col-6"><label class="form-label">Fecha de pago*</label>
                    <input type="date" class="form-control" name="fecha_pago" required value="<?= h($hoy) ?>"></div>
                <div class="col-12"><label class="form-label">Usuario</label>
                    <input class="form-control" name="usuario" value="cajero"></div>
                <div class="col-12 mt-3"><button class="btn btn-primary">Aplicar cobro</button></div>
            </form>
            <p class="small text-muted mt-2 mb-0">La multa y el interés por mora se calculan automáticamente según la fecha de pago.</p>
        </div></div>
    </div>

    <div class="col-lg-7">
        <h2 class="h5">Facturas del día <?= h($hoy) ?> (pendientes de cierre)</h2>
        <div class="table-responsive">
            <table class="table table-sm table-striped bg-white">
                <thead><tr><th>Recibo</th><th>Cuenta</th><th>Cód.Cat.</th><th>Trib.</th><th>Periodo</th>
                    <th class="text-end">Monto</th><th class="text-end">Multa</th><th class="text-end">Interés</th></tr></thead>
                <tbody>
                <?php $tot = 0.0; foreach ($pendientes as $f): $tot += (float) $f['monto'] + (float) $f['multa'] + (float) $f['inter']; ?>
                    <tr>
                        <td><?= h($f['corre']) ?></td><td><?= h($f['cuent']) ?></td><td><?= h($f['codca']) ?></td>
                        <td><?= h($f['tribu']) ?></td><td><?= h($f['perio']) ?></td>
                        <td class="text-end"><?= money($f['monto']) ?></td>
                        <td class="text-end"><?= money($f['multa']) ?></td>
                        <td class="text-end"><?= money($f['inter']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($pendientes === []): ?>
                    <tr><td colspan="8" class="text-center text-muted">Sin cobros hoy.</td></tr>
                <?php else: ?>
                    <tr class="fw-bold"><td colspan="7" class="text-end">Total general</td><td class="text-end">$<?= money($tot) ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
