<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

/**
 * Acceso al catálogo de tributos (tribu).
 */
final class TributoRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array{codtr:string,descl:string,descc:?string,tipo:string,mtari:string,cod_v:?string,periodicidad:string}|null */
    public function find(string $codtr): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tribu WHERE codtr = :c');
        $stmt->execute(['c' => $codtr]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM tribu ORDER BY codtr')->fetchAll();
    }
}
