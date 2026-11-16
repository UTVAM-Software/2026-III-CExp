<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/consulta.php';
require_once __DIR__ . '/../models/paciente.php';

function obtenerDatosConsultas($id_paciente = 0, $id_cita = 0)
{
    $database      = new Database();
    $db            = $database->getConnection();
    $consultaModel = new consulta($db);
    $pacienteModel = new paciente($db);

    $pacientes_lista  = $pacienteModel->listarConFiltros();
    $citas_pendientes = $consultaModel->obtenerCitasPendientes();

    $cita_seleccionada = null;
    $id_cita = (int)$id_cita;
    if ($id_cita > 0) {
        $cita_seleccionada = $consultaModel->obtenerCitaPorId($id_cita);
    }

    $id_paciente = (int)$id_paciente;
    if ($id_paciente <= 0 && $cita_seleccionada) {
        $id_paciente = (int)$cita_seleccionada['id_paciente'];
    }

    $paciente_actual = $id_paciente > 0
        ? $pacienteModel->obtenerPorIdConMedico($id_paciente)
        : null;

    $consultas_recientes = $consultaModel->obtenerConsultas(0, 15);

    return [
        'pacientes_lista'     => $pacientes_lista,
        'citas_pendientes'    => $citas_pendientes,
        'cita_seleccionada'   => $cita_seleccionada,
        'paciente_actual'     => $paciente_actual,
        'consultas_recientes' => $consultas_recientes,
    ];
}

function obtenerDatosHistorial($id_paciente = 0)
{
    $database      = new Database();
    $db            = $database->getConnection();
    $consultaModel = new consulta($db);
    $pacienteModel = new paciente($db);

    $pacientes_lista = $pacienteModel->listarConFiltros();
    $id_paciente_seleccionado = (int)$id_paciente;

    if ($id_paciente_seleccionado <= 0 && !empty($pacientes_lista)) {
        $id_paciente_seleccionado = (int)$pacientes_lista[0]['id_paciente'];
    }

    $paciente_actual       = null;
    $antecedentes_paciente = [];
    $historial_consultas   = [];

    if ($id_paciente_seleccionado > 0) {
        $paciente_actual       = $pacienteModel->obtenerPorIdConMedico($id_paciente_seleccionado);
        $antecedentes_paciente = $pacienteModel->obtenerAntecedentes($id_paciente_seleccionado);
        $historial_consultas   = $consultaModel->obtenerConsultas($id_paciente_seleccionado, 50);
    }

    return [
        'pacientes_lista'          => $pacientes_lista,
        'id_paciente_seleccionado' => $id_paciente_seleccionado,
        'paciente_actual'          => $paciente_actual,
        'antecedentes_paciente'    => $antecedentes_paciente,
        'historial_consultas'      => $historial_consultas,
    ];
}

if (basename($_SERVER['PHP_SELF']) === 'consultaController.php') {
    $database      = new Database();
    $db            = $database->getConnection();
    $consultaModel = new consulta($db);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        requerirPermiso('add_consultation', '../views/consultas.php');
        requerirPermiso('add_prescription', '../views/consultas.php');

        $id_paciente             = (int)($_POST['id_paciente'] ?? 0);
        $id_cita                 = (int)($_POST['id_cita'] ?? 0);
        $fecha_raw               = trim($_POST['fecha'] ?? '');
        $fecha                   = str_replace('T', ' ', $fecha_raw);
        $temperatura             = (float)($_POST['temperatura'] ?? 0);
        $saturacion_oxigeno      = (int)($_POST['saturacion_oxigeno'] ?? 0);
        $frecuencia_respiratoria = (int)($_POST['frecuencia_respiratoria'] ?? 0);
        $pulso_cardiaco          = (int)($_POST['pulso_cardiaco'] ?? 0);
        $diagnostico             = trim($_POST['diagnostico'] ?? '');

        $meds_nombre = $_POST['medicamento'] ?? [];
        $meds_dosis  = $_POST['dosis'] ?? [];
        $meds_frec   = $_POST['frecuencia'] ?? [];
        $meds_dur    = $_POST['duracion'] ?? [];

        $medicamentos = [];
        if (is_array($meds_nombre)) {
            foreach ($meds_nombre as $idx => $nombre_med) {
                $nombre_med = trim($nombre_med);
                $dosis      = trim($meds_dosis[$idx] ?? '');
                $frecuencia = trim($meds_frec[$idx] ?? '');
                $duracion   = trim($meds_dur[$idx] ?? '');

                if ($nombre_med !== '' && $dosis !== '' && $frecuencia !== '' && $duracion !== '') {
                    $medicamentos[] = [
                        'medicamento' => $nombre_med,
                        'dosis'       => $dosis,
                        'frecuencia'  => $frecuencia,
                        'duracion'    => $duracion,
                    ];
                }
            }
        }

        if ($id_paciente <= 0 || $fecha_raw === '' || strtotime($fecha) === false || $diagnostico === '' || empty($medicamentos)) {
            header("Location: ../views/consultas.php?id_paciente={$id_paciente}&error=" . urlencode("Completa los datos de la consulta, diagnóstico y al menos un medicamento."));
            exit();
        }

        try {
            $consultaModel->registrarConsultaConReceta([
                'id_paciente'             => $id_paciente,
                'id_cita'                 => $id_cita,
                'fecha'                   => $fecha,
                'temperatura'             => $temperatura,
                'saturacion_oxigeno'      => $saturacion_oxigeno,
                'frecuencia_respiratoria' => $frecuencia_respiratoria,
                'pulso_cardiaco'          => $pulso_cardiaco,
                'diagnostico'             => $diagnostico,
            ], $medicamentos);

            header("Location: ../views/consultas.php?id_paciente={$id_paciente}&mensaje=" . urlencode("Consulta y receta guardadas correctamente."));
        } catch (Throwable $e) {
            header("Location: ../views/consultas.php?id_paciente={$id_paciente}&error=" . urlencode("No se pudo registrar la consulta médica."));
        }
        exit();
    }

    header("Location: ../views/consultas.php");
    exit();
}
