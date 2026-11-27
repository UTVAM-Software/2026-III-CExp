<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/paciente.php';

function crearModeloPaciente()
{
    return new paciente((new Database())->getConnection());
}

function redirigirPaciente($destino = '../views/pacientes.php', $tipo = '', $texto = '')
{
    if ($tipo !== '' && $texto !== '') {
        $sep = strpos($destino, '?') !== false ? '&' : '?';
        $destino .= "{$sep}{$tipo}=" . urlencode($texto);
    }
    header("Location: {$destino}");
    exit();
}

function obtenerDatosPacientes($buscar = '', $id_medico = 0, $alergia = '')
{
    $pacienteModel = crearModeloPaciente();
    return [
        'medicos_lista'   => $pacienteModel->obtenerMedicos(),
        'pacientes_lista' => $pacienteModel->listarConFiltros($buscar, $id_medico, $alergia),
    ];
}

if (basename($_SERVER['PHP_SELF']) === 'pacienteController.php') {
    $pacienteModel = crearModeloPaciente();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        verificarCsrf();

        if (($_POST['accion'] ?? '') === 'eliminar') {
            requerirPermiso('change_patient', '../views/pacientes.php');
            $id_paciente = (int)($_POST['id'] ?? 0);
            try {
                if ($id_paciente > 0) {
                    $pacienteModel->delete($id_paciente);
                    redirigirPaciente('../views/pacientes.php', 'mensaje', 'Paciente eliminado correctamente.');
                }
                redirigirPaciente();
            } catch (mysqli_sql_exception $e) {
                redirigirPaciente('../views/pacientes.php', 'error', 'No se puede eliminar: el paciente tiene citas, antecedentes o consultas registradas.');
            }
        }

        $tipo        = $_POST['tipo_formulario'] ?? 'paciente';
        $destinoBase = '../views/pacientes.php';

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
                $origen           = ($_POST['origen'] ?? '') === 'consultas' ? 'consultas' : 'pacientes';
                $destinoBase      = $origen === 'consultas' ? '../views/consultas.php' : '../views/pacientes.php';

                if ($id_medico <= 0 || $nombre === '' || $apellido_paterno === '' || $fecha_nacimiento === '') {
                    redirigirPaciente($destinoBase, 'error', 'Completa todos los campos obligatorios del paciente.');
                }

                if ($id_paciente === 0) {
                    requerirPermiso('add_patient', $destinoBase);
                    $nuevo_id = $pacienteModel->crearPaciente($id_medico, $nombre, $apellido_paterno, $apellido_materno, $fecha_nacimiento, $alergia, $contacto);
                    $destinoOk = $origen === 'consultas' ? "../views/consultas.php?id_paciente={$nuevo_id}" : '../views/pacientes.php';
                    redirigirPaciente($destinoOk, 'mensaje', 'Paciente registrado correctamente.');
                }

                requerirPermiso('change_patient', '../views/pacientes.php');
                $pacienteModel->actualizarPaciente($id_paciente, $id_medico, $nombre, $apellido_paterno, $apellido_materno, $fecha_nacimiento, $alergia, $contacto);
                redirigirPaciente('../views/pacientes.php', 'mensaje', 'Paciente actualizado correctamente.');
            } elseif ($tipo === 'antecedentes') {
                requerirPermiso('change_patient', '../views/pacientes.php');
                $id_paciente = (int)($_POST['id_paciente'] ?? 0);
                $notas       = trim($_POST['notas'] ?? '');
                $origen      = ($_POST['origen'] ?? '') === 'historial' ? 'historial' : 'pacientes';
                $destinoBase = $origen === 'historial' ? "../views/historial.php?id_paciente={$id_paciente}" : '../views/pacientes.php';

                if ($id_paciente <= 0 || $notas === '') {
                    redirigirPaciente($destinoBase, 'error', 'Las notas de antecedentes son obligatorias.');
                }

                if ($origen === 'historial') {
                    $pacienteModel->agregarAntecedente($id_paciente, $notas);
                } else {
                    $pacienteModel->guardarAntecedentes($id_paciente, $notas);
                }
                redirigirPaciente($destinoBase, 'mensaje', 'Antecedentes médicos guardados correctamente.');
            }

            redirigirPaciente();
        } catch (mysqli_sql_exception $e) {
            redirigirPaciente($destinoBase, 'error', 'No se pudo guardar la información del paciente.');
        }
    }

    redirigirPaciente();
}
