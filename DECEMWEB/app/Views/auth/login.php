<h2 class="auth-title">Iniciar sesión</h2>

<form method="POST" action="<?= url('/login') ?>" novalidate>
    <?= Csrf::campo() ?>

    <div class="mb-3">
        <label for="usuario" class="form-label">Usuario</label>
        <input
            type="text"
            id="usuario"
            name="usuario"
            class="form-control"
            value="<?= e(old('usuario')) ?>"
            autocomplete="username"
            autofocus
            required>
    </div>

    <div class="mb-3">
        <label for="clave" class="form-label">Contraseña</label>
        <input
            type="password"
            id="clave"
            name="clave"
            class="form-control"
            autocomplete="current-password"
            required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
</form>
