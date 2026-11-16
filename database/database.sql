-- =====================================================================
-- Archivo : database/database.sql
-- Proyecto: Proyecto Login
-- Motor   : MySQL 8 o MariaDB (XAMPP). Ejecutar completo en MySQL Workbench.
-- Tablas  : roles, usuarios, accesos
-- =====================================================================

-- Se elimina la base si ya existía, para poder ejecutar el script desde cero.
DROP DATABASE IF EXISTS login_db;

-- Se crea la base de datos con soporte para tildes y ñ.
CREATE DATABASE login_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE login_db;

-- ---------------------------------------------------------------------
-- Tabla "roles": tipos de usuario del sistema.
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id_rol      INT UNSIGNED NOT NULL AUTO_INCREMENT,    -- Clave primaria
    nombre      VARCHAR(30)  NOT NULL,                   -- Obligatorio
    descripcion VARCHAR(100) NULL,                       -- Opcional
    PRIMARY KEY (id_rol),
    UNIQUE KEY uq_roles_nombre (nombre)
) ENGINE=InnoDB;

-- Roles iniciales: al registrarse, cada usuario recibe el rol "usuario".
INSERT INTO roles (nombre, descripcion) VALUES
    ('usuario',       'Usuario registrado del sistema'),
    ('administrador', 'Usuario con permisos de administración');

-- ---------------------------------------------------------------------
-- Tabla "usuarios": todas las personas registradas.
-- Cada usuario tiene un rol (relación muchos a uno con roles).
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario     INT UNSIGNED NOT NULL AUTO_INCREMENT,   -- Clave primaria
    nombre         VARCHAR(60)  NOT NULL,
    apellido       VARCHAR(60)  NOT NULL,
    correo         VARCHAR(120) NOT NULL,                  -- Es el "usuario" para ingresar
    password_hash  VARCHAR(255) NOT NULL,                  -- La contraseña NUNCA se guarda en texto plano
    fecha_registro TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_rol         INT UNSIGNED NOT NULL,                  -- Clave foránea -> roles
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uq_usuarios_correo (correo),                -- No permite dos cuentas con el mismo correo
    CONSTRAINT fk_usuarios_roles
        FOREIGN KEY (id_rol) REFERENCES roles (id_rol)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla "accesos": historial de inicios de sesión de cada usuario
-- (relación uno a muchos con usuarios).
-- ---------------------------------------------------------------------
CREATE TABLE accesos (
    id_acceso    INT UNSIGNED NOT NULL AUTO_INCREMENT,     -- Clave primaria
    id_usuario   INT UNSIGNED NOT NULL,                    -- Clave foránea -> usuarios
    fecha_acceso DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resultado    VARCHAR(20)  NOT NULL,                    -- 'exitoso' o 'fallido'
    direccion_ip VARCHAR(45)  NULL,                        -- Opcional
    PRIMARY KEY (id_acceso),
    KEY idx_accesos_usuario (id_usuario),
    -- Si se borra un usuario, se borra también su historial.
    CONSTRAINT fk_accesos_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Usuario de base de datos que usa la aplicación (privilegios mínimos).
-- Su clave debe coincidir con la de src/config.php
-- ---------------------------------------------------------------------
CREATE USER IF NOT EXISTS 'login_user'@'localhost' IDENTIFIED BY 'CambiarEstaClave_123';
CREATE USER IF NOT EXISTS 'login_user'@'127.0.0.1' IDENTIFIED BY 'CambiarEstaClave_123';
ALTER USER 'login_user'@'localhost' IDENTIFIED BY 'CambiarEstaClave_123';
ALTER USER 'login_user'@'127.0.0.1' IDENTIFIED BY 'CambiarEstaClave_123';

GRANT SELECT, INSERT, UPDATE ON login_db.usuarios TO 'login_user'@'localhost';
GRANT SELECT                 ON login_db.roles    TO 'login_user'@'localhost';
GRANT SELECT, INSERT         ON login_db.accesos  TO 'login_user'@'localhost';
GRANT SELECT, INSERT, UPDATE ON login_db.usuarios TO 'login_user'@'127.0.0.1';
GRANT SELECT                 ON login_db.roles    TO 'login_user'@'127.0.0.1';
GRANT SELECT, INSERT         ON login_db.accesos  TO 'login_user'@'127.0.0.1';
FLUSH PRIVILEGES;
