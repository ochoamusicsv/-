<?php

declare(strict_types=1);

namespace App\Tariff;

/**
 * Un tramo (nivel) de una tabla de tarifa variable V01..V16.
 */
final class Bracket
{
    public function __construct(
        public readonly int $nivel,
        public readonly float $inicio,
        public readonly float $final,
        public readonly float $fijo,
        public readonly float $exceso,
    ) {
    }

    public function contains(float $activo): bool
    {
        return $activo >= $this->inicio && $activo <= $this->final;
    }

    /** @param array{nivel:int|string,inicio:float|string,final:float|string,fijo:float|string,exceso:float|string} $row */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['nivel'],
            (float) $row['inicio'],
            (float) $row['final'],
            (float) $row['fijo'],
            (float) $row['exceso'],
        );
    }
}
