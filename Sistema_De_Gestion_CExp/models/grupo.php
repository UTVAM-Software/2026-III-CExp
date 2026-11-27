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
        $sql = "SELECT g.id_grupo,
                       g.nombre,
                       COUNT(DISTINCT c.id_usuario) AS total_usuarios,
                       GROUP_CONCAT(DISTINCT t.id_permiso ORDER BY t.id_permiso ASC SEPARATOR ',') AS permisos_ids_csv,
                       GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.id_permiso ASC SEPARATOR '|') AS permisos_nombres_csv
                FROM {$this->tabla} g
                LEFT JOIN contiene c ON g.id_grupo = c.id_grupo
                LEFT JOIN tiene t ON g.id_grupo = t.id_grupo
                LEFT JOIN permiso p ON t.id_permiso = p.id_permiso
                GROUP BY g.id_grupo, g.nombre
                ORDER BY g.id_grupo ASC";

        $stmt = $this->ejecutar($sql);
        $resultado = $stmt->get_result();
        $filas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

        foreach ($filas as &$fila) {
            $fila['permisos_ids'] = !empty($fila['permisos_ids_csv'])
                ? array_map('intval', explode(',', $fila['permisos_ids_csv']))
                : [];
            $fila['permisos_nombres'] = !empty($fila['permisos_nombres_csv'])
                ? explode('|', $fila['permisos_nombres_csv'])
                : [];
            unset($fila['permisos_ids_csv'], $fila['permisos_nombres_csv']);
        }
        unset($fila);

        return $filas;
    }

    public function contarUsuarios($id_grupo)
    {
        $sql = "SELECT COUNT(*) AS total FROM contiene WHERE id_grupo = ?";
        $fila = $this->ejecutar($sql, 'i', [(int)$id_grupo])->get_result()->fetch_assoc();
        return (int)($fila['total'] ?? 0);
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
