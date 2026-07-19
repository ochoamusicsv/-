<?php

/**
 * Envoltorio para el manejo de la sesión: datos de usuario y mensajes flash.
 */
class Session
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function set(string $clave, $valor): void
    {
        $_SESSION[$clave] = $valor;
    }

    public static function get(string $clave, $porDefecto = null)
    {
        return $_SESSION[$clave] ?? $porDefecto;
    }

    public static function has(string $clave): bool
    {
        return isset($_SESSION[$clave]);
    }

    public static function remove(string $clave): void
    {
        unset($_SESSION[$clave]);
    }

    public static function destruir(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Regenera el ID de sesión (recomendado tras iniciar sesión).
     */
    public static function regenerar(): void
    {
        session_regenerate_id(true);
    }

    /* ---------- Mensajes flash (viven una sola petición) ---------- */

    public static function flash(string $tipo, string $mensaje): void
    {
        $_SESSION['_flash'][$tipo][] = $mensaje;
    }

    public static function obtenerFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        return $flash;
    }
}
