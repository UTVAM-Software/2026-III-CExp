<?php
require_once '../config/auth.php';
require_once '../controllers/reporteController.php';
$titulo_pagina = 'Reportes y Estadísticas - Consultorio Médico';
$pagina_activa = 'reportes';
$cargar_chartjs = true;
$script_adicional = '../assets/js/reportes.js';

if (!defined('EN_PLANTILLA_BASE')) {
    $vista = __FILE__;
    require_once '../templatesReutilizables/template.base.php';
    return;
}

$datos = obtenerDatosReportes();
$proximas_citas = $datos['proximas_citas'] ?? [];
$tendencia = $datos['tendencia_semestral'] ?? ['etiquetas' => [], 'consultas' => [], 'citas' => []];
$dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

$tarjetas_estadisticas = [
    [
        'titulo' => 'Pacientes Activos',
        'valor' => (int) ($datos['total_pacientes'] ?? 0),
        'icono' => 'bi-people-fill',
        'estilo_icono' => 'bg-primary-subtle text-primary',
        'subtitulo' => 'Registrados en expediente',
        'icono_subtitulo' => 'bi-folder2-open text-primary',
        'clase_subtitulo' => 'text-secondary fw-medium',
    ],
    [
        'titulo' => 'Citas Pendientes',
        'valor' => (int) ($datos['citas_pendientes'] ?? 0),
        'icono' => 'bi-clock-fill',
        'estilo_icono' => 'bg-warning-subtle text-warning-emphasis',
        'subtitulo' => 'Programadas y confirmadas',
        'icono_subtitulo' => 'bi-calendar-event',
        'clase_subtitulo' => 'text-warning-emphasis fw-semibold',
    ],
    [
        'titulo' => 'Consultas Realizadas',
        'valor' => (int) ($datos['total_consultas'] ?? 0),
        'icono' => 'bi-heart-pulse-fill',
        'estilo_icono' => 'bg-info-subtle text-info-emphasis',
        'subtitulo' => 'Atenciones médicas registradas',
        'icono_subtitulo' => 'bi-clipboard2-pulse',
        'clase_subtitulo' => 'text-info-emphasis fw-medium',
    ],
    [
        'titulo' => 'Tasa de Asistencia',
        'valor' => ($datos['tasa_asistencia'] ?? 0) . '%',
        'icono' => 'bi-check-circle-fill',
        'estilo_icono' => 'bg-success-subtle text-success',
        'subtitulo' => 'Citas no canceladas vs total',
        'icono_subtitulo' => 'bi-check2',
        'clase_subtitulo' => 'text-success fw-semibold',
    ],
];
?>

<header
    class="bg-white p-3 px-4 rounded-4 shadow-sm mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-graph-up text-primary fs-5"></i>
        <h5 class="fw-bold mb-0 text-dark">Resumen del dia</h5>
        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis fw-semibold px-3 py-2 ms-2">
            Semana Actual
        </span>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="citas.php" class="btn btn-primary btn-sm rounded-pill fw-semibold px-3 py-2 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i>Nueva Cita
        </a>
    </div>
</header>

<!-- fila de las tarjetas-->
<section class="row g-3 mb-4">
    <?php foreach ($tarjetas_estadisticas as $tarjeta): ?>
        <div class="col-3">
            <article class="bg-white p-4 rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary fw-medium small"><?= $tarjeta['titulo'] ?></span>
                    <span
                        class="rounded-3 d-inline-flex align-items-center justify-content-center <?= $tarjeta['estilo_icono'] ?>"
                        style="width: 38px; height: 38px;">
                        <i class="bi <?= $tarjeta['icono'] ?> fs-5"></i>
                    </span>
                </div>
                <h2 class="fw-bold text-dark mb-2"><?= htmlspecialchars((string) $tarjeta['valor']) ?></h2>
                <small class="<?= $tarjeta['clase_subtitulo'] ?>">
                    <i class="bi <?= $tarjeta['icono_subtitulo'] ?> me-1"></i><?= $tarjeta['subtitulo'] ?>
                </small>
            </article>
        </div>
    <?php endforeach; ?>
</section>


<section class="row g-4">
    <!-- aqui se pintan las barras de la grafcua -->
    <div class="col-8">
        <article class="bg-white p-4 rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between">
            <header class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">
                        <i class="bi bi-graph-up text-primary me-1"></i>Tendencia de Consultas y Citas Agendadas
                    </h6>
                    <small class="text-secondary">Actividad mensual de los últimos 6 meses</small>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-3 fw-bold"><?= date('Y') ?></span>
            </header>

            <!-- aqui se esta usando la grafica de chart.js -->
            <div class="position-relative my-2" style="height: 245px;">
                <canvas id="graficaTendencia"
                    data-etiquetas='<?= htmlspecialchars(json_encode($tendencia['etiquetas'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'
                    data-consultas='<?= htmlspecialchars(json_encode($tendencia['consultas']), ENT_QUOTES, 'UTF-8') ?>'
                    data-citas='<?= htmlspecialchars(json_encode($tendencia['citas']), ENT_QUOTES, 'UTF-8') ?>'>
                </canvas>
            </div>

            <footer class="d-flex justify-content-center align-items-center gap-4 pt-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-block"
                        style="width: 11px; height: 11px; background-color: #4e73df;"></span>
                    <small class="text-secondary fw-semibold">Consultas Realizadas</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-block"
                        style="width: 11px; height: 11px; background-color: #a6c1ee;"></span>
                    <small class="text-secondary fw-semibold">Citas Agendadas</small>
                </div>
            </footer>
        </article>
    </div>

    <!-- priximas citas -->
    <div class="col-4">
        <article class="bg-white p-4 rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between">
            <div>
                <header class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-list-check text-warning me-2"></i>Próximas Citas
                    </h6>
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1 fw-semibold">
                        <?= (int) ($datos['citas_pendientes'] ?? 0) ?> Pendientes
                    </span>
                </header>

                <div class="d-flex flex-column gap-3" id="listaProximasCitas">

                    <?php if (empty($proximas_citas)): ?>
                        <div class="p-3 rounded-3 bg-light text-center text-secondary small">
                            No hay citas pendientes próximas.
                        </div>
                    <?php else: ?>

                        <?php foreach ($proximas_citas as $cita):
                            $marca_tiempo = strtotime($cita['fecha']);
                            $es_hoy = (date('Y-m-d', $marca_tiempo) === date('Y-m-d'));
                            $confirmada = ($cita['estado'] === 'confirmada');
                            ?>

                            <article
                                class="p-3 rounded-3 d-flex justify-content-between align-items-center gap-2 item-proxima-cita border <?= $es_hoy ? 'border-primary border-opacity-25' : 'border-light' ?>"
                                style="background-color: <?= $es_hoy ? '#eef3fe' : '#f4f6fb' ?>;">
                                <div class="min-w-0">
                                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.92rem;">
                                        <a href="historial.php?id_paciente=<?= (int) $cita['id_paciente'] ?>"
                                            class="text-decoration-none text-dark">
                                            <?= htmlspecialchars(trim($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido'])) ?>
                                        </a>
                                    </h6>
                                    <small class="text-secondary d-block" style="font-size: 0.78rem;">
                                        <?php if ($es_hoy): ?>
                                            <span class="badge bg-primary text-white rounded-pill me-1">Hoy</span>
                                        <?php else: ?>
                                            <?= $dias_semana[(int) date('w', $marca_tiempo)] . ' ' . date('d/m', $marca_tiempo) ?>,
                                        <?php endif; ?>
                                        <?= date('h:i A', $marca_tiempo) ?> &bull; ID
                                        <?= str_pad((string) $cita['id_cita'], 3, '0', STR_PAD_LEFT) ?>
                                    </small>

                                    <?php if (!empty($cita['medico_nombre'])): ?>
                                        <small class="text-muted d-block text-truncate mt-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($cita['medico_nombre']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0">
                                    <span
                                        class="badge <?= $confirmada ? 'bg-info-subtle text-info-emphasis' : 'bg-warning-subtle text-warning-emphasis' ?> rounded-2 px-2 py-1 fw-semibold">
                                        <?= $confirmada ? 'Confirmada' : 'Pendiente' ?>
                                    </span>
                                    <?php if (tienePermiso('add_consultation')): ?>
                                        <a href="consultas.php?id_paciente=<?= (int) $cita['id_paciente'] ?>&id_cita=<?= (int) $cita['id_cita'] ?>"
                                            class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2"
                                            style="font-size: 0.75rem;">
                                            <i class="bi bi-clipboard2-pulse me-1"></i>Atender
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <footer class="mt-4 pt-2">
                <a href="citas.php" class="btn btn-light w-100 rounded-3 py-2 fw-bold text-dark border-0"
                    style="background-color: #f4f6fb;">
                    Ir al Calendario Completo <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </footer>
        </article>
    </div>
</section>