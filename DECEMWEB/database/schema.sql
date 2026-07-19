-- =============================================================
--  DECEM · Sistema de Recaudación
--  Esquema de base de datos y datos iniciales.
--
--  Uso en XAMPP:
--    1. Abrir phpMyAdmin (http://localhost/phpmyadmin).
--    2. Pestaña "Importar" y seleccionar este archivo, o
--       ejecutar su contenido en la pestaña "SQL".
-- =============================================================

CREATE DATABASE IF NOT EXISTS `decem`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `decem`;

-- -------------------------------------------------------------
--  Tabla: usuarios
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`        VARCHAR(150) NULL,
    `usuario`       VARCHAR(100) NOT NULL,
    `clave`         VARCHAR(255) NOT NULL,
    `activo`        TINYINT(1)   NOT NULL DEFAULT 1,
    `fechacreacion` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Usuario administrador por defecto.
--  Credenciales:  usuario = admin   contraseña = admin123
--  (El hash corresponde a password_hash('admin123', PASSWORD_DEFAULT).)
--
--  IMPORTANTE: cambia esta contraseña después del primer inicio de sesión.
-- -------------------------------------------------------------
INSERT INTO `usuarios` (`nombre`, `usuario`, `clave`, `activo`)
SELECT 'Administrador', 'admin',
       '$2y$10$Etn4a67OhO88uKtfzqqOh.ZeZizUNe8MrHsl7c0jc6f8L1wFztSsW', 1
WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `usuario` = 'admin');
