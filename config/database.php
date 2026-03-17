<?php
// Database configuration for Student Registration System

class Database
{
    private $host = 'localhost';
    private $db = 'student_reg';
    private $user = 'root';
    private $pass = 'Timo9380.';
    private $charset = 'utf8mb4';

    public $pdo;

    public function connect()
    {
        if ($this->pdo) {
            return $this->pdo;
        }
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return $this->pdo;
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
}

// Test connection in console - DISABLED to prevent output interference
if (php_sapi_name() === 'cli') {
    $db = new Database();
    $conn = $db->connect();
    if ($conn) {
        echo "Database connected!\n";
    }
}
