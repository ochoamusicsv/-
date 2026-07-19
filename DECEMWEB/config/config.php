<?php

/**
 * Configuración central de la aplicación DECEM.
 *
 * Los valores se toman de variables de entorno cuando existen (archivo .env)
 * y de lo contrario usan los valores por defecto de XAMPP para que el
 * proyecto funcione sin configuración adicional.
 */

// Cargar variables desde el archivo .env (si existe) hacia $_ENV / getenv().
(function () {
    $rutaEnv = dirname(__DIR__) . '/.env';

    if (!is_file($rutaEnv)) {
        return;
    }

    foreach (file($rutaEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        $linea = trim($linea);

        if ($linea === '' || $linea[0] === '#') {
            continue;
        }

        if (!str_contains($linea, '=')) {
            continue;
        }

        [$clave, $valor] = explode('=', $linea, 2);
        $clave  = trim($clave);
        $valor  = trim($valor);

        // Quitar comillas envolventes.
        if (strlen($valor) >= 2 && ($valor[0] === '"' || $valor[0] === "'")) {
            $valor = substr($valor, 1, -1);
        }

        if (getenv($clave) === false) {
            putenv("{$clave}={$valor}");
            $_ENV[$clave] = $valor;
        }
    }
})();

/**
 * Helper para leer una variable de entorno con valor por defecto.
 */
if (!function_exists('env')) {
    function env(string $clave, $porDefecto = null)
    {
        $valor = getenv($clave);

        if ($valor === false) {
            return $porDefecto;
        }

        return match (strtolower($valor)) {
            'true'  => true,
            'false' => false,
            'null'  => null,
            default => $valor,
        };
    }
}

return [
    'app' => [
        'name'  => env('APP_NAME', 'DECEM'),
        'env'   => env('APP_ENV', 'local'),
        'debug' => (bool) env('APP_DEBUG', true),
    ],
    'db' => [
        'host'    => env('DB_HOST', '127.0.0.1'),
        'port'    => env('DB_PORT', '3306'),
        'name'    => env('DB_NAME', 'decem'),
        'user'    => env('DB_USER', 'root'),
        'pass'    => env('DB_PASS', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
    ],
];
