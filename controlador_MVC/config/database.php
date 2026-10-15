<?php
class Database {
    private $host = "localhost";
    private $db_name = "cexp_plus"; // Cambia esto por el nombre exacto en phpMyAdmin
    private $username = "root"; // Usuario por defecto en WampServer
    private $password = ""; // Contraseña por defecto en WampServer (vacía)
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false
                ]
            );
        } catch (PDOException $exception) {
            die("Error de conexión a la base de datos: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>
