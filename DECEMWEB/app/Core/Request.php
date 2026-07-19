<?php

/**
 * Abstracción de la petición HTTP entrante.
 */
class Request
{
    public static function metodo(): string
    {
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Soporte para method spoofing (_method en formularios).
        if ($metodo === 'POST' && isset($_POST['_method'])) {
            $metodo = strtoupper($_POST['_method']);
        }

        return $metodo;
    }

    public static function uri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    }

    /**
     * Obtiene un valor de POST, con recorte de espacios y valor por defecto.
     */
    public static function post(string $clave, $porDefecto = null)
    {
        $valor = $_POST[$clave] ?? $porDefecto;

        return is_string($valor) ? trim($valor) : $valor;
    }

    public static function get(string $clave, $porDefecto = null)
    {
        $valor = $_GET[$clave] ?? $porDefecto;

        return is_string($valor) ? trim($valor) : $valor;
    }

    public static function esPost(): bool
    {
        return self::metodo() === 'POST';
    }

    public static function todos(): array
    {
        return $_POST;
    }
}
