# Test Report — Municipal Tax Collection System (PR #2)

**Branch:** `devin/1784224795-sistema-recaudacion` · **Repo:** ochoamusicsv/-
**How tested:** Ran the PHP dev server locally against a freshly-loaded `recaudacion` MariaDB, and exercised every golden path through the browser UI. Unit/integration suite (`composer test`) = 30 tests OK.

## Summary of results

| # | Test | Result |
|---|------|--------|
| 1a | Create contribuyente + appears in list | ✅ Pass |
| 1b | **Search box (`?q=`)** | ❌ **FAIL — HTTP 500** |
| 1c | Edit contribuyente (phone) | ✅ Pass |
| 2a | Variable tributo V03, activo 1000 → $128.91 | ✅ Pass |
| 2b | Fixed tributo TREN → $2.86 | ✅ Pass |
| 3a | Mora recibo: $128.91 + $12.89 + $0.43 = $142.23 (151 días) | ✅ Pass |
| 3b | Double-payment rejected | ✅ Pass |
| 3c | Invalid periodo `2026-13` rejected | ✅ Pass |
| 4a | Cierre resumen for 2026-06-01 | ✅ Pass |
| 4b | Execute cierre; fdiar emptied; appears in Cierres del mes | ✅ Pass |
| 4c | Double cierre rejected (backend) | ✅ Pass |
| 4d | Empty-day cierre → button disabled | ✅ Pass |
| 5a | Reportería: Clientes en mora | ✅ Pass |
| 5b | Reportería: Recaudación por cierre | ✅ Pass |

---

## ❌ FAIL — Contribuyentes search returns HTTP 500

Typing anything into the "Buscar" box (i.e. requesting `contribuyentes.php?q=9001`) crashes with **HTTP 500**.

**Root cause:** `src/Repository/ContribuyenteRepository.php:31` reuses the named placeholder `:q` four times:
```sql
SELECT * FROM contr WHERE nombr LIKE :q OR apell LIKE :q OR cuent LIKE :q OR ndocu LIKE :q ORDER BY nombr
```
`src/Database.php:34` sets `PDO::ATTR_EMULATE_PREPARES => false`, and native MySQL prepares do **not** allow reusing the same named placeholder. Server log:
```
PHP Fatal error: Uncaught PDOException: SQLSTATE[HY093]: Invalid parameter number
  in ContribuyenteRepository.php:33
```
**Suggested fix:** either enable emulated prepares, or use 4 distinct placeholders (`:q1..:q4`) bound to the same value.

![Search returns HTTP 500](https://app.devin.ai/attachments/dbc2df90-53be-4ea4-84e8-c34794386d16/ss_1bdc8d93.png)

> Note: the unit/integration suite passes because it doesn't exercise the multi-placeholder search path through a real MySQL connection with emulation off.

---

## ⚠️ Minor — PHP warning on malformed cierre POST

`public/cierre.php:15` reads `$_POST['fecha']` without a null-coalescing guard, so a POST lacking `fecha` emits `PHP Warning: Undefined array key "fecha"`. The normal UI path always sends the hidden `fecha` field, so it is not user-facing, but it's worth hardening. No other warnings/notices were observed on the golden paths.

---

## Passing evidence

### Contribuyente created
![Contribuyente 9001 created](https://app.devin.ai/attachments/361ed06f-30be-4684-aba9-bf522a50832b/ss_84c237eb.png)

### Tariff math — Variable V03 (activo 1000 → $128.91)
Formula `fijo + exceso×(activo−inicio)` = `0.34 + 0.3×(1000−571.44)` = **128.91**.
![Variable tributo $128.91](https://app.devin.ai/attachments/25f05dcd-c3a7-4082-850b-7a71e1c2683e/ss_de221346.png)

### Tariff math — Fixed TREN → $2.86
![Fixed tributo $2.86](https://app.devin.ai/attachments/5d1e4a56-dda4-4f4f-ae22-182e0ad3fde2/ss_57e5f598.png)

### Mora — recibo with multa & interés
Periodo 2026-01 (venc 2026-01-01) paid 2026-06-01 = 151 days (>150) → multa 128.91×0.10=**12.89**, interés 2.86×0.001×151=**0.43**, total **142.23**.
![Recibo con mora](https://app.devin.ai/attachments/5aa4c0ea-2274-4ce7-baeb-08db6942b348/ss_a5bb18a1.png)

### Double-payment rejected
![Double payment rejected](https://app.devin.ai/attachments/ffb3d03f-e256-4040-9a7f-854dcd2e01d3/ss_d5ad6a9d.png)

### Invalid periodo rejected
![Invalid periodo 2026-13](https://app.devin.ai/attachments/14f37554-eed5-4296-a232-511bc1edb418/ss_b7c42e18.png)

### Cierre resumen
![Cierre resumen 2026-06-01](https://app.devin.ai/attachments/02075849-6668-4c51-be81-c990b425b4b7/ss_5f7f7f99.png)

### Cierre executed — fdiar emptied, cierre archived
![Cierre archived](https://app.devin.ai/attachments/20144755-75ec-4605-b05f-a5d812aac1df/ss_120b04c1.png)

Backend double-cierre guard (via direct POST, button is disabled in UI):
```
Error: El día 2026-06-01 ya tiene cierre.
```

### Reportería — mora & recaudación
Clientes en mora: CAT-9001, 6 meses pendientes × 128.91 = **$773.46**. Recaudación por cierre: 2026-06-01, 1 recibo, **$142.23**.
![Reportería](https://app.devin.ai/attachments/0007f9bf-b00a-4260-b917-256cb3335cea/ss_b109a290.png)
