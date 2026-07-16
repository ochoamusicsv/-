<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Utilidades monetarias. Redondeo a 2 decimales (centavos) con medio-arriba.
 */
final class Money
{
    public static function round(float $amount): float
    {
        return round($amount, 2, PHP_ROUND_HALF_UP);
    }

    public static function max(float $a, float $b): float
    {
        return self::round($a >= $b ? $a : $b);
    }
}
