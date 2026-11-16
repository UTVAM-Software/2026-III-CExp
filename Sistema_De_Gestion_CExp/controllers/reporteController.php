<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/reporte.php';

function obtenerDatosReportes()
{
    $database     = new Database();
    $db           = $database->getConnection();
    $reporteModel = new reporte($db);

    return [
        'total_pacientes'  => $reporteModel->contarPacientes(),
        'citas_pendientes' => $reporteModel->contarCitasPendientes(),
        'total_consultas'  => $reporteModel->contarConsultas(),
        'tasa_asistencia'  => $reporteModel->calcularTasaAsistencia(),
        'proximas_citas'   => $reporteModel->obtenerProximasCitas(4),
    ];
}
