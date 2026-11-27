<?php
require_once '../config/auth.php';
require_once '../controllers/consultaController.php';

$titulo_pagina = 'Consultas Médicas - CEx+';
$pagina_activa = 'consultas';

if (empty($vista)) {
    $vista = __FILE__;
    require __DIR__ . '/../templatesReutilizables/template.base.php';
    return;
}

$datos_consultas     = obtenerDatosConsultas($_GET['id_paciente'] ?? 0, $_GET['id_cita'] ?? 0);
$pacientes_lista     = $datos_consultas['pacientes_lista'] ?? [];
$medicos_lista       = $datos_consultas['medicos_lista'] ?? [];
$citas_pendientes    = $datos_consultas['citas_pendientes'] ?? [];
$cita_seleccionada   = $datos_consultas['cita_seleccionada'] ?? null;
$paciente_actual     = $datos_consultas['paciente_actual'] ?? null;
$consultas_recientes = $datos_consultas['consultas_recientes'] ?? [];

$signos_vitales = [
    ['id' => 'temperatura',             'label' => 'Temperatura (°C)',    'step' => '0.01', 'min' => '30.00', 'max' => '45.00', 'placeholder' => '36.50'],
    ['id' => 'saturacion_oxigeno',      'label' => 'Saturación O₂ (%)',   'step' => '1',    'min' => '50',    'max' => '100',   'placeholder' => '98'],
    ['id' => 'frecuencia_respiratoria', 'label' => 'Frec. Respiratoria',  'step' => '1',    'min' => '5',     'max' => '80',    'placeholder' => '16'],
    ['id' => 'pulso_cardiaco',          'label' => 'Pulso Cardíaco (lpm)','step' => '1',    'min' => '30',    'max' => '250',   'placeholder' => '72'],
];

$columnas_medicamento = [
    ['name' => 'medicamento', 'max' => 100, 'placeholder' => 'Ej. Paracetamol 500 mg'],
    ['name' => 'dosis',       'max' => 50,  'placeholder' => 'Ej. 1 tableta'],
    ['name' => 'frecuencia',  'max' => 50,  'placeholder' => 'Ej. Cada 8 h'],
    ['name' => 'duracion',    'max' => 50,  'placeholder' => 'Ej. 5 días'],
];
?>

<header class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #0d1b3e;">Consulta Médica</h4>
        <p class="text-muted small mb-0">Registro de signos vitales, diagnóstico y receta médica</p>
    </div>
    <?php if (!empty($paciente_actual['id_paciente'])): ?>
        <a href="historial.php?id_paciente=<?= (int)$paciente_actual['id_paciente'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-journal-medical me-1"></i> Ver Expediente
        </a>
    <?php endif; ?>
</header>

<?php foreach (['mensaje' => ['success', 'check-circle-fill'], 'error' => ['danger', 'exclamation-triangle-fill']] as $clave => [$color, $icono]):
    if (!empty($_GET[$clave])): ?>
        <div class="alert alert-<?= $color ?> alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-<?= $icono ?> me-2"></i><?= htmlspecialchars($_GET[$clave]) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif;
endforeach; ?>

<!-- Encabezado con datos del paciente / cita seleccionada -->
<?php if ($paciente_actual): ?>
<section class="bg-white p-4 rounded-4 shadow-sm mb-4">
    <div class="row align-items-center g-3">
        <div class="col-7">
            <div class="d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 56px; height: 56px;">
                    <i class="bi bi-person-vcard fs-3"></i>
                </span>
                <div>
                    <?php if ($cita_seleccionada): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill mb-1">
                            Cita #<?= (int)$cita_seleccionada['id_cita'] ?> &bull; <?= htmlspecialchars(ucfirst($cita_seleccionada['estado'])) ?>
                        </span>
                    <?php endif; ?>
                    <h5 class="fw-bold mb-1 text-dark">
                        <?= htmlspecialchars(trim(($paciente_actual['nombre'] ?? '') . ' ' . ($paciente_actual['apellido_paterno'] ?? '') . ' ' . ($paciente_actual['apellido_materno'] ?? ''))) ?>
                    </h5>
                    <p class="text-muted small d-flex flex-wrap gap-3 mb-0">
                        <span><i class="bi bi-calendar-event me-1"></i>Nac.: <?= htmlspecialchars($paciente_actual['fecha_nacimiento'] ?? 'No registrado') ?></span>
                        <span><i class="bi bi-telephone me-1"></i>Contacto: <?= htmlspecialchars($paciente_actual['contacto'] ?? 'N/A') ?></span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-5 text-end">
            <aside class="p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 text-start">
                <strong class="d-block small fw-semibold text-danger mb-1">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> Alergias registradas
                </strong>
                <p class="small text-dark mb-0">
                    <?= htmlspecialchars(!empty($paciente_actual['alergia']) ? $paciente_actual['alergia'] : 'Ninguna registrada') ?>
                </p>
            </aside>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Formulario principal de consulta -->
<?php if (tienePermiso('add_consultation') && tienePermiso('add_prescription')): ?>
<form action="../controllers/consultaController.php" method="POST" class="row g-4">
    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
    <input type="hidden" name="accion" value="guardar">

    <!-- Selección de paciente, cita y signos vitales -->
    <div class="col-4">
        <section class="bg-white p-4 rounded-4 shadow-sm h-100">
            <header class="mb-3">
                <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
                    <i class="bi bi-heart-pulse text-danger me-2"></i>Signos Vitales
                </h6>
            </header>

            <div class="mb-3">
                <label for="id_paciente" class="form-label small fw-semibold">Paciente</label>
                <div class="input-group">
                    <select id="id_paciente" name="id_paciente" class="form-select" required>
                        <option value="">Seleccione un paciente...</option>
                        <?php foreach ($pacientes_lista as $pac): ?>
                            <option value="<?= (int)$pac['id_paciente'] ?>" <?= ((int)($paciente_actual['id_paciente'] ?? 0) === (int)$pac['id_paciente']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(trim($pac['nombre'] . ' ' . $pac['apellido_paterno'] . ' ' . $pac['apellido_materno'])) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (tienePermiso('add_patient')): ?>
                            <option value="__nuevo__">+ Registrar nuevo paciente...</option>
                        <?php endif; ?>
                    </select>
                    <?php if (tienePermiso('add_patient')): ?>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoPacienteConsulta" title="Registrar paciente nuevo">
                            <i class="bi bi-person-plus"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="id_cita" class="form-label small fw-semibold">Cita asociada (opcional)</label>
                <select id="id_cita" name="id_cita" class="form-select">
                    <option value="">Consulta sin cita previa</option>
                    <?php foreach ($citas_pendientes as $cita): ?>
                        <option value="<?= (int)$cita['id_cita'] ?>" <?= ((int)($cita_seleccionada['id_cita'] ?? 0) === (int)$cita['id_cita']) ? 'selected' : '' ?>>
                            #<?= (int)$cita['id_cita'] ?> - <?= htmlspecialchars($cita['paciente_nombre']) ?> (<?= htmlspecialchars(date('d/m H:i', strtotime($cita['fecha']))) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="fecha" class="form-label small fw-semibold">Fecha y hora de consulta</label>
                <input type="datetime-local" id="fecha" name="fecha" class="form-control" value="<?= htmlspecialchars(date('Y-m-d\TH:i')) ?>" required>
            </div>

            <div class="row g-3">
                <?php foreach ($signos_vitales as $sv): ?>
                    <div class="col-6">
                        <label for="<?= $sv['id'] ?>" class="form-label small fw-semibold"><?= $sv['label'] ?></label>
                        <input type="number" id="<?= $sv['id'] ?>" name="<?= $sv['id'] ?>" class="form-control"
                               step="<?= $sv['step'] ?>" min="<?= $sv['min'] ?>" max="<?= $sv['max'] ?>"
                               placeholder="<?= $sv['placeholder'] ?>" required>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <!-- Diagnóstico (tabla receta) y Medicamentos (tabla receta_detalle) -->
    <div class="col-8">
        <section class="bg-white p-4 rounded-4 shadow-sm h-100">
            <header class="mb-3">
                <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
                    <i class="bi bi-file-medical text-primary me-2"></i>Diagnóstico y Receta Médica
                </h6>
            </header>

            <div class="mb-4">
                <label for="diagnostico" class="form-label small fw-semibold">Diagnóstico (Receta)</label>
                <textarea id="diagnostico" name="diagnostico" class="form-control" rows="3" placeholder="Escriba el diagnóstico clínico del paciente..." required></textarea>
            </div>

            <div class="mb-3">
                <header class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label small fw-semibold mb-0">Detalle de Medicamentos</label>
                    <small class="text-muted">El primer medicamento es obligatorio; los siguientes son opcionales</small>
                </header>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Medicamento</th>
                                <th>Dosis</th>
                                <th>Frecuencia</th>
                                <th>Duración</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < 4; $i++): ?>
                                <tr>
                                    <?php foreach ($columnas_medicamento as $col): ?>
                                        <td>
                                            <input type="text" name="<?= $col['name'] ?>[]" class="form-control form-control-sm"
                                                   maxlength="<?= $col['max'] ?>" placeholder="<?= $col['placeholder'] ?>" <?= $i === 0 ? 'required' : '' ?>>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <footer class="d-flex justify-content-end gap-2 mt-4">
                <button type="reset" class="btn btn-light rounded-pill px-4">Limpiar</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-save me-1"></i> Guardar Consulta y Receta
                </button>
            </footer>
        </section>
    </div>
</form>
<?php else: ?>
    <div class="alert alert-warning rounded-4 shadow-sm mb-4">
        <i class="bi bi-lock-fill me-2"></i>No tienes permiso asignado para registrar nuevas consultas médicas y emitir recetas.
    </div>
<?php endif; ?>

<!-- Consultas recientes -->
<section class="bg-white p-4 rounded-4 shadow-sm mt-4">
    <header class="mb-3">
        <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
            <i class="bi bi-clock-history text-primary me-2"></i>Consultas Recientes
        </h6>
    </header>
    <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small sticky-top" style="z-index: 2;">
                <tr>
                    <th>Fecha</th>
                    <th>Paciente</th>
                    <th>Signos Vitales</th>
                    <th>Diagnóstico</th>
                    <th class="text-end">Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($consultas_recientes)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No hay consultas registradas recientemente.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($consultas_recientes as $cons):
                        $fecha_fmt = date('d/m/Y H:i', strtotime($cons['fecha']));
                    ?>
                        <tr>
                            <td class="small text-nowrap"><time><?= htmlspecialchars($fecha_fmt) ?></time></td>
                            <td class="fw-semibold small"><?= htmlspecialchars($cons['paciente_nombre']) ?></td>
                            <td class="small">
                                <span class="badge bg-light text-dark border">Temp: <?= htmlspecialchars($cons['temperatura']) ?> °C</span>
                                <span class="badge bg-light text-dark border">SpO₂: <?= (int)$cons['saturacion_oxigeno'] ?>%</span>
                                <span class="badge bg-light text-dark border">FR: <?= (int)$cons['frecuencia_respiratoria'] ?> rpm</span>
                                <span class="badge bg-light text-dark border">Pulso: <?= (int)$cons['pulso_cardiaco'] ?> lpm</span>
                            </td>
                            <td class="small"><?= htmlspecialchars($cons['diagnostico']) ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill"
                                    data-bs-toggle="modal" data-bs-target="#modalReceta"
                                    data-id="<?= (int)$cons['id_consulta'] ?>"
                                    data-receta="<?= (int)$cons['id_receta'] ?>"
                                    data-paciente="<?= htmlspecialchars($cons['paciente_nombre']) ?>"
                                    data-fecha="<?= htmlspecialchars($fecha_fmt) ?>"
                                    data-diagnostico="<?= htmlspecialchars($cons['diagnostico']) ?>"
                                    data-detalles='<?= htmlspecialchars(json_encode($cons['detalles'] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'>
                                    <i class="bi bi-eye"></i> Ver receta
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Modal único reutilizable para detalle de receta -->
<div class="modal fade" id="modalReceta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <article class="modal-content border-0 shadow rounded-4">
            <header class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalRecetaTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>
            <div class="modal-body p-4">
                <p class="small text-muted mb-2" id="modalRecetaMeta"></p>
                <p class="p-3 rounded-3 bg-light mb-3 small" id="modalRecetaDiagnostico"></p>
                <h6 class="fw-bold small mb-2">Medicamentos recetados</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Medicamento</th>
                                <th>Dosis</th>
                                <th>Frecuencia</th>
                                <th>Duración</th>
                            </tr>
                        </thead>
                        <tbody id="modalRecetaDetalles"></tbody>
                    </table>
                </div>
            </div>
        </article>
    </div>
</div>

<?php if (tienePermiso('add_patient')): ?>
<!-- Modal acceso rápido: Registrar Nuevo Paciente desde Consultas -->
<div class="modal fade" id="modalNuevoPacienteConsulta" tabindex="-1" aria-labelledby="modalNuevoPacienteConsultaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content border-0 shadow rounded-4" action="../controllers/pacienteController.php" method="POST">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
            <input type="hidden" name="tipo_formulario" value="paciente">
            <input type="hidden" name="origen" value="consultas">

            <header class="modal-header bg-primary-subtle border-0">
                <h5 class="modal-title fw-bold text-primary-emphasis" id="modalNuevoPacienteConsultaLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Paciente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>

            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Médico Asignado</label>
                        <select name="id_medico" class="form-select" required>
                            <option value="" selected disabled>Selecciona un médico...</option>
                            <?php foreach ($medicos_lista as $m): ?>
                                <option value="<?= (int)$m['id_medico'] ?>"><?= htmlspecialchars($m['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Nombre(s)</label>
                        <input type="text" name="nombre" class="form-control" maxlength="20" placeholder="Ej. Sofía" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" class="form-control" maxlength="20" placeholder="Ej. Martínez" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Apellido Materno</label>
                        <input type="text" name="apellido_materno" class="form-control" maxlength="20" placeholder="Opcional">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Contacto (Teléfono / Correo)</label>
                        <input type="text" name="contacto" class="form-control" maxlength="100" placeholder="Opcional (Teléfono o correo)">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Alergias</label>
                        <textarea name="alergia" class="form-control" rows="2" placeholder="Ej. Penicilina, ninguna..."></textarea>
                    </div>
                </div>
            </div>

            <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Guardar y Seleccionar</button>
            </footer>
        </form>
    </div>
</div>
<?php endif; ?>
