<?php
class Database {
    private $host = "localhost";
    private $db_name = "ponglert_tilapia_farm";
    private $username = "root";  // Standard XAMPP MySQL username
    private $password = "";      // Standard XAMPP MySQL password (empty)
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_PERSISTENT => true,  // เปิด Persistent Connection
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // ปิดการจำลอง Prepared Statements
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>
