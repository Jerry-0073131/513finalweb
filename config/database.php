<?php
// Database configuration for TechPioneer
class Database {
    private $host = "sql306.infinityfree.com";
    private $db_name = "if0_37507179_wp99";
    private $username = "if0_37507179";
    private $password = "kwgqwe889922";
    public $conn;

    // Get database connection (保持为公共实例方法)
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // Log error and redirect to error page
            error_log("Database connection error: " . $exception->getMessage());
            header("Location: ../pages/database_error.php");
            exit;
        }
        return $this->conn;
    }

    // 添加静态方法获取数据库连接 (用于新代码)
    public static function getPDO() {
        $instance = new self();
        return $instance->getConnection();
    }
}

// 全局辅助函数
function getDB() {
    return Database::getPDO();
}
?>