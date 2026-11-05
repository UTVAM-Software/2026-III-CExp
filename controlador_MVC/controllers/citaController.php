<?php
require_once '../config/database.php';
require_once '../models/cita.php';

// 1. Conectar a la base de datos e instanciar el modelo Cita
$database = new Database();
$db = $database->getConnection();
$citaModel = new cita($db);

// 2. Si la petición viene del formulario del Modal (Método POST: Crear o Actualizar)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cita     = $_POST['id_cita'] ?? '';
    $id_paciente = (int)$_POST['id_paciente'];
    // El input datetime-local envía 'YYYY-MM-DDTHH:MM', reemplazamos la 'T' por espacio para MySQL
    $fecha       = str_replace('T', ' ', $_POST['fecha']);
    $estado      = $_POST['estado'];

    if (empty($id_cita)) {
        // No hay id_cita -> Es una NUEVA cita (INSERT)
        $citaModel->crearCita($id_paciente, $fecha, $estado);
    } else {
        // Sí hay id_cita -> Es ACTUALIZAR una cita existente (UPDATE)
        $citaModel->actualizarCita((int)$id_cita, $id_paciente, $fecha, $estado);
    }

    header("Location: ../views/citas.php");
    exit();
}

// 3. Si la petición viene por enlace URL (Método GET: Eliminar cita)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['accion'])) {
    if ($_GET['accion'] === 'eliminar' && !empty($_GET['id'])) {
        $id_cita = (int)$_GET['id'];
        $citaModel->eliminarCita($id_cita);
    }

    header("Location: ../views/citas.php");
    exit();
}
?>

