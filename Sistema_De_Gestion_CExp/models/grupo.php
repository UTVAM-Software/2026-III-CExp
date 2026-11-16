<?php
require_once __DIR__ . '/Modelo.php';

class grupo extends Modelo
{
    protected $tabla = 'grupo';
    protected $pk = 'id_grupo';

    public function getgrupoByNombre($nombre)
    {
        $sql = "SELECT id_grupo, nombre FROM {$this->tabla} WHERE nombre = ? LIMIT 1";
        $stmt = $this->ejecutar($sql, 's', [trim($nombre)]);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc() : null;
    }

    public function getgrupoById($id)
    {
        return $this->get($id);
    }

    public function listarConTotalUsuarios()
    {
        $sql = "SELECT g.id_grupo, g.nombre, COUNT(c.id_usuario) AS total_usuarios
                FROM {$this->tabla} g
                LEFT JOIN contiene c ON g.id_grupo = c.id_grupo
                GROUP BY g.id_grupo, g.nombre
                ORDER BY g.id_grupo ASC";

        $stmt = $this->ejecutar($sql);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function crearGrupo($nombre)
    {
        $sql = "INSERT INTO {$this->tabla} (nombre) VALUES (?)";
        $this->ejecutar($sql, 's', [trim($nombre)]);
        return (int)$this->conn->insert_id;
    }

    public function actualizarGrupo($id_grupo, $nombre)
    {
        $sql = "UPDATE {$this->tabla} SET nombre = ? WHERE {$this->pk} = ?";
        $this->ejecutar($sql, 'si', [trim($nombre), (int)$id_grupo]);
        return true;
    }
}
