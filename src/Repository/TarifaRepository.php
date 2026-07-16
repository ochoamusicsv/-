<?php

declare(strict_types=1);

namespace App\Repository;

use App\Tariff\Bracket;
use PDO;

/**
 * Acceso a las tablas de tarifa variable (tarif_v).
 */
final class TarifaRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<Bracket> */
    public function tramos(string $codV): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT nivel, inicio, final, fijo, exceso FROM tarif_v WHERE cod_v = :cod ORDER BY inicio'
        );
        $stmt->execute(['cod' => $codV]);

        return array_map(
            static fn (array $row): Bracket => Bracket::fromRow($row),
            $stmt->fetchAll()
        );
    }

    /** @return list<array{cod_v:string,descr:?string}> */
    public function catalogo(): array
    {
        $sql = 'SELECT cod_v, MAX(descr) AS descr FROM tarif_v GROUP BY cod_v ORDER BY cod_v';

        return $this->pdo->query($sql)->fetchAll();
    }
}
