<?php
declare(strict_types=1);

/**
 * Archivo: src/config.php
 * Datos de conexión a MySQL y funciones compartidas por todas las páginas.
 */

// Datos de conexión (deben coincidir con los de database.sql).
const DB_HOST = '127.0.0.1';          // 127.0.0.1 = localhost por TCP
const DB_PORT = 3306;                 // Puerto de MySQL indicado en la consigna
const DB_NAME = 'login_db';
const DB_USER = 'login_user';
const DB_PASS = 'CambiarEstaClave_123';

/** Devuelve la conexión a la base de datos usando PDO. */
function obtener_conexion(): PDO
{
    // "static": la conexión se abre una sola vez por petición.
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Errores como excepciones
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Filas como arreglos asociativos
        PDO::ATTR_EMULATE_PREPARES   => false,                    // Consultas preparadas reales (anti inyección SQL)
    ]);

    return $pdo;
}

/** Inicia la sesión con cookies seguras. Se llama al comienzo de cada página. */
function iniciar_sesion_segura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,         // La cookie dura hasta cerrar el navegador
        'path'     => '/',
        'secure'   => $https,    // Solo por HTTPS si está disponible
        'httponly' => true,      // JavaScript no puede leer la cookie
        'samesite' => 'Strict',  // No se envía desde otros sitios
    ]);
    session_start();
}

/** Escapa un texto para mostrarlo en HTML sin riesgo (evita XSS). */
function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Guarda un intento de acceso en la tabla "accesos".
 * Si falla, solo se anota en el log: nunca debe impedir que el usuario ingrese.
 */
function registrar_acceso(PDO $pdo, int $idUsuario, string $resultado): void
{
    try {
        $pdo->prepare(
            'INSERT INTO accesos (id_usuario, resultado, direccion_ip)
             VALUES (:id, :resultado, :ip)'
        )->execute([
            ':id'        => $idUsuario,
            ':resultado' => $resultado,                       // 'exitoso' o 'fallido'
            ':ip'        => $_SERVER['REMOTE_ADDR'] ?? null,  // IP desde donde se ingresó
        ]);
    } catch (PDOException $e) {
        error_log('[accesos] ' . $e->getMessage());
    }
}
