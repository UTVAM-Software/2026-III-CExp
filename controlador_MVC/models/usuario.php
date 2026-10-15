<?php
class usuario {
    private $conn;
    private $table_name = "usuario";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para buscar un usuario por su username (Para el Login)
    public function getUsuarioByUsername($username) {
        // Uso de consultas preparadas (evita Inyección SQL)
        $query = "SELECT id_usuario, username, contrasena, estado FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar y vincular el parámetro
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":username", $username);
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>