<?php
class Database {
    private $host = "localhost";
    private $db_name = "cexp_plus"; // Cambia esto por el nombre exacto en phpMyAdmin
    private $username = "root"; // Usuario por defecto en WampServer
    private $password = ""; // Contraseña por defecto en WampServer (vacía)
    public $conn;

    public function getConnection() {
        $this->conn = null;

        // Crear la conexión usando la clase mysqli
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

        // Verificar si hubo un error de conexión
        if ($this->conn->connect_error) {
            die("Error de conexión a la base de datos: " . $this->conn->connect_error);
        }

        // Forzar el uso de UTF-8 para que los acentos y las 'ñ' de pacientes/médicos se vean bien
        $this->conn->set_charset("utf8");

        return $this->conn;
    }
}
?>
