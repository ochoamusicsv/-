<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

/**
 * Acceso a facturas del día (fdiar) e historial (fhisto).
 */
final class FacturaRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function siguienteCorrelativo(): string
    {
        $max = $this->pdo->query(
            "SELECT MAX(CAST(corre AS UNSIGNED)) FROM (
                SELECT corre FROM fdiar UNION ALL SELECT corre FROM fhisto
             ) t"
        )->fetchColumn();

        $next = ((int) $max) + 1;

        return str_pad((string) $next, 8, '0', STR_PAD_LEFT);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function insertDiaria(array $data): int
    {
        $sql = 'INSERT INTO fdiar (cuent, codca, tipoc, tribu, corre, perio, monto, fechp, multa, inter, usuam)
                VALUES (:cuent, :codca, :tipoc, :tribu, :corre, :perio, :monto, :fechp, :multa, :inter, :usuam)';
        $this->pdo->prepare($sql)->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public function pendientesDia(?string $fecha = null): array
    {
        if ($fecha === null) {
            return $this->pdo->query('SELECT * FROM fdiar ORDER BY corre')->fetchAll();
        }
        $stmt = $this->pdo->prepare('SELECT * FROM fdiar WHERE fechp = :f ORDER BY corre');
        $stmt->execute(['f' => $fecha]);

        return $stmt->fetchAll();
    }

    /** @return list<array<string,mixed>> */
    public function historialCuenta(string $cuent): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM fhisto WHERE cuent = :c ORDER BY fechp DESC, corre DESC');
        $stmt->execute(['c' => $cuent]);

        return $stmt->fetchAll();
    }
}
