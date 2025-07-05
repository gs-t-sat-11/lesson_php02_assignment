<?php
// ヘルスチェックエンドポイント
header('Content-Type: application/json');

try {
    // データベース接続チェック（オプション）
    require_once dirname(__DIR__) . '/includes/db.php';
    $db = Database::getInstance()->getConnection();
    $db->query("SELECT 1");
    
    http_response_code(200);
    echo json_encode([
        'status' => 'healthy',
        'service' => 'x-fab-php-app',
        'timestamp' => date('c')
    ]);
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'unhealthy',
        'service' => 'x-fab-php-app',
        'error' => 'Database connection failed',
        'timestamp' => date('c')
    ]);
}