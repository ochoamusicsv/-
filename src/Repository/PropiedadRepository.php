<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

/**
 * CRUD de propiedad/empresa (propi).
 */
final class PropiedadRepository
{
    private const COLUMNS = [
        'cuent', 'codca', 'tipoc', 'frent', 'ladoa', 'ladob', 'fondo', 'areai',
        'areac', 'tipoi', 'cantp', 'empre', 'tipoe', 'tribu', 'activ', 'monto', 'obser',
    ];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        $sql = 'SELECT p.*, t.descl AS tributo_desc, c.nombr, c.apell
                FROM propi p
                JOIN tribu t ON t.codtr = p.tribu
                LEFT JOIN contr c ON c.cuent = p.cuent
                ORDER BY p.codca';

        return $this->pdo->query($sql)->fetchAll();
    }

    /** @return list<array<string,mixed>> */
    public function byCuenta(string $cuent): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM propi WHERE cuent = :c ORDER BY codca');
        $stmt->execute(['c' => $cuent]);

        return $stmt->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM propi WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @param array<string,mixed> $data */
    public function create(array $data): int
    {
        $cols = self::COLUMNS;
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $cols);
        $sql = sprintf(
            'INSERT INTO propi (%s) VALUES (%s)',
            implode(', ', $cols),
            implode(', ', $placeholders)
        );
        $this->pdo->prepare($sql)->execute($this->bind($data));

        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): void
    {
        $sets = array_map(static fn (string $c): string => "$c = :$c", self::COLUMNS);
        $sql = sprintf('UPDATE propi SET %s WHERE id = :id', implode(', ', $sets));
        $params = $this->bind($data);
        $params['id'] = $id;
        $this->pdo->prepare($sql)->execute($params);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM propi WHERE id = :id')->execute(['id' => $id]);
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function bind(array $data): array
    {
        $params = [];
        foreach (self::COLUMNS as $col) {
            $value = $data[$col] ?? null;
            $params[$col] = $value === '' ? null : $value;
        }

        return $params;
    }
}
