<?php
require_once __DIR__ . '/../config/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../models/grupo.php';
require_once __DIR__ . '/../models/permiso.php';

function obtenerModelosUsuario()
{
    $db = (new Database())->getConnection();
    return [new usuario($db), new grupo($db), new permiso($db)];
}

function redirigirConfiguracion($tipo = '', $texto = '')
{
    $url = '../views/configuracion.php';
    if ($tipo !== '' && $texto !== '') {
        $url .= "?{$tipo}=" . urlencode($texto);
    }
    header("Location: {$url}");
    exit();
}

function obtenerDatosConfiguracion()
{
    [$usuarioModel, $grupoModel, $permisoModel] = obtenerModelosUsuario();

    return [
        'usuarios_lista' => $usuarioModel->listarConRolYPermisos(),
        'grupos_lista'   => $grupoModel->listarConTotalUsuarios(),
        'permisos_lista' => $permisoModel->listarTodos(),
    ];
}

if (basename($_SERVER['PHP_SELF']) === 'usuarioController.php') {
    [$usuarioModel, $grupoModel, $permisoModel] = obtenerModelosUsuario();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        verificarCsrf();

        if (isset($_POST['accion'])) {
            $accion = $_POST['accion'];
            $id     = (int)($_POST['id'] ?? 0);
            $accionesEliminar = [
                'eliminar'         => [$usuarioModel, 'Usuario eliminado.'],
                'eliminar_rol'     => [$grupoModel, 'Rol eliminado.'],
                'eliminar_permiso' => [$permisoModel, 'Permiso eliminado.'],
            ];

            try {
                if ($accion === 'eliminar' && $id === (int)$_SESSION['id_usuario']) {
                    redirigirConfiguracion('error', 'No puedes eliminar tu propia cuenta.');
                } elseif ($accion === 'eliminar_rol' && $id === 1) {
                    redirigirConfiguracion('error', 'No se puede eliminar el rol Administrador.');
                } elseif ($id > 0 && isset($accionesEliminar[$accion])) {
                    [$modelo, $msg] = $accionesEliminar[$accion];
                    $modelo->delete($id);
                    redirigirConfiguracion('mensaje', $msg);
                }
                redirigirConfiguracion();
            } catch (mysqli_sql_exception $e) {
                redirigirConfiguracion('error', 'No se pudo eliminar: el registro tiene datos relacionados.');
            }
        }

        $tipo = $_POST['tipo_formulario'] ?? 'usuario';

        try {
            if ($tipo === 'usuario') {
                $id_usuario      = (int)($_POST['id_usuario'] ?? 0);
                $nombre_completo = trim($_POST['nombre_completo'] ?? '');
                $username        = trim($_POST['username'] ?? '');
                $contrasena      = $_POST['contrasena'] ?? '';
                $email           = trim($_POST['email'] ?? '');
                $id_grupo        = (int)($_POST['id_grupo'] ?? 0);
                $estado          = in_array($_POST['estado'] ?? '', ['activo', 'inactivo'], true) ? $_POST['estado'] : 'activo';

                if ($nombre_completo === '' || $username === '' || $email === '' || $id_grupo <= 0) {
                    redirigirConfiguracion('error', 'Todos los campos obligatorios deben completarse.');
                }

                if ($id_usuario === 0) {
                    if ($contrasena === '') {
                        redirigirConfiguracion('error', 'La contraseña es requerida para crear un usuario.');
                    }
                    $id_usuario = $usuarioModel->crearUsuario($nombre_completo, $username, password_hash($contrasena, PASSWORD_DEFAULT), $email, $estado);
                    $mensaje    = 'Usuario creado correctamente.';
                } else {
                    $hash = $contrasena !== '' ? password_hash($contrasena, PASSWORD_DEFAULT) : '';
                    $usuarioModel->actualizarUsuario($id_usuario, $nombre_completo, $username, $hash, $email, $estado);
                    $mensaje = 'Usuario actualizado correctamente.';
                }

                $usuarioModel->asignarGrupo($id_usuario, $id_grupo);
                $usuarioModel->sincronizarMedico($id_usuario, $nombre_completo, $id_grupo);
                redirigirConfiguracion('mensaje', $mensaje);
            } elseif ($tipo === 'asignar_permisos') {
                $id_grupo = (int)($_POST['id_grupo'] ?? 0);
                $permisos = $_POST['permisos'] ?? [];
                $permisoModel->sincronizarPermisosGrupo($id_grupo, is_array($permisos) ? $permisos : []);
                redirigirConfiguracion('mensaje', 'Permisos actualizados correctamente.');
            } elseif ($tipo === 'rol') {
                $id_grupo = (int)($_POST['id_grupo'] ?? 0);
                $nombre   = trim($_POST['nombre'] ?? '');

                if ($nombre === '') {
                    redirigirConfiguracion('error', 'El nombre del rol es obligatorio.');
                }

                if ($id_grupo === 0) {
                    $grupoModel->crearGrupo($nombre);
                    redirigirConfiguracion('mensaje', 'Rol creado correctamente.');
                }
                $grupoModel->actualizarGrupo($id_grupo, $nombre);
                redirigirConfiguracion('mensaje', 'Rol actualizado correctamente.');
            } elseif ($tipo === 'permiso') {
                $id_permiso = (int)($_POST['id_permiso'] ?? 0);
                $nombre     = trim($_POST['nombre'] ?? '');
                $codename   = trim($_POST['codename'] ?? '');

                if ($nombre === '' || $codename === '') {
                    redirigirConfiguracion('error', 'El nombre y codename del permiso son obligatorios.');
                }

                if ($id_permiso === 0) {
                    $permisoModel->crearPermiso($nombre, $codename);
                    redirigirConfiguracion('mensaje', 'Permiso creado correctamente.');
                }
                $permisoModel->actualizarPermiso($id_permiso, $nombre, $codename);
                redirigirConfiguracion('mensaje', 'Permiso actualizado correctamente.');
            }

            redirigirConfiguracion();
        } catch (mysqli_sql_exception $e) {
            $msg = $e->getCode() === 1062
                ? 'Ya existe un registro con ese nombre o usuario.'
                : 'Error al procesar la operación.';
            redirigirConfiguracion('error', $msg);
        }
    }

    redirigirConfiguracion();
}
