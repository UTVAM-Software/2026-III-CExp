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

    /**
     * Ejecuta una consulta SELECT y devuelve todas las filas.
     */
    protected function obtenerTodos($sql, $types = '', $params = [])
    {
        $resultado = $this->ejecutar($sql, $types, $params)->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Ejecuta una consulta SELECT y devuelve una sola fila o null.
     */
    protected function obtenerUno($sql, $types = '', $params = [])
    {
        $resultado = $this->ejecutar($sql, $types, $params)->get_result();
        return $resultado ? $resultado->fetch_assoc() : null;
    }

    /**
     * Ejecuta una operación dentro de una transacción segura.
     */
    protected function transaccion(callable $operacion)
    {
        $this->conn->begin_transaction();
        try {
            $resultado = $operacion();
            $this->conn->commit();
            return $resultado;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // Obtiene todos los registros de la tabla asociada.
    public function all()
    {
        return $this->obtenerTodos("SELECT * FROM {$this->tabla}");
    }

    /**
     * Obtiene un registro por su llave primaria.
     */
    public function get($id)
    {
        return $this->obtenerUno("SELECT * FROM {$this->tabla} WHERE {$this->pk} = ? LIMIT 1", 'i', [(int)$id]);
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
