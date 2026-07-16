-- Seed base: parámetros globales y catálogo de tributos.
SET NAMES utf8mb4;

-- Parámetros globales del sistema
INSERT INTO config (clave, valor, descripcion) VALUES
    ('monto_minimo',        '2.86',  'Monto mínimo aplicable a multas e intereses'),
    ('tasa_interes_diario', '0.001', 'Tasa de interés diaria por mora'),
    ('dias_gracia',         '60',    'Días de gracia antes de aplicar multa'),
    ('dias_multa_media',    '150',   'Límite de días para multa del 5% (luego 10%)')
ON DUPLICATE KEY UPDATE valor = VALUES(valor), descripcion = VALUES(descripcion);

-- Catálogo de tributos (ejemplos: fijos y variables ligados a tablas V)
INSERT INTO tribu (codtr, descl, descc, tipo, mtari, cod_v, periodicidad) VALUES
    ('IB',  'Impuesto de Bienes Inmuebles', 'BIENES INM', 'F', 5.00, NULL,  'MENSUAL'),
    ('TREN','Tren de Aseo',                 'ASEO',       'F', 2.86, NULL,  'MENSUAL'),
    ('COM', 'Comerciantes Sociales/Indiv',  'COMERCIANTE','V', 0.00, 'V01', 'MENSUAL'),
    ('IND', 'Empresa Industrial/Fábrica',   'INDUSTRIA',  'V', 0.00, 'V03', 'MENSUAL'),
    ('AGRO','Empresa Agropecuaria Establo', 'AGROPEC',    'V', 0.00, 'V04', 'MENSUAL'),
    ('TIEN','Tiendas',                      'TIENDAS',    'V', 0.00, 'V10', 'MENSUAL'),
    ('FIN', 'Instituciones Financieras',    'FINANCIERA', 'V', 0.00, 'V14', 'MENSUAL')
ON DUPLICATE KEY UPDATE descl = VALUES(descl), descc = VALUES(descc),
    tipo = VALUES(tipo), mtari = VALUES(mtari), cod_v = VALUES(cod_v),
    periodicidad = VALUES(periodicidad);
