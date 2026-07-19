<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0"><?= count($usuarios) ?> usuario(s)</p>
    <a href="<?= url('/usuarios/crear') ?>" class="btn btn-primary btn-sm">Nuevo usuario</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= e($usuario['id']) ?></td>
                        <td><?= e($usuario['nombre']) ?></td>
                        <td><?= e($usuario['usuario']) ?></td>
                        <td>
                            <?php if ((int) $usuario['activo'] === 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($usuario['fechacreacion'] ?? '') ?></td>
                        <td class="text-end">
                            <a href="<?= url('/usuarios/' . $usuario['id'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="POST" action="<?= url('/usuarios/' . $usuario['id'] . '/eliminar') ?>" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar este usuario?');">
                                <?= Csrf::campo() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
