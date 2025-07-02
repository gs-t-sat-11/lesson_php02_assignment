<?php
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

// 必須パラメータのチェック
$required = ['feed_url', 'user_name', 'post_content'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        jsonResponse(false, 'error', ['message' => $field . ' は必須項目です'], 400);
    }
}

try {
    $db = Database::getInstance()->getConnection();
    
    // feed_dataの構築
    $feedData = [
        'images' => $input['images'] ?? [],
        'videos' => $input['videos'] ?? [],
        'quoted_tweet' => $input['quoted_tweet'] ?? null,
        'raw_data' => []
    ];
    
    // feedDataに日時も含める
    $feedData['post_date'] = $input['post_date'] ?? null;
    
    // データの挿入（post_dateはNULLで固定）
    $sql = "INSERT INTO x_feeds (
        feed_url, user_name, user_icon_url, post_content, 
        post_date, feed_data
    ) VALUES (
        :feed_url, :user_name, :user_icon_url, :post_content,
        NULL, :feed_data
    )";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':feed_url' => $input['feed_url'],
        ':user_name' => $input['user_name'],
        ':user_icon_url' => $input['user_icon_url'] ?? null,
        ':post_content' => $input['post_content'],
        ':feed_data' => json_encode($feedData, JSON_UNESCAPED_UNICODE)
    ]);
    
    $insertId = $db->lastInsertId();
    
    jsonResponse(true, 'ok', [
        'id' => $insertId,
        'message' => 'フィードを保存しました'
    ]);
    
} catch (Exception $e) {
    $errorMessage = ENV === 'development' ? $e->getMessage() : 'サーバーエラーが発生しました';
    jsonResponse(false, 'error', ['message' => $errorMessage], 500);
}