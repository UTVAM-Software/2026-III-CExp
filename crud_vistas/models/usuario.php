<?php
class usuario {
    private $conn;
    private $table_name = "usuario";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUsuarioByUsername($username) { //<-- consulta
        $query = "SELECT id_usuario, username, contrasena, estado FROM " . $this->table_name . " WHERE username = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Error al preparar la consulta: " . $this->conn->error);
        }
        
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bind_param("s", $username); //<-- (s)string, (i)integer, (d)decimal, (b)binary
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        return $resultado->fetch_assoc();
    }

    public function crearUsuario( $nombre_completo, $username, $contrasena, $email, $estado) {
        $query = "INSERT INTO " . $this->table_name . " (nombre_completo, username, contrasena, email, estado) VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        if(!$stmt){
            die("Error al crear el usuario: " . $this->conn->error);
        }
        $stmt->bind_param("sssss", $nombre_completo, $username, $contrasena, $email, $estado);
        return $stmt->execute();

    }

    public function actualizarUsuario($id_usuario, $nombre_completo, $username, $contrasena, $email, $estado){
        $query = "UPDATE " . $this->table_name . " SET nombre_completo = ?, username = ?, contrasena = ?, email = ?, estado = ? WHERE id_usuario = ?";

        $stmt = $this->conn->prepare($query);
        if(!$stmt){
            die("Error al modificar el usuario: " . $this->conn->error);
        }
        $stmt->bind_param("sssssi", $nombre_completo, $username, $contrasena, $email, $estado, $id_usuario);
        return $stmt->execute();
    }

    public function eliminarUsuario($id_usuario, $nombre_completo, $username, $contrasena, $email, $estado){
        if($this->id_usuario != 0){
        $query = "DELETE FROM " . $this->table_name . " WHERE id_usuario = ?";

        $stmt = $this->conn->prepare($query);
        if(!$stmt){
            die("Error al eliminar el usuario: " . $this->conn->error);
        }
        $stmt->bind_param("i", $id_usuario);
        return $stmt->execute();

        $this->$id_usuario = 0;
        $this->$nombre_completo = "";
        $this->$username = "";
        $this->$contrasena = "";
        $this->$email = "";
        $this->$estado = "";
        }
    }
}
?>