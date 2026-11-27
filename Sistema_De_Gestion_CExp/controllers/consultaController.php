<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/consulta.php';
require_once __DIR__ . '/../models/paciente.php';

function obtenerModelosConsulta()
{
    $db = (new Database())->getConnection();
    return [new consulta($db), new paciente($db)];
}

function redirigirConsulta($id_paciente = 0, $tipo = '', $texto = '')
{
    $url = '../views/consultas.php';
    if ($tipo !== '' && $texto !== '') {
        $url .= '?id_paciente=' . (int)$id_paciente . "&{$tipo}=" . urlencode($texto);
    }
    header("Location: {$url}");
    exit();
}

function obtenerDatosConsultas($id_paciente = 0, $id_cita = 0)
{
    [$consultaModel, $pacienteModel] = obtenerModelosConsulta();

    $id_cita = (int)$id_cita;
    $cita_seleccionada = $id_cita > 0 ? $consultaModel->obtenerCitaPorId($id_cita) : null;

    $id_paciente = (int)$id_paciente;
    if ($id_paciente <= 0 && $cita_seleccionada) {
        $id_paciente = (int)$cita_seleccionada['id_paciente'];
    }

    return [
        'pacientes_lista'     => $pacienteModel->listarConFiltros(),
        'medicos_lista'       => $pacienteModel->obtenerMedicos(),
        'citas_pendientes'    => $consultaModel->obtenerCitasPendientes(),
        'cita_seleccionada'   => $cita_seleccionada,
        'paciente_actual'     => $id_paciente > 0 ? $pacienteModel->obtenerPorIdConMedico($id_paciente) : null,
        'consultas_recientes' => $consultaModel->obtenerConsultas(0, 15),
    ];
}

function obtenerDatosHistorial($id_paciente = 0)
{
    [$consultaModel, $pacienteModel] = obtenerModelosConsulta();

    $pacientes_lista          = $pacienteModel->listarConFiltros();
    $id_paciente_seleccionado = (int)$id_paciente;

    if ($id_paciente_seleccionado <= 0 && !empty($pacientes_lista)) {
        $id_paciente_seleccionado = (int)$pacientes_lista[0]['id_paciente'];
    }

    return [
        'pacientes_lista'          => $pacientes_lista,
        'id_paciente_seleccionado' => $id_paciente_seleccionado,
        'paciente_actual'          => $id_paciente_seleccionado > 0 ? $pacienteModel->obtenerPorIdConMedico($id_paciente_seleccionado) : null,
        'antecedentes_paciente'    => $id_paciente_seleccionado > 0 ? $pacienteModel->obtenerAntecedentes($id_paciente_seleccionado) : [],
        'historial_consultas'      => $id_paciente_seleccionado > 0 ? $consultaModel->obtenerConsultas($id_paciente_seleccionado, 50) : [],
    ];
}

if (basename($_SERVER['PHP_SELF']) === 'consultaController.php') {
    [$consultaModel] = obtenerModelosConsulta();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        verificarCsrf();
        requerirPermiso('add_consultation', '../views/consultas.php');
        requerirPermiso('add_prescription', '../views/consultas.php');

        $fecha_raw = trim($_POST['fecha'] ?? '');
        $datos = [
            'id_paciente'             => (int)($_POST['id_paciente'] ?? 0),
            'id_cita'                 => (int)($_POST['id_cita'] ?? 0),
            'fecha'                   => str_replace('T', ' ', $fecha_raw),
            'temperatura'             => (float)($_POST['temperatura'] ?? 0),
            'saturacion_oxigeno'      => (int)($_POST['saturacion_oxigeno'] ?? 0),
            'frecuencia_respiratoria' => (int)($_POST['frecuencia_respiratoria'] ?? 0),
            'pulso_cardiaco'          => (int)($_POST['pulso_cardiaco'] ?? 0),
            'diagnostico'             => trim($_POST['diagnostico'] ?? ''),
        ];

        $medicamentos = [];
        foreach (($_POST['medicamento'] ?? []) as $idx => $nombre_med) {
            $med = [
                'medicamento' => trim($nombre_med),
                'dosis'       => trim($_POST['dosis'][$idx] ?? ''),
                'frecuencia'  => trim($_POST['frecuencia'][$idx] ?? ''),
                'duracion'    => trim($_POST['duracion'][$idx] ?? ''),
            ];
            if ($med['medicamento'] !== '' && $med['dosis'] !== '' && $med['frecuencia'] !== '' && $med['duracion'] !== '') {
                $medicamentos[] = $med;
            }
        }

        if ($datos['id_paciente'] <= 0 || $fecha_raw === '' || strtotime($datos['fecha']) === false || $datos['diagnostico'] === '' || empty($medicamentos)) {
            redirigirConsulta($datos['id_paciente'], 'error', 'Completa los datos de la consulta, diagnóstico y al menos un medicamento.');
        }

        try {
            $consultaModel->registrarConsultaConReceta($datos, $medicamentos);
            redirigirConsulta($datos['id_paciente'], 'mensaje', 'Consulta y receta guardadas correctamente.');
        } catch (Throwable $e) {
            redirigirConsulta($datos['id_paciente'], 'error', 'No se pudo registrar la consulta médica.');
        }
    }

    redirigirConsulta();
}
