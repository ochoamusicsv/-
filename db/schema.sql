-- Sistema de Recaudación Municipal — Esquema de base de datos
-- Motor: MySQL / MariaDB (XAMPP)
SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ---------------------------------------------------------------------------
-- CONFIG: parámetros globales del sistema (monto mínimo, tasa de interés, etc.)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS config (
    clave        VARCHAR(50)  NOT NULL PRIMARY KEY,
    valor        VARCHAR(100) NOT NULL,
    descripcion  VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- CONTR: contribuyentes
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contr (
    ndocu  VARCHAR(30)  NOT NULL,             -- Número de Documento
    tdocu  VARCHAR(20)  NULL,                 -- Tipo de Documento
    cuent  VARCHAR(20)  NOT NULL,             -- Cuenta
    nombr  VARCHAR(100) NOT NULL,             -- Nombre
    apell  VARCHAR(100) NULL,                 -- Apellidos
    gener  CHAR(1)      NULL,                 -- Género (M/F)
    zona   VARCHAR(60)  NULL,                 -- Zona
    refer  VARCHAR(150) NULL,                 -- Referencia
    distr  VARCHAR(60)  NULL,                 -- Distrito
    munic  VARCHAR(60)  NULL,                 -- Municipio
    depar  VARCHAR(60)  NULL,                 -- Departamento
    telef  VARCHAR(30)  NULL,                 -- Teléfono
    fechn  DATE         NULL,                 -- Fecha de Nacimiento
    estad  VARCHAR(20)  NULL DEFAULT 'ACTIVO',-- Estado
    fechr  DATE         NULL,                 -- Fecha de Registro
    profe  VARCHAR(80)  NULL,                 -- Profesión
    usuam  VARCHAR(40)  NULL,                 -- Usuario Modificación
    fechm  DATETIME     NULL,                 -- Fecha de Modificación
    PRIMARY KEY (cuent),
    UNIQUE KEY uq_contr_ndocu (ndocu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- TRIBU: catálogo de tributos
--   tipo = 'F' fijo (usa mtari) | 'V' variable (usa cod_v -> tarif_v)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tribu (
    codtr        VARCHAR(10)  NOT NULL,       -- Código de Tributo
    descl        VARCHAR(120) NOT NULL,       -- Descripción larga
    descc        VARCHAR(40)  NULL,           -- Descripción corta
    tipo         CHAR(1)      NOT NULL DEFAULT 'F', -- 'F' fijo | 'V' variable
    mtari        DECIMAL(14,2) NOT NULL DEFAULT 0,  -- Monto tarifa (si tipo=F)
    cod_v        VARCHAR(5)   NULL,           -- Código de tabla variable (V01..V16)
    periodicidad ENUM('MENSUAL','ANUAL') NOT NULL DEFAULT 'MENSUAL',
    PRIMARY KEY (codtr)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- TARIF_V: tablas de tarifa variable V01..V16 (tramos por valor de activo)
--   monto = fijo + exceso * (activo - inicio)   [supuesto documentado]
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tarif_v (
    cod_v   VARCHAR(5)    NOT NULL,           -- V01..V16
    descr   VARCHAR(120)  NULL,
    nivel   INT           NOT NULL,           -- Nivel (L)
    inicio  DECIMAL(16,2) NOT NULL,           -- Rango inicio
    final   DECIMAL(16,2) NOT NULL,           -- Rango final
    fijo    DECIMAL(14,4) NOT NULL DEFAULT 0, -- Monto fijo del tramo
    exceso  DECIMAL(14,4) NOT NULL DEFAULT 0, -- Factor sobre el excedente
    PRIMARY KEY (cod_v, nivel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- PROPI: propiedad (inmueble/empresa) asociada a una cuenta
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS propi (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    cuent  VARCHAR(20)   NOT NULL,            -- Cuenta (FK contr)
    codca  VARCHAR(30)   NOT NULL,            -- Código Catastral / Empresa
    tipoc  ENUM('INMUEBLE','EMPRESA') NOT NULL,
    frent  DECIMAL(12,2) NULL,               -- Medida Frente
    ladoa  DECIMAL(12,2) NULL,               -- Medida Izq
    ladob  DECIMAL(12,2) NULL,               -- Medida Der
    fondo  DECIMAL(12,2) NULL,               -- Medida Fondo
    areai  DECIMAL(14,2) NULL,               -- Área total inmueble
    areac  DECIMAL(14,2) NULL,               -- Área total construcción
    tipoi  VARCHAR(40)   NULL,               -- Tipo de inmueble
    cantp  INT           NULL,               -- Cantidad de pisos
    empre  VARCHAR(120)  NULL,               -- Nombre de la empresa
    tipoe  VARCHAR(60)   NULL,               -- Tipo de empresa
    tribu  VARCHAR(10)   NOT NULL,           -- Tributo (FK tribu)
    activ  DECIMAL(16,2) NOT NULL DEFAULT 0, -- Valor de activo
    monto  DECIMAL(14,2) NOT NULL DEFAULT 0, -- Monto tarifa calculado
    obser  VARCHAR(255)  NULL,
    UNIQUE KEY uq_propi (codca, tribu),
    KEY ix_propi_cuent (cuent),
    CONSTRAINT fk_propi_contr FOREIGN KEY (cuent) REFERENCES contr(cuent),
    CONSTRAINT fk_propi_tribu FOREIGN KEY (tribu) REFERENCES tribu(codtr)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- PARAM: parámetros de cobro mensual/anual (estado de cuenta por año)
--   F1..F12 = monto pagado por mes | fp = nº meses pagados
--   ma = meses antiguos (antes del año vigente) | perio = mes a cobrar
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS param (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    cuent  VARCHAR(20)   NOT NULL,
    codca  VARCHAR(30)   NOT NULL,
    tipoc  ENUM('INMUEBLE','EMPRESA') NOT NULL,
    tribu  VARCHAR(10)   NOT NULL,
    anio   INT           NOT NULL,           -- Año del ejercicio
    monto  DECIMAL(14,2) NOT NULL DEFAULT 0, -- Monto mensual a cobrar
    f1     DECIMAL(14,2) NOT NULL DEFAULT 0,
    f2     DECIMAL(14,2) NOT NULL DEFAULT 0,
    f3     DECIMAL(14,2) NOT NULL DEFAULT 0,
    f4     DECIMAL(14,2) NOT NULL DEFAULT 0,
    f5     DECIMAL(14,2) NOT NULL DEFAULT 0,
    f6     DECIMAL(14,2) NOT NULL DEFAULT 0,
    f7     DECIMAL(14,2) NOT NULL DEFAULT 0,
    f8     DECIMAL(14,2) NOT NULL DEFAULT 0,
    f9     DECIMAL(14,2) NOT NULL DEFAULT 0,
    f10    DECIMAL(14,2) NOT NULL DEFAULT 0,
    f11    DECIMAL(14,2) NOT NULL DEFAULT 0,
    f12    DECIMAL(14,2) NOT NULL DEFAULT 0,
    fp     INT           NOT NULL DEFAULT 0,  -- Número de meses pagados
    ma     DECIMAL(14,2) NOT NULL DEFAULT 0,  -- Saldo de meses antiguos
    perio  INT           NULL,               -- Periodo (mes) a cobrar 1..12
    UNIQUE KEY uq_param (codca, tribu, anio),
    KEY ix_param_cuent (cuent),
    CONSTRAINT fk_param_tribu FOREIGN KEY (tribu) REFERENCES tribu(codtr)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- FDIAR: facturas del día (cobros aplicados, pendientes de cierre)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fdiar (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    cuent   VARCHAR(20)   NOT NULL,
    codca   VARCHAR(30)   NOT NULL,
    tipoc   ENUM('INMUEBLE','EMPRESA') NOT NULL,
    tribu   VARCHAR(10)   NOT NULL,
    corre   VARCHAR(20)   NOT NULL,          -- Correlativo de recibo
    perio   VARCHAR(7)    NOT NULL,          -- Periodo pagado (YYYY-MM)
    monto   DECIMAL(14,2) NOT NULL DEFAULT 0,-- Monto mensual
    fechp   DATE          NOT NULL,          -- Fecha de pago
    multa   DECIMAL(14,2) NOT NULL DEFAULT 0,-- Multa pagada
    inter   DECIMAL(14,2) NOT NULL DEFAULT 0,-- Interés pagado
    usuam   VARCHAR(40)   NULL,
    UNIQUE KEY uq_fdiar_corre (corre),
    KEY ix_fdiar_fechp (fechp),
    KEY ix_fdiar_cuent (cuent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- FHISTO: historial (generado por el cierre diario a partir de FDIAR)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fhisto (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    cuent   VARCHAR(20)   NOT NULL,
    codca   VARCHAR(30)   NOT NULL,
    tipoc   ENUM('INMUEBLE','EMPRESA') NOT NULL,
    tribu   VARCHAR(10)   NOT NULL,
    corre   VARCHAR(20)   NOT NULL,
    perio   VARCHAR(7)    NOT NULL,
    monto   DECIMAL(14,2) NOT NULL DEFAULT 0,
    fechp   DATE          NOT NULL,
    multa   DECIMAL(14,2) NOT NULL DEFAULT 0,
    inter   DECIMAL(14,2) NOT NULL DEFAULT 0,
    fecha_cierre DATE     NOT NULL,          -- Fecha del cierre que lo archivó
    UNIQUE KEY uq_fhisto_corre (corre),
    KEY ix_fhisto_cuent (cuent),
    KEY ix_fhisto_cierre (fecha_cierre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- CIERRE: cabecera de cierres diarios
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cierre (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    fecha_cierre DATE          NOT NULL,
    recibos      INT           NOT NULL DEFAULT 0,
    total_monto  DECIMAL(16,2) NOT NULL DEFAULT 0,
    total_multa  DECIMAL(16,2) NOT NULL DEFAULT 0,
    total_inter  DECIMAL(16,2) NOT NULL DEFAULT 0,
    total_general DECIMAL(16,2) NOT NULL DEFAULT 0,
    usuario      VARCHAR(40)   NULL,
    creado_en    DATETIME      NOT NULL,
    UNIQUE KEY uq_cierre_fecha (fecha_cierre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
