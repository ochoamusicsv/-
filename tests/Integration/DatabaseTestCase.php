<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base para pruebas de integración contra una base MySQL de pruebas.
 * Carga el esquema una vez y trunca las tablas antes de cada prueba.
 *
 * Configurable con TEST_DB_HOST/PORT/NAME/USER/PASS (defaults para XAMPP local).
 */
abstract class DatabaseTestCase extends TestCase
{
    protected static ?PDO $pdo = null;

    public static function setUpBeforeClass(): void
    {
        if (self::$pdo instanceof PDO) {
            return;
        }

        $host = getenv('TEST_DB_HOST') ?: '127.0.0.1';
        $port = getenv('TEST_DB_PORT') ?: '3306';
        $name = getenv('TEST_DB_NAME') ?: 'test_recaudacion';
        $user = getenv('TEST_DB_USER') ?: 'recaud';
        $pass = getenv('TEST_DB_PASS') ?: 'recaud';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        $dir = dirname(__DIR__, 2) . '/db';
        self::runSqlFile($dir . '/schema.sql');
        self::runSqlFile($dir . '/seed_base.sql');
        self::runSqlFile($dir . '/seed_tarifas.sql');
    }

    private static function runSqlFile(string $path): void
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            self::fail("No se pudo leer {$path}");
        }
        self::$pdo->exec($sql);
    }

    protected function setUp(): void
    {
        $this->truncate(['fhisto', 'fdiar', 'cierre', 'param', 'propi', 'contr']);
    }

    /** @param list<string> $tables */
    protected function truncate(array $tables): void
    {
        self::$pdo->exec('SET foreign_key_checks = 0');
        foreach ($tables as $t) {
            self::$pdo->exec("TRUNCATE TABLE {$t}");
        }
        self::$pdo->exec('SET foreign_key_checks = 1');
    }

    protected function seedContribuyente(string $cuent = '1001', string $ndocu = 'DOC-1001'): void
    {
        self::$pdo->prepare(
            'INSERT INTO contr (ndocu, tdocu, cuent, nombr, apell, estad, fechr)
             VALUES (:n, :t, :c, :nom, :ape, :e, CURDATE())'
        )->execute([
            'n' => $ndocu, 't' => 'DUI', 'c' => $cuent,
            'nom' => 'Juan', 'ape' => 'Perez', 'e' => 'ACTIVO',
        ]);
    }
}
