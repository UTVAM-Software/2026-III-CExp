<?php
$titulo_pagina = 'Agenda Médica - CEx+';
$pagina_activa = 'agenda';

if (empty($vista)) {
    $vista = __FILE__;
    require __DIR__ . '/../templatesReutilizables/template.base.php';
    return;
}

// VARIABLES TEMPPORRALES CHAVAL , HASTA QUE HAGAS LOS CONTROLADORES
$medicos_lista          = $medicos_lista ?? [];
$pacientes_lista        = $pacientes_lista ?? [];
$id_medico_seleccionado = $id_medico_seleccionado ?? (int)($_GET['id_medico'] ?? 0);
$fecha_seleccionada     = $fecha_seleccionada ?? ($_GET['fecha'] ?? date('Y-m-d'));
$citas_por_hora         = $citas_por_hora ?? [];

$bloques_manana = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30'];
$bloques_tarde  = ['15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30'];
?>

<header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #0d1b3e;">Agenda Médica</h4>
        <p class="text-muted small mb-0">Disponibilidad diaria por médico y registro rápido de citas</p>
    </div>

    <form method="GET" action="agenda.php" class="d-flex flex-wrap gap-2">
        <select name="id_medico" class="form-select form-select-sm rounded-pill px-3" required>
            <?php foreach ($medicos_lista as $med): ?>
                <option value="<?= (int)$med['id_medico'] ?>" <?= ((int)$med['id_medico'] === $id_medico_seleccionado) ? 'selected' : '' ?>>
                    Dr(a). <?= htmlspecialchars(trim($med['nombre'] . ' ' . $med['apellido_paterno'])) ?> - <?= htmlspecialchars($med['especialidad']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="fecha" class="form-control form-control-sm rounded-pill px-3" value="<?= htmlspecialchars($fecha_seleccionada) ?>" required>
        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-funnel me-1"></i> Filtrar
        </button>
    </form>
</header>

<?php if (!empty($_GET['mensaje'])): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_GET['mensaje']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Bloques visuales de disponibilidad basados en tabla cita -->
    <div class="col-8">
        <section class="bg-white p-4 rounded-4 shadow-sm mb-4">
            <header class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
                    <i class="bi bi-brightness-high text-warning me-2"></i>Turno Matutino (<time><?= htmlspecialchars(date('d/m/Y', strtotime($fecha_seleccionada))) ?></time>)
                </h6>
                <span class="badge bg-light text-dark border">09:00 - 13:00</span>
            </header>
            <div class="row g-3">
                <?php foreach ($bloques_manana as $hora): ?>
                    <?php $cita_slot = $citas_por_hora[$hora] ?? null; ?>
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
                            <article class="p-3 rounded-3 border border-success border-opacity-25 bg-success bg-opacity-10 h-100">
                                <header class="d-flex justify-content-between align-items-center mb-1">
                                    <time class="fw-bold small text-success"><?= htmlspecialchars($hora) ?></time>
                                    <span class="badge bg-success rounded-pill">Libre</span>
                                </header>
                                <p class="small text-muted mb-0">Horario disponible</p>
                            </article>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="bg-white p-4 rounded-4 shadow-sm">
            <header class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
                    <i class="bi bi-moon-stars text-primary me-2"></i>Turno Vespertino (<time><?= htmlspecialchars(date('d/m/Y', strtotime($fecha_seleccionada))) ?></time>)
                </h6>
                <span class="badge bg-light text-dark border">15:00 - 19:00</span>
            </header>
            <div class="row g-3">
                <?php foreach ($bloques_tarde as $hora): ?>
                    <?php $cita_slot = $citas_por_hora[$hora] ?? null; ?>
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
                            <article class="p-3 rounded-3 border border-success border-opacity-25 bg-success bg-opacity-10 h-100">
                                <header class="d-flex justify-content-between align-items-center mb-1">
                                    <time class="fw-bold small text-success"><?= htmlspecialchars($hora) ?></time>
                                    <span class="badge bg-success rounded-pill">Libre</span>
                                </header>
                                <p class="small text-muted mb-0">Horario disponible</p>
                            </article>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <!-- Formulario rápido de cita (tabla cita) -->
    <aside class="col-4">
        <section class="bg-white p-4 rounded-4 shadow-sm h-100">
            <header class="mb-3">
                <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
                    <i class="bi bi-calendar-plus text-primary me-2"></i>Agendar Cita Rápida
                </h6>
            </header>
            <form action="../controllers/citaController.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <div class="mb-3">
                    <label for="id_paciente" class="form-label small fw-semibold">Paciente</label>
                    <select id="id_paciente" name="id_paciente" class="form-select" required>
                        <option value="">Seleccione un paciente...</option>
                        <?php foreach ($pacientes_lista as $pac): ?>
                            <option value="<?= (int)$pac['id_paciente'] ?>">
                                <?= htmlspecialchars($pac['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="fecha" class="form-label small fw-semibold">Fecha y hora de la cita</label>
                    <input type="datetime-local" id="fecha" name="fecha" class="form-control" value="<?= htmlspecialchars($fecha_seleccionada . 'T09:00') ?>" required>
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
</div>
