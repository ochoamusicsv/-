<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Repository\ContribuyenteRepository;

$repo = new ContribuyenteRepository(pdo());

if (is_post()) {
    $accion = $_POST['accion'] ?? '';
    $data = $_POST;
    try {
        if ($accion === 'crear') {
            $repo->create($data);
            flash('success', 'Contribuyente registrado.');
        } elseif ($accion === 'editar') {
            $repo->update((string) $_POST['orig_cuent'], $data);
            flash('success', 'Contribuyente actualizado.');
        } elseif ($accion === 'eliminar') {
            $repo->delete((string) $_POST['cuent']);
            flash('success', 'Contribuyente eliminado.');
        }
    } catch (Throwable $e) {
        flash('danger', 'Error: ' . $e->getMessage());
    }
    redirect('contribuyentes.php');
}

$filtro = trim((string) ($_GET['q'] ?? ''));
$editar = null;
if (isset($_GET['edit'])) {
    $editar = $repo->find((string) $_GET['edit']);
}
$lista = $repo->all($filtro);

$title = 'Contribuyentes';
require __DIR__ . '/partials/header.php';
?>

<div class="row">
    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <h2 class="h5"><?= $editar ? 'Editar contribuyente' : 'Nuevo contribuyente' ?></h2>
            <form method="post" class="row g-2">
                <input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>">
                <?php if ($editar): ?>
                    <input type="hidden" name="orig_cuent" value="<?= h($editar['cuent']) ?>">
                <?php endif; ?>
                <div class="col-6"><label class="form-label">Cuenta*</label>
                    <input class="form-control" name="cuent" required value="<?= h($editar['cuent'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Nº Documento*</label>
                    <input class="form-control" name="ndocu" required value="<?= h($editar['ndocu'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Tipo Documento</label>
                    <input class="form-control" name="tdocu" value="<?= h($editar['tdocu'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Género</label>
                    <select class="form-select" name="gener">
                        <?php foreach (['' => '-', 'M' => 'M', 'F' => 'F'] as $k => $v): ?>
                            <option value="<?= h($k) ?>" <?= ($editar['gener'] ?? '') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-6"><label class="form-label">Nombre*</label>
                    <input class="form-control" name="nombr" required value="<?= h($editar['nombr'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Apellidos</label>
                    <input class="form-control" name="apell" value="<?= h($editar['apell'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Teléfono</label>
                    <input class="form-control" name="telef" value="<?= h($editar['telef'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Profesión</label>
                    <input class="form-control" name="profe" value="<?= h($editar['profe'] ?? '') ?>"></div>
                <div class="col-4"><label class="form-label">Zona</label>
                    <input class="form-control" name="zona" value="<?= h($editar['zona'] ?? '') ?>"></div>
                <div class="col-4"><label class="form-label">Distrito</label>
                    <input class="form-control" name="distr" value="<?= h($editar['distr'] ?? '') ?>"></div>
                <div class="col-4"><label class="form-label">Municipio</label>
                    <input class="form-control" name="munic" value="<?= h($editar['munic'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Departamento</label>
                    <input class="form-control" name="depar" value="<?= h($editar['depar'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">Referencia</label>
                    <input class="form-control" name="refer" value="<?= h($editar['refer'] ?? '') ?>"></div>
                <div class="col-4"><label class="form-label">Nacimiento</label>
                    <input type="date" class="form-control" name="fechn" value="<?= h($editar['fechn'] ?? '') ?>"></div>
                <div class="col-4"><label class="form-label">Registro</label>
                    <input type="date" class="form-control" name="fechr" value="<?= h($editar['fechr'] ?? date('Y-m-d')) ?>"></div>
                <div class="col-4"><label class="form-label">Estado</label>
                    <input class="form-control" name="estad" value="<?= h($editar['estad'] ?? 'ACTIVO') ?>"></div>
                <div class="col-12 mt-3">
                    <button class="btn btn-primary" type="submit"><?= $editar ? 'Actualizar' : 'Guardar' ?></button>
                    <?php if ($editar): ?><a class="btn btn-secondary" href="contribuyentes.php">Cancelar</a><?php endif; ?>
                </div>
            </form>
        </div></div>
    </div>

    <div class="col-lg-7">
        <form class="mb-2" method="get">
            <div class="input-group">
                <input class="form-control" name="q" placeholder="Buscar por nombre, cuenta o documento" value="<?= h($filtro) ?>">
                <button class="btn btn-outline-secondary">Buscar</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-sm table-striped bg-white">
                <thead><tr><th>Cuenta</th><th>Documento</th><th>Nombre</th><th>Teléfono</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($lista as $c): ?>
                    <tr>
                        <td><?= h($c['cuent']) ?></td>
                        <td><?= h($c['ndocu']) ?></td>
                        <td><?= h(trim($c['nombr'] . ' ' . ($c['apell'] ?? ''))) ?></td>
                        <td><?= h($c['telef']) ?></td>
                        <td><?= h($c['estad']) ?></td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="?edit=<?= h(urlencode($c['cuent'])) ?>">Editar</a>
                            <a class="btn btn-sm btn-outline-info" href="propiedades.php?cuent=<?= h(urlencode($c['cuent'])) ?>">Propiedades</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar?')">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="cuent" value="<?= h($c['cuent']) ?>">
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
