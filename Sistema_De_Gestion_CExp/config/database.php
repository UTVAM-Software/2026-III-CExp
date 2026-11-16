<?php
require_once __DIR__ . '/env.php';

class Database
{
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private static $cnn = null;

    
    public function getConnection()
    {
        if (self::$cnn === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                self::$cnn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
                self::$cnn->set_charset("utf8mb4");
            } catch (mysqli_sql_exception $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        return self::$cnn;
    }
}
