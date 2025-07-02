<?php
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// CORSヘッダー設定
setCorsHeaders();

// GETメソッドのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'error', ['message' => 'Method Not Allowed'], 405);
}

try {
    $db = Database::getInstance()->getConnection();
    
    // パラメータの取得
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 500;
    $offset = ($page - 1) * $limit;
    $search = $_GET['q'] ?? '';
    $sort = $_GET['sort'] === 'asc' ? 'ASC' : 'DESC';
    
    // 基本クエリ
    $whereClause = '';
    $params = [];
    
    // 検索条件の構築
    if (!empty($search)) {
        $whereClause = 'WHERE post_content LIKE :search OR user_name LIKE :search';
        $params[':search'] = '%' . $search . '%';
    }
    
    // 総件数の取得
    $countSql = "SELECT COUNT(*) as total FROM x_feeds $whereClause";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch()['total'];
    $totalPages = ceil($totalCount / $limit);
    
    // データの取得
    $sql = "SELECT * FROM x_feeds 
            $whereClause 
            ORDER BY created_at $sort 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $feeds = $stmt->fetchAll();
    
    // feed_dataのデコード
    foreach ($feeds as &$feed) {
        $feed['feed_data'] = json_decode($feed['feed_data'], true);
    }
    
    jsonResponse(true, 'ok', [
        'feeds' => $feeds,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
            'per_page' => $limit
        ]
    ]);
    
} catch (Exception $e) {
    $errorMessage = ENV === 'development' ? $e->getMessage() : 'サーバーエラーが発生しました';
    jsonResponse(false, 'error', ['message' => $errorMessage], 500);
}