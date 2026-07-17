<?php

declare(strict_types=1);

namespace App\Tariff;

use App\Support\Money;
use InvalidArgumentException;

/**
 * Calcula el monto de un tributo variable (V01..V16) según el valor de activo.
 *
 * Fórmula del tramo:  monto = fijo + exceso * (activo - inicio)
 *
 * - Si el activo cae por debajo del primer tramo se usa el primer tramo.
 * - Si cae por encima del último tramo se usa el último tramo.
 */
final class TariffCalculator
{
    /**
     * @param list<Bracket> $brackets
     */
    public function calcular(array $brackets, float $activo): float
    {
        $bracket = $this->encontrarTramo($brackets, $activo);
        $monto = $bracket->fijo + $bracket->exceso * ($activo - $bracket->inicio);

        return Money::round(max(0.0, $monto));
    }

    /**
     * @param list<Bracket> $brackets
     */
    public function encontrarTramo(array $brackets, float $activo): Bracket
    {
        if ($brackets === []) {
            throw new InvalidArgumentException('La tabla de tarifa no tiene tramos definidos.');
        }

        $brackets = $this->ordenar($brackets);

        foreach ($brackets as $bracket) {
            if ($bracket->contains($activo)) {
                return $bracket;
            }
        }

        $primero = $brackets[0];
        if ($activo < $primero->inicio) {
            return $primero;
        }

        return $brackets[count($brackets) - 1];
    }

    /**
     * @param list<Bracket> $brackets
     * @return list<Bracket>
     */
    private function ordenar(array $brackets): array
    {
        usort($brackets, static fn (Bracket $a, Bracket $b): int => $a->inicio <=> $b->inicio);

        return $brackets;
    }
}
