<?php
require_once __DIR__ . '/Modelo.php';

class permiso extends Modelo
{
    protected $tabla = 'permiso';
    protected $pk = 'id_permiso';

    public function listarTodos()
    {
        return $this->obtenerTodos("SELECT id_permiso, nombre, codename FROM {$this->tabla} ORDER BY id_permiso ASC");
    }

    public function obtenerCodenamesPorUsuario($id_grupo)
    {
        $query = "SELECT p.codename
                FROM {$this->tabla} p
                INNER JOIN tiene t ON p.id_permiso = t.id_permiso
                WHERE t.id_grupo = ?";
        $filas = $this->obtenerTodos($query, 'i', [(int)$id_grupo]);
        return array_column($filas, 'codename');
    }

    public function sincronizarPermisosGrupo($id_grupo, $permisos_ids = [])
    {
        $id_grupo = (int)$id_grupo;
        if ($id_grupo <= 0) {
            return false;
        }

        $ids_validos = array_values(array_unique(array_filter(
            array_map('intval', $permisos_ids),
            fn($id) => $id > 0
        )));

        return $this->transaccion(function () use ($id_grupo, $ids_validos) {
            $this->ejecutar("DELETE FROM tiene WHERE id_grupo = ?", 'i', [$id_grupo]);

            if (!empty($ids_validos)) {
                $stmt = $this->conn->prepare("INSERT INTO tiene (id_grupo, id_permiso) VALUES (?, ?)");
                if (!$stmt) {
                    throw new RuntimeException("Error al preparar inserción de permisos: " . $this->conn->error);
                }

                foreach ($ids_validos as $id_permiso) {
                    $stmt->bind_param('ii', $id_grupo, $id_permiso);
                    $stmt->execute();
                }
            }

            return true;
        });
    }
}
