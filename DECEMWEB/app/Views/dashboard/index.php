<div class="row g-3">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <p class="stat-label">Usuarios registrados</p>
                <p class="stat-value"><?= e($totalUsuarios) ?></p>
                <a href="<?= url('/usuarios') ?>" class="stretched-link small">Administrar usuarios &rarr;</a>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h2 class="h5">Bienvenido al Sistema de Recaudación DECEM</h2>
        <p class="text-muted mb-0">
            Desde este panel podrás administrar los usuarios del sistema. Los módulos de
            personas, matrículas, recibos, cartas y solvencias están preparados en la
            estructura del proyecto para su próxima implementación.
        </p>
    </div>
</div>
