<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('EN_PLANTILLA_BASE')) {
    define('EN_PLANTILLA_BASE', true);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina ?? 'Consultorio Médico') ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos de Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/citas.css">
    <?php if (!empty($cargar_fullcalendar)): ?>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <?php endif; ?>
    <?php if (!empty($cargar_chartjs)): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <?php endif; ?>
</head>

<body class="bg-light">

    <div class="container-fluid min-vh-100">
        <div class="row min-vh-100">
            <?php include_once __DIR__ . '/sidebar.php'; ?>

            <main class="col-9 col-lg-10 p-4" style="background-color: #f4f7fe;">
                <?php
                if (!empty($vista) && file_exists($vista)) {
                    require $vista;
                } elseif (isset($contenido_vista)) {
                    echo $contenido_vista;
                }
                ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <?php if (!empty($script_adicional)): ?>
        <script src="<?= htmlspecialchars($script_adicional) ?>"></script>
    <?php endif; ?>
</body>

</html>
