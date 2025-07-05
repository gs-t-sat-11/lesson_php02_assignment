<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            // Cloud SQLの場合
            if (strpos(DB_HOST, '/cloudsql/') === 0) {
                $dsn = sprintf(
                    'mysql:unix_socket=%s;dbname=%s;charset=utf8mb4',
                    DB_HOST,
                    DB_NAME
                );
            }
            // MacのXAMPPの場合
            elseif (DB_HOST === 'localhost' && PHP_OS === 'Darwin') {
                $dsn = sprintf(
                    'mysql:unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=%s;charset=utf8mb4',
                    DB_NAME
                );
            }
            // 通常の接続
            else {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    DB_HOST,
                    DB_PORT,
                    DB_NAME
                );
            }
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);
        } catch (PDOException $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            $message = ENV === 'development' ? 'Database connection error: ' . $e->getMessage() : 'Database connection error';
            echo json_encode([
                'success' => false,
                'status' => 'error',
                'result' => ['message' => $message]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // クローン、デシリアライズを禁止
    private function __clone() {}
    private function __wakeup() {}
}