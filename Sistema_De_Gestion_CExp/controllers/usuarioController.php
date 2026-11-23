<?php
require_once __DIR__ . '/../config/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../models/grupo.php';
require_once __DIR__ . '/../models/permiso.php';

function obtenerDatosConfiguracion()
{
    $database     = new Database();
    $db           = $database->getConnection();
    $usuarioModel = new usuario($db);
    $grupoModel   = new grupo($db);
    $permisoModel = new permiso($db);

    return [
        'usuarios_lista' => $usuarioModel->listarConRolYPermisos(),
        'grupos_lista'    => $grupoModel->listarConTotalUsuarios(),
        'permisos_lista' => $permisoModel->listarTodos(),
        
    ];
}

if (basename($_SERVER['PHP_SELF']) === 'usuarioController.php') {
    $database     = new Database();
    $db           = $database->getConnection();
    $usuarioModel = new usuario($db);
    $grupoModel   = new grupo($db);
    $permisoModel = new permiso($db);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $tipo = $_POST['tipo_formulario'] ?? 'usuario';

        try {
            if ($tipo === 'usuario') {
                $id_usuario      = (int)($_POST['id_usuario'] ?? 0);
                $nombre_completo = trim($_POST['nombre_completo'] ?? '');
                $username        = trim($_POST['username'] ?? '');
                $contrasena      = $_POST['contrasena'] ?? '';
                $email           = trim($_POST['email'] ?? '');
                $id_grupo        = (int)($_POST['id_grupo'] ?? 0);
                $estado          = $_POST['estado'] ?? 'activo';

                if ($nombre_completo === '' || $username === '' || $email === '' || $id_grupo <= 0) {
                    header("Location: ../views/configuracion.php?error=" . urlencode("Todos los campos obligatorios deben completarse."));
                    exit();
                }

                if (!in_array($estado, ['activo', 'inactivo'], true)) {
                    $estado = 'activo';
                }

                if ($id_usuario === 0) {
                    if ($contrasena === '') {
                        header("Location: ../views/configuracion.php?error=" . urlencode("La contraseña es requerida para crear un usuario."));
                        exit();
                    }
                    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $nuevo_id = $usuarioModel->crearUsuario($nombre_completo, $username, $contrasena_hash, $email, $estado);
                    $usuarioModel->asignarGrupo($nuevo_id, $id_grupo);
                    $usuarioModel->sincronizarMedico($nuevo_id, $nombre_completo, $id_grupo);
                    header("Location: ../views/configuracion.php?mensaje=" . urlencode("Usuario creado correctamente."));
                } else {
                    $contrasena_hash = $contrasena !== '' ? password_hash($contrasena, PASSWORD_DEFAULT) : '';
                    $usuarioModel->actualizarUsuario($id_usuario, $nombre_completo, $username, $contrasena_hash, $email, $estado);
                    $usuarioModel->asignarGrupo($id_usuario, $id_grupo);
                    $usuarioModel->sincronizarMedico($id_usuario, $nombre_completo, $id_grupo);
                    header("Location: ../views/configuracion.php?mensaje=" . urlencode("Usuario actualizado correctamente."));
                }
            } elseif ($tipo === 'asignar_permisos') {
                $id_usuario = (int)($_POST['id_usuario'] ?? 0);
                $permisos   = $_POST['permisos'] ?? [];
                $permisoModel->sincronizarPermisosUsuario($id_usuario, is_array($permisos) ? $permisos : []);
                header("Location: ../views/configuracion.php?mensaje=" . urlencode("Permisos actualizados correctamente."));
            } elseif ($tipo === 'rol') {
                $id_grupo = (int)($_POST['id_grupo'] ?? 0);
                $nombre   = trim($_POST['nombre'] ?? '');

                if ($nombre === '') {
                    header("Location: ../views/configuracion.php?error=" . urlencode("El nombre del rol es obligatorio."));
                    exit();
                }

                if ($id_grupo === 0) {
                    $grupoModel->crearGrupo($nombre);
                    header("Location: ../views/configuracion.php?mensaje=" . urlencode("Rol creado correctamente."));
                } else {
                    $grupoModel->actualizarGrupo($id_grupo, $nombre);
                    header("Location: ../views/configuracion.php?mensaje=" . urlencode("Rol actualizado correctamente."));
                }
            } elseif ($tipo === 'permiso') {
                $id_permiso = (int)($_POST['id_permiso'] ?? 0);
                $nombre     = trim($_POST['nombre'] ?? '');
                $codename   = trim($_POST['codename'] ?? '');

                if ($nombre === '' || $codename === '') {
                    header("Location: ../views/configuracion.php?error=" . urlencode("El nombre y codename del permiso son obligatorios."));
                    exit();
                }

                if ($id_permiso === 0) {
                    $permisoModel->crearPermiso($nombre, $codename);
                    header("Location: ../views/configuracion.php?mensaje=" . urlencode("Permiso creado correctamente."));
                } else {
                    $permisoModel->actualizarPermiso($id_permiso, $nombre, $codename);
                    header("Location: ../views/configuracion.php?mensaje=" . urlencode("Permiso actualizado correctamente."));
                }
            } else {
                header("Location: ../views/configuracion.php");
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                header("Location: ../views/configuracion.php?error=" . urlencode("Ya existe un registro con ese nombre o usuario."));
            } else {
                header("Location: ../views/configuracion.php?error=" . urlencode("Error al procesar la operación."));
            }
        }
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['accion'])) {
        $accion = $_GET['accion'];
        $id     = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            if ($accion === 'eliminar') {
                $usuarioModel->delete($id);
                header("Location: ../views/configuracion.php?mensaje=" . urlencode("Usuario eliminado."));
                exit();
            } elseif ($accion === 'eliminar_rol') {
                $grupoModel->delete($id);
                header("Location: ../views/configuracion.php?mensaje=" . urlencode("Rol eliminado."));
                exit();
            } elseif ($accion === 'eliminar_permiso') {
                $permisoModel->delete($id);
                header("Location: ../views/configuracion.php?mensaje=" . urlencode("Permiso eliminado."));
                exit();
            }
        }
    }

    header("Location: ../views/configuracion.php");
    exit();
}
?>