<?php
declare(strict_types=1);

/** Archivo: logout.php — Cierra la sesión y vuelve al login. */
require_once __DIR__ . '/config.php';
iniciar_sesion_segura();

// Se vacían los datos de la sesión.
$_SESSION = [];

// Se borra la cookie de sesión del navegador.
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Se destruye la sesión en el servidor.
session_destroy();

header('Location: ../public/index.php');
exit;
