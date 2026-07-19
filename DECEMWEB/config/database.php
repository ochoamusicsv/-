<?php

/**
 * Conexión PDO a MySQL/MariaDB usando el patrón singleton.
 *
 * Devuelve siempre la misma instancia de PDO para evitar abrir múltiples
 * conexiones en una misma petición.
 */
class Database
{
    private static ?PDO $instancia = null;

    private function __construct()
    {
    }

    public static function conectar(): PDO
    {
        if (self::$instancia instanceof PDO) {
            return self::$instancia;
        }

        $config = require __DIR__ . '/config.php';
        $db     = $config['db'];

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $db['host'],
            $db['port'],
            $db['name'],
            $db['charset']
        );

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$instancia = new PDO($dsn, $db['user'], $db['pass'], $opciones);
        } catch (PDOException $e) {
            if ($config['app']['debug']) {
                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }

            http_response_code(500);
            die('No fue posible conectar con la base de datos.');
        }

        return self::$instancia;
    }
}
