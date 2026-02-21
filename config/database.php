<?php
/**
 * ZURIHUB TECHNOLOGY - Database Connection Class
 * PDO-based database connection with error handling
 */

define('ZURIHUB', true);
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (ENVIRONMENT === 'development') {
                die("Database Connection Failed: " . $e->getMessage());
            } else {
                error_log("Database Connection Failed: " . $e->getMessage());
                die("Database connection error. Please try again later.");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function to get database connection
function db() {
    return Database::getInstance()->getConnection();
}

// Helper function for quick queries
function query($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Helper function to fetch single row
function fetchOne($sql, $params = []) {
    return query($sql, $params)->fetch();
}

// Helper function to fetch all rows
function fetchAll($sql, $params = []) {
    return query($sql, $params)->fetchAll();
}

// Helper function to get last insert ID
function lastInsertId() {
    return db()->lastInsertId();
}

// Helper function to escape for LIKE queries
function escapeLike($str) {
    return str_replace(['%', '_'], ['\\%', '\\_'], $str);
}
