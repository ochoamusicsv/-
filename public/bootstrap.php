<?php

declare(strict_types=1);

/**
 * Bootstrap común de la aplicación web: autoload, sesión y helpers.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Database;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/** Escapa texto para HTML. */
function h(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Formatea un número como monto. */
function money(mixed $value): string
{
    return number_format((float) $value, 2, '.', ',');
}

/** Guarda un mensaje flash para la próxima petición. */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** @return list<array{type:string,message:string}> */
function take_flash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $flash;
}

/** Redirige y termina la ejecución. */
function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function pdo(): PDO
{
    return Database::connection();
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}
