<?php
require_once '../config/auth.php';
requerirPermiso('ver_agenda', '../views/reportes.php');
require_once '../controllers/citaController.php';
$titulo_pagina = 'Agenda Médica - CEx+';
$pagina_activa = 'agenda';

if (empty($vista)) {
    $vista = __FILE__;
    require __DIR__ . '/../templatesReutilizables/template.base.php';
    return;
}

$datos_agenda           = obtenerDatosAgenda($_GET['id_medico'] ?? 0, $_GET['fecha'] ?? '');
$medicos_lista          = $datos_agenda['medicos_lista'] ?? [];
$pacientes_lista        = $datos_agenda['pacientes_lista'] ?? [];
$id_medico_seleccionado = $datos_agenda['id_medico_seleccionado'] ?? 0;
$fecha_seleccionada     = $datos_agenda['fecha_seleccionada'] ?? date('Y-m-d');
$citas_por_hora         = $datos_agenda['citas_por_hora'] ?? [];

$turnos = [
    [
        'titulo'  => 'Turno Matutino',
        'icono'   => 'bi-brightness-high text-warning',
        'rango'   => '09:00 - 13:00',
        'bloques' => ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30'],
    ],
    [
        'titulo'  => 'Turno Vespertino',
        'icono'   => 'bi-moon-stars text-primary',
        'rango'   => '15:00 - 19:00',
        'bloques' => ['15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30'],
    ],
];
?>

<header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #0d1b3e;">Agenda Médica</h4>
        <p class="text-muted small mb-0">Disponibilidad diaria por médico y registro rápido de citas</p>
    </div>

    <form method="GET" action="agenda.php" class="d-flex align-items-center gap-2">
        <select name="id_medico" class="form-select form-select-sm rounded-pill px-3 w-auto" onchange="this.form.submit()" aria-label="Seleccionar médico" required>
            <?php foreach ($medicos_lista as $med): ?>
                <option value="<?= (int)$med['id_medico'] ?>" <?= ((int)$med['id_medico'] === $id_medico_seleccionado) ? 'selected' : '' ?>>
                    Dr(a). <?= htmlspecialchars(trim($med['nombre'] . ' ' . $med['apellido_paterno'])) ?> - <?= htmlspecialchars($med['especialidad']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="fecha" class="form-control form-control-sm rounded-pill px-3 w-auto" value="<?= htmlspecialchars($fecha_seleccionada) ?>" onchange="this.form.submit()" aria-label="Seleccionar fecha" required>
    </form>
</header>

<?php foreach (['mensaje' => ['success', 'check-circle-fill'], 'error' => ['danger', 'exclamation-triangle-fill']] as $clave => [$color, $icono]):
    if (!empty($_GET[$clave])): ?>
        <div class="alert alert-<?= $color ?> alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-<?= $icono ?> me-2"></i><?= htmlspecialchars($_GET[$clave]) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif;
endforeach; ?>

<?php $puede_agendar = tienePermiso('add_date'); ?>
<div class="row g-4">
    <!-- Bloques visuales de disponibilidad basados en tabla cita -->
    <div class="<?= $puede_agendar ? 'col-8' : 'col-12' ?>">
        <?php foreach ($turnos as $idx => $turno): ?>
            <section class="bg-white p-4 rounded-4 shadow-sm <?= $idx === 0 ? 'mb-4' : '' ?>">
                <header class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
                        <i class="bi <?= $turno['icono'] ?> me-2"></i><?= $turno['titulo'] ?> (<time><?= htmlspecialchars(date('d/m/Y', strtotime($fecha_seleccionada))) ?></time>)
                    </h6>
                    <span class="badge bg-light text-dark border"><?= $turno['rango'] ?></span>
                </header>
                <div class="row g-3">
                    <?php foreach ($turno['bloques'] as $hora):
                        $cita_slot = $citas_por_hora[$hora] ?? null;
                    ?>
                        <div class="col-3">
                            <?php if ($cita_slot): ?>
                                <article class="p-3 rounded-3 border border-primary border-opacity-25 bg-primary bg-opacity-10 h-100">
                                    <header class="d-flex justify-content-between align-items-center mb-1">
                                        <time class="fw-bold small text-primary"><?= htmlspecialchars($hora) ?></time>
                                        <span class="badge bg-primary rounded-pill"><?= htmlspecialchars(ucfirst($cita_slot['estado'])) ?></span>
                                    </header>
                                    <p class="small text-dark text-truncate mb-0" title="<?= htmlspecialchars($cita_slot['paciente_nombre']) ?>">
                                        <?= htmlspecialchars($cita_slot['paciente_nombre']) ?>
                                    </p>
                                </article>
                            <?php else: ?>
                                <button type="button"
                                    class="<?= $puede_agendar ? 'btn-slot-libre' : '' ?> text-start w-100 p-3 rounded-3 border border-success border-opacity-25 bg-success bg-opacity-10 h-100"
                                    data-fecha-hora="<?= htmlspecialchars($fecha_seleccionada . 'T' . $hora) ?>"
                                    <?= $puede_agendar ? 'title="Clic para agendar a las ' . htmlspecialchars($hora) . '"' : 'disabled' ?>>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <time class="fw-bold small text-success"><?= htmlspecialchars($hora) ?></time>
                                        <span class="badge bg-success rounded-pill">Libre</span>
                                    </div>
                                    <p class="small text-muted mb-0"><?= $puede_agendar ? 'Clic para elegir' : 'Horario disponible' ?></p>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <?php if ($puede_agendar): ?>
    <!-- Formulario rápido de cita (tabla cita) -->
    <aside class="col-4">
        <section class="bg-white p-4 rounded-4 shadow-sm h-100">
            <header class="mb-3">
                <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
                    <i class="bi bi-calendar-plus text-primary me-2"></i>Agendar Cita Rápida
                </h6>
            </header>
            <form action="../controllers/citaController.php" method="POST">
                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                <input type="hidden" name="accion" value="crear">
                <input type="hidden" name="origen" value="agenda">
                <input type="hidden" name="id_medico_filtro" value="<?= (int)$id_medico_seleccionado ?>">

                <div class="mb-3">
                    <label for="id_paciente_agenda" class="form-label small fw-semibold">Paciente</label>
                    <select id="id_paciente_agenda" name="id_paciente" class="form-select" required>
                        <option value="">Seleccione un paciente...</option>
                        <?php foreach ($pacientes_lista as $pac): ?>
                            <option value="<?= (int)$pac['id_paciente'] ?>">
                                <?= htmlspecialchars($pac['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="fecha_agenda" class="form-label small fw-semibold">Fecha y hora de la cita</label>
                    <input type="datetime-local" id="fecha_agenda" name="fecha" class="form-control" value="<?= htmlspecialchars($fecha_seleccionada . 'T09:00') ?>" required>
                </div>

                <div class="mb-4">
                    <label for="estado" class="form-label small fw-semibold">Estado inicial</label>
                    <select id="estado" name="estado" class="form-select" required>
                        <option value="programada" selected>Programada</option>
                        <option value="confirmada">Confirmada</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-pill">
                    <i class="bi bi-check2-circle me-1"></i> Registrar Cita
                </button>
            </form>
        </section>
    </aside>
    <?php endif; ?>
</div>
