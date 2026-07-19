<?php

/**
 * Front controller: punto de entrada único de la aplicación DECEM.
 */

define('ROOT_PATH', dirname(__DIR__));

$config = require ROOT_PATH . '/config/config.php';

// Reporte de errores según el entorno.
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Autoloader de clases del núcleo, controladores, modelos y middlewares.
require_once ROOT_PATH . '/app/Core/Autoloader.php';

Autoloader::registrar([
    ROOT_PATH . '/app/Core',
    ROOT_PATH . '/app/Controllers',
    ROOT_PATH . '/app/Models',
    ROOT_PATH . '/app/Middleware',
]);

// La conexión a la BD se carga bajo demanda desde los modelos.
require_once ROOT_PATH . '/config/database.php';

// Funciones de ayuda globales.
require_once ROOT_PATH . '/app/Helpers/functions.php';

// Iniciar la sesión.
Session::iniciar();

// Enrutamiento.
$router = new Router();

// Variables globales para las vistas (base de la app y ruta actual).
$GLOBALS['APP_BASE'] = $router->base();
$uriActual           = Request::uri();
if ($router->base() !== '' && str_starts_with($uriActual, $router->base())) {
    $uriActual = substr($uriActual, strlen($router->base()));
}
$GLOBALS['CURRENT_PATH'] = '/' . ltrim($uriActual, '/');

require ROOT_PATH . '/routes/web.php';

$router->dispatch();
