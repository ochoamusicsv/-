<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

/**
 * Consultas de reportería: clientes en mora y recaudación.
 */
final class ReporteRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Clientes en mora: por cada parámetro del año, cuenta los meses vencidos
     * (mes <= hastaMes) sin pago y estima la deuda (monto * pendientes + saldo antiguo).
     *
     * @return list<array<string,mixed>>
     */
    public function clientesEnMora(int $anio, int $hastaMes): array
    {
        $hastaMes = max(1, min(12, $hastaMes)); // acotado 1..12, entero seguro
        $mesesImpagos = [];
        for ($m = 1; $m <= 12; $m++) {
            // Cuenta como impago sólo si el mes ya venció (m <= hastaMes) y f{m} = 0.
            if ($m <= $hastaMes) {
                $mesesImpagos[] = "(CASE WHEN p.f{$m} = 0 THEN 1 ELSE 0 END)";
            }
        }
        $sumaPendientes = implode(' + ', $mesesImpagos);

        $sql = "SELECT p.cuent, c.nombr, c.apell, c.telef, p.codca, p.tribu, t.descl AS tributo_desc,
                       p.monto, p.ma AS saldo_antiguo,
                       ({$sumaPendientes}) AS meses_pendientes,
                       ROUND(p.monto * ({$sumaPendientes}) + p.ma, 2) AS deuda_estimada
                FROM param p
                JOIN tribu t ON t.codtr = p.tribu
                LEFT JOIN contr c ON c.cuent = p.cuent
                WHERE p.anio = :anio
                HAVING meses_pendientes > 0 OR saldo_antiguo > 0
                ORDER BY deuda_estimada DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['anio' => $anio]);

        return $stmt->fetchAll();
    }

    /**
     * Recaudación histórica agrupada por fecha de cierre.
     *
     * @return list<array<string,mixed>>
     */
    public function recaudacionPorCierre(string $desde, string $hasta): array
    {
        $sql = 'SELECT fecha_cierre, recibos, total_monto, total_multa, total_inter, total_general
                FROM cierre
                WHERE fecha_cierre BETWEEN :d AND :h
                ORDER BY fecha_cierre';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['d' => $desde, 'h' => $hasta]);

        return $stmt->fetchAll();
    }

    /**
     * Resumen de facturas del día aún no cerradas.
     *
     * @return array{recibos:int,total_monto:float,total_multa:float,total_inter:float,total_general:float}
     */
    public function resumenDia(string $fecha): array
    {
        $sql = 'SELECT COUNT(*) AS recibos,
                       COALESCE(SUM(monto),0) AS total_monto,
                       COALESCE(SUM(multa),0) AS total_multa,
                       COALESCE(SUM(inter),0) AS total_inter,
                       COALESCE(SUM(monto+multa+inter),0) AS total_general
                FROM fdiar WHERE fechp = :f';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['f' => $fecha]);
        $row = $stmt->fetch();

        return [
            'recibos'       => (int) $row['recibos'],
            'total_monto'   => (float) $row['total_monto'],
            'total_multa'   => (float) $row['total_multa'],
            'total_inter'   => (float) $row['total_inter'],
            'total_general' => (float) $row['total_general'],
        ];
    }
}
