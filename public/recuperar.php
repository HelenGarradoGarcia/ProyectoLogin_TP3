<?php
declare(strict_types=1);

/** Archivo: public/recuperar.php — Pide el correo y muestra la contraseña nueva generada. */
require_once __DIR__ . '/../src/config.php';
iniciar_sesion_segura();

$mensajes = [
    'vacios'          => 'Ingresá tu correo.',
    'correo_invalido' => 'El correo no es válido.',
    'no_existe'       => 'No hay ninguna cuenta registrada con ese correo.',
    'bd'              => 'No se pudo conectar con la base de datos. Revisá que MySQL esté iniciado.',
];
$codigo = $_GET['error'] ?? '';
$error  = is_string($codigo) ? ($mensajes[$codigo] ?? null) : null;

// La contraseña nueva llega por la sesión y se muestra UNA sola vez (se borra al leerla).
$claveNueva = $_SESSION['clave_nueva'] ?? null;
$correoNuevo = $_SESSION['correo_recuperado'] ?? '';
unset($_SESSION['clave_nueva'], $_SESSION['correo_recuperado']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proyecto Login | Recuperar contraseña</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body>
    <button type="button" id="btn-tema" class="btn-tema"></button>

    <main class="card">
        <h1>Recuperar contraseña</h1>

        <?php if ($claveNueva !== null): ?>
            <!-- Se muestra la contraseña nueva en pantalla (en un sistema real se enviaría por correo) -->
            <p class="alerta alerta-ok">Generamos una contraseña nueva para <strong><?= e((string)$correoNuevo) ?></strong>:</p>
            <p class="clave-nueva"><?= e((string)$claveNueva) ?></p>
            <p class="enlace">Ingresá con esta contraseña y guardala.</p>
            <a href="index.php" class="boton">Ir al login</a>
        <?php else: ?>
            <?php if ($error !== null): ?>
                <p class="alerta alerta-error"><?= e($error) ?></p>
            <?php endif; ?>

            <p class="enlace">Ingresá tu correo registrado y te generamos una contraseña nueva.</p>

            <form action="../src/procesar_recuperar.php" method="post" data-validar>
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" required>
                <small class="error" data-error-for="correo"></small>

                <button type="submit">Generar contraseña nueva</button>
            </form>

            <p class="enlace"><a href="index.php">← Volver al login</a></p>
        <?php endif; ?>
    </main>
</body>
</html>
