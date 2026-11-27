<?php
require_once __DIR__ . '/Modelo.php';

class permiso extends Modelo
{
    protected $tabla = 'permiso';
    protected $pk = 'id_permiso';

    public function listarTodos()
    {
        $sql = "SELECT id_permiso, nombre, codename FROM {$this->tabla} ORDER BY id_permiso ASC";
        $stmt = $this->ejecutar($sql);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function crearPermiso($nombre, $codename)
    {
        $sql = "INSERT INTO {$this->tabla} (nombre, codename) VALUES (?, ?)";
        $this->ejecutar($sql, 'ss', [trim($nombre), trim($codename)]);
        return (int)$this->conn->insert_id;
    }

    public function actualizarPermiso($id_permiso, $nombre, $codename)
    {
        $sql = "UPDATE {$this->tabla} SET nombre = ?, codename = ? WHERE {$this->pk} = ?";
        $this->ejecutar($sql, 'ssi', [trim($nombre), trim($codename), (int)$id_permiso]);
        return true;
    }

    public function obtenerCodenamesPorUsuario($id_grupo)
    {
        $query = "SELECT p.codename
                FROM {$this->tabla} p
                INNER JOIN tiene t ON p.id_permiso = t.id_permiso
                WHERE t.id_grupo = ?";
        $stmt = $this->ejecutar($query, 'i', [(int)$id_grupo]);
        $resultado = $stmt->get_result();
        $filas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        return array_column($filas, 'codename');
    }

    public function sincronizarPermisosGrupo($id_grupo, $permisos_ids = [])
    {
        $id_grupo = (int)$id_grupo;
        if ($id_grupo <= 0) {
            return false;
        }

        $this->conn->begin_transaction();
        try {
            $this->ejecutar("DELETE FROM tiene WHERE id_grupo = ?", 'i', [$id_grupo]);

            if (!empty($permisos_ids)) {
                $stmtInsert = $this->conn->prepare("INSERT INTO tiene (id_grupo, id_permiso) VALUES (?, ?)");
                if (!$stmtInsert) {
                    throw new RuntimeException("Error al preparar inserción de permisos: " . $this->conn->error);
                }

                foreach ($permisos_ids as $id_permiso) {
                    $id_permiso = (int)$id_permiso;
                    if ($id_permiso > 0) {
                        $stmtInsert->bind_param('ii', $id_grupo, $id_permiso);
                        $stmtInsert->execute();
                    }
                }
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
