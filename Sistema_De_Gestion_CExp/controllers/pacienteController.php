<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/paciente.php';

function obtenerDatosPacientes($buscar = '', $id_medico = 0, $alergia = '')
{
    $database      = new Database();
    $db            = $database->getConnection();
    $pacienteModel = new paciente($db);

    return [
        'medicos_lista'   => $pacienteModel->obtenerMedicos(),
        'pacientes_lista' => $pacienteModel->listarConFiltros($buscar, $id_medico, $alergia),
    ];
}

if (basename($_SERVER['PHP_SELF']) === 'pacienteController.php') {
    $database      = new Database();
    $db            = $database->getConnection();
    $pacienteModel = new paciente($db);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $tipo = $_POST['tipo_formulario'] ?? 'paciente';

        try {
            if ($tipo === 'paciente') {
                $id_paciente      = (int)($_POST['id_paciente'] ?? 0);
                $id_medico        = (int)($_POST['id_medico'] ?? 0);
                $nombre           = trim($_POST['nombre'] ?? '');
                $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
                $apellido_materno = trim($_POST['apellido_materno'] ?? '');
                $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
                $alergia          = trim($_POST['alergia'] ?? '');
                $contacto         = trim($_POST['contacto'] ?? '');

                if ($id_medico <= 0 || $nombre === '' || $apellido_paterno === '' || $fecha_nacimiento === '' || $contacto === '') {
                    header("Location: ../views/pacientes.php?error=" . urlencode("Completa todos los campos obligatorios del paciente."));
                    exit();
                }

                if ($id_paciente === 0) {
                    requerirPermiso('add_patient', '../views/pacientes.php');
                    $pacienteModel->crearPaciente($id_medico, $nombre, $apellido_paterno, $apellido_materno, $fecha_nacimiento, $alergia, $contacto);
                    header("Location: ../views/pacientes.php?mensaje=" . urlencode("Paciente registrado correctamente."));
                } else {
                    requerirPermiso('change_patient', '../views/pacientes.php');
                    $pacienteModel->actualizarPaciente($id_paciente, $id_medico, $nombre, $apellido_paterno, $apellido_materno, $fecha_nacimiento, $alergia, $contacto);
                    header("Location: ../views/pacientes.php?mensaje=" . urlencode("Paciente actualizado correctamente."));
                }
            } elseif ($tipo === 'antecedentes') {
                $id_paciente = (int)($_POST['id_paciente'] ?? 0);
                $notas       = trim($_POST['notas'] ?? '');
                $origen      = ($_POST['origen'] ?? '') === 'historial' ? 'historial' : 'pacientes';
                $destino     = $origen === 'historial'
                    ? "../views/historial.php?id_paciente={$id_paciente}&"
                    : "../views/pacientes.php?";

                if ($id_paciente <= 0 || $notas === '') {
                    header("Location: {$destino}error=" . urlencode("Las notas de antecedentes son obligatorias."));
                    exit();
                }

                if ($origen === 'historial') {
                    $pacienteModel->agregarAntecedente($id_paciente, $notas);
                } else {
                    $pacienteModel->guardarAntecedentes($id_paciente, $notas);
                }
                header("Location: {$destino}mensaje=" . urlencode("Antecedentes médicos guardados correctamente."));
            } else {
                header("Location: ../views/pacientes.php");
            }
        } catch (mysqli_sql_exception $e) {
            header("Location: ../views/pacientes.php?error=" . urlencode("No se pudo guardar la información del paciente."));
        }
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['accion'])) {
        if ($_GET['accion'] === 'eliminar' && !empty($_GET['id'])) {
            $id_paciente = (int)$_GET['id'];
            $pacienteModel->delete($id_paciente);
            header("Location: ../views/pacientes.php?mensaje=" . urlencode("Paciente eliminado correctamente."));
            exit();
        }
    }

    header("Location: ../views/pacientes.php");
    exit();
}
?>