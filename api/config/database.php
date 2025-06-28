<?php
// ===================================
// api/config/database.php
// ===================================
class Database {
    private $host = "localhost";
    //private $db_name = "u987436224_dashboard";
    //private $username = "u987436224_dashboard";
    //private $password = "$C4^RTk$Vtnk";
    private $db_name = "trello_clone";
    private $username = "root";
    private $password = "";    
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}