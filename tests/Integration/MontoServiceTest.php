<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Repository\TarifaRepository;
use App\Repository\TributoRepository;
use App\Service\MontoService;

final class MontoServiceTest extends DatabaseTestCase
{
    private function service(): MontoService
    {
        return new MontoService(
            new TributoRepository(self::$pdo),
            new TarifaRepository(self::$pdo),
        );
    }

    public function testTributoFijoUsaMtari(): void
    {
        // TREN es fijo con mtari 2.86
        self::assertSame(2.86, $this->service()->montoMensual('TREN', 5000.0));
    }

    public function testTributoVariableUsaTablaV(): void
    {
        // COM -> V01; activo 100 cae en nivel 1 (fijo 0.34)
        self::assertSame(0.34, $this->service()->montoMensual('COM', 100.0));
        // activo 1000 -> nivel 3 (1.14)
        self::assertSame(1.14, $this->service()->montoMensual('COM', 1000.0));
    }

    public function testTributoIndustrialConExceso(): void
    {
        // IND -> V03 nivel 2: 0.34 + 0.30*(1000-571.44)
        $esperado = round(0.34 + 0.30 * (1000.0 - 571.44), 2);
        self::assertSame($esperado, $this->service()->montoMensual('IND', 1000.0));
    }

    public function testTributoInexistenteLanza(): void
    {
        $this->expectExceptionMessageMatches('/no encontrado/i');
        $this->service()->montoMensual('XXX', 10.0);
    }
}
