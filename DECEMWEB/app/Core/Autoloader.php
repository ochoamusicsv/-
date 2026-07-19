<?php

/**
 * Autoloader sencillo basado en PSR-0/PSR-4 simplificado.
 *
 * Busca las clases en app/Core, app/Controllers, app/Models,
 * app/Middleware y app/Helpers, evitando los require_once manuales.
 */
class Autoloader
{
    private static array $rutas = [];

    public static function registrar(array $rutas): void
    {
        self::$rutas = $rutas;

        spl_autoload_register(static function (string $clase): void {
            foreach (self::$rutas as $ruta) {
                $archivo = $ruta . '/' . $clase . '.php';

                if (is_file($archivo)) {
                    require_once $archivo;
                    return;
                }
            }
        });
    }
}
