<?php

declare(strict_types=1);

namespace App\Billing;

use App\Support\Money;
use DateTimeInterface;

/**
 * Cálculo de multa e interés por mora.
 *
 * Multa (fórmula Excel del usuario):
 *   dias = HOY - fecha_a_pagar
 *   dias <= 60             -> 0
 *   dias <= 150            -> MAX(monto * 0.05, monto_minimo)
 *   dias  > 150            -> MAX(monto * 0.10, monto_minimo)
 *
 * Interés (fórmula Excel del usuario):  monto_minimo * tasa_diaria * dias
 * (se aplica sólo una vez superados los días de gracia, igual que la multa).
 */
final class PenaltyCalculator
{
    public function __construct(
        private readonly float $montoMinimo = 2.86,
        private readonly float $tasaInteresDiario = 0.001,
        private readonly int $diasGracia = 60,
        private readonly int $diasMultaMedia = 150,
        private readonly float $tasaMultaMedia = 0.05,
        private readonly float $tasaMultaAlta = 0.10,
    ) {
    }

    public function multa(float $monto, int $diasMora): float
    {
        if ($diasMora <= $this->diasGracia) {
            return 0.0;
        }

        $tasa = $diasMora <= $this->diasMultaMedia ? $this->tasaMultaMedia : $this->tasaMultaAlta;

        return Money::max($monto * $tasa, $this->montoMinimo);
    }

    public function interes(int $diasMora): float
    {
        if ($diasMora <= $this->diasGracia) {
            return 0.0;
        }

        return Money::round($this->montoMinimo * $this->tasaInteresDiario * $diasMora);
    }

    public function diasMora(DateTimeInterface $fechaVencimiento, DateTimeInterface $fechaActual): int
    {
        $dias = (int) floor(($fechaActual->getTimestamp() - $fechaVencimiento->getTimestamp()) / 86400);

        return max(0, $dias);
    }

    /**
     * Recargo total (multa + interés) para un monto vencido a una fecha dada.
     *
     * @return array{dias_mora:int,multa:float,interes:float,total:float}
     */
    public function recargo(float $monto, DateTimeInterface $fechaVencimiento, DateTimeInterface $fechaActual): array
    {
        $dias = $this->diasMora($fechaVencimiento, $fechaActual);
        $multa = $this->multa($monto, $dias);
        $interes = $this->interes($dias);

        return [
            'dias_mora' => $dias,
            'multa'     => $multa,
            'interes'   => $interes,
            'total'     => Money::round($monto + $multa + $interes),
        ];
    }
}
