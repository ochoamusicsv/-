<?php

declare(strict_types=1);

namespace App\Service;

use App\Support\Money;
use DateTimeInterface;
use PDO;
use RuntimeException;

/**
 * Cierre diario: archiva las facturas del día (fdiar) en el historial (fhisto),
 * registra la cabecera del cierre con totales y vacía fdiar de esa fecha.
 */
final class CierreService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{fecha:string,recibos:int,total_monto:float,total_multa:float,total_inter:float,total_general:float}
     */
    public function cerrarDia(DateTimeInterface $fecha, string $usuario = 'sistema'): array
    {
        $fechaStr = $fecha->format('Y-m-d');

        $existe = $this->pdo->prepare('SELECT 1 FROM cierre WHERE fecha_cierre = :f');
        $existe->execute(['f' => $fechaStr]);
        if ($existe->fetchColumn() !== false) {
            throw new RuntimeException("El día {$fechaStr} ya tiene cierre.");
        }

        $this->pdo->beginTransaction();
        try {
            $pend = $this->pdo->prepare('SELECT * FROM fdiar WHERE fechp = :f FOR UPDATE');
            $pend->execute(['f' => $fechaStr]);
            $rows = $pend->fetchAll();

            if ($rows === []) {
                $this->pdo->rollBack();
                throw new RuntimeException("No hay facturas del día {$fechaStr} para cerrar.");
            }

            $insert = $this->pdo->prepare(
                'INSERT INTO fhisto (cuent, codca, tipoc, tribu, corre, perio, monto, fechp, multa, inter, fecha_cierre)
                 VALUES (:cuent, :codca, :tipoc, :tribu, :corre, :perio, :monto, :fechp, :multa, :inter, :fcierre)'
            );

            $totMonto = $totMulta = $totInter = 0.0;
            foreach ($rows as $r) {
                $insert->execute([
                    'cuent' => $r['cuent'], 'codca' => $r['codca'], 'tipoc' => $r['tipoc'],
                    'tribu' => $r['tribu'], 'corre' => $r['corre'], 'perio' => $r['perio'],
                    'monto' => $r['monto'], 'fechp' => $r['fechp'], 'multa' => $r['multa'],
                    'inter' => $r['inter'], 'fcierre' => $fechaStr,
                ]);
                $totMonto += (float) $r['monto'];
                $totMulta += (float) $r['multa'];
                $totInter += (float) $r['inter'];
            }

            $this->pdo->prepare('DELETE FROM fdiar WHERE fechp = :f')->execute(['f' => $fechaStr]);

            $totMonto = Money::round($totMonto);
            $totMulta = Money::round($totMulta);
            $totInter = Money::round($totInter);
            $totGeneral = Money::round($totMonto + $totMulta + $totInter);

            $this->pdo->prepare(
                'INSERT INTO cierre (fecha_cierre, recibos, total_monto, total_multa, total_inter, total_general, usuario, creado_en)
                 VALUES (:f, :recibos, :tm, :tmu, :ti, :tg, :u, NOW())'
            )->execute([
                'f' => $fechaStr, 'recibos' => count($rows), 'tm' => $totMonto,
                'tmu' => $totMulta, 'ti' => $totInter, 'tg' => $totGeneral, 'u' => $usuario,
            ]);

            $this->pdo->commit();

            return [
                'fecha'         => $fechaStr,
                'recibos'       => count($rows),
                'total_monto'   => $totMonto,
                'total_multa'   => $totMulta,
                'total_inter'   => $totInter,
                'total_general' => $totGeneral,
            ];
        } catch (RuntimeException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
