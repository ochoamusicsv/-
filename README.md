# Sistema de Recaudación Municipal

Sistema para la gestión de recaudación de tributos municipales: registro de
contribuyentes, propiedad/empresa, parámetros de cobro, facturación diaria,
cierres diarios con historial y reportería de mora.

Stack: **PHP 8** + **MySQL/MariaDB** (XAMPP) + **PHPUnit**.

## Estructura

```
db/                 Esquema y datos semilla (SQL)
  schema.sql        Todas las tablas
  seed_base.sql     Config global + catálogo de tributos de ejemplo
  seed_tarifas.sql  Tablas de tarifa variable V01..V16
src/                Lógica de la aplicación (PSR-4  App\)
  Config.php, Database.php
  Support/Money.php
  Tariff/           Cálculo de tarifa variable por rango de activo
  Billing/          Cálculo de multa e interés por mora
  Repository/       Acceso a datos (PDO)
  Service/          MontoService, FacturaService, CierreService
public/             Interfaz web (Bootstrap)
tests/Unit          Pruebas puras (tarifas, multa, interés)
tests/Integration   Pruebas contra MySQL de prueba
```

## Modelo de datos

| Tabla    | Descripción |
|----------|-------------|
| `contr`  | Contribuyentes |
| `propi`  | Propiedad/empresa por cuenta |
| `param`  | Parámetros de cobro anual (F1..F12, meses pagados, saldo antiguo) |
| `tribu`  | Catálogo de tributos (fijo `F` / variable `V`) |
| `tarif_v`| Tramos de tarifa variable V01..V16 |
| `fdiar`  | Facturas del día (pendientes de cierre) |
| `fhisto` | Historial (generado por el cierre) |
| `cierre` | Cabecera de cierres diarios |

## Reglas de negocio

- **Tarifa variable (V01..V16):** para tributos tipo `V`, el monto se calcula según
  el valor de activo (`activ`) buscando el tramo `inicio ≤ activo ≤ final`:

  ```
  monto = fijo + exceso * (activo - inicio)
  ```

- **Multa por mora** (días = fecha de pago − vencimiento):
  - ≤ 60 días → 0
  - ≤ 150 días → `max(monto * 0.05, 2.86)`
  - > 150 días → `max(monto * 0.10, 2.86)`

- **Interés por mora:** `monto_mínimo(2.86) * tasa_diaria * días` (aplica pasados
  los días de gracia).

Los parámetros globales (`monto_minimo`, `tasa_interes_diario`, `dias_gracia`,
`dias_multa_media`) viven en la tabla `config`.

## Instalación

```bash
composer install

# Crear base y cargar esquema + datos
mysql -u root -e "CREATE DATABASE recaudacion CHARACTER SET utf8mb4"
mysql -u root recaudacion < db/schema.sql
mysql -u root recaudacion < db/seed_base.sql
mysql -u root recaudacion < db/seed_tarifas.sql
```

Configura la conexión con variables de entorno (valores por defecto para XAMPP):
`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.

## Ejecutar

```bash
# Servidor de desarrollo
php -S localhost:8000 -t public
# En XAMPP, copia el proyecto a htdocs y visita http://localhost/<carpeta>/public/
```

## Pruebas

```bash
composer test                 # todas
vendor/bin/phpunit --testsuite unit          # sólo lógica pura (sin BD)
vendor/bin/phpunit --testsuite integration   # requiere MySQL de prueba
```

Las pruebas de integración usan una base `test_recaudacion` (configurable con
`TEST_DB_HOST/PORT/NAME/USER/PASS`).
