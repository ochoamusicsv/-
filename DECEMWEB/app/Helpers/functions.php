<?php

/**
 * Funciones de ayuda globales disponibles en toda la aplicación.
 */

if (!function_exists('base_url')) {
    /**
     * Base de la aplicación detectada por el enrutador (ej. "/DECEMWEB").
     */
    function base_url(): string
    {
        return $GLOBALS['APP_BASE'] ?? '';
    }
}

if (!function_exists('url')) {
    /**
     * Construye una URL absoluta respecto a la base de la aplicación.
     */
    function url(string $ruta = '/'): string
    {
        return base_url() . '/' . ltrim($ruta, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Ruta a un recurso estático dentro de public/assets.
     */
    function asset(string $ruta): string
    {
        return base_url() . '/assets/' . ltrim($ruta, '/');
    }
}

if (!function_exists('e')) {
    /**
     * Escapa una cadena para imprimirla de forma segura en HTML.
     */
    function e($valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('old')) {
    /**
     * Recupera un valor previamente enviado (para repoblar formularios).
     */
    function old(string $clave, $porDefecto = '')
    {
        return $_SESSION['_old'][$clave] ?? $porDefecto;
    }
}
