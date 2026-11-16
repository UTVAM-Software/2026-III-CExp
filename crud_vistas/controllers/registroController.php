<?php
require_once '../config/database.php';
require_once '../models/usuario.php';

session_start();

    $database       = new Database();
    $db             = $database->getConnection();
    $usuarioModel   = new usuario($db);
    //id_usuario, username, contrasena, estado pero tengo que agregar nombre_completo y email

    if($_SERVER["REQUEST_METHOD"] === "POST"){

        // $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $username        = trim($_POST['username'] ?? '');
        $contrasena      = $_POST['contrasena'];
        $email           = trim($_POST['email'] ?? '');
        $estado          = $_POST['estado'] ?? 'activo';

    if ($nombre_completo === '' || $username === '' || $contrasena === '' || $email === '') {
        header("Location: ../views/registro.php?error=campos_vacios");
        exit();
    }
    if (!in_array($estado, ['activo', 'inactivo'], true)) {
        $estado = 'activo';
    }

    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

    try {
        $usuarioModel->crearUsuario($nombre_completo, $username, $contrasena_hash, $email, $estado);
    } catch (mysqli_sql_exception $e){
        if ($e->get_Code() === 1062){
            header("Location: ../views/registro.php?error=usuario_existente");
        } else {
            header("Location: ../views/registro.php?error=servidor");
        }
        exit();
    }

    header("Location: ../views/login.php?registro=ok"); //<-- una vez que se registra se redirige a el login para checar que este hecho correctamente
    exit();
    }
?>