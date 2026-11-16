<?php
declare(strict_types=1);

/**
 * Archivo: src/procesar_recuperar.php
 * Busca el correo, genera una contraseña nueva aleatoria y la guarda (como hash) con un UPDATE.
 */
require_once __DIR__ . '/config.php';
iniciar_sesion_segura();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ../public/recuperar.php');
    exit;
}

$correo = trim((string)($_POST['correo'] ?? ''));

if ($correo === '') {
    header('Location: ../public/recuperar.php?error=vacios');
    exit;
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../public/recuperar.php?error=correo_invalido');
    exit;
}

/** Genera una contraseña aleatoria de 12 caracteres (mayúsculas, minúsculas y números). */
function generar_password(int $largo = 12): string
{
    // Se omiten caracteres que se confunden al leer: I, l, 1, O, 0.
    $mayusculas = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $minusculas = 'abcdefghijkmnopqrstuvwxyz';
    $numeros    = '23456789';
    $todos      = $mayusculas . $minusculas . $numeros;

    // random_int usa aleatoriedad segura (a diferencia de rand()).
    $caracteres = [
        $mayusculas[random_int(0, strlen($mayusculas) - 1)],
        $minusculas[random_int(0, strlen($minusculas) - 1)],
        $numeros[random_int(0, strlen($numeros) - 1)],
    ];
    while (count($caracteres) < $largo) {
        $caracteres[] = $todos[random_int(0, strlen($todos) - 1)];
    }
    // Se mezclan para que no queden siempre en el mismo orden.
    for ($i = count($caracteres) - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        [$caracteres[$i], $caracteres[$j]] = [$caracteres[$j], $caracteres[$i]];
    }
    return implode('', $caracteres);
}

try {
    $pdo = obtener_conexion();

    // SELECT preparado: busca si el correo está registrado.
    $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE correo = :correo LIMIT 1');
    $stmt->execute([':correo' => $correo]);
    $usuario = $stmt->fetch();

    if ($usuario === false) {
        header('Location: ../public/recuperar.php?error=no_existe');
        exit;
    }

    // Se genera la contraseña nueva y se guarda SOLO su hash.
    $nueva = generar_password();
    $pdo->prepare('UPDATE usuarios SET password_hash = :hash WHERE id_usuario = :id')
        ->execute([
            ':hash' => password_hash($nueva, PASSWORD_BCRYPT),
            ':id'   => $usuario['id_usuario'],
        ]);
} catch (PDOException $e) {
    error_log('[recuperar] ' . $e->getMessage());
    header('Location: ../public/recuperar.php?error=bd');
    exit;
}

// La contraseña nueva se pasa a la pantalla por la sesión (se muestra una sola vez).
$_SESSION['clave_nueva']       = $nueva;
$_SESSION['correo_recuperado'] = $correo;
header('Location: ../public/recuperar.php');
exit;
