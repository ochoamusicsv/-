<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Billing\PenaltyCalculator;
use App\Repository\FacturaRepository;
use App\Repository\ParamRepository;
use App\Repository\PropiedadRepository;
use App\Repository\ReporteRepository;
use App\Service\CierreService;
use App\Service\FacturaService;
use DateTimeImmutable;
use RuntimeException;

final class FacturaCierreTest extends DatabaseTestCase
{
    private FacturaService $facturaSvc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedContribuyente('1001');
        (new PropiedadRepository(self::$pdo))->create([
            'cuent' => '1001', 'codca' => 'CAT-1', 'tipoc' => 'EMPRESA',
            'tribu' => 'COM', 'activ' => 1000.0, 'monto' => 1.14, 'empre' => 'Tienda X',
        ]);

        $this->facturaSvc = new FacturaService(
            new FacturaRepository(self::$pdo),
            new ParamRepository(self::$pdo),
            new PenaltyCalculator(2.86, 0.001),
        );
    }

    public function testAplicarCobroGeneraReciboYMarcaMes(): void
    {
        $pago = $this->facturaSvc->aplicarCobro(
            '1001', 'CAT-1', 'EMPRESA', 'COM', '2026-01', 1.14,
            new DateTimeImmutable('2026-01-15')
        );

        self::assertSame('00000001', $pago['corre']);
        self::assertSame(0.0, $pago['multa']); // dentro de gracia
        self::assertSame(1.14, $pago['total']);

        $param = self::$pdo->query("SELECT f1, fp FROM param WHERE codca='CAT-1'")->fetch();
        self::assertGreaterThan(0, (float) $param['f1']);
        self::assertSame(1, (int) $param['fp']);
    }

    public function testCobroConMoraAplicaMultaEInteres(): void
    {
        // Vence 2026-01-01, se paga 2026-06-01 -> ~151 dias -> multa 10%
        $pago = $this->facturaSvc->aplicarCobro(
            '1001', 'CAT-1', 'EMPRESA', 'COM', '2026-01', 1000.0,
            new DateTimeImmutable('2026-06-01')
        );

        self::assertGreaterThan(150, $pago['dias_mora']);
        self::assertSame(100.0, $pago['multa']);
        self::assertGreaterThan(0.0, $pago['interes']);
    }

    public function testNoPermiteDoblePagoDelMismoMes(): void
    {
        $this->facturaSvc->aplicarCobro('1001', 'CAT-1', 'EMPRESA', 'COM', '2026-01', 1.14, new DateTimeImmutable('2026-01-15'));
        $this->expectException(RuntimeException::class);
        $this->facturaSvc->aplicarCobro('1001', 'CAT-1', 'EMPRESA', 'COM', '2026-01', 1.14, new DateTimeImmutable('2026-01-16'));
    }

    public function testCierreDiarioArchivaYVaciaFdiar(): void
    {
        $this->facturaSvc->aplicarCobro('1001', 'CAT-1', 'EMPRESA', 'COM', '2026-01', 10.0, new DateTimeImmutable('2026-03-10'));
        $this->facturaSvc->aplicarCobro('1001', 'CAT-1', 'EMPRESA', 'COM', '2026-02', 10.0, new DateTimeImmutable('2026-03-10'));

        $cierre = new CierreService(self::$pdo);
        $res = $cierre->cerrarDia(new DateTimeImmutable('2026-03-10'));

        self::assertSame(2, $res['recibos']);
        self::assertSame(20.0, $res['total_monto']);

        self::assertSame(0, (int) self::$pdo->query('SELECT COUNT(*) FROM fdiar')->fetchColumn());
        self::assertSame(2, (int) self::$pdo->query('SELECT COUNT(*) FROM fhisto')->fetchColumn());
    }

    public function testNoSePuedeCerrarDosVeces(): void
    {
        $this->facturaSvc->aplicarCobro('1001', 'CAT-1', 'EMPRESA', 'COM', '2026-01', 10.0, new DateTimeImmutable('2026-03-10'));
        $cierre = new CierreService(self::$pdo);
        $cierre->cerrarDia(new DateTimeImmutable('2026-03-10'));

        $this->expectException(RuntimeException::class);
        $cierre->cerrarDia(new DateTimeImmutable('2026-03-10'));
    }

    public function testReporteMoraDetectaMesesPendientes(): void
    {
        // Paga sólo enero; a mayo (hastaMes=5) faltan feb-may = 4 meses
        $this->facturaSvc->aplicarCobro('1001', 'CAT-1', 'EMPRESA', 'COM', '2026-01', 10.0, new DateTimeImmutable('2026-01-10'));

        $rep = new ReporteRepository(self::$pdo);
        $mora = $rep->clientesEnMora(2026, 5);

        self::assertCount(1, $mora);
        self::assertSame(4, (int) $mora[0]['meses_pendientes']);
        self::assertSame('CAT-1', $mora[0]['codca']);
    }
}
