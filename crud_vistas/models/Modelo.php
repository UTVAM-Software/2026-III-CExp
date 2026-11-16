<?php
require_once __DIR__ . '/../config/database.php';

class Modelo
{
    protected $conn;
    protected $tabla = '';
    protected $pk = 'id';

    public function __construct($db = null)
    {
        if ($db !== null) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Prepara, vincula parámetros y ejecuta una consulta SQL de forma segura.
     */
    protected function ejecutar($sql, $types = '', $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException("Error al preparar la consulta: " . $this->conn->error);
        }

        if ($types !== '' && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    // Obtiene todos los registros de la tabla asociada.
    public function all()
    {
        $sql = "SELECT * FROM {$this->tabla}";
        $stmt = $this->ejecutar($sql);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene un registro por su llave primaria.
     */
    public function get($id)
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE {$this->pk} = ? LIMIT 1";
        $stmt = $this->ejecutar($sql, 'i', [(int)$id]);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc() : null;
    }

    /**
     * Elimina un registro por su llave primaria.
     */
    public function delete($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }

        $sql = "DELETE FROM {$this->tabla} WHERE {$this->pk} = ?";
        $stmt = $this->ejecutar($sql, 'i', [$id]);
        return $stmt->affected_rows > 0;
    }
}
