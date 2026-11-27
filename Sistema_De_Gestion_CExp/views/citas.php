<?php
require_once '../config/auth.php';
require_once '../controllers/citaController.php';
$titulo_pagina = 'Citas - Consultorio Médico';
$pagina_activa = 'citas';
$cargar_fullcalendar = true;
$script_adicional = '../assets/js/calendario.js';

if (!defined('EN_PLANTILLA_BASE')) {
    $vista = __FILE__;
    require_once '../templatesReutilizables/template.base.php';
    return;
}

$datos_citas         = obtenerDatosCitas();
$medicos             = $datos_citas['medicos'] ?? [];
$pacientes_recientes = $datos_citas['pacientes_recientes'] ?? [];
$citas_bd            = $datos_citas['citas_bd'] ?? [];
$eventos_citas       = $datos_citas['eventos_citas'] ?? [];
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

<!-- SECCIÓN PRINCIPAL DE CITAS -->
<section class="bg-white p-4 rounded-4 shadow-sm mb-4">
    <header class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-calendar-check text-primary me-2"></i>Citas
            </h4>
            <p class="text-muted small mb-0">Agenda, filtra y administra las citas médicas en vista de lista o calendario.</p>
        </div>
        <button type="button" class="btn btn-primary fw-semibold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalCita">
            <i class="bi bi-plus-lg me-1"></i>Nueva cita
        </button>
    </header>

    <!-- Barra de Filtros y Selector -->
    <form class="row g-2 align-items-center mb-3" id="formFiltrosCitas" action="citas.php" method="GET" onsubmit="return false;">
        <div class="col-auto">
            <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualización">
                <button type="button" id="btnModoLista" class="btn btn-primary active">
                    <i class="bi bi-list-ul me-1"></i>Lista
                </button>
                <button type="button" id="btnModoCalendario" class="btn btn-outline-primary">
                    <i class="bi bi-calendar3 me-1"></i>Calendario
                </button>
            </div>
        </div>

        <div class="col-auto">
            <select id="filtroMedico" name="id_medico" class="form-select form-select-sm">
                <option value="">Todos los doctores</option>
                <?php foreach ($medicos as $m): ?>
                    <option value="<?= (int)$m['id_medico'] ?>"><?= htmlspecialchars($m['nombre_completo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-auto">
            <select id="filtroEstado" name="estado" class="form-select form-select-sm">
                <option value="">Todos los estados</option>
                <option value="programada">Programada</option>
                <option value="confirmada">Confirmada</option>
                <option value="concluida">Concluida</option>
                <option value="cancelada">Cancelada</option>
            </select>
        </div>

        <div class="col-auto">
            <input type="date" id="filtroFechaInicio" name="fecha_inicio" class="form-control form-control-sm" aria-label="Fecha desde">
        </div>
        <div class="col-auto">
            <input type="date" id="filtroFechaFin" name="fecha_fin" class="form-control form-control-sm" aria-label="Fecha hasta">
        </div>

        <div class="col">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="search" id="filtroPaciente" name="buscar" class="form-control border-start-0" placeholder="Buscar paciente..." value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
            </div>
        </div>
    </form>

    <!-- Navegación y Selector de Fechas de Calendario) -->
    <nav id="navControlesCalendario" class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pt-2 border-top d-none" aria-label="Controles del calendario">
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btnPrevWeek" class="btn btn-outline-primary btn-sm rounded-3" aria-label="Anterior">&lt;</button>
            <time id="calendarTitle" class="fw-bold fs-6 text-capitalize"></time>
            <button type="button" id="btnNextWeek" class="btn btn-outline-primary btn-sm rounded-3" aria-label="Siguiente">&gt;</button>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btnToday" class="btn btn-outline-secondary btn-sm rounded-3">Hoy</button>
            <div class="btn-group btn-group-sm" role="group" aria-label="Vistas de calendario">
                <button type="button" class="btn btn-outline-primary btn-vista-cal" data-view="timeGridDay">Día</button>
                <button type="button" class="btn btn-primary btn-vista-cal active" data-view="timeGridWeek">Semana</button>
                <button type="button" class="btn btn-outline-primary btn-vista-cal" data-view="dayGridMonth">Mes</button>
            </div>
        </div>
    </nav>

    <!-- Contenedor de Calendario -->
    <div id="calendar" class="d-none" data-eventos='<?= htmlspecialchars(json_encode($eventos_citas, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'></div>

    <!-- Contenedor de Lista -->
    <div id="vistaListaCitas" class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th scope="col" class="ps-3">ID</th>
                    <th scope="col">Paciente</th>
                    <th scope="col">Médico</th>
                    <th scope="col">Fecha y Hora</th>
                    <th scope="col">Estado</th>
                    <th scope="col" class="text-end pe-3">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaListaCitasBody">
                <?php if (empty($eventos_citas)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No hay citas registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($eventos_citas as $ev):
                        $est = $ev['extendedProps']['estado'];
                        $badgeMap = [
                            'concluida'  => 'bg-success-subtle text-success-emphasis',
                            'confirmada' => 'bg-primary-subtle text-primary-emphasis',
                            'programada' => 'bg-warning-subtle text-warning-emphasis',
                            'cancelada'  => 'bg-danger-subtle text-danger-emphasis'
                        ];
                        $badgeClase = $badgeMap[$est] ?? 'bg-secondary-subtle';
                    ?>
                        <tr>
                            <td class="ps-3 fw-medium text-secondary">#<?= (int)$ev['id'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($ev['title']) ?></td>
                            <td class="text-secondary small"><?= htmlspecialchars($ev['extendedProps']['medico']) ?></td>
                            <td><time><?= date('d/m/Y H:i', strtotime($ev['start'])) ?></time></td>
                            <td><span class="badge <?= $badgeClase ?>"><?= ucfirst(htmlspecialchars($est)) ?></span></td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-3 me-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCita"
                                    data-id="<?= (int)$ev['id'] ?>"
                                    data-paciente="<?= (int)$ev['extendedProps']['id_paciente'] ?>"
                                    data-fecha="<?= htmlspecialchars(substr($ev['start'], 0, 16)) ?>"
                                    data-estado="<?= htmlspecialchars($est) ?>">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminarCita"
                                    data-id="<?= (int)$ev['id'] ?>"
                                    data-paciente="<?= htmlspecialchars($ev['title']) ?>">
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

<!-- MODAL CREAR / EDITAR CITA -->
<div class="modal fade" id="modalCita" tabindex="-1" aria-labelledby="modalCitaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow" action="../controllers/citaController.php" method="POST">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
            <header class="modal-header bg-primary-subtle border-0">
                <h5 class="modal-title fw-bold text-primary-emphasis" id="modalCitaLabel">
                    <i class="bi bi-calendar-plus me-2"></i>Agendar / Editar Cita
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>

            <div class="modal-body p-4">
                <input type="hidden" name="id_cita" id="id_cita" value="">

                <div class="mb-3">
                    <label for="id_paciente" class="form-label fw-semibold small">Paciente</label>
                    <select name="id_paciente" id="id_paciente" class="form-select" required>
                        <option value="" selected disabled>Selecciona un paciente...</option>
                        <?php foreach ($pacientes_recientes as $p): ?>
                            <option value="<?= (int)$p['id_paciente'] ?>">
                                <?= htmlspecialchars(trim($p['nombre'] . ' ' . $p['apellido_paterno'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="fecha" class="form-label fw-semibold small">Fecha y Hora</label>
                    <input type="datetime-local" name="fecha" id="fecha" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label for="estado" class="form-label fw-semibold small">Estado de la cita</label>
                    <select name="estado" id="estado" class="form-select" required>
                        <option value="programada">Programada</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="concluida">Concluida</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>

            <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Guardar Cita</button>
            </footer>
        </form>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINACIÓN DE CITA -->
<div class="modal fade" id="modalEliminarCita" tabindex="-1" aria-labelledby="modalEliminarCitaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow">
            <header class="modal-header bg-danger-subtle border-0">
                <h5 class="modal-title fw-bold text-danger-emphasis" id="modalEliminarCitaLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar Cita
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>

            <div class="modal-body p-4 text-center">
                <i class="bi bi-trash3 fs-1 text-danger d-block mb-3"></i>
                <p class="mb-1 fs-6">¿Estás seguro de que deseas eliminar la cita del paciente:</p>
                <p class="fw-bold fs-5 text-dark mb-0" id="nombrePacienteEliminar"></p>
                <small class="text-muted">Esta acción no se puede deshacer.</small>
            </div>

            <footer class="modal-footer border-0 pt-0 px-4 pb-4 justify-content-center">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="../controllers/citaController.php" class="d-inline">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" id="idCitaEliminar" value="">
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-semibold">
                        <i class="bi bi-trash me-1"></i>Sí, eliminar
                    </button>
                </form>
            </footer>
        </article>
    </div>
</div>
