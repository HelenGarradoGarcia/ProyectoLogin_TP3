<?php
declare(strict_types=1);

/**
 * Archivo: src/procesar_login.php
 * Recibe el formulario del login, busca al usuario con un SELECT, registra el acceso y abre la sesión.
 */
require_once __DIR__ . '/config.php';
iniciar_sesion_segura();

// Solo se aceptan envíos del formulario (método POST).
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ../public/index.php');
    exit;
}

$correo   = trim((string)($_POST['correo'] ?? ''));
$password = (string)($_POST['password'] ?? '');

// Validación en el servidor (la de JavaScript puede saltearse).
if ($correo === '' || $password === '') {
    header('Location: ../public/index.php?error=vacios');
    exit;
}

try {
    $pdo = obtener_conexion();

    // Consulta preparada que une usuarios con su rol (JOIN).
    $stmt = $pdo->prepare(
        'SELECT u.id_usuario, u.nombre, u.apellido, u.password_hash, r.nombre AS rol
           FROM usuarios u
     INNER JOIN roles r ON r.id_rol = u.id_rol
          WHERE u.correo = :correo
          LIMIT 1'
    );
    $stmt->execute([':correo' => $correo]);
    $usuario = $stmt->fetch();

    // El correo no existe: no hay usuario al que asignarle el intento.
    if ($usuario === false) {
        header('Location: ../public/index.php?error=credenciales');
        exit;
    }

    // Contraseña incorrecta: se guarda el intento fallido en el historial.
    if (!password_verify($password, $usuario['password_hash'])) {
        registrar_acceso($pdo, (int)$usuario['id_usuario'], 'fallido');
        header('Location: ../public/index.php?error=credenciales');
        exit;
    }

    // Antes de registrar el acceso nuevo, se busca el último acceso exitoso anterior.
    $stmt = $pdo->prepare(
        "SELECT fecha_acceso FROM accesos
          WHERE id_usuario = :id AND resultado = 'exitoso'
       ORDER BY fecha_acceso DESC, id_acceso DESC
          LIMIT 1"
    );
    $stmt->execute([':id' => $usuario['id_usuario']]);
    $ultimoAcceso = $stmt->fetchColumn();

    registrar_acceso($pdo, (int)$usuario['id_usuario'], 'exitoso');
} catch (PDOException $e) {
    error_log('[login] ' . $e->getMessage());
    header('Location: ../public/index.php?error=bd');
    exit;
}

// Login correcto: se renueva el ID de sesión y se guardan los datos del usuario.
session_regenerate_id(true);
$_SESSION['id_usuario']    = (int)$usuario['id_usuario'];
$_SESSION['nombre']        = $usuario['nombre'];
$_SESSION['apellido']      = $usuario['apellido'];
$_SESSION['rol']           = $usuario['rol'];
$_SESSION['ultimo_acceso'] = $ultimoAcceso !== false ? (string)$ultimoAcceso : null;

header('Location: ../public/dashboard.php');
exit;
