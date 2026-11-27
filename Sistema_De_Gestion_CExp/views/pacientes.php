<?php
require_once '../config/auth.php';
require_once '../controllers/pacienteController.php';
$titulo_pagina = 'Pacientes - Consultorio Médico';
$pagina_activa = 'pacientes';

if (!defined('EN_PLANTILLA_BASE')) {
    $vista = __FILE__;
    require_once '../templatesReutilizables/template.base.php';
    return;
}

$datos_pacientes = obtenerDatosPacientes();
$medicos_lista   = $datos_pacientes['medicos_lista'] ?? [];
$pacientes_lista = $datos_pacientes['pacientes_lista'] ?? [];
?>

<?php foreach (['mensaje' => ['success', 'check-circle-fill'], 'error' => ['danger', 'exclamation-triangle-fill']] as $clave => [$color, $icono]):
    if (!empty($_GET[$clave])): ?>
        <div class="alert alert-<?= $color ?> alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-<?= $icono ?> me-2"></i><?= htmlspecialchars($_GET[$clave]) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif;
endforeach; ?>

<!-- SECCIÓN PRINCIPAL DE PACIENTES -->
<section class="bg-white p-4 rounded-4 shadow-sm mb-4">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-people text-primary me-2"></i>Gestión de Pacientes
            </h4>
            <p class="text-muted small mb-0">Directorio clínico de pacientes, alergias y antecedentes médicos.</p>
        </div>
        <?php if (tienePermiso('add_patient')): ?>
            <button type="button" class="btn btn-primary fw-semibold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalPaciente">
                <i class="bi bi-person-plus-fill me-1"></i>Nuevo Paciente
            </button>
        <?php endif; ?>
    </header>

    <!-- Barra de Filtros -->
    <form class="row g-2 align-items-center mb-3" id="formFiltrosPacientes" onsubmit="return false;">
        <div class="col-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="search" id="filtroBuscarPaciente" class="form-control border-start-0" placeholder="Buscar por ID, nombre, contacto o alergia...">
            </div>
        </div>

        <div class="col-auto">
            <select id="filtroMedicoPaciente" class="form-select form-select-sm">
                <option value="">Todos los médicos</option>
                <?php foreach ($medicos_lista as $m): ?>
                    <option value="<?= (int)$m['id_medico'] ?>">
                        <?= htmlspecialchars($m['nombre_completo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-auto">
            <select id="filtroAlergiaPaciente" class="form-select form-select-sm">
                <option value="">Todas las condiciones</option>
                <option value="con_alergia">Con alergias</option>
                <option value="sin_alergia">Sin alergias</option>
            </select>
        </div>

        <div class="col-auto">
            <button type="button" id="btnLimpiarPacientes" class="btn btn-outline-secondary btn-sm rounded-3">Limpiar</button>
        </div>
    </form>

    <!-- Tabla de Pacientes -->
    <div class="table-responsive" style="max-height: 540px; overflow-y: auto;">
        <table class="table table-hover align-middle mb-0" id="tablaPacientes">
            <thead class="table-primary sticky-top" style="z-index: 2;">
                <tr>
                    <th scope="col" class="ps-3">ID</th>
                    <th scope="col">Nombre del Paciente</th>
                    <th scope="col">Edad / Fecha Nac.</th>
                    <th scope="col">Contacto</th>
                    <th scope="col">Alergias</th>
                    <th scope="col">Médico Asignado</th>
                    <th scope="col" class="text-end pe-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pacientes_lista)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            No se encontraron pacientes con los filtros seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pacientes_lista as $p):
                        $id_p = (int)$p['id_paciente'];
                        $nombre_completo = trim($p['nombre'] . ' ' . $p['apellido_paterno'] . ' ' . $p['apellido_materno']);
                        $tiene_alergia = !empty(trim($p['alergia'] ?? ''));
                    ?>
                        <tr data-medico="<?= (int)$p['id_medico'] ?>"
                            data-alergia="<?= $tiene_alergia ? 'con_alergia' : 'sin_alergia' ?>"
                            data-busqueda="<?= htmlspecialchars(strtolower("#{$id_p} {$id_p} {$nombre_completo} " . ($p['contacto'] ?? '') . ' ' . ($p['alergia'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                            <td class="ps-3 fw-medium text-secondary">#<?= $id_p ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($nombre_completo) ?></td>
                            <td>
                                <small class="text-muted"><?= !empty($p['fecha_nacimiento']) ? htmlspecialchars(date('d/m/Y', strtotime($p['fecha_nacimiento']))) : 'N/A' ?></small>
                            </td>
                            <td class="text-secondary small">
                                <i class="bi bi-telephone me-1"></i><?= htmlspecialchars(($p['contacto'] ?? '') !== '' ? $p['contacto'] : 'No registrado') ?>
                            </td>
                            <td>
                                <?php if ($tiene_alergia): ?>
                                    <span class="badge bg-danger-subtle text-danger-emphasis">
                                        <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($p['alergia']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">Ninguna</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-secondary small"><?= htmlspecialchars($p['medico_nombre']) ?></td>
                            <td class="text-end pe-3">
                                <a href="historial.php?id_paciente=<?= $id_p ?>" class="btn btn-outline-info btn-sm rounded-3 me-1">
                                    <i class="bi bi-clock-history"></i> Historial
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3 me-1"
                                    data-bs-toggle="modal" data-bs-target="#modalAntecedentes"
                                    data-id="<?= $id_p ?>"
                                    data-nombre="<?= htmlspecialchars($nombre_completo, ENT_QUOTES, 'UTF-8') ?>"
                                    data-antecedentes="<?= htmlspecialchars($p['antecedentes'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="bi bi-journal-medical"></i> Antecedentes
                                </button>
                                <?php if (tienePermiso('change_patient')): ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-3 me-1"
                                        data-bs-toggle="modal" data-bs-target="#modalPaciente"
                                        data-id="<?= $id_p ?>"
                                        data-nombre="<?= htmlspecialchars($p['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-paterno="<?= htmlspecialchars($p['apellido_paterno'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-materno="<?= htmlspecialchars($p['apellido_materno'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-nacimiento="<?= htmlspecialchars($p['fecha_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-contacto="<?= htmlspecialchars($p['contacto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-medico="<?= (int)$p['id_medico'] ?>"
                                        data-alergia="<?= htmlspecialchars($p['alergia'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3"
                                        data-bs-toggle="modal" data-bs-target="#modalEliminarPaciente"
                                        data-id="<?= $id_p ?>"
                                        data-nombre="<?= htmlspecialchars($nombre_completo, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- MODAL ÚNICO REUTILIZABLE: CREAR / EDITAR PACIENTE -->
<div class="modal fade" id="modalPaciente" tabindex="-1" aria-labelledby="modalPacienteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content border-0 shadow" action="../controllers/pacienteController.php" method="POST">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
            <input type="hidden" name="tipo_formulario" value="paciente">
            <input type="hidden" name="id_paciente" id="pac_id" value="">

            <header class="modal-header bg-primary-subtle border-0">
                <h5 class="modal-title fw-bold text-primary-emphasis" id="modalPacienteTitulo">Registrar Nuevo Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>

            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <label for="pac_nombre" class="form-label fw-semibold small">Nombre(s)</label>
                        <input type="text" name="nombre" id="pac_nombre" class="form-control" maxlength="20" placeholder="Ej. Carlos" required>
                    </div>
                    <div class="col-4">
                        <label for="pac_paterno" class="form-label fw-semibold small">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" id="pac_paterno" class="form-control" maxlength="20" placeholder="Ej. Ramírez" required>
                    </div>
                    <div class="col-4">
                        <label for="pac_materno" class="form-label fw-semibold small">Apellido Materno</label>
                        <input type="text" name="apellido_materno" id="pac_materno" class="form-control" maxlength="20" placeholder="Ej. López">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <label for="pac_nacimiento" class="form-label fw-semibold small">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="pac_nacimiento" class="form-control" required>
                    </div>
                    <div class="col-4">
                        <label for="pac_contacto" class="form-label fw-semibold small">Teléfono o Correo de Contacto</label>
                        <input type="text" name="contacto" id="pac_contacto" class="form-control" maxlength="100" placeholder="Opcional (Teléfono o correo)">
                    </div>
                    <div class="col-4">
                        <label for="pac_medico" class="form-label fw-semibold small">Médico Asignado</label>
                        <select name="id_medico" id="pac_medico" class="form-select" required>
                            <option value="" selected disabled>Selecciona un médico...</option>
                            <?php foreach ($medicos_lista as $m): ?>
                                <option value="<?= (int)$m['id_medico'] ?>"><?= htmlspecialchars($m['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label for="pac_alergia" class="form-label fw-semibold small">Alergias Conocidas</label>
                    <textarea name="alergia" id="pac_alergia" class="form-control" rows="2" placeholder="Ej. Penicilina, polen, sulfas... (Dejar en blanco si no presenta alergias)"></textarea>
                </div>
            </div>

            <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold" id="modalPacienteBtnGuardar">Guardar Paciente</button>
            </footer>
        </form>
    </div>
</div>

<!-- MODAL ÚNICO: ANTECEDENTES MÉDICOS -->
<div class="modal fade" id="modalAntecedentes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow" action="../controllers/pacienteController.php" method="POST">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
            <input type="hidden" name="tipo_formulario" value="antecedentes">
            <input type="hidden" name="id_paciente" id="ant_id_paciente" value="">

            <header class="modal-header bg-primary-subtle border-0">
                <h5 class="modal-title fw-bold text-primary-emphasis">
                    <i class="bi bi-journal-medical me-2"></i>Antecedentes Médicos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>

            <div class="modal-body p-4">
                <p class="mb-3 small text-muted">
                    Paciente: <strong class="text-dark" id="ant_nombre_paciente"></strong>
                </p>

                <div class="mb-2">
                    <label for="ant_notas" class="form-label fw-semibold small">Notas de Antecedentes Clínicos y Familiares</label>
                    <textarea name="notas" id="ant_notas" class="form-control" rows="5" required></textarea>
                </div>
            </div>

            <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Guardar Antecedentes</button>
            </footer>
        </form>
    </div>
</div>

<!-- MODAL ÚNICO: CONFIRMAR ELIMINACIÓN DE PACIENTE -->
<div class="modal fade" id="modalEliminarPaciente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow">
            <header class="modal-header bg-danger-subtle border-0">
                <h5 class="modal-title fw-bold text-danger-emphasis">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar Paciente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>

            <div class="modal-body p-4 text-center">
                <i class="bi bi-trash3 fs-1 text-danger d-block mb-3"></i>
                <p class="mb-1 fs-6">¿Estás seguro de que deseas eliminar el expediente del paciente:</p>
                <p class="fw-bold fs-5 text-dark mb-0" id="elim_nombre_paciente"></p>
                <small class="text-muted">Se eliminarán también sus citas y antecedentes asociados.</small>
            </div>

            <footer class="modal-footer border-0 pt-0 px-4 pb-4 justify-content-center">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="../controllers/pacienteController.php" class="d-inline">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" id="elim_id_paciente" value="">
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-semibold">
                        <i class="bi bi-trash me-1"></i>Sí, eliminar
                    </button>
                </form>
            </footer>
        </article>
    </div>
</div>
