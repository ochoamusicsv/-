<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Tariff\Bracket;
use App\Tariff\TariffCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TariffCalculatorTest extends TestCase
{
    private TariffCalculator $calc;

    protected function setUp(): void
    {
        $this->calc = new TariffCalculator();
    }

    /** @return list<Bracket> V01 COMERCIANTES SOCIALES O INDIV */
    private function v01(): array
    {
        return [
            new Bracket(1, 0.00, 228.57, 0.34, 0.0),
            new Bracket(2, 228.58, 571.43, 0.57, 0.0),
            new Bracket(3, 571.44, 1142.86, 1.14, 0.0),
            new Bracket(4, 1142.87, 9999999999.99, 1.14, 1.0),
        ];
    }

    /** @return list<Bracket> V03 EMPR.INDUST (tramos con exceso) */
    private function v03(): array
    {
        return [
            new Bracket(1, 0.01, 571.43, 0.34, 0.0),
            new Bracket(2, 571.44, 2857.14, 0.34, 0.30),
            new Bracket(3, 2857.15, 5714.29, 0.86, 0.29),
        ];
    }

    public function testTramoFijoSinExceso(): void
    {
        // activo 100 cae en nivel 1 de V01 -> monto = 0.34
        self::assertSame(0.34, $this->calc->calcular($this->v01(), 100.0));
        // activo 500 cae en nivel 2 -> 0.57
        self::assertSame(0.57, $this->calc->calcular($this->v01(), 500.0));
        // activo 1000 cae en nivel 3 -> 1.14
        self::assertSame(1.14, $this->calc->calcular($this->v01(), 1000.0));
    }

    public function testTramoConExcesoAplicaFactorSobreExcedente(): void
    {
        // V01 nivel 4: fijo 1.14 + exceso 1 * (activo - 1142.87)
        $activo = 1200.0;
        $esperado = 1.14 + 1.0 * ($activo - 1142.87); // 58.27
        self::assertSame(round($esperado, 2), $this->calc->calcular($this->v01(), $activo));
    }

    public function testExcesoFraccionario(): void
    {
        // V03 nivel 2: fijo 0.34 + 0.30 * (1000 - 571.44)
        $activo = 1000.0;
        $esperado = round(0.34 + 0.30 * ($activo - 571.44), 2);
        self::assertSame($esperado, $this->calc->calcular($this->v03(), $activo));
    }

    public function testLimiteInferiorExactoPerteneceAlTramo(): void
    {
        self::assertSame(0.34, $this->calc->calcular($this->v01(), 0.0));
        self::assertSame(0.57, $this->calc->calcular($this->v01(), 228.58));
    }

    public function testActivoDebajoDelPrimerTramoUsaPrimerTramo(): void
    {
        // V03 empieza en 0.01; activo 0 -> usa nivel 1 (fijo 0.34)
        self::assertSame(0.34, $this->calc->calcular($this->v03(), 0.0));
    }

    public function testActivoSobreElUltimoTramoUsaUltimoTramo(): void
    {
        // V03 sin infinito; activo enorme -> nivel 3
        $activo = 9_000_000.0;
        $esperado = round(0.86 + 0.29 * ($activo - 2857.15), 2);
        self::assertSame($esperado, $this->calc->calcular($this->v03(), $activo));
    }

    public function testEncontrarTramoDevuelveNivelCorrecto(): void
    {
        self::assertSame(2, $this->calc->encontrarTramo($this->v01(), 300.0)->nivel);
    }

    public function testOrdenIndependiente(): void
    {
        $desordenado = array_reverse($this->v01());
        self::assertSame(0.57, $this->calc->calcular($desordenado, 500.0));
    }

    public function testTablaVaciaLanzaExcepcion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calc->calcular([], 100.0);
    }

    public function testMontoNuncaNegativo(): void
    {
        $brackets = [new Bracket(1, 0.0, 100.0, 0.0, -5.0)];
        self::assertSame(0.0, $this->calc->calcular($brackets, 50.0));
    }
}
