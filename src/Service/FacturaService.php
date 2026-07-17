<?php

declare(strict_types=1);

namespace App\Service;

use App\Billing\PenaltyCalculator;
use App\Repository\FacturaRepository;
use App\Repository\ParamRepository;
use App\Support\Money;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;

/**
 * Aplica un cobro diario sobre una propiedad/tributo para un periodo (YYYY-MM):
 *   - calcula multa e interés por mora respecto a la fecha de vencimiento del periodo,
 *   - registra el recibo en fdiar con correlativo,
 *   - marca el mes correspondiente como pagado en param.
 */
final class FacturaService
{
    public function __construct(
        private readonly FacturaRepository $facturas,
        private readonly ParamRepository $params,
        private readonly PenaltyCalculator $penalty,
        private readonly int $diaVencimiento = 1,
    ) {
    }

    /**
     * @return array{corre:string,monto:float,multa:float,interes:float,total:float,dias_mora:int,periodo:string}
     */
    public function aplicarCobro(
        string $cuent,
        string $codca,
        string $tipoc,
        string $tribu,
        string $periodo,
        float $montoMensual,
        DateTimeInterface $fechaPago,
        string $usuario = 'sistema'
    ): array {
        [$anio, $mes] = $this->parsePeriodo($periodo);

        $param = $this->params->findOrCreate($cuent, $codca, $tipoc, $tribu, $anio, $montoMensual);
        if ($this->params->mesPagado((int) $param['id'], $mes)) {
            throw new RuntimeException("El periodo {$periodo} ya fue pagado para {$codca}/{$tribu}.");
        }

        $vencimiento = new DateTimeImmutable(sprintf('%04d-%02d-%02d', $anio, $mes, $this->diaVencimiento));
        $recargo = $this->penalty->recargo($montoMensual, $vencimiento, $fechaPago);
        $corre = $this->facturas->siguienteCorrelativo();

        $this->facturas->insertDiaria([
            'cuent' => $cuent,
            'codca' => $codca,
            'tipoc' => $tipoc,
            'tribu' => $tribu,
            'corre' => $corre,
            'perio' => $periodo,
            'monto' => Money::round($montoMensual),
            'fechp' => $fechaPago->format('Y-m-d'),
            'multa' => $recargo['multa'],
            'inter' => $recargo['interes'],
            'usuam' => $usuario,
        ]);

        $this->params->registrarPagoMes((int) $param['id'], $mes, Money::round($montoMensual));

        return [
            'corre'     => $corre,
            'monto'     => Money::round($montoMensual),
            'multa'     => $recargo['multa'],
            'interes'   => $recargo['interes'],
            'total'     => $recargo['total'],
            'dias_mora' => $recargo['dias_mora'],
            'periodo'   => $periodo,
        ];
    }

    /** @return array{0:int,1:int} [año, mes] */
    private function parsePeriodo(string $periodo): array
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $periodo, $m) !== 1) {
            throw new RuntimeException("Periodo inválido (se espera YYYY-MM): {$periodo}");
        }
        $mes = (int) $m[2];
        if ($mes < 1 || $mes > 12) {
            throw new RuntimeException("Mes inválido en periodo: {$periodo}");
        }

        return [(int) $m[1], $mes];
    }
}
