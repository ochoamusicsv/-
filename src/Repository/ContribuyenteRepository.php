<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

/**
 * CRUD de contribuyentes (contr).
 */
final class ContribuyenteRepository
{
    private const COLUMNS = [
        'ndocu', 'tdocu', 'cuent', 'nombr', 'apell', 'gener', 'zona', 'refer',
        'distr', 'munic', 'depar', 'telef', 'fechn', 'estad', 'fechr', 'profe',
    ];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array<string,mixed>> */
    public function all(string $filtro = ''): array
    {
        if ($filtro === '') {
            return $this->pdo->query('SELECT * FROM contr ORDER BY nombr, apell')->fetchAll();
        }

        $stmt = $this->pdo->prepare(
            'SELECT * FROM contr WHERE nombr LIKE :q1 OR apell LIKE :q2 OR cuent LIKE :q3 OR ndocu LIKE :q4 ORDER BY nombr'
        );
        $like = '%' . $filtro . '%';
        $stmt->execute(['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like]);

        return $stmt->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public function find(string $cuent): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contr WHERE cuent = :c');
        $stmt->execute(['c' => $cuent]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @param array<string,mixed> $data */
    public function create(array $data): void
    {
        $cols = self::COLUMNS;
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $cols);
        $sql = sprintf(
            'INSERT INTO contr (%s, usuam, fechm) VALUES (%s, :usuam, NOW())',
            implode(', ', $cols),
            implode(', ', $placeholders)
        );
        $this->pdo->prepare($sql)->execute($this->bind($data));
    }

    /** @param array<string,mixed> $data */
    public function update(string $cuent, array $data): void
    {
        $sets = array_map(static fn (string $c): string => "$c = :$c", self::COLUMNS);
        $sql = sprintf(
            'UPDATE contr SET %s, usuam = :usuam, fechm = NOW() WHERE cuent = :orig_cuent',
            implode(', ', $sets)
        );
        $params = $this->bind($data);
        $params['orig_cuent'] = $cuent;
        $this->pdo->prepare($sql)->execute($params);
    }

    public function delete(string $cuent): void
    {
        $this->pdo->prepare('DELETE FROM contr WHERE cuent = :c')->execute(['c' => $cuent]);
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function bind(array $data): array
    {
        $params = ['usuam' => $data['usuam'] ?? 'sistema'];
        foreach (self::COLUMNS as $col) {
            $value = $data[$col] ?? null;
            $params[$col] = $value === '' ? null : $value;
        }

        return $params;
    }
}
