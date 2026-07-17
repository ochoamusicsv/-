<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Billing\PenaltyCalculator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PenaltyCalculatorTest extends TestCase
{
    private PenaltyCalculator $calc;

    protected function setUp(): void
    {
        // monto_minimo 2.86, tasa diaria 0.001
        $this->calc = new PenaltyCalculator(2.86, 0.001);
    }

    public function testSinMultaDentroDeLosDiasDeGracia(): void
    {
        self::assertSame(0.0, $this->calc->multa(1000.0, 0));
        self::assertSame(0.0, $this->calc->multa(1000.0, 60));
    }

    public function testMultaMediaAplicaCincoPorCiento(): void
    {
        // 61..150 dias -> 5%; monto 1000 -> 50 (> minimo)
        self::assertSame(50.0, $this->calc->multa(1000.0, 61));
        self::assertSame(50.0, $this->calc->multa(1000.0, 150));
    }

    public function testMultaMediaRespetaMontoMinimo(): void
    {
        // monto pequeño: 5% de 10 = 0.5 -> se aplica el mínimo 2.86
        self::assertSame(2.86, $this->calc->multa(10.0, 90));
    }

    public function testMultaAltaAplicaDiezPorCiento(): void
    {
        self::assertSame(100.0, $this->calc->multa(1000.0, 151));
        self::assertSame(100.0, $this->calc->multa(1000.0, 400));
    }

    public function testMultaAltaRespetaMontoMinimo(): void
    {
        self::assertSame(2.86, $this->calc->multa(10.0, 200));
    }

    public function testInteresEsMontoMinimoPorTasaPorDias(): void
    {
        // 2.86 * 0.001 * 100 = 0.286 -> 0.29
        self::assertSame(0.29, $this->calc->interes(100));
    }

    public function testInteresCeroSinDias(): void
    {
        self::assertSame(0.0, $this->calc->interes(0));
        self::assertSame(0.0, $this->calc->interes(-5));
    }

    public function testDiasMoraCuentaDiferenciaEnDias(): void
    {
        $venc = new DateTimeImmutable('2026-01-01');
        $hoy = new DateTimeImmutable('2026-03-02'); // 60 dias
        self::assertSame(60, $this->calc->diasMora($venc, $hoy));
    }

    public function testDiasMoraNuncaNegativo(): void
    {
        $venc = new DateTimeImmutable('2026-05-01');
        $hoy = new DateTimeImmutable('2026-01-01');
        self::assertSame(0, $this->calc->diasMora($venc, $hoy));
    }

    public function testRecargoCombinaMultaEInteres(): void
    {
        $venc = new DateTimeImmutable('2026-01-01');
        $hoy = new DateTimeImmutable('2026-04-01'); // 90 dias -> multa media
        $r = $this->calc->recargo(1000.0, $venc, $hoy);

        self::assertSame(90, $r['dias_mora']);
        self::assertSame(50.0, $r['multa']);
        self::assertSame(round(2.86 * 0.001 * 90, 2), $r['interes']);
        self::assertSame(round(1000.0 + $r['multa'] + $r['interes'], 2), $r['total']);
    }
}
