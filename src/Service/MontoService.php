<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\TarifaRepository;
use App\Repository\TributoRepository;
use App\Support\Money;
use App\Tariff\TariffCalculator;
use RuntimeException;

/**
 * Determina el monto de un tributo para una propiedad/empresa:
 *   - tributo fijo (tipo 'F'): usa tribu.mtari
 *   - tributo variable (tipo 'V'): usa la tabla V y el valor de activo
 */
final class MontoService
{
    public function __construct(
        private readonly TributoRepository $tributos,
        private readonly TarifaRepository $tarifas,
        private readonly TariffCalculator $calc = new TariffCalculator(),
    ) {
    }

    public function montoMensual(string $codtr, float $activo): float
    {
        $tributo = $this->tributos->find($codtr);
        if ($tributo === null) {
            throw new RuntimeException("Tributo no encontrado: {$codtr}");
        }

        if ($tributo['tipo'] === 'V') {
            $codV = $tributo['cod_v'];
            if ($codV === null) {
                throw new RuntimeException("Tributo variable sin tabla V asignada: {$codtr}");
            }

            return $this->calc->calcular($this->tarifas->tramos($codV), $activo);
        }

        return Money::round((float) $tributo['mtari']);
    }
}
