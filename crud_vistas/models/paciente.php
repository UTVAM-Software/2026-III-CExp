<?php
require_once __DIR__ . '/Modelo.php';

class paciente extends Modelo
{
    protected $tabla = 'paciente';
    protected $pk = 'id_paciente';

    public function listarConFiltros($buscar = '', $id_medico = 0, $alergia = '')
    {
        $condiciones = [];
        $types       = '';
        $params      = [];

        $buscar = trim($buscar);
        if ($buscar !== '') {
            $like = '%' . $buscar . '%';
            $condiciones[] = "(p.nombre LIKE ? OR p.apellido_paterno LIKE ? OR p.apellido_materno LIKE ? OR p.contacto LIKE ? OR p.alergia LIKE ?)";
            $types .= 'sssss';
            array_push($params, $like, $like, $like, $like, $like);
        }

        $id_medico = (int)$id_medico;
        if ($id_medico > 0) {
            $condiciones[] = "p.id_medico = ?";
            $types .= 'i';
            $params[] = $id_medico;
        }

        if ($alergia === 'con_alergia') {
            $condiciones[] = "(p.alergia IS NOT NULL AND TRIM(p.alergia) <> '')";
        } elseif ($alergia === 'sin_alergia') {
            $condiciones[] = "(p.alergia IS NULL OR TRIM(p.alergia) = '')";
        }

        $where = !empty($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '';

        $query = "SELECT
                    p.id_paciente,
                    p.id_medico,
                    p.nombre,
                    p.apellido_paterno,
                    COALESCE(p.apellido_materno, '') AS apellido_materno,
                    p.fecha_nacimiento,
                    COALESCE(p.alergia, '') AS alergia,
                    COALESCE(p.contacto, '') AS contacto,
                    CONCAT(m.nombre, ' ', m.apellido_paterno) AS medico_nombre,
                    COALESCE(MAX(a.notas), '') AS antecedentes
                  FROM {$this->tabla} p
                  INNER JOIN medico m ON p.id_medico = m.id_medico
                  LEFT JOIN antecedentes a ON p.id_paciente = a.id_paciente
                  {$where}
                  GROUP BY p.id_paciente, p.id_medico, p.nombre, p.apellido_paterno, p.apellido_materno,
                           p.fecha_nacimiento, p.alergia, p.contacto, m.nombre, m.apellido_paterno
                  ORDER BY p.id_paciente ASC";

        $stmt = $this->ejecutar($query, $types, $params);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerMedicos()
    {
        $query = "SELECT id_medico, CONCAT(nombre, ' ', apellido_paterno) AS nombre_completo
                  FROM medico
                  ORDER BY nombre ASC";
        $stmt = $this->ejecutar($query);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function crearPaciente($id_medico, $nombre, $apellido_paterno, $apellido_materno, $fecha_nacimiento, $alergia, $contacto)
    {
        $query = "INSERT INTO {$this->tabla} (id_medico, nombre, apellido_paterno, apellido_materno, fecha_nacimiento, alergia, contacto)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $this->ejecutar($query, 'issssss', [
            (int)$id_medico,
            $nombre,
            $apellido_paterno,
            $apellido_materno,
            $fecha_nacimiento,
            $alergia,
            $contacto,
        ]);
        return (int)$this->conn->insert_id;
    }

    public function actualizarPaciente($id_paciente, $id_medico, $nombre, $apellido_paterno, $apellido_materno, $fecha_nacimiento, $alergia, $contacto)
    {
        $query = "UPDATE {$this->tabla}
                  SET id_medico = ?, nombre = ?, apellido_paterno = ?, apellido_materno = ?,
                      fecha_nacimiento = ?, alergia = ?, contacto = ?
                  WHERE {$this->pk} = ?";
        $this->ejecutar($query, 'issssssi', [
            (int)$id_medico,
            $nombre,
            $apellido_paterno,
            $apellido_materno,
            $fecha_nacimiento,
            $alergia,
            $contacto,
            (int)$id_paciente,
        ]);
        return true;
    }

    public function guardarAntecedentes($id_paciente, $notas)
    {
        $id_paciente = (int)$id_paciente;
        $queryCheck = "SELECT id_antecedente FROM antecedentes WHERE id_paciente = ? LIMIT 1";
        $stmt = $this->ejecutar($queryCheck, 'i', [$id_paciente]);
        $existente = $stmt->get_result()->fetch_assoc();

        if ($existente) {
            $queryUpdate = "UPDATE antecedentes SET notas = ? WHERE id_antecedente = ?";
            $this->ejecutar($queryUpdate, 'si', [$notas, (int)$existente['id_antecedente']]);
        } else {
            $queryInsert = "INSERT INTO antecedentes (id_paciente, notas) VALUES (?, ?)";
            $this->ejecutar($queryInsert, 'is', [$id_paciente, $notas]);
        }

        return true;
    }

    public function agregarAntecedente($id_paciente, $notas)
    {
        $query = "INSERT INTO antecedentes (id_paciente, notas) VALUES (?, ?)";
        $this->ejecutar($query, 'is', [(int)$id_paciente, $notas]);
        return (int)$this->conn->insert_id;
    }

    public function obtenerPorIdConMedico($id_paciente)
    {
        $query = "SELECT
                    p.id_paciente,
                    p.id_medico,
                    p.nombre,
                    p.apellido_paterno,
                    COALESCE(p.apellido_materno, '') AS apellido_materno,
                    p.fecha_nacimiento,
                    COALESCE(p.alergia, '') AS alergia,
                    COALESCE(p.contacto, '') AS contacto,
                    CONCAT(m.nombre, ' ', m.apellido_paterno) AS medico_nombre
                  FROM {$this->tabla} p
                  INNER JOIN medico m ON p.id_medico = m.id_medico
                  WHERE p.id_paciente = ?
                  LIMIT 1";
        $stmt = $this->ejecutar($query, 'i', [(int)$id_paciente]);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc() : null;
    }

    public function obtenerAntecedentes($id_paciente)
    {
        $query = "SELECT id_antecedente, id_paciente, notas
                  FROM antecedentes
                  WHERE id_paciente = ?
                  ORDER BY id_antecedente DESC";
        $stmt = $this->ejecutar($query, 'i', [(int)$id_paciente]);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
}
