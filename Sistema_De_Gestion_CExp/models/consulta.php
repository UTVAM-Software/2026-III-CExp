<?php
require_once __DIR__ . '/Modelo.php';

class consulta extends Modelo
{
    protected $tabla = 'consulta';
    protected $pk = 'id_consulta';

    public function obtenerCitasPendientes()
    {
        $query = "SELECT
                    c.id_cita,
                    c.id_paciente,
                    c.fecha,
                    c.estado,
                    CONCAT(p.nombre, ' ', p.apellido_paterno) AS paciente_nombre
                  FROM cita c
                  INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                  WHERE c.estado IN ('programada', 'confirmada')
                  ORDER BY c.fecha ASC";

        return $this->obtenerTodos($query);
    }

    public function obtenerCitaPorId($id_cita)
    {
        $query = "SELECT id_cita, id_paciente, fecha, estado
                  FROM cita
                  WHERE id_cita = ?
                  LIMIT 1";
        return $this->obtenerUno($query, 'i', [(int)$id_cita]);
    }

    public function registrarConsultaConReceta($datos, $medicamentos = [])
    {
        return $this->transaccion(function () use ($datos, $medicamentos) {
            $this->ejecutar(
                "INSERT INTO receta (diagnostico, fecha) VALUES (?, ?)",
                'ss',
                [$datos['diagnostico'], $datos['fecha']]
            );
            $id_receta = (int)$this->conn->insert_id;

            $stmtDetalle = $this->conn->prepare(
                "INSERT INTO receta_detalle (id_receta, medicamento, dosis, duracion, frecuencia)
                 VALUES (?, ?, ?, ?, ?)"
            );
            if (!$stmtDetalle) {
                throw new RuntimeException("Error al preparar detalle de receta: " . $this->conn->error);
            }

            foreach ($medicamentos as $med) {
                $stmtDetalle->bind_param(
                    'issss',
                    $id_receta,
                    $med['medicamento'],
                    $med['dosis'],
                    $med['duracion'],
                    $med['frecuencia']
                );
                $stmtDetalle->execute();
            }

            $id_cita = $datos['id_cita'] > 0 ? (int)$datos['id_cita'] : null;
            $queryConsulta = "INSERT INTO {$this->tabla}
                                (id_paciente, id_receta, id_cita, fecha, temperatura, saturacion_oxigeno, frecuencia_respiratoria, pulso_cardiaco)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $this->ejecutar($queryConsulta, 'iiisdiii', [
                (int)$datos['id_paciente'],
                $id_receta,
                $id_cita,
                $datos['fecha'],
                (float)$datos['temperatura'],
                (int)$datos['saturacion_oxigeno'],
                (int)$datos['frecuencia_respiratoria'],
                (int)$datos['pulso_cardiaco'],
            ]);
            $id_consulta = (int)$this->conn->insert_id;

            if ($id_cita !== null) {
                $this->ejecutar("UPDATE cita SET estado = 'concluida' WHERE id_cita = ?", 'i', [$id_cita]);
            }

            return $id_consulta;
        });
    }

    public function obtenerConsultas($id_paciente = 0, $limite = 15)
    {
        $id_paciente = (int)$id_paciente;
        $limite      = (int)$limite;
        $where       = $id_paciente > 0 ? 'WHERE c.id_paciente = ?' : '';
        $types       = $id_paciente > 0 ? 'ii' : 'i';
        $params      = $id_paciente > 0 ? [$id_paciente, $limite] : [$limite];

        $query = "SELECT
                    c.id_consulta,
                    c.id_paciente,
                    c.id_receta,
                    c.id_cita,
                    c.fecha,
                    c.temperatura,
                    c.saturacion_oxigeno,
                    c.frecuencia_respiratoria,
                    c.pulso_cardiaco,
                    r.diagnostico,
                    CONCAT(p.nombre, ' ', p.apellido_paterno) AS paciente_nombre
                FROM {$this->tabla} c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                INNER JOIN receta r ON c.id_receta = r.id_receta
                {$where}
                ORDER BY c.fecha DESC
                LIMIT ?";

        $consultas = $this->obtenerTodos($query, $types, $params);
        foreach ($consultas as &$cons) {
            $cons['detalles'] = $this->obtenerDetallesReceta((int)$cons['id_receta']);
        }

        return $consultas;
    }

    public function obtenerDetallesReceta($id_receta)
    {
        $query = "SELECT id_detalle, medicamento, dosis, duracion, frecuencia
                FROM receta_detalle
                WHERE id_receta = ?
                ORDER BY id_detalle ASC";
        return $this->obtenerTodos($query, 'i', [(int)$id_receta]);
    }
}
