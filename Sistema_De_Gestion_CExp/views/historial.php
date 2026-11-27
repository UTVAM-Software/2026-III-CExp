<?php
require_once '../config/auth.php';
require_once '../controllers/consultaController.php';
$titulo_pagina = 'Historial Clínico - CEx+';
$pagina_activa = 'historial';

if (empty($vista)) {
    $vista = __FILE__;
    require __DIR__ . '/../templatesReutilizables/template.base.php';
    return;
}

$datos_historial          = obtenerDatosHistorial($_GET['id_paciente'] ?? 0);
$pacientes_lista          = $datos_historial['pacientes_lista'] ?? [];
$id_paciente_seleccionado = $datos_historial['id_paciente_seleccionado'] ?? 0;
$paciente_actual          = $datos_historial['paciente_actual'] ?? null;
$antecedentes_paciente    = $datos_historial['antecedentes_paciente'] ?? [];
$historial_consultas      = $datos_historial['historial_consultas'] ?? [];
?>

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

<header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #0d1b3e;">Historial Clínico y Expedientes</h4>
        <p class="text-muted small mb-0">Consulta de antecedentes médicos, alergias y consultas previas</p>
    </div>

    <!-- Selector de paciente -->
    <form method="GET" action="historial.php" class="d-flex gap-2">
        <select name="id_paciente" class="form-select form-select-sm rounded-pill px-3" required>
            <?php foreach ($pacientes_lista as $pac): ?>
                <option value="<?= (int)$pac['id_paciente'] ?>" <?= ((int)$pac['id_paciente'] === $id_paciente_seleccionado) ? 'selected' : '' ?>>
                    <?= htmlspecialchars(trim($pac['nombre'] . ' ' . $pac['apellido_paterno'] . ' ' . $pac['apellido_materno'])) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-search me-1"></i> Consultar
        </button>
    </form>
</header>

<?php if ($paciente_actual): ?>
    <section class="row g-4 mb-4">
        <!-- Datos generales del paciente (tabla paciente) -->
        <div class="col-5">
            <article class="bg-white p-4 rounded-4 shadow-sm h-100">
                <header class="d-flex align-items-center gap-3 mb-3">
                    <span class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 52px; height: 52px;">
                        <i class="bi bi-person-badge fs-4"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <?= htmlspecialchars(trim($paciente_actual['nombre'] . ' ' . $paciente_actual['apellido_paterno'] . ' ' . $paciente_actual['apellido_materno'])) ?>
                        </h5>
                        <small class="text-muted">Expediente #<?= (int)$paciente_actual['id_paciente'] ?></small>
                    </div>
                </header>

                <ul class="list-unstyled small mb-3">
                    <li class="mb-2">
                        <i class="bi bi-calendar3 text-primary me-2"></i>
                        <strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($paciente_actual['fecha_nacimiento']) ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-telephone text-primary me-2"></i>
                        <strong>Contacto:</strong> <?= htmlspecialchars($paciente_actual['contacto']) ?>
                    </li>
                </ul>

                <aside class="p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25">
                    <strong class="d-block small fw-semibold text-danger mb-1">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Alergias
                    </strong>
                    <p class="small text-dark mb-0">
                        <?= htmlspecialchars(!empty($paciente_actual['alergia']) ? $paciente_actual['alergia'] : 'Ninguna registrada') ?>
                    </p>
                </aside>
            </article>
        </div>

        <!-- Antecedentes médicos (tabla antecedentes) -->
        <div class="col-7">
            <section class="bg-white p-4 rounded-4 shadow-sm h-100">
                <header class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
                        <i class="bi bi-clipboard2-pulse text-primary me-2"></i>Antecedentes Clínicos
                    </h6>
                </header>

                <?php if (empty($antecedentes_paciente)): ?>
                    <p class="text-muted small mb-0">No hay notas de antecedentes registradas para este paciente.</p>
                <?php else: ?>
                    <?php foreach ($antecedentes_paciente as $ant): ?>
                        <article class="p-3 rounded-3 bg-light mb-2 small">
                            <?= nl2br(htmlspecialchars($ant['notas'])) ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>

                <hr class="my-3">
                <form action="../controllers/pacienteController.php" method="POST">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="tipo_formulario" value="antecedentes">
                    <input type="hidden" name="origen" value="historial">
                    <input type="hidden" name="id_paciente" value="<?= (int)$paciente_actual['id_paciente'] ?>">
                    <label for="notas" class="form-label small fw-semibold">Agregar nota de antecedentes</label>
                    <div class="input-group">
                        <input type="text" id="notas" name="notas" class="form-control form-control-sm" placeholder="Ej. Cirugía previa, padecimiento crónico..." required>
                        <button type="submit" class="btn btn-outline-primary btn-sm">Guardar</button>
                    </div>
                </form>
            </section>
        </div>
    </section>

    <!-- Línea de consultas previas (tablas consulta + receta + receta_detalle) -->
    <section class="bg-white p-4 rounded-4 shadow-sm">
        <header class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0" style="color: #0d1b3e;">
                <i class="bi bi-journal-text text-primary me-2"></i>Consultas Previas
            </h6>
            <a href="consultas.php?id_paciente=<?= (int)$paciente_actual['id_paciente'] ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Nueva Consulta
            </a>
        </header>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Fecha</th>
                        <th>Signos Vitales</th>
                        <th>Diagnóstico</th>
                        <th class="text-end">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historial_consultas)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Este paciente aún no tiene consultas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historial_consultas as $cons): ?>
                            <tr>
                                <td class="small text-nowrap"><time><?= htmlspecialchars(date('d/m/Y H:i', strtotime($cons['fecha']))) ?></time></td>
                                <td class="small">
                                    <span class="badge bg-light text-dark border">Temp: <?= htmlspecialchars($cons['temperatura']) ?> °C</span>
                                    <span class="badge bg-light text-dark border">SpO₂: <?= (int)$cons['saturacion_oxigeno'] ?>%</span>
                                    <span class="badge bg-light text-dark border">FR: <?= (int)$cons['frecuencia_respiratoria'] ?> rpm</span>
                                    <span class="badge bg-light text-dark border">Pulso: <?= (int)$cons['pulso_cardiaco'] ?> lpm</span>
                                </td>
                                <td class="small"><?= htmlspecialchars($cons['diagnostico']) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalHistorial<?= (int)$cons['id_consulta'] ?>">
                                        <i class="bi bi-eye"></i> Ver detalle
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Modales de detalle -->
    <?php foreach ($historial_consultas as $cons): ?>
        <div class="modal fade" id="modalHistorial<?= (int)$cons['id_consulta'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <article class="modal-content border-0 shadow rounded-4">
                    <header class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Consulta #<?= (int)$cons['id_consulta'] ?> &bull; Receta #<?= (int)$cons['id_receta'] ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </header>
                    <div class="modal-body p-4">
                        <div class="row g-2 mb-3 small">
                            <div class="col-3"><strong>Temperatura:</strong> <?= htmlspecialchars($cons['temperatura']) ?> °C</div>
                            <div class="col-3"><strong>SpO₂:</strong> <?= (int)$cons['saturacion_oxigeno'] ?>%</div>
                            <div class="col-3"><strong>Frec. Resp.:</strong> <?= (int)$cons['frecuencia_respiratoria'] ?> rpm</div>
                            <div class="col-3"><strong>Pulso:</strong> <?= (int)$cons['pulso_cardiaco'] ?> lpm</div>
                        </div>
                        <p class="p-3 rounded-3 bg-light mb-3 small">
                            <strong>Diagnóstico:</strong> <?= htmlspecialchars($cons['diagnostico']) ?>
                        </p>
                        <h6 class="fw-bold small mb-2">Receta Médica</h6>
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
<?php else: ?>
    <div class="alert alert-info rounded-4">No hay pacientes registrados para consultar su historial.</div>
<?php endif; ?>
