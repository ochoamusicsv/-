<?php

declare(strict_types=1);

namespace App;

/**
 * Carga la configuración de conexión desde variables de entorno con valores
 * por defecto pensados para una instalación XAMPP local.
 */
final class Config
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }

        return $value;
    }

    /** @return array{host:string,port:string,dbname:string,user:string,pass:string,charset:string} */
    public static function db(): array
    {
        return [
            'host'    => self::get('DB_HOST', '127.0.0.1') ?? '127.0.0.1',
            'port'    => self::get('DB_PORT', '3306') ?? '3306',
            'dbname'  => self::get('DB_NAME', 'recaudacion') ?? 'recaudacion',
            'user'    => self::get('DB_USER', 'root') ?? 'root',
            'pass'    => self::get('DB_PASS', '') ?? '',
            'charset' => 'utf8mb4',
        ];
    }
}
