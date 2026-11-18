<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no ha iniciado sesión, redirigir al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/login.php");
    exit();
}

function tienePermiso($codename)
{
    if (isset($_SESSION['id_grupo']) && (int)$_SESSION['id_grupo'] === 1) {
        return true;
    }

    $permisos = $_SESSION['permisos'] ?? [];
    return in_array($codename, $permisos, true);
}

function requerirPermiso($codename, $redirigir = '../views/reportes.php')
{
    if (!tienePermiso($codename)) {
        $separador = strpos($redirigir, '?') !== false ? '&' : '?';
        header("Location: {$redirigir}{$separador}error=" . urlencode("No tienes permiso para realizar esta acción."));
        exit();
    }
}
?>