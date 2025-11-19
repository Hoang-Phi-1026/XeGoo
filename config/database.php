<?php
// Config
define('DB_HOST', 'localhost');
define('DB_NAME', 'xegoo_db');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
             // Set MySQL session timezone to match PHP timezone (Asia/Ho_Chi_Minh)
            $this->conn->exec("SET time_zone = '+07:00'");
            error_log("[Database] Connected successfully to " . DB_NAME);
        } catch (PDOException $e) {
            error_log("[Database] Connection failed: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->conn;
    }
}

// Helper
function query($sql, $params = []) {
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fetch($sql, $params = []) {
    return query($sql, $params)->fetch();
}

function fetchAll($sql, $params = []) {
    return query($sql, $params)->fetchAll();
}

function fetchOne($sql, $params = []) {
    return query($sql, $params)->fetch();
}

function lastInsertId() {
    return Database::getInstance()->lastInsertId();
}
?>
