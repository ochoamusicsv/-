<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Repository\ContribuyenteRepository;
use App\Repository\PropiedadRepository;
use App\Repository\TarifaRepository;
use App\Repository\TributoRepository;
use App\Service\MontoService;

$repo = new PropiedadRepository(pdo());
$tributos = new TributoRepository(pdo());
$monto = new MontoService($tributos, new TarifaRepository(pdo()));
$contr = new ContribuyenteRepository(pdo());

if (is_post()) {
    $accion = $_POST['accion'] ?? '';
    $data = $_POST;
    try {
        if ($accion === 'eliminar') {
            $repo->delete((int) $_POST['id']);
            flash('success', 'Propiedad eliminada.');
        } else {
            // Calcula el monto según el tributo (fijo o variable por activo).
            $data['monto'] = $monto->montoMensual((string) $data['tribu'], (float) ($data['activ'] ?? 0));
            if ($accion === 'crear') {
                $repo->create($data);
                flash('success', 'Propiedad registrada. Monto calculado: $' . money($data['monto']));
            } elseif ($accion === 'editar') {
                $repo->update((int) $_POST['id'], $data);
                flash('success', 'Propiedad actualizada. Monto: $' . money($data['monto']));
            }
        }
    } catch (Throwable $e) {
        flash('danger', 'Error: ' . $e->getMessage());
    }
    redirect('propiedades.php' . (isset($_POST['cuent']) ? '?cuent=' . urlencode((string) $_POST['cuent']) : ''));
}

$editar = isset($_GET['edit']) ? $repo->find((int) $_GET['edit']) : null;
$cuentaFiltro = trim((string) ($_GET['cuent'] ?? ($editar['cuent'] ?? '')));
$lista = $cuentaFiltro !== '' ? $repo->byCuenta($cuentaFiltro) : $repo->all();
$tributosLista = $tributos->all();
$contribuyentes = $contr->all();

$title = 'Propiedad / Empresa';
require __DIR__ . '/partials/header.php';
?>

<div class="row">
    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <h2 class="h5"><?= $editar ? 'Editar propiedad' : 'Nueva propiedad/empresa' ?></h2>
            <form method="post" class="row g-2">
                <input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>">
                <?php if ($editar): ?><input type="hidden" name="id" value="<?= h((string) $editar['id']) ?>"><?php endif; ?>
                <div class="col-6"><label class="form-label">Cuenta*</label>
                    <select class="form-select" name="cuent" required>
                        <option value="">-</option>
                        <?php foreach ($contribuyentes as $c): $sel = ($editar['cuent'] ?? $cuentaFiltro) === $c['cuent']; ?>
                            <option value="<?= h($c['cuent']) ?>" <?= $sel ? 'selected' : '' ?>>
                                <?= h($c['cuent'] . ' - ' . $c['nombr']) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-6"><label class="form-label">Código Catastral/Empresa*</label>
                    <input class="form-control" name="codca" required value="<?= h($editar['codca'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Tipo</label>
                    <select class="form-select" name="tipoc">
                        <?php foreach (['INMUEBLE', 'EMPRESA'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($editar['tipoc'] ?? 'INMUEBLE') === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-6"><label class="form-label">Tributo*</label>
                    <select class="form-select" name="tribu" required>
                        <?php foreach ($tributosLista as $t): ?>
                            <option value="<?= h($t['codtr']) ?>" <?= ($editar['tribu'] ?? '') === $t['codtr'] ? 'selected' : '' ?>>
                                <?= h($t['codtr'] . ' - ' . $t['descl'] . ' (' . ($t['tipo'] === 'V' ? 'Variable ' . $t['cod_v'] : 'Fijo') . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-6"><label class="form-label">Valor de Activo</label>
                    <input type="number" step="0.01" class="form-control" name="activ" value="<?= h($editar['activ'] ?? '0') ?>">
                    <small class="text-muted">Sólo para tributos variables.</small></div>
                <div class="col-6"><label class="form-label">Nombre empresa</label>
                    <input class="form-control" name="empre" value="<?= h($editar['empre'] ?? '') ?>"></div>
                <div class="col-4"><label class="form-label">Frente</label>
                    <input type="number" step="0.01" class="form-control" name="frent" value="<?= h($editar['frent'] ?? '') ?>"></div>
                <div class="col-4"><label class="form-label">Fondo</label>
                    <input type="number" step="0.01" class="form-control" name="fondo" value="<?= h($editar['fondo'] ?? '') ?>"></div>
                <div class="col-4"><label class="form-label">Pisos</label>
                    <input type="number" class="form-control" name="cantp" value="<?= h($editar['cantp'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Área inmueble</label>
                    <input type="number" step="0.01" class="form-control" name="areai" value="<?= h($editar['areai'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Área construcción</label>
                    <input type="number" step="0.01" class="form-control" name="areac" value="<?= h($editar['areac'] ?? '') ?>"></div>
                <div class="col-12"><label class="form-label">Observaciones</label>
                    <input class="form-control" name="obser" value="<?= h($editar['obser'] ?? '') ?>"></div>
                <div class="col-12 mt-3">
                    <button class="btn btn-primary">El monto se calcula automáticamente al guardar</button>
                    <?php if ($editar): ?><a class="btn btn-secondary" href="propiedades.php">Cancelar</a><?php endif; ?>
                </div>
            </form>
        </div></div>
    </div>

    <div class="col-lg-7">
        <?php if ($cuentaFiltro !== ''): ?>
            <p>Filtrando por cuenta <strong><?= h($cuentaFiltro) ?></strong>. <a href="propiedades.php">Ver todas</a></p>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-sm table-striped bg-white">
                <thead><tr><th>Cód. Catastral</th><th>Tipo</th><th>Tributo</th><th>Activo</th><th class="text-end">Monto</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($lista as $p): ?>
                    <tr>
                        <td><?= h($p['codca']) ?></td>
                        <td><?= h($p['tipoc']) ?></td>
                        <td><?= h($p['tribu']) ?></td>
                        <td class="text-end"><?= money($p['activ']) ?></td>
                        <td class="text-end">$<?= money($p['monto']) ?></td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="?edit=<?= (int) $p['id'] ?>">Editar</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar?')">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger">X</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($lista === []): ?>
                    <tr><td colspan="6" class="text-center text-muted">Sin registros.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
