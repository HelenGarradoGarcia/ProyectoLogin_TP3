<?php
declare(strict_types=1);

/** Archivo: public/registro.php — Formulario para crear un usuario nuevo. */
require_once __DIR__ . '/../src/config.php';
iniciar_sesion_segura();

$mensajes = [
    'vacios'         => 'Completá todos los campos.',
    'datos_invalidos'=> 'Nombre, apellido o correo no son válidos.',
    'corta'          => 'La contraseña debe tener al menos 8 caracteres.',
    'no_coinciden'   => 'Las contraseñas no coinciden.',
    'correo_existe'  => 'Ese correo ya está registrado.',
    'bd'             => 'No se pudo conectar con la base de datos. Revisá que MySQL esté iniciado.',
];
$codigo = $_GET['error'] ?? '';
$error  = is_string($codigo) ? ($mensajes[$codigo] ?? null) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proyecto Login | Crear usuario</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body>
    <button type="button" id="btn-tema" class="btn-tema"></button>

    <main class="card">
        <h1>Crear usuario nuevo</h1>

        <?php if ($error !== null): ?>
            <p class="alerta alerta-error"><?= e($error) ?></p>
        <?php endif; ?>

        <!-- Envía los datos por POST a procesar_registro.php -->
        <form action="../src/procesar_registro.php" method="post" data-validar>
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" maxlength="60" required>
            <small class="error" data-error-for="nombre"></small>

            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" maxlength="60" required>
            <small class="error" data-error-for="apellido"></small>

            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" maxlength="120" required>
            <small class="error" data-error-for="correo"></small>

            <label for="password">Contraseña (mínimo 8 caracteres)</label>
            <input type="password" id="password" name="password" required>
            <small class="error" data-error-for="password"></small>

            <label for="confirmar_password">Confirmar contraseña</label>
            <input type="password" id="confirmar_password" name="confirmar_password" required>
            <small class="error" data-error-for="confirmar_password"></small>

            <button type="submit">Crear usuario</button>
        </form>

        <p class="enlace"><a href="index.php">← Volver al login</a></p>
    </main>
</body>
</html>
