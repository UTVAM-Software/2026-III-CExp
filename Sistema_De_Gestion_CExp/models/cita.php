<?php
require_once __DIR__ . '/Modelo.php';

class cita extends Modelo
{
    protected $tabla = 'cita';
    protected $pk = 'id_cita';
//funcion generica para los metodos Obtener()
    private function consultarTodos($query, $types = '', $params = [])
    {
        $resultado = $this->ejecutar($query, $types, $params)->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerCitas()
    {
        $query = "SELECT
                    c.id_cita,
                    c.id_paciente,
                    c.fecha,
                    c.estado,
                    p.nombre AS paciente_nombre,
                    p.apellido_paterno AS paciente_apellido,
                    m.id_medico,
                    m.nombre AS medico_nombre,
                    m.apellido_paterno AS medico_apellido
                FROM {$this->tabla} c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                INNER JOIN medico m ON p.id_medico = m.id_medico
                ORDER BY c.fecha ASC";

        return $this->consultarTodos($query);
    }

    public function obtenerCitasPorMedicoYFecha($id_medico, $fecha)
    {
        $query = "SELECT
                    c.id_cita,
                    c.fecha,
                    c.estado,
                    CONCAT(p.nombre, ' ', p.apellido_paterno) AS paciente_nombre
                FROM {$this->tabla} c
                INNER JOIN paciente p ON c.id_paciente = p.id_paciente
                WHERE p.id_medico = ?
                AND DATE(c.fecha) = ?
                AND c.estado <> 'cancelada'
                ORDER BY c.fecha ASC";

        $filas = $this->consultarTodos($query, 'is', [(int) $id_medico, $fecha]);

        $citas_por_hora = [];
        foreach ($filas as $fila) {
            $hora = date('H:i', strtotime($fila['fecha']));
            $citas_por_hora[$hora] = $fila;
        }

        return $citas_por_hora;
    }

    public function obtenerMedicos()
    {
        $query = "SELECT
                    id_medico,
                    nombre,
                    apellido_paterno,
                    especialidad,
                    CONCAT(nombre, ' ', apellido_paterno) AS nombre_completo
                FROM medico
                ORDER BY nombre ASC";

        return $this->consultarTodos($query);
    }

    public function obtenerPacientes()
    {
        $query = "SELECT
                    id_paciente,
                    nombre,
                    apellido_paterno,
                    CONCAT(nombre, ' ', apellido_paterno) AS nombre_completo
                FROM paciente
                ORDER BY nombre ASC";

        return $this->consultarTodos($query);
    }

    public function crearCita($id_paciente, $fecha, $estado)
    {
        $query = "INSERT INTO {$this->tabla} (id_paciente, fecha, estado) VALUES (?, ?, ?)";
        $this->ejecutar($query, 'iss', [(int) $id_paciente, $fecha, $estado]);
        return (int) $this->conn->insert_id;
    }

    public function actualizarCita($id_cita, $id_paciente, $fecha, $estado)
    {
        $query = "UPDATE {$this->tabla} SET id_paciente = ?, fecha = ?, estado = ? WHERE {$this->pk} = ?";
        $this->ejecutar($query, 'issi', [(int) $id_paciente, $fecha, $estado, (int) $id_cita]);
        return true;
    }
}
