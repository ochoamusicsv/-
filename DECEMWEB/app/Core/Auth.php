<?php

/**
 * Gestión del usuario autenticado.
 */
class Auth
{
    private const CLAVE = '_auth_user';

    /**
     * Guarda en sesión los datos públicos del usuario y regenera el ID.
     */
    public static function login(array $usuario): void
    {
        unset($usuario['clave']);

        Session::regenerar();
        Session::set(self::CLAVE, $usuario);
    }

    public static function logout(): void
    {
        Session::remove(self::CLAVE);
        Session::regenerar();
    }

    public static function check(): bool
    {
        return Session::has(self::CLAVE);
    }

    public static function usuario(): ?array
    {
        return Session::get(self::CLAVE);
    }

    public static function id(): ?int
    {
        return self::usuario()['id'] ?? null;
    }
}
