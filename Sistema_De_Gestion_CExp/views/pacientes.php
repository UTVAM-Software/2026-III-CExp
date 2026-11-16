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

$filtro_buscar   = trim($_GET['buscar'] ?? '');
$filtro_medico   = $_GET['medico'] ?? '';
$filtro_alergia  = $_GET['alergia'] ?? '';

$datos_pacientes = obtenerDatosPacientes($filtro_buscar, $filtro_medico, $filtro_alergia);
$medicos_lista   = $datos_pacientes['medicos_lista'] ?? [];
$pacientes_lista = $datos_pacientes['pacientes_lista'] ?? [];
?>

<?php if (!empty($_GET['mensaje'])): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_GET['mensaje']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<!-- SECCION PRINCIPAL DE PACIENTES -->
<section class="bg-white p-4 rounded-4 shadow-sm mb-4">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-people text-primary me-2"></i>Gestión de Pacientes
            </h4>
            <p class="text-muted small mb-0">Directorio clínico de pacientes, alergias y antecedentes médicos.</p>
        </div>
        <?php if (tienePermiso('add_patient')): ?>
            <button type="button" class="btn btn-primary fw-semibold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalNuevoPaciente">
                <i class="bi bi-person-plus-fill me-1"></i>Nuevo Paciente
            </button>
        <?php endif; ?>
    </header>

    <!-- Barra de Filtros  -->
    <form class="row g-2 align-items-center mb-3" method="GET" action="pacientes.php">
        <div class="col-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="search" name="buscar" class="form-control border-start-0" placeholder="Buscar por nombre, contacto o alergia..." value="<?= htmlspecialchars($filtro_buscar) ?>">
            </div>
        </div>

        <div class="col-auto">
            <select name="medico" class="form-select form-select-sm">
                <option value="">Todos los médicos</option>
                <?php foreach ($medicos_lista as $m): ?>
                    <option value="<?= (int)$m['id_medico'] ?>" <?= ($filtro_medico == $m['id_medico']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['nombre_completo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-auto">
            <select name="alergia" class="form-select form-select-sm">
                <option value="">Todas las condiciones</option>
                <option value="con_alergia" <?= ($filtro_alergia === 'con_alergia') ? 'selected' : '' ?>>Con alergias</option>
                <option value="sin_alergia" <?= ($filtro_alergia === 'sin_alergia') ? 'selected' : '' ?>>Sin alergias</option>
            </select>
        </div>

        <div class="col-auto d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm rounded-3 px-3">
                <i class="bi bi-funnel me-1"></i>Filtrar
            </button>
            <?php if ($filtro_buscar !== '' || $filtro_medico !== '' || $filtro_alergia !== ''): ?>
                <a href="pacientes.php" class="btn btn-outline-secondary btn-sm rounded-3">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Tabla de Pacientes -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
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
                        <tr>
                            <td class="ps-3 fw-medium text-secondary">#<?= $id_p ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($nombre_completo) ?></td>
                            <td>
                                <small class="text-muted"><?= !empty($p['fecha_nacimiento']) ? htmlspecialchars(date('d/m/Y', strtotime($p['fecha_nacimiento']))) : 'N/A' ?></small>
                            </td>
                            <td class="text-secondary small">
                                <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($p['contacto']) ?>
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
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3 me-1" data-bs-toggle="modal" data-bs-target="#modalAntecedentes<?= $id_p ?>">
                                    <i class="bi bi-journal-medical"></i> Antecedentes
                                </button>
                                <?php if (tienePermiso('change_patient')): ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-3 me-1" data-bs-toggle="modal" data-bs-target="#modalEditarPaciente<?= $id_p ?>">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalEliminarPaciente<?= $id_p ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- MODALES POR CADA PACIENTE -->
<?php foreach ($pacientes_lista as $p):
    $id_p = (int)$p['id_paciente'];
    $nombre_completo = trim($p['nombre'] . ' ' . $p['apellido_paterno'] . ' ' . $p['apellido_materno']);
?>
    <!-- MODAL EDITAR PACIENTE #<?= $id_p ?> -->
    <div class="modal fade" id="modalEditarPaciente<?= $id_p ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="modal-content border-0 shadow" action="../controllers/pacienteController.php" method="POST">
                <header class="modal-header bg-primary-subtle border-0">
                    <h5 class="modal-title fw-bold text-primary-emphasis">
                        <i class="bi bi-pencil-square me-2"></i>Editar Paciente #<?= $id_p ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </header>

                <div class="modal-body p-4">
                    <input type="hidden" name="tipo_formulario" value="paciente">
                    <input type="hidden" name="id_paciente" value="<?= $id_p ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <label class="form-label fw-semibold small">Nombre(s)</label>
                            <input type="text" name="nombre" class="form-control" maxlength="20" value="<?= htmlspecialchars($p['nombre']) ?>" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold small">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" class="form-control" maxlength="20" value="<?= htmlspecialchars($p['apellido_paterno']) ?>" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold small">Apellido Materno</label>
                            <input type="text" name="apellido_materno" class="form-control" maxlength="20" value="<?= htmlspecialchars($p['apellido_materno']) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <label class="form-label fw-semibold small">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" value="<?= htmlspecialchars($p['fecha_nacimiento']) ?>" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold small">Teléfono o Correo</label>
                            <input type="text" name="contacto" class="form-control" maxlength="100" value="<?= htmlspecialchars($p['contacto']) ?>" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold small">Médico Asignado</label>
                            <select name="id_medico" class="form-select" required>
                                <?php foreach ($medicos_lista as $m): ?>
                                    <option value="<?= (int)$m['id_medico'] ?>" <?= ((int)$p['id_medico'] === (int)$m['id_medico']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Alergias Conocidas</label>
                        <textarea name="alergia" class="form-control" rows="2"><?= htmlspecialchars($p['alergia']) ?></textarea>
                    </div>
                </div>

                <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Actualizar Paciente</button>
                </footer>
            </form>
        </div>
    </div>

    <!-- MODAL ANTECEDENTES MÉDICOS #<?= $id_p ?> -->
    <div class="modal fade" id="modalAntecedentes<?= $id_p ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content border-0 shadow" action="../controllers/pacienteController.php" method="POST">
                <header class="modal-header bg-primary-subtle border-0">
                    <h5 class="modal-title fw-bold text-primary-emphasis">
                        <i class="bi bi-journal-medical me-2"></i>Antecedentes Médicos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </header>

                <div class="modal-body p-4">
                    <input type="hidden" name="tipo_formulario" value="antecedentes">
                    <input type="hidden" name="id_paciente" value="<?= $id_p ?>">

                    <p class="mb-3 small text-muted">
                        Paciente: <strong class="text-dark"><?= htmlspecialchars($nombre_completo) ?></strong>
                    </p>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Notas de Antecedentes Clínicos y Familiares</label>
                        <textarea name="notas" class="form-control" rows="5" required><?= htmlspecialchars($p['antecedentes']) ?></textarea>
                    </div>
                </div>

                <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Guardar Antecedentes</button>
                </footer>
            </form>
        </div>
    </div>

    <!-- MODAL ELIMINAR PACIENTE #<?= $id_p ?> -->
    <div class="modal fade" id="modalEliminarPaciente<?= $id_p ?>" tabindex="-1" aria-hidden="true">
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
                    <p class="fw-bold fs-5 text-dark mb-0"><?= htmlspecialchars($nombre_completo) ?></p>
                    <small class="text-muted">Se eliminarán también sus citas y antecedentes asociados.</small>
                </div>

                <footer class="modal-footer border-0 pt-0 px-4 pb-4 justify-content-center">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <a href="../controllers/pacienteController.php?accion=eliminar&id=<?= $id_p ?>" class="btn btn-danger rounded-3 px-4 fw-semibold">
                        <i class="bi bi-trash me-1"></i>Sí, eliminar
                    </a>
                </footer>
            </article>
        </div>
    </div>
<?php endforeach; ?>

<!-- MODAL NUEVO PACIENTE -->
<div class="modal fade" id="modalNuevoPaciente" tabindex="-1" aria-labelledby="modalNuevoPacienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content border-0 shadow" action="../controllers/pacienteController.php" method="POST">
            <header class="modal-header bg-primary-subtle border-0">
                <h5 class="modal-title fw-bold text-primary-emphasis" id="modalNuevoPacienteLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Paciente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>

            <div class="modal-body p-4">
                <input type="hidden" name="tipo_formulario" value="paciente">
                <input type="hidden" name="id_paciente" value="">

                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <label for="nuevo_nombre" class="form-label fw-semibold small">Nombre(s)</label>
                        <input type="text" name="nombre" id="nuevo_nombre" class="form-control" maxlength="20" placeholder="Ej. Carlos" required>
                    </div>
                    <div class="col-4">
                        <label for="nuevo_paterno" class="form-label fw-semibold small">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" id="nuevo_paterno" class="form-control" maxlength="20" placeholder="Ej. Ramírez" required>
                    </div>
                    <div class="col-4">
                        <label for="nuevo_materno" class="form-label fw-semibold small">Apellido Materno</label>
                        <input type="text" name="apellido_materno" id="nuevo_materno" class="form-control" maxlength="20" placeholder="Ej. López">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <label for="nuevo_nacimiento" class="form-label fw-semibold small">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="nuevo_nacimiento" class="form-control" required>
                    </div>
                    <div class="col-4">
                        <label for="nuevo_contacto" class="form-label fw-semibold small">Teléfono o Correo de Contacto</label>
                        <input type="text" name="contacto" id="nuevo_contacto" class="form-control" maxlength="100" placeholder="Ej. 555-123-4567" required>
                    </div>
                    <div class="col-4">
                        <label for="nuevo_medico" class="form-label fw-semibold small">Médico Asignado</label>
                        <select name="id_medico" id="nuevo_medico" class="form-select" required>
                            <option value="" selected disabled>Selecciona un médico...</option>
                            <?php foreach ($medicos_lista as $m): ?>
                                <option value="<?= (int)$m['id_medico'] ?>"><?= htmlspecialchars($m['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label for="nuevo_alergia" class="form-label fw-semibold small">Alergias Conocidas</label>
                    <textarea name="alergia" id="nuevo_alergia" class="form-control" rows="2" placeholder="Ej. Penicilina, polen, sulfas... (Dejar en blanco si no presenta alergias)"></textarea>
                </div>
            </div>

            <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Guardar Paciente</button>
            </footer>
        </form>
    </div>
</div>
