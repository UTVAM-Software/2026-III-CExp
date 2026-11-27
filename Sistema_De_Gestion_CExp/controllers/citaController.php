<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/cita.php';

function crearModeloCita()
{
    return new cita((new Database())->getConnection());
}

function redirigirCita($destino, $tipo = '', $texto = '')
{
    if ($tipo !== '' && $texto !== '') {
        $sep = strpos($destino, '?') !== false ? '&' : '?';
        $destino .= "{$sep}{$tipo}=" . urlencode($texto);
    }
    header("Location: {$destino}");
    exit();
}

function obtenerDatosCitas()
{
    $citaModel = crearModeloCita();

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
    $citaModel = crearModeloCita();

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
    $citaModel       = crearModeloCita();
    $estados_validos = ['programada', 'confirmada', 'concluida', 'cancelada'];

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        verificarCsrf();
        requerirPermiso('add_date', '../views/citas.php');

        if (($_POST['accion'] ?? '') === 'eliminar') {
            $id_cita = (int)($_POST['id'] ?? 0);
            if ($id_cita > 0) {
                $citaModel->delete($id_cita);
                redirigirCita('../views/citas.php', 'mensaje', 'Cita eliminada correctamente.');
            }
            redirigirCita('../views/citas.php');
        }

        $id_cita     = (int)($_POST['id_cita'] ?? 0);
        $id_paciente = (int)($_POST['id_paciente'] ?? 0);
        $fecha_raw   = trim($_POST['fecha'] ?? '');
        $fecha       = str_replace('T', ' ', $fecha_raw);
        $estado      = in_array($_POST['estado'] ?? '', $estados_validos, true) ? $_POST['estado'] : 'programada';
        $origen      = ($_POST['origen'] ?? '') === 'agenda' ? 'agenda' : 'citas';

        $destinoBase = '../views/citas.php';
        if ($origen === 'agenda') {
            $id_med_filtro = (int)($_POST['id_medico_filtro'] ?? 0);
            $ts_filtro     = strtotime($fecha);
            $fecha_filtro  = ($fecha_raw !== '' && $ts_filtro !== false) ? date('Y-m-d', $ts_filtro) : date('Y-m-d');
            $destinoBase   = '../views/agenda.php?id_medico=' . $id_med_filtro . '&fecha=' . urlencode($fecha_filtro);
        }

        if ($id_paciente <= 0 || $fecha_raw === '' || strtotime($fecha) === false) {
            redirigirCita($destinoBase, 'error', 'Debes seleccionar un paciente y una fecha válida.');
        }

        try {
            if ($id_cita === 0) {
                $citaModel->crearCita($id_paciente, $fecha, $estado);
                redirigirCita($destinoBase, 'mensaje', 'Cita agendada correctamente.');
            }
            $citaModel->actualizarCita($id_cita, $id_paciente, $fecha, $estado);
            redirigirCita($destinoBase, 'mensaje', 'Cita actualizada correctamente.');
        } catch (mysqli_sql_exception $e) {
            redirigirCita($destinoBase, 'error', 'No se pudo guardar la cita.');
        }
    }

    redirigirCita('../views/citas.php');
}
?>