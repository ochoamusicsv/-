<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Repository\ContribuyenteRepository;

final class ContribuyenteRepositoryTest extends DatabaseTestCase
{
    public function testBusquedaConMultiplesCriterios(): void
    {
        $this->seedContribuyente('7001', 'DOC-7001');
        $repo = new ContribuyenteRepository(self::$pdo);

        // Reutilizar el mismo texto en varias columnas no debe romper la consulta
        // (regresión: placeholder repetido con EMULATE_PREPARES=false).
        self::assertCount(1, $repo->all('Juan'));
        self::assertCount(1, $repo->all('7001'));
        self::assertCount(1, $repo->all('DOC-7001'));
        self::assertCount(0, $repo->all('inexistente'));
    }

    public function testListadoCompletoSinFiltro(): void
    {
        $this->seedContribuyente('7001', 'DOC-7001');
        $this->seedContribuyente('7002', 'DOC-7002');
        self::assertCount(2, (new ContribuyenteRepository(self::$pdo))->all());
    }
}
