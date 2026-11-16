<?php
declare(strict_types=1);

/**
 * Archivo: src/procesar_registro.php
 * Recibe el formulario de registro, valida los datos y los guarda con un INSERT.
 */
require_once __DIR__ . '/config.php';
iniciar_sesion_segura();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ../public/registro.php');
    exit;
}

$nombre    = trim((string)($_POST['nombre'] ?? ''));
$apellido  = trim((string)($_POST['apellido'] ?? ''));
$correo    = trim((string)($_POST['correo'] ?? ''));
$password  = (string)($_POST['password'] ?? '');
$confirmar = (string)($_POST['confirmar_password'] ?? '');

// Todos los campos son obligatorios.
if ($nombre === '' || $apellido === '' || $correo === '' || $password === '' || $confirmar === '') {
    header('Location: ../public/registro.php?error=vacios');
    exit;
}

// Nombre y apellido: solo letras (con tildes), espacios, apóstrofe y guion.
$patronNombre = "/^[\p{L}][\p{L}\s'-]{1,59}$/u";
if (!preg_match($patronNombre, $nombre) || !preg_match($patronNombre, $apellido)
    || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 120) {
    header('Location: ../public/registro.php?error=datos_invalidos');
    exit;
}

if (strlen($password) < 8) {
    header('Location: ../public/registro.php?error=corta');
    exit;
}
if ($password !== $confirmar) {
    header('Location: ../public/registro.php?error=no_coinciden');
    exit;
}

// La contraseña se guarda convertida en hash (bcrypt), nunca en texto plano.
$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $pdo = obtener_conexion();

    // A todo usuario nuevo se le asigna el rol "usuario" (se busca su id en la tabla roles).
    $stmt = $pdo->prepare("SELECT id_rol FROM roles WHERE nombre = 'usuario' LIMIT 1");
    $stmt->execute();
    $idRol = $stmt->fetchColumn();
    if ($idRol === false) {
        error_log('[registro] No existe el rol "usuario" en la tabla roles');
        header('Location: ../public/registro.php?error=bd');
        exit;
    }

    // INSERT preparado con los valores de los inputs y el rol.
    $pdo->prepare(
        'INSERT INTO usuarios (nombre, apellido, correo, password_hash, id_rol)
         VALUES (:nombre, :apellido, :correo, :hash, :rol)'
    )->execute([
        ':nombre'   => $nombre,
        ':apellido' => $apellido,
        ':correo'   => $correo,
        ':hash'     => $hash,
        ':rol'      => $idRol,
    ]);
} catch (PDOException $e) {
    // 23000 = el correo ya existe (restricción UNIQUE de la tabla).
    if ($e->getCode() === '23000') {
        header('Location: ../public/registro.php?error=correo_existe');
        exit;
    }
    error_log('[registro] ' . $e->getMessage());
    header('Location: ../public/registro.php?error=bd');
    exit;
}

// Registro correcto: se vuelve al login con un mensaje de éxito.
header('Location: ../public/index.php?registrado=1');
exit;
