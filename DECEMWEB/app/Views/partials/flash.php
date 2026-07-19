<?php $flash = Session::obtenerFlash(); ?>
<?php foreach ($flash as $tipo => $mensajes): ?>
    <?php foreach ($mensajes as $mensaje): ?>
        <div class="alert alert-<?= e($tipo) ?> alert-dismissible fade show" role="alert">
            <?= e($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
