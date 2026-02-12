<?php
/**
 * PDO Database Connection Singleton
 */
require_once(__DIR__ . '/../config.php');

class DB {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $host = $GLOBALS['db_config']['host2'];
        $dbname = $GLOBALS['db_config']['dbname2'];
        $user = $GLOBALS['db_config']['user2'];
        $pass = $GLOBALS['db_config']['pass2'];
        
        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getPDO() {
        return $this->pdo;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function for easy access
function getDB() {
    return DB::getInstance()->getPDO();
}
