# Test Plan — Municipal Tax Collection System (PR #2)

App: http://127.0.0.1:8000/index.php (PHP dev server, DB `recaudacion`, freshly reloaded).

Math verified against code/seed:
- V03 tramo2 (inicio 571.44, fijo 0.34, exceso 0.3), activo 1000 → 0.34 + 0.3×(1000−571.44) = **128.91**
- TREN fixed mtari → **2.86**
- periodo 2026-01 (venc 2026-01-01) paid 2026-06-01 = 151 days (>150): multa 128.91×0.10 = **12.89**, interés 2.86×0.001×151 = **0.43**

## Test 1 — Create contribuyente + search + edit
1. Contribuyentes → create: Cuenta `9001`, Nº Documento `DOC-9001`, Nombre `Prueba QA`. Save.
   - PASS: success flash, row `9001 / DOC-9001 / Prueba QA` in list.
2. Search box: type `9001`, Buscar. PASS: only matching row shown. Type `zzz`: "Sin registros."
3. Edit `9001`: change Teléfono to `7777-7777`, Actualizar. PASS: list shows phone `7777-7777`.
4. Adversarial: create with empty Nombre → browser required validation blocks submit (no new row). Note behavior.

## Test 2 — Propiedad monto auto-calc (VARIABLE + FIXED)
1. Propiedades → create: Cuenta `9001`, Código `CAT-9001`, Tributo `IND ... (Variable V03)`, Valor de Activo `1000`. Save.
   - PASS: flash "Monto calculado: $128.91"; list row shows Activo 1000.00, Monto **$128.91**. FAIL if any other value.
2. Create second propiedad: Cuenta `9001`, Código `CAT-9002`, Tributo `TREN (Fijo)`, activo left 0. Save.
   - PASS: flash "Monto calculado: $2.86"; list Monto **$2.86**.

## Test 3 — Facturación with mora
1. Facturación → select propiedad `CAT-9001` (128.91), Periodo `2026-01`, Fecha de pago `2026-06-01`, Aplicar cobro.
   - PASS: flash "Monto $128.91 + Multa $12.89 + Interés $0.43 = Total $142.23 (151 días de mora)".
   - Note: "Facturas del día" table is keyed to today's date, so the new recibo may NOT appear there (fechp=2026-06-01). Verify recibo via DB / cierre page instead.
2. Double payment: submit same propiedad + periodo `2026-01` + same date again.
   - PASS: danger flash "El periodo 2026-01 ya fue pagado para CAT-9001/IND."
3. Adversarial bad periodo: enter `2026-13` (pattern allows digits; service should reject) → observe. Enter `abc` → HTML pattern blocks.

## Test 4 — Cierre diario
1. Cierre → set Fecha `2026-06-01`, Ver resumen.
   - PASS: Recibos 1, Monto 128.91, Multa 12.89, Interés 0.43, Total general 142.23. Cerrar button enabled.
2. Click "Cerrar día 2026-06-01", confirm.
   - PASS: flash "Cierre 2026-06-01: 1 recibos, monto $128.91, multa $12.89, interés $0.43, total $142.23." Cierres del mes table shows the row.
3. Re-run resumen for 2026-06-01: Recibos 0, Cerrar button disabled (fdiar emptied).
4. Second cierre same date: (button disabled with 0 recibos) — confirm cannot re-close; if forced, expect "ya tiene cierre".
5. Empty-day cierre: pick a date with no facturas (e.g. today) → button disabled.

## Test 5 — Reportería
1. Reportes → Recaudación por cierre: Desde `2026-06-01` Hasta `2026-06-01`, Filtrar.
   - PASS: row fecha_cierre 2026-06-01, Recibos 1, Total $142.23; "Total recaudado $142.23".
2. Clientes en mora: Año `2026`, Hasta el mes `12`, Filtrar.
   - PASS: `9001 / Prueba QA / CAT-9001` appears with meses_pendientes = 11 (paid Jan only) and a deuda_estimada > 0.

## Evidence
- Screenshot each flash/result. Watch /tmp/phpserver.log for PHP warnings/notices after each action.
