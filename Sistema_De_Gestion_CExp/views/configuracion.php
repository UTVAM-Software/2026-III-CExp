<?php
require_once '../config/auth_admin.php';
require_once '../controllers/usuarioController.php';
// requerirPermiso('add_consultation');

$titulo_pagina = 'Configuración - Consultorio Médico';
$pagina_activa = 'configuracion';
$script_adicional = '../assets/js/configuracion.js';

if (!defined('EN_PLANTILLA_BASE')) {
    $vista = __FILE__;
    require_once '../templatesReutilizables/template.base.php';
    return;
}
// var_dump(obtenerDatosConfiguracion());
$datos_config   = obtenerDatosConfiguracion();
//$roles_lista    = $datos_config['roles_lista'] ?? [];
$permisos_lista = $datos_config['permisos_lista'] ?? [];
$grupos_lista = $datos_config['grupos_lista'] ?? [];
$usuarios_lista = $datos_config['usuarios_lista'] ?? [];
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

<section class="bg-white p-4 rounded-4 shadow-sm mb-4">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-gear text-primary me-2"></i>Gestión de Usuarios y Perfiles
            </h4>
            <p class="text-muted small mb-0">Administra las cuentas del sistema, perfiles y permisos asignados a cada perfil.</p>
        </div>
    </header>

    <!-- NAVEGACIÓN POR PESTAÑAS -->
    <ul class="nav nav-tabs mb-4" id="tabsConfiguracion" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" id="tab-usuarios-btn" data-bs-toggle="tab" data-bs-target="#tabUsuarios" type="button" role="tab" aria-controls="tabUsuarios" aria-selected="true">
                <i class="bi bi-people me-1"></i>1. Usuarios
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tab-roles-btn" data-bs-toggle="tab" data-bs-target="#tabRoles" type="button" role="tab" aria-controls="tabRoles" aria-selected="false">
                <i class="bi bi-shield-lock me-1"></i>2. Perfiles y Roles
            </button>
        </li>
    </ul>

    <!-- CONTENIDO DE LAS PESTAÑAS -->
    <div class="tab-content" id="tabsConfiguracionContent">

        <!-- GESTIÓN DE USUARIOS -->
        <section class="tab-pane fade show active" id="tabUsuarios" role="tabpanel" aria-labelledby="tab-usuarios-btn">
            <form class="row g-2 align-items-center mb-3" id="formFiltrosUsuarios" onsubmit="return false;">
                <div class="col-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="search" id="filtroBuscarUsuario" class="form-control border-start-0" placeholder="Buscar por nombre, usuario o correo...">
                    </div>
                </div>

                <div class="col-auto">
                    <select id="filtroRolUsuario" class="form-select form-select-sm">
                        <option value="">Todos los perfiles</option>
                        <?php foreach ($grupos_lista as $r): ?>
                            <option value="<?= htmlspecialchars($r['nombre']) ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-auto">
                    <select id="filtroEstadoUsuario" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="col-auto ms-auto text-end">
                    <button type="button" class="btn btn-primary btn-sm fw-semibold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalUsuario" id="btnNuevoUsuario">
                        <i class="bi bi-person-plus me-1"></i>Nuevo Usuario
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaUsuarios">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col" class="ps-3">ID</th>
                            <th scope="col">Nombre Completo</th>
                            <th scope="col">Usuario</th>
                            <th scope="col">Correo Electrónico</th>
                            <th scope="col">Perfil / Rol</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    
                        <?php foreach ($usuarios_lista as $u):
                            $badgeEstado = ($u['estado'] === 'activo') //ERROR EN EL ESTADO
                                ? 'bg-success-subtle text-success-emphasis'
                                : 'bg-danger-subtle text-danger-emphasis';
                        ?>
                            <tr data-rol="<?= htmlspecialchars($u['rol_nombre']) ?>" data-estado="<?= htmlspecialchars($u['estado']) ?>">
                                <td class="ps-3 fw-medium text-secondary">#<?= (int)$u['id_usuario'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($u['nombre_completo']) ?></td>
                                <td><span class="badge bg-light text-dark border">@<?= htmlspecialchars($u['username']) ?></span></td>
                                <td class="text-secondary small"><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary-emphasis">
                                        <?= htmlspecialchars($u['rol_nombre']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeEstado ?>">
                                        <?= ucfirst(htmlspecialchars($u['estado'])) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-3 me-1 btn-editar-usuario"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalUsuario"
                                        data-id="<?= (int)$u['id_usuario'] ?>"
                                        data-nombre="<?= htmlspecialchars($u['nombre_completo']) ?>"
                                        data-username="<?= htmlspecialchars($u['username']) ?>"
                                        data-email="<?= htmlspecialchars($u['email']) ?>"
                                        data-grupo="<?= (int)$u['id_grupo'] ?>"
                                        data-estado="<?= htmlspecialchars($u['estado']) ?>">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>

                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3 btn-eliminar-usuario"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminarUsuario"
                                        data-id="<?= (int)$u['id_usuario'] ?>"
                                        data-nombre="<?= htmlspecialchars($u['nombre_completo']) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!--  PERFILES y ROLES -->
        <section class="tab-pane fade" id="tabRoles" role="tabpanel" aria-labelledby="tab-roles-btn">
            <header class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-secondary">Perfiles y Roles registrados</h6>
                <button type="button" class="btn btn-primary btn-sm fw-semibold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalRol" id="btnNuevoRol">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo Perfil
                </button>
            </header>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col" class="ps-3">ID Grupo</th>
                            <th scope="col">Nombre del Perfil / Rol</th>
                            <th scope="col">Usuarios Asignados</th>
                            <th scope="col">Permisos Asignados</th>
                            <th scope="col" class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grupos_lista as $r): ?>
                            <tr>
                                <td class="ps-3 fw-medium text-secondary">#<?= (int)$r['id_grupo'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($r['nombre']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= (int)$r['total_usuarios'] ?> usuario(s)</span></td>
                                <td>
                                    <?php if (!empty($r['permisos_nombres'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($r['permisos_nombres'] as $nombre_permiso): ?>
                                                <span class="badge bg-primary-subtle text-primary-emphasis fw-medium"><?= htmlspecialchars($nombre_permiso) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin permisos</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-3 me-1 btn-editar-rol"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalRol"
                                        data-id="<?= (int)$r['id_grupo'] ?>"
                                        data-nombre="<?= htmlspecialchars($r['nombre']) ?>"
                                        data-permisos="<?= htmlspecialchars(json_encode($r['permisos_ids'])) ?>">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <?php if ((int)$r['id_grupo'] > 3 && (int)$r['total_usuarios'] === 0): ?>
                                        <form method="POST" action="../controllers/usuarioController.php" class="d-inline">
                                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                            <input type="hidden" name="accion" value="eliminar_rol">
                                            <input type="hidden" name="id" value="<?= (int)$r['id_grupo'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" title="Eliminar perfil">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" disabled title="Perfil protegido o con usuarios asignados">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</section>

<!-- CREAR Y EDITAR USUARIO -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow" action="../controllers/usuarioController.php" method="POST">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
            <header class="modal-header bg-primary-subtle border-0">
                <h5 class="modal-title fw-bold text-primary-emphasis" id="modalUsuarioLabel">
                    <i class="bi bi-person-gear me-2"></i>Crear / Editar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>

            <div class="modal-body p-4">
                <input type="hidden" name="tipo_formulario" value="usuario">
                <input type="hidden" name="id_usuario" id="id_usuario" value="">

                <div class="mb-3">
                    <label for="nombre_completo" class="form-label fw-semibold small">Nombre Completo</label>
                    <input type="text" name="nombre_completo" id="nombre_completo" class="form-control" maxlength="60" placeholder="Ej. Dra. Samantha Samudio" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="username" class="form-label fw-semibold small">Nombre de Usuario</label>
                        <input type="text" name="username" id="username" class="form-control" maxlength="30" placeholder="Ej. ssamudio" required>
                    </div>
                    <div class="col-6">
                        <label for="contrasena" class="form-label fw-semibold small">Contraseña</label>
                        <input type="password" name="contrasena" id="contrasena" class="form-control" placeholder="••••••••">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control" maxlength="100" placeholder="correo@cexplus.com" required>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <label for="id_grupo" class="form-label fw-semibold small">Perfil / Rol (Grupo)</label>
                        <select name="id_grupo" id="id_grupo" class="form-select" required>
                            <option value="" selected disabled>Selecciona un rol...</option>
                            <?php foreach ($grupos_lista as $r): ?>
                                <option value="<?= (int)$r['id_grupo'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="estado_usuario" class="form-label fw-semibold small">Estado</label>
                        <select name="estado" id="estado_usuario" class="form-select" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Guardar Usuario</button>
            </footer>
        </form>
    </div>
</div>

<!-- CREAR, EDITAR PERFIL O ROL Y SUS PERMISOS -->
<div class="modal fade" id="modalRol" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow" action="../controllers/usuarioController.php" method="POST">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
            <header class="modal-header bg-primary-subtle border-0">
                <h5 class="modal-title fw-bold text-primary-emphasis" id="modalRolLabel">
                    <i class="bi bi-shield-plus me-2"></i>Perfil / Rol
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>

            <div class="modal-body p-4">
                <input type="hidden" name="tipo_formulario" value="rol">
                <input type="hidden" name="id_grupo" id="id_grupo_modal" value="">

                <div class="mb-3">
                    <label for="nombre_grupo" class="form-label fw-semibold small">Nombre del Perfil</label>
                    <input type="text" name="nombre" id="nombre_grupo" class="form-control" maxlength="15" placeholder="Ej. Enfermería" required>
                </div>

                <div>
                    <p class="form-label fw-semibold small mb-2">Permisos del sistema</p>
                    <div class="list-group">
                        <?php foreach ($permisos_lista as $perm): ?>
                            <label class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="fw-medium small"><?= htmlspecialchars($perm['nombre']) ?></span>
                                <input class="form-check-input ms-2 check-permiso-rol" type="checkbox" name="permisos[]" value="<?= (int)$perm['id_permiso'] ?>">
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <footer class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Guardar Perfil</button>
            </footer>
        </form>
    </div>
</div>

<!-- CONFIRMAR ELIMINACIÓN DE USUARIO -->
<div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-labelledby="modalEliminarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <article class="modal-content border-0 shadow">
            <header class="modal-header bg-danger-subtle border-0">
                <h5 class="modal-title fw-bold text-danger-emphasis" id="modalEliminarUsuarioLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </header>

            <div class="modal-body p-4 text-center">
                <i class="bi bi-trash3 fs-1 text-danger d-block mb-3"></i>
                <p class="mb-1 fs-6">¿Estás seguro de que deseas eliminar al usuario:</p>
                <p class="fw-bold fs-5 text-dark mb-0" id="nombreUsuarioEliminar"></p>
                <small class="text-muted">Esta acción no se puede deshacer.</small>
            </div>

            <footer class="modal-footer border-0 pt-0 px-4 pb-4 justify-content-center">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="../controllers/usuarioController.php" class="d-inline">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" id="idUsuarioEliminar" value="">
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-semibold">
                        <i class="bi bi-trash me-1"></i>Sí, eliminar
                    </button>
                </form>
            </footer>
        </article>
    </div>
</div>
