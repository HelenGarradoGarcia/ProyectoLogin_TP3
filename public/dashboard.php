<?php
declare(strict_types=1);

/** Archivo: public/dashboard.php — Escritorio del usuario logueado (bienvenida, rol e historial de accesos). */
require_once __DIR__ . '/../src/config.php';
iniciar_sesion_segura();

// Si no hay sesión iniciada, se manda al login.
if (empty($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

/** Convierte una fecha de la base (2026-11-15 10:30:00) a formato 15/11/2026 10:30. */
function formatear_fecha(string $fecha): string
{
    return date('d/m/Y H:i', strtotime($fecha));
}

// Se consultan los últimos 5 accesos del usuario (historial).
$accesos = [];
try {
    $stmt = obtener_conexion()->prepare(
        'SELECT fecha_acceso, resultado FROM accesos
          WHERE id_usuario = :id
       ORDER BY fecha_acceso DESC, id_acceso DESC
          LIMIT 5'
    );
    $stmt->execute([':id' => $_SESSION['id_usuario']]);
    $accesos = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('[dashboard] ' . $e->getMessage());
}

$rol    = (string)($_SESSION['rol'] ?? 'usuario');
$ultimo = $_SESSION['ultimo_acceso'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proyecto Login | Escritorio</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body>
    <button type="button" id="btn-tema" class="btn-tema"></button>

    <main class="card">
        <h1>¡Bienvenido/a, <?= e($_SESSION['nombre']) ?> <?= e($_SESSION['apellido']) ?>!</h1>
        <p class="alerta alerta-ok">Iniciaste sesión correctamente. Este es tu escritorio.</p>

        <ul class="datos">
            <li><strong>Rol:</strong> <?= e($rol) ?></li>
            <li><strong>Acceso anterior:</strong>
                <?= $ultimo !== null ? e(formatear_fecha((string)$ultimo)) : 'es tu primer acceso' ?></li>
        </ul>

        <h2 class="subtitulo">Historial de accesos</h2>
        <table class="tabla-accesos">
            <thead><tr><th>Fecha</th><th>Resultado</th></tr></thead>
            <tbody>
            <?php foreach ($accesos as $a): ?>
                <tr>
                    <td><?= e(formatear_fecha((string)$a['fecha_acceso'])) ?></td>
                    <td class="res-<?= e((string)$a['resultado']) ?>"><?= e((string)$a['resultado']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$accesos): ?>
                <tr><td colspan="2">Todavía no hay accesos registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <a href="../src/logout.php" class="boton">Cerrar sesión</a>
    </main>
</body>
</html>
