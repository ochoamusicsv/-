<?php

declare(strict_types=1);

namespace App\Repository;

use InvalidArgumentException;
use PDO;

/**
 * Parámetros de cobro anual por (codca, tributo, año) con control de pagos
 * mensuales F1..F12, número de meses pagados (fp) y saldo antiguo (ma).
 */
final class ParamRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string,mixed> */
    public function findOrCreate(string $cuent, string $codca, string $tipoc, string $tribu, int $anio, float $monto): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM param WHERE codca = :codca AND tribu = :tribu AND anio = :anio');
        $stmt->execute(['codca' => $codca, 'tribu' => $tribu, 'anio' => $anio]);
        $row = $stmt->fetch();
        if ($row !== false) {
            return $row;
        }

        $ins = $this->pdo->prepare(
            'INSERT INTO param (cuent, codca, tipoc, tribu, anio, monto, perio)
             VALUES (:cuent, :codca, :tipoc, :tribu, :anio, :monto, 1)'
        );
        $ins->execute([
            'cuent' => $cuent, 'codca' => $codca, 'tipoc' => $tipoc,
            'tribu' => $tribu, 'anio' => $anio, 'monto' => $monto,
        ]);

        return $this->findOrCreate($cuent, $codca, $tipoc, $tribu, $anio, $monto);
    }

    /**
     * Marca un mes (1..12) como pagado con el monto indicado y recalcula fp/perio.
     */
    public function registrarPagoMes(int $id, int $mes, float $monto): void
    {
        if ($mes < 1 || $mes > 12) {
            throw new InvalidArgumentException("Mes fuera de rango: {$mes}");
        }

        $col = 'f' . $mes;
        $sql = "UPDATE param
                SET {$col} = :monto,
                    fp = (
                        (f1>0)+(f2>0)+(f3>0)+(f4>0)+(f5>0)+(f6>0)
                        +(f7>0)+(f8>0)+(f9>0)+(f10>0)+(f11>0)+(f12>0)
                    ),
                    perio = LEAST(12, (
                        (f1>0)+(f2>0)+(f3>0)+(f4>0)+(f5>0)+(f6>0)
                        +(f7>0)+(f8>0)+(f9>0)+(f10>0)+(f11>0)+(f12>0)
                    ) + 1)
                WHERE id = :id";
        // El monto del mes se fija antes de recalcular fp para incluirlo en el conteo.
        $this->pdo->prepare("UPDATE param SET {$col} = :monto WHERE id = :id")
            ->execute(['monto' => $monto, 'id' => $id]);
        $this->pdo->prepare(
            "UPDATE param SET
                fp = ((f1>0)+(f2>0)+(f3>0)+(f4>0)+(f5>0)+(f6>0)+(f7>0)+(f8>0)+(f9>0)+(f10>0)+(f11>0)+(f12>0)),
                perio = LEAST(12, ((f1>0)+(f2>0)+(f3>0)+(f4>0)+(f5>0)+(f6>0)+(f7>0)+(f8>0)+(f9>0)+(f10>0)+(f11>0)+(f12>0)) + 1)
             WHERE id = :id"
        )->execute(['id' => $id]);
    }

    public function mesPagado(int $id, int $mes): bool
    {
        $col = 'f' . $mes;
        $stmt = $this->pdo->prepare("SELECT {$col} AS v FROM param WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $v = $stmt->fetchColumn();

        return $v !== false && (float) $v > 0;
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM param WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
