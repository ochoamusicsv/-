<?php $esEdicion = $usuario !== null; ?>
<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= e($accion) ?>" novalidate>
            <?= Csrf::campo() ?>

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                       value="<?= e($usuario['nombre'] ?? old('nombre')) ?>" required>
            </div>

            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" id="usuario" name="usuario" class="form-control"
                       value="<?= e($usuario['usuario'] ?? old('usuario')) ?>" required>
            </div>

            <div class="mb-3">
                <label for="clave" class="form-label">
                    Contraseña <?= $esEdicion ? '<small class="text-muted">(dejar en blanco para no cambiar)</small>' : '' ?>
                </label>
                <input type="password" id="clave" name="clave" class="form-control" autocomplete="new-password"
                       <?= $esEdicion ? '' : 'required' ?>>
            </div>

            <div class="mb-3">
                <label for="clave_confirmation" class="form-label">Confirmar contraseña</label>
                <input type="password" id="clave_confirmation" name="clave_confirmation" class="form-control" autocomplete="new-password">
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" id="activo" name="activo" class="form-check-input" value="1"
                       <?= (!$esEdicion || (int) ($usuario['activo'] ?? 1) === 1) ? 'checked' : '' ?>>
                <label for="activo" class="form-check-label">Usuario activo</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Crear usuario' ?></button>
                <a href="<?= url('/usuarios') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
