<?php

/**
 * Protección CSRF mediante token por sesión.
 */
class Csrf
{
    private const CLAVE = '_csrf_token';

    public static function token(): string
    {
        if (!Session::has(self::CLAVE)) {
            Session::set(self::CLAVE, bin2hex(random_bytes(32)));
        }

        return Session::get(self::CLAVE);
    }

    /**
     * Campo oculto listo para insertar en un formulario.
     */
    public static function campo(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    public static function validar(?string $token): bool
    {
        return is_string($token)
            && Session::has(self::CLAVE)
            && hash_equals(Session::get(self::CLAVE), $token);
    }
}
