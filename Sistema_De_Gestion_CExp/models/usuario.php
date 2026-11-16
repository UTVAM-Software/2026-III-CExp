<?php
require_once __DIR__ . '/Modelo.php';

class usuario extends Modelo
{
    protected $tabla = 'usuario';
    protected $pk = 'id_usuario';

    public function getUsuarioByUsername($username)
    {
        $sql = "SELECT u.id_usuario, u.nombre_completo, u.username, u.contrasena, u.estado,
                       c.id_grupo, g.nombre AS nombre_grupo
                FROM {$this->tabla} u
                LEFT JOIN contiene c ON u.id_usuario = c.id_usuario
                LEFT JOIN grupo g ON c.id_grupo = g.id_grupo
                WHERE u.username = ?
                LIMIT 1";

        $stmt = $this->ejecutar($sql, 's', [trim($username)]);
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_assoc() : null;
    }

    public function listarConRolYPermisos()
    {
        $sql = "SELECT u.id_usuario, u.nombre_completo, u.username, u.email, u.estado,
                       COALESCE(MAX(c.id_grupo), 0) AS id_grupo,
                       COALESCE(MAX(g.nombre), 'Sin rol') AS rol_nombre,
                       GROUP_CONCAT(DISTINCT t.id_permiso ORDER BY t.id_permiso SEPARATOR ',') AS permisos_csv
                FROM {$this->tabla} u
                LEFT JOIN contiene c ON u.id_usuario = c.id_usuario
                LEFT JOIN grupo g ON c.id_grupo = g.id_grupo
                LEFT JOIN tiene t ON u.id_usuario = t.id_usuario
                GROUP BY u.id_usuario, u.nombre_completo, u.username, u.email, u.estado
                ORDER BY u.id_usuario ASC";

        $stmt = $this->ejecutar($sql);
        $resultado = $stmt->get_result();
        $filas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

        foreach ($filas as &$fila) {
            $fila['id_grupo'] = (int)$fila['id_grupo'];
            if (!empty($fila['permisos_csv'])) {
                $fila['permisos_ids'] = array_map('intval', explode(',', $fila['permisos_csv']));
            } else {
                $fila['permisos_ids'] = [];
            }
            unset($fila['permisos_csv']);
        }

        return $filas;
    }

    public function crearUsuario($nombre_completo, $username, $contrasena, $email, $estado)
    {
        $sql = "INSERT INTO {$this->tabla} (nombre_completo, username, contrasena, email, estado)
                VALUES (?, ?, ?, ?, ?)";

        $this->ejecutar($sql, 'sssss', [$nombre_completo, $username, $contrasena, $email, $estado]);
        return (int)$this->conn->insert_id;
    }

    public function actualizarUsuario($id_usuario, $nombre_completo, $username, $contrasena, $email, $estado)
    {
        if ($contrasena !== '') {
            $sql = "UPDATE {$this->tabla}
                    SET nombre_completo = ?, username = ?, contrasena = ?, email = ?, estado = ?
                    WHERE {$this->pk} = ?";
            $this->ejecutar($sql, 'sssssi', [$nombre_completo, $username, $contrasena, $email, $estado, (int)$id_usuario]);
        } else {
            $sql = "UPDATE {$this->tabla}
                    SET nombre_completo = ?, username = ?, email = ?, estado = ?
                    WHERE {$this->pk} = ?";
            $this->ejecutar($sql, 'ssssi', [$nombre_completo, $username, $email, $estado, (int)$id_usuario]);
        }

        return true;
    }

    public function asignarGrupo($id_usuario, $id_grupo)
    {
        $id_usuario = (int)$id_usuario;
        $id_grupo   = (int)$id_grupo;

        if ($id_usuario <= 0) {
            return false;
        }

        $this->ejecutar("DELETE FROM contiene WHERE id_usuario = ?", 'i', [$id_usuario]);

        if ($id_grupo > 0) {
            $this->ejecutar("INSERT INTO contiene (id_usuario, id_grupo) VALUES (?, ?)", 'ii', [$id_usuario, $id_grupo]);
        }

        return true;
    }

    public function sincronizarMedico($id_usuario, $nombre_completo, $id_grupo)
    {
        $id_usuario = (int)$id_usuario;
        if ($id_usuario <= 0 || (int)$id_grupo !== 2) {
            return false;
        }

        $stmt = $this->ejecutar("SELECT id_medico FROM medico WHERE id_usuario = ? LIMIT 1", 'i', [$id_usuario]);
        if ($stmt->get_result()->fetch_assoc()) {
            return true;
        }

        $partes = preg_split('/\s+/', trim($nombre_completo));
        $nombre = mb_substr($partes[0] ?? 'Medico', 0, 20);
        $apellido_paterno = mb_substr($partes[1] ?? 'General', 0, 20);
        $apellido_materno = mb_substr($partes[2] ?? '', 0, 20);

        $query = "INSERT INTO medico (id_usuario, nombre, apellido_paterno, apellido_materno, telefono, cedula_profesional, especialidad)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $this->ejecutar($query, 'issssss', [
            $id_usuario,
            $nombre,
            $apellido_paterno,
            $apellido_materno,
            '0000000000',
            'PENDIENT',
            'Medicina General',
        ]);

        return true;
    }
}
