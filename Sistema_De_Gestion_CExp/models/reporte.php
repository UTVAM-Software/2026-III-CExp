<?php
require_once __DIR__ . '/Modelo.php';

class reporte extends Modelo
{
    protected $tabla = 'cita';
    protected $pk = 'id_cita';

    // metodo generico para contar 
    private function contar($tabla, $where = '')
    {
        $query = "SELECT COUNT(*) AS total FROM {$tabla}" . ($where ? " WHERE {$where}" : '');
        $fila  = $this->ejecutar($query)->get_result()->fetch_assoc();
        return (int) ($fila['total'] ?? 0);
    }

    // Contar total de pacientes registrados
    public function contarPacientes()
    {
        return $this->contar('paciente');
    }

    // Contar citas pendientes (programadas o confirmadas)
    public function contarCitasPendientes()
    {
        return $this->contar('cita', "estado IN ('programada', 'confirmada')");
    }

    // Contar consultas realizadas
    public function contarConsultas()
    {
        return $this->contar('consulta');
    }

    // Calcular tasa de asistencia (% de citas que NO fueron canceladas sobre el total)
    public function calcularTasaAsistencia()
    {
        $query = "SELECT COUNT(*) AS total,SUM(CASE WHEN estado != 'cancelada' THEN 1 ELSE 0 END) AS asistidas FROM cita";
        $stmt = $this->ejecutar($query);
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();

        $total = (int) ($fila['total'] ?? 0);
        if ($total === 0) {
            return 0.0;
        }
        $asistidas = (int) ($fila['asistidas'] ?? 0);
        return round(($asistidas / $total) * 100, 1);
    }

    // obtiene las proximas citas y las muestra en el dashboard
    public function obtenerProximasCitas($limite = 4)
    {
        $query = "SELECT
                    c.id_cita,
                    c.id_paciente,
                    c.fecha,
                    c.estado,
                    p.nombre AS paciente_nombre,
                    p.apellido_paterno AS paciente_apellido,
                    CONCAT('Dr(a). ', m.nombre, ' ', m.apellido_paterno) AS medico_nombre
                FROM cita c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                INNER JOIN medico m ON p.id_medico = m.id_medico
                WHERE c.estado IN ('programada', 'confirmada')
                ORDER BY c.fecha ASC
                LIMIT ?";

        $stmt = $this->ejecutar($query, 'i', [(int) $limite]);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

     

    public function obtenerTendenciaSemestral($meses = 6)// obtiene la tendencia de citas realizdas o citas agendadas,y las grafica
    {
        $meses = max(1, (int) $meses);
        $nombres = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $inicio = (new DateTimeImmutable('first day of this month'))->modify('-' . ($meses - 1) . ' months');
        $fecha_inicio = $inicio->format('Y-m-01 00:00:00');

        $etiquetas = [];
        $claves_mes = [];
        $series = ['consultas' => array_fill(0, $meses, 0), 'citas' => array_fill(0, $meses, 0)];

        for ($i = 0; $i < $meses; $i++) {
            $mes = $inicio->modify("+{$i} months");
            $etiquetas[] = $nombres[(int) $mes->format('n')];
            $claves_mes[$mes->format('Y-m')] = $i;
        }

        foreach (['consultas' => 'consulta', 'citas' => 'cita'] as $clave => $tabla) {
            $query = "SELECT DATE_FORMAT(fecha, '%Y-%m') AS ym, COUNT(*) AS total FROM {$tabla} WHERE fecha >= ? GROUP BY ym";
            $resultado = $this->ejecutar($query, 's', [$fecha_inicio])->get_result();
            while ($resultado && ($fila = $resultado->fetch_assoc())) {
                if (isset($claves_mes[$fila['ym']])) {
                    $series[$clave][$claves_mes[$fila['ym']]] = (int) $fila['total'];
                }
            }
        }

        return ['etiquetas' => $etiquetas] + $series;
    }
}
