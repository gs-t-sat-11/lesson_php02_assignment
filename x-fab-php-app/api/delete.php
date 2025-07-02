<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// CORSヘッダー設定
setCorsHeaders();

// POSTメソッドのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'error', ['message' => 'Method Not Allowed'], 405);
}

// リクエストボディの取得
$input = json_decode(file_get_contents('php://input'), true);

// IDのチェック
if (empty($input['id'])) {
    jsonResponse(false, 'error', ['message' => 'IDは必須項目です'], 400);
}

// CSRFトークンのチェック（APIデバッグページ以外からのアクセス時）
if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/api/debug') === false) {
    if (empty($input['csrf_token']) || !validateCsrfToken($input['csrf_token'])) {
        jsonResponse(false, 'error', ['message' => '不正なリクエストです'], 403);
    }
}

try {
    $db = Database::getInstance()->getConnection();
    
    // データの削除
    $sql = "DELETE FROM x_feeds WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $input['id']]);
    
    if ($stmt->rowCount() > 0) {
        jsonResponse(true, 'ok', ['message' => 'フィードを削除しました']);
    } else {
        jsonResponse(false, 'error', ['message' => '指定されたフィードが見つかりません'], 404);
    }
    
} catch (Exception $e) {
    $errorMessage = ENV === 'development' ? $e->getMessage() : 'サーバーエラーが発生しました';
    jsonResponse(false, 'error', ['message' => $errorMessage], 500);
}