<?php
declare(strict_types=1);

/** Archivo: public/index.php — Pantalla de inicio de sesión (login). */
require_once __DIR__ . '/../src/config.php';
iniciar_sesion_segura();

// Si ya hay una sesión iniciada, se va directo al escritorio.
if (!empty($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}

// Mensajes posibles; llegan por la URL como un código (nunca como texto libre).
$mensajes = [
    'vacios'       => 'Completá usuario y contraseña.',
    'credenciales' => 'Usuario o contraseña incorrectos.',
    'bd'           => 'No se pudo conectar con la base de datos. Revisá que MySQL esté iniciado.',
];
$codigo = $_GET['error'] ?? '';
$error  = is_string($codigo) ? ($mensajes[$codigo] ?? null) : null;
$registrado = isset($_GET['registrado']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proyecto Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body>
    <button type="button" id="btn-tema" class="btn-tema"></button>

    <main class="card">
        <h1>Iniciar sesión</h1>

        <?php if ($registrado): ?>
            <p class="alerta alerta-ok">¡Cuenta creada! Ya podés ingresar.</p>
        <?php endif; ?>
        <?php if ($error !== null): ?>
            <p class="alerta alerta-error"><?= e($error) ?></p>
        <?php endif; ?>

        <!-- Envía los datos por POST a procesar_login.php -->
        <form action="../src/procesar_login.php" method="post" data-validar>
            <label for="correo">Usuario (correo)</label>
            <input type="email" id="correo" name="correo" required>
            <small class="error" data-error-for="correo"></small>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
            <small class="error" data-error-for="password"></small>

            <button type="submit">Ingresar</button>
        </form>

        <p class="enlace"><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>
        <p class="enlace">¿No tenés cuenta? <a href="registro.php">Crear usuario nuevo</a></p>
    </main>
</body>
</html>
