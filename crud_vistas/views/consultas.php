<?php
$titulo_pagina = 'Consultas Médicas - CEx+';
$pagina_activa = 'consultas';

if (empty($vista)) {
    $vista = __FILE__;
    require __DIR__ . '/../templatesReutilizables/template.base.php';
    return;
}

// VARIABLES TEMPPORRALES CHAVAL , HASTA QUE HAGAS LOS CONTROLADORES
$pacientes_lista     = $pacientes_lista ?? [];
$citas_pendientes    = $citas_pendientes ?? [];
$cita_seleccionada   = $cita_seleccionada ?? null;
$paciente_actual     = $paciente_actual ?? null;
$consultas_recientes = $consultas_recientes ?? [];
?>

<header class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #0d1b3e;">Consulta Médica</h4>
        <p class="text-muted small mb-0">Registro de signos vitales, diagnóstico y receta médica</p>
    </div>
    <a href="historial.php?id_paciente=<?= (int)($paciente_actual['id_paciente'] ?? 0) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
        <i class="bi bi-journal-medical me-1"></i> Ver Expediente
    </a>
</header>

<?php if (!empty($_GET['mensaje'])): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_GET['mensaje']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<!-- Encabezado con datos del paciente / cita seleccionada -->
<section class="bg-white p-4 rounded-4 shadow-sm mb-4">
    <div class="row align-items-center g-3">
        <div class="col-7">
            <div class="d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 56px; height: 56px;">
                    <i class="bi bi-person-vcard fs-3"></i>
                </span>
                <div>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill mb-1">
                        Cita #<?= (int)($cita_seleccionada['id_cita'] ?? 0) ?> &bull; <?= htmlspecialchars(ucfirst($cita_seleccionada['estado'] ?? 'programada')) ?>
                    </span>
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

<!-- Formulario principal de consulta -->
<form action="../controllers/consultaController.php" method="POST" class="row g-4">
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
                <select id="id_paciente" name="id_paciente" class="form-select" required>
                    <option value="">Seleccione un paciente...</option>
                    <?php foreach ($pacientes_lista as $pac): ?>
                        <option value="<?= (int)$pac['id_paciente'] ?>" <?= ((int)($paciente_actual['id_paciente'] ?? 0) === (int)$pac['id_paciente']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(trim($pac['nombre'] . ' ' . $pac['apellido_paterno'] . ' ' . $pac['apellido_materno'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                <div class="col-6">
                    <label for="temperatura" class="form-label small fw-semibold">Temperatura (°C)</label>
                    <input type="number" id="temperatura" name="temperatura" class="form-control" step="0.01" min="30.00" max="45.00" placeholder="36.50" required>
                </div>
                <div class="col-6">
                    <label for="saturacion_oxigeno" class="form-label small fw-semibold">Saturación O₂ (%)</label>
                    <input type="number" id="saturacion_oxigeno" name="saturacion_oxigeno" class="form-control" min="50" max="100" placeholder="98" required>
                </div>
                <div class="col-6">
                    <label for="frecuencia_respiratoria" class="form-label small fw-semibold">Frec. Respiratoria</label>
                    <input type="number" id="frecuencia_respiratoria" name="frecuencia_respiratoria" class="form-control" min="5" max="80" placeholder="16" required>
                </div>
                <div class="col-6">
                    <label for="pulso_cardiaco" class="form-label small fw-semibold">Pulso Cardíaco (lpm)</label>
                    <input type="number" id="pulso_cardiaco" name="pulso_cardiaco" class="form-control" min="30" max="250" placeholder="72" required>
                </div>
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
                                    <td>
                                        <input type="text" name="medicamento[]" class="form-control form-control-sm" maxlength="100" placeholder="Ej. Paracetamol 500 mg" <?= $i === 0 ? 'required' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="dosis[]" class="form-control form-control-sm" maxlength="50" placeholder="Ej. 1 tableta" <?= $i === 0 ? 'required' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="frecuencia[]" class="form-control form-control-sm" maxlength="50" placeholder="Ej. Cada 8 h" <?= $i === 0 ? 'required' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="duracion[]" class="form-control form-control-sm" maxlength="50" placeholder="Ej. 5 días" <?= $i === 0 ? 'required' : '' ?>>
                                    </td>
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

<!-- Consultas recientes -->
<section class="bg-white p-4 rounded-4 shadow-sm mt-4">
    <header class="mb-3">
        <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
            <i class="bi bi-clock-history text-primary me-2"></i>Consultas Recientes
        </h6>
    </header>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
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
                    <?php foreach ($consultas_recientes as $cons): ?>
                        <tr>
                            <td class="small text-nowrap"><time><?= htmlspecialchars(date('d/m/Y H:i', strtotime($cons['fecha']))) ?></time></td>
                            <td class="fw-semibold small"><?= htmlspecialchars($cons['paciente_nombre']) ?></td>
                            <td class="small">
                                <span class="badge bg-light text-dark border">Temp: <?= htmlspecialchars($cons['temperatura']) ?> °C</span>
                                <span class="badge bg-light text-dark border">SpO₂: <?= (int)$cons['saturacion_oxigeno'] ?>%</span>
                                <span class="badge bg-light text-dark border">FR: <?= (int)$cons['frecuencia_respiratoria'] ?> rpm</span>
                                <span class="badge bg-light text-dark border">Pulso: <?= (int)$cons['pulso_cardiaco'] ?> lpm</span>
                            </td>
                            <td class="small"><?= htmlspecialchars($cons['diagnostico']) ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalConsulta<?= (int)$cons['id_consulta'] ?>">
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

<!-- Modales de detalle de receta -->
<?php foreach ($consultas_recientes as $cons): ?>
    <div class="modal fade" id="modalConsulta<?= (int)$cons['id_consulta'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <article class="modal-content border-0 shadow rounded-4">
                <header class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Consulta #<?= (int)$cons['id_consulta'] ?> &bull; Receta #<?= (int)$cons['id_receta'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </header>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-2">
                        <strong>Paciente:</strong> <?= htmlspecialchars($cons['paciente_nombre']) ?> |
                        <strong>Fecha:</strong> <time><?= htmlspecialchars(date('d/m/Y H:i', strtotime($cons['fecha']))) ?></time>
                    </p>
                    <p class="p-3 rounded-3 bg-light mb-3 small">
                        <strong>Diagnóstico:</strong> <?= htmlspecialchars($cons['diagnostico']) ?>
                    </p>
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
                            <tbody>
                                <?php foreach ($cons['detalles'] ?? [] as $det): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($det['medicamento']) ?></td>
                                        <td><?= htmlspecialchars($det['dosis']) ?></td>
                                        <td><?= htmlspecialchars($det['frecuencia']) ?></td>
                                        <td><?= htmlspecialchars($det['duracion']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        </div>
    </div>
<?php endforeach; ?>
