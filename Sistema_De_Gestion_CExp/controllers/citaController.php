<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/cita.php';

function obtenerDatosCitas()
{
    $database  = new Database();
    $db        = $database->getConnection();
    $citaModel = new cita($db);

    $citas_bd  = $citaModel->obtenerCitas();
    $medicos   = $citaModel->obtenerMedicos();
    $pacientes = $citaModel->obtenerPacientes();

    $eventos_citas = [];
    foreach ($citas_bd as $c) {
        $eventos_citas[] = [
            'id'            => (int)$c['id_cita'],
            'title'         => trim($c['paciente_nombre'] . ' ' . $c['paciente_apellido']),
            'start'         => date('Y-m-d\TH:i:s', strtotime($c['fecha'])),
            'extendedProps' => [
                'id_paciente' => (int)$c['id_paciente'],
                'id_medico'   => (int)$c['id_medico'],
                'medico'      => trim($c['medico_nombre'] . ' ' . $c['medico_apellido']),
                'estado'      => $c['estado'],
            ],
        ];
    }

    return [
        'medicos'             => $medicos,
        'pacientes_recientes' => $pacientes,
        'citas_bd'            => $citas_bd,
        'eventos_citas'       => $eventos_citas,
    ];
}

function obtenerDatosAgenda($id_medico = 0, $fecha = '')
{
    $database  = new Database();
    $db        = $database->getConnection();
    $citaModel = new cita($db);

    $medicos_lista   = $citaModel->obtenerMedicos();
    $pacientes_lista = $citaModel->obtenerPacientes();

    $id_medico_seleccionado = (int)$id_medico;
    if ($id_medico_seleccionado <= 0 && !empty($medicos_lista)) {
        $id_medico_seleccionado = (int)$medicos_lista[0]['id_medico'];
    }

    $fecha_seleccionada = ($fecha !== '' && strtotime($fecha) !== false)
        ? date('Y-m-d', strtotime($fecha))
        : date('Y-m-d');

    $citas_por_hora = ($id_medico_seleccionado > 0)
        ? $citaModel->obtenerCitasPorMedicoYFecha($id_medico_seleccionado, $fecha_seleccionada)
        : [];

    return [
        'medicos_lista'          => $medicos_lista,
        'pacientes_lista'        => $pacientes_lista,
        'id_medico_seleccionado' => $id_medico_seleccionado,
        'fecha_seleccionada'     => $fecha_seleccionada,
        'citas_por_hora'         => $citas_por_hora,
    ];
}

if (basename($_SERVER['PHP_SELF']) === 'citaController.php') {
    $database  = new Database();
    $db        = $database->getConnection();
    $citaModel = new cita($db);

    $estados_validos = ['programada', 'confirmada', 'concluida', 'cancelada'];

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id_cita     = (int)($_POST['id_cita'] ?? 0);
        $id_paciente = (int)($_POST['id_paciente'] ?? 0);
        $fecha_raw   = trim($_POST['fecha'] ?? '');
        $fecha       = str_replace('T', ' ', $fecha_raw);
        $estado      = $_POST['estado'] ?? 'programada';
        $origen      = ($_POST['origen'] ?? '') === 'agenda' ? 'agenda' : 'citas';

        if (!in_array($estado, $estados_validos, true)) {
            $estado = 'programada';
        }

        $destinoBase = $origen === 'agenda' ? '../views/agenda.php' : '../views/citas.php';
        $extraQuery  = '';
        if ($origen === 'agenda') {
            $id_med_filtro = (int)($_POST['id_medico_filtro'] ?? 0);
            $fecha_filtro  = $fecha_raw !== '' ? date('Y-m-d', strtotime($fecha)) : date('Y-m-d');
            $extraQuery    = 'id_medico=' . $id_med_filtro . '&fecha=' . urlencode($fecha_filtro) . '&';
        }

        if ($id_paciente <= 0 || $fecha_raw === '' || strtotime($fecha) === false) {
            header("Location: {$destinoBase}?{$extraQuery}error=" . urlencode("Debes seleccionar un paciente y una fecha válida."));
            exit();
        }

        try {
            if ($id_cita === 0) {
                $citaModel->crearCita($id_paciente, $fecha, $estado);
                header("Location: {$destinoBase}?{$extraQuery}mensaje=" . urlencode("Cita agendada correctamente."));
            } else {
                $citaModel->actualizarCita($id_cita, $id_paciente, $fecha, $estado);
                header("Location: {$destinoBase}?{$extraQuery}mensaje=" . urlencode("Cita actualizada correctamente."));
            }
        } catch (mysqli_sql_exception $e) {
            header("Location: {$destinoBase}?{$extraQuery}error=" . urlencode("No se pudo guardar la cita."));
        }
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['accion'])) {
        if ($_GET['accion'] === 'eliminar' && !empty($_GET['id'])) {
            $id_cita = (int)$_GET['id'];
            $citaModel->delete($id_cita);
            header("Location: ../views/citas.php?mensaje=" . urlencode("Cita eliminada correctamente."));
            exit();
        }
    }

    header("Location: ../views/citas.php");
    exit();
}