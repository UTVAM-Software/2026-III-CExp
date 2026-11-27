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

function csrfToken()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verificarCsrf()
{
    $enviado = $_POST['csrf'] ?? '';
    if (!is_string($enviado) || !hash_equals($_SESSION['csrf'] ?? '', $enviado)) {
        http_response_code(403);
        exit('Solicitud no válida. Recarga la página e inténtalo de nuevo.');
    }
}
?>