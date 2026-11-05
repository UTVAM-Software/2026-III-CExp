<?php
/**
 * Módulo de Gestión de Pacientes y Antecedentes
 * Proyecto: Consultorio Médico CExp+
 * Estructura de BD: tablas `paciente`, `medico` y `antecedentes`
 */

$titulo_pagina = 'Pacientes - Consultorio Medico Maroca';
$pagina_activa = 'pacientes';

// Método de cálculo: calcula automáticamente la edad a partir de la fecha de nacimiento (Punto 3 del Entregable)
function calcularEdad($fecha_nacimiento) {
    if (empty($fecha_nacimiento)) return 0;
    $nacimiento = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    return $hoy->diff($nacimiento)->y;
}

// Datos de médicos para asignación en el formulario
$medicos_lista = [
    ['id_medico' => 1, 'nombre_completo' => 'Dra. Samantha Samudio', 'especialidad' => 'Médico General'],
    ['id_medico' => 2, 'nombre_completo' => 'Dr. Roberto Gómez', 'especialidad' => 'Pediatría'],
];

// Datos de prueba para el listado visual
$pacientes_lista = [
    [
        'id_paciente'      => 1,
        'id_medico'        => 1,
        'medico_nombre'    => 'Dra. Samantha Samudio',
        'nombre'           => 'Carlos',
        'apellido_paterno' => 'Ramírez',
        'apellido_materno' => 'López',
        'fecha_nacimiento' => '1990-05-14',
        'alergia'          => 'Penicilina',
        'contacto'         => '555-123-4567',
        'antecedentes'     => 'Hipertensión arterial controlada desde 2022. Padre con diabetes tipo 2.'
    ],
    [
        'id_paciente'      => 2,
        'id_medico'        => 1,
        'medico_nombre'    => 'Dra. Samantha Samudio',
        'nombre'           => 'María Fernanda',
        'apellido_paterno' => 'Torres',
        'apellido_materno' => 'García',
        'fecha_nacimiento' => '1998-11-23',
        'alergia'          => '',
        'contacto'         => 'mfernanda@correo.com',
        'antecedentes'     => 'Sin antecedentes patológicos de relevancia. Cirugía de apendicectomía en 2018.'
    ],
    [
        'id_paciente'      => 3,
        'id_medico'        => 2,
        'medico_nombre'    => 'Dr. Roberto Gómez',
        'nombre'           => 'Jorge',
        'apellido_paterno' => 'Mendoza',
        'apellido_materno' => 'Sánchez',
        'fecha_nacimiento' => '2015-03-08',
        'alergia'          => 'Polen, Ibuprofeno',
        'contacto'         => '555-987-6543',
        'antecedentes'     => 'Asma leve estacional tratada con salbutamol en caso de crisis.'
    ]
];

include_once '../templatesReutilizables/header.php';
include_once '../templatesReutilizables/sidebar.php';
?>

<main class="col-md-9 col-lg-10 p-4" style="background-color: #e6f0fe;">

    <!-- SECCIÓN PRINCIPAL DE PACIENTES -->
    <section class="bg-white p-4 rounded-3 shadow-sm mb-4">
        <header class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="fw-bold mb-1">Gestión de Pacientes</h4>
                <p class="text-muted small mb-0">Directorio clínico de pacientes, alergias y antecedentes médicos.</p>
            </div>
            <button type="button" class="btn btn-primary fw-semibold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalPaciente" id="btnNuevoPaciente">
                <i class="bi bi-person-plus-fill me-1"></i>Nuevo Paciente
            </button>
        </header>

        <!-- Barra de Filtros en vivo -->
        <form class="row g-2 align-items-center mb-3" id="formFiltrosPacientes" onsubmit="return false;">
            <!-- Buscador general por texto -->
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="search" id="filtroBuscarPaciente" class="form-control border-start-0" placeholder="Buscar por nombre, apellidos, contacto o alergia...">
                </div>
            </div>

            <!-- Filtro por Médico Asignado -->
            <div class="col-6 col-md-auto">
                <select id="filtroMedicoPaciente" class="form-select form-select-sm">
                    <option value="">Todos los médicos</option>
                    <?php foreach ($medicos_lista as $m): ?>
                        <option value="<?= (int)$m['id_medico'] ?>"><?= htmlspecialchars($m['nombre_completo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro por Condición de Alergias -->
            <div class="col-6 col-md-auto">
                <select id="filtroAlergiaPaciente" class="form-select form-select-sm">
                    <option value="">Todas las condiciones</option>
                    <option value="con_alergia">Con alergias</option>
                    <option value="sin_alergia">Sin alergias</option>
                </select>
            </div>
        </form>

        <!-- Tabla de Pacientes -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaPacientes">
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
                    <?php foreach ($pacientes_lista as $p): 
                        $nombre_completo = trim($p['nombre'] . ' ' . $p['apellido_paterno'] . ' ' . $p['apellido_materno']);
                        $edad = calcularEdad($p['fecha_nacimiento']);
                        $tiene_alergia = !empty(trim($p['alergia']));
                    ?>
                        <tr data-medico="<?= (int)$p['id_medico'] ?>" data-alergia="<?= $tiene_alergia ? 'con_alergia' : 'sin_alergia' ?>">
                            <td class="ps-3 fw-medium text-secondary">#<?= (int)$p['id_paciente'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($nombre_completo) ?></td>
                            <td>
                                <span class="badge bg-info-subtle text-info-emphasis me-1"><?= $edad ?> años</span>
                                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($p['fecha_nacimiento'])) ?></small>
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
                                <!-- Botón Antecedentes Médicos (Tabla: antecedentes) -->
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3 me-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAntecedentes"
                                    data-id="<?= (int)$p['id_paciente'] ?>"
                                    data-nombre="<?= htmlspecialchars($nombre_completo) ?>"
                                    data-notas="<?= htmlspecialchars($p['antecedentes']) ?>">
                                    <i class="bi bi-journal-medical"></i> Antecedentes
                                </button>

                                <!-- Botón Editar Paciente -->
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-3 me-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPaciente"
                                    data-id="<?= (int)$p['id_paciente'] ?>"
                                    data-medico="<?= (int)$p['id_medico'] ?>"
                                    data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                    data-paterno="<?= htmlspecialchars($p['apellido_paterno']) ?>"
                                    data-materno="<?= htmlspecialchars($p['apellido_materno']) ?>"
                                    data-nacimiento="<?= htmlspecialchars($p['fecha_nacimiento']) ?>"
                                    data-contacto="<?= htmlspecialchars($p['contacto']) ?>"
                                    data-alergia="<?= htmlspecialchars($p['alergia']) ?>">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>

                                <!-- Botón Eliminar Paciente -->
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminarPaciente"
                                    data-id="<?= (int)$p['id_paciente'] ?>"
                                    data-nombre="<?= htmlspecialchars($nombre_completo) ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- =========================================================
         MODAL 1: CREAR / EDITAR PACIENTE (Tabla: paciente)
         ========================================================= -->
    <div class="modal fade" id="modalPaciente" tabindex="-1" aria-labelledby="modalPacienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="modal-content border-0 shadow" action="../controllers/pacienteController.php" method="POST">
                <header class="modal-header bg-primary-subtle border-0">
                    <h5 class="modal-title fw-bold text-primary-emphasis" id="modalPacienteLabel">
                        <i class="bi bi-person-vcard me-2"></i>Registrar / Editar Paciente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </header>

                <div class="modal-body p-4">
                    <input type="hidden" name="tipo_formulario" value="paciente">
                    <input type="hidden" name="id_paciente" id="id_paciente" value="">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="nombre_paciente" class="form-label fw-semibold small">Nombre(s)</label>
                            <input type="text" name="nombre" id="nombre_paciente" class="form-control" maxlength="20" placeholder="Ej. Carlos" required>
                        </div>
                        <div class="col-md-4">
                            <label for="apellido_paterno" class="form-label fw-semibold small">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" id="apellido_paterno" class="form-control" maxlength="20" placeholder="Ej. Ramírez" required>
                        </div>
                        <div class="col-md-4">
                            <label for="apellido_materno" class="form-label fw-semibold small">Apellido Materno</label>
                            <input type="text" name="apellido_materno" id="apellido_materno" class="form-control" maxlength="20" placeholder="Ej. López">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="fecha_nacimiento" class="form-label fw-semibold small">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="contacto" class="form-label fw-semibold small">Teléfono o Correo de Contacto</label>
                            <input type="text" name="contacto" id="contacto" class="form-control" maxlength="100" placeholder="Ej. 555-123-4567" required>
                        </div>
                        <div class="col-md-4">
                            <label for="id_medico" class="form-label fw-semibold small">Médico Asignado</label>
                            <select name="id_medico" id="id_medico" class="form-select" required>
                                <option value="" selected disabled>Selecciona un médico...</option>
                                <?php foreach ($medicos_lista as $m): ?>
                                    <option value="<?= (int)$m['id_medico'] ?>"><?= htmlspecialchars($m['nombre_completo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="alergia" class="form-label fw-semibold small">Alergias Conocidas</label>
                        <textarea name="alergia" id="alergia" class="form-control" rows="2" placeholder="Ej. Penicilina, polen, sulfas... (Dejar en blanco si no presenta alergias)"></textarea>
                    </div>
                </div>

                <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Guardar Paciente</button>
                </footer>
            </form>
        </div>
    </div>

    <!-- =========================================================
         MODAL 2: ANTECEDENTES MÉDICOS (Tabla: antecedentes)
         ========================================================= -->
    <div class="modal fade" id="modalAntecedentes" tabindex="-1" aria-labelledby="modalAntecedentesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content border-0 shadow" action="../controllers/pacienteController.php" method="POST">
                <header class="modal-header bg-primary-subtle border-0">
                    <h5 class="modal-title fw-bold text-primary-emphasis" id="modalAntecedentesLabel">
                        <i class="bi bi-journal-medical me-2"></i>Antecedentes Médicos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </header>

                <div class="modal-body p-4">
                    <input type="hidden" name="tipo_formulario" value="antecedentes">
                    <input type="hidden" name="id_paciente" id="antecedentes_id_paciente" value="">

                    <p class="mb-3 small text-muted">
                        Paciente: <strong class="text-dark" id="antecedentes_nombre_paciente"></strong>
                    </p>

                    <div class="mb-2">
                        <label for="notas_antecedentes" class="form-label fw-semibold small">Notas de Antecedentes Clínicos y Familiares</label>
                        <textarea name="notas" id="notas_antecedentes" class="form-control" rows="5" placeholder="Describe antecedentes heredofamiliares, patológicos, cirugías previas o enfermedades crónicas..." required></textarea>
                    </div>
                </div>

                <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Guardar Antecedentes</button>
                </footer>
            </form>
        </div>
    </div>

    <!-- =========================================================
         MODAL 3: CONFIRMAR ELIMINACIÓN DE PACIENTE
         ========================================================= -->
    <div class="modal fade" id="modalEliminarPaciente" tabindex="-1" aria-labelledby="modalEliminarPacienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <header class="modal-header bg-danger-subtle border-0">
                    <h5 class="modal-title fw-bold text-danger-emphasis" id="modalEliminarPacienteLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar Paciente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </header>

                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger">
                        <i class="bi bi-trash3 fs-1"></i>
                    </div>
                    <p class="mb-1 fs-6">¿Estás seguro de que deseas eliminar el expediente del paciente:</p>
                    <p class="fw-bold fs-5 text-dark mb-0" id="nombrePacienteEliminar"></p>
                    <small class="text-muted">Se eliminarán también sus citas y antecedentes asociados.</small>
                </div>

                <footer class="modal-footer border-0 pt-0 px-4 pb-4 justify-content-center">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="btnConfirmarEliminarPaciente" class="btn btn-danger rounded-3 px-4 fw-semibold">
                        <i class="bi bi-trash me-1"></i>Sí, eliminar
                    </a>
                </footer>
            </div>
        </div>
    </div>

</main>

<?php
$script_adicional = 'js/pacientes.js';
include_once '../templatesReutilizables/footer.php';
?>
