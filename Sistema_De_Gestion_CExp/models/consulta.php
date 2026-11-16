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

        $stmt = $this->ejecutar($query);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerCitaPorId($id_cita)
    {
        $query = "SELECT id_cita, id_paciente, fecha, estado
                  FROM cita
                  WHERE id_cita = ?
                  LIMIT 1";
        $stmt = $this->ejecutar($query, 'i', [(int)$id_cita]);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc() : null;
    }

    public function registrarConsultaConReceta($datos, $medicamentos = [])
    {
        $this->conn->begin_transaction();

        try {
            $queryReceta = "INSERT INTO receta (diagnostico, fecha) VALUES (?, ?)";
            $this->ejecutar($queryReceta, 'ss', [$datos['diagnostico'], $datos['fecha']]);
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

            $this->conn->commit();
            return $id_consulta;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function obtenerConsultas($id_paciente = 0, $limite = 15)
    {
        $id_paciente = (int)$id_paciente;
        $limite      = (int)$limite;

        if ($id_paciente > 0) {
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
                      WHERE c.id_paciente = ?
                      ORDER BY c.fecha DESC
                      LIMIT ?";
            $stmt = $this->ejecutar($query, 'ii', [$id_paciente, $limite]);
        } else {
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
                      ORDER BY c.fecha DESC
                      LIMIT ?";
            $stmt = $this->ejecutar($query, 'i', [$limite]);
        }

        $resultado = $stmt->get_result();
        $consultas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

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
        $stmt = $this->ejecutar($query, 'i', [(int)$id_receta]);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
}
