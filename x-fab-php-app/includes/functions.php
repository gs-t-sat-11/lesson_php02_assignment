<?php
// 共通関数

/**
 * JSON形式でレスポンスを返す
 */
function jsonResponse($success, $status, $result, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'status' => $status,
        'result' => $result
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * HTMLエスケープ
 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRFトークンの生成
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRFトークンの検証
 */
function validateCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * CORSヘッダーの設定
 */
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: https://x.com');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // プリフライトリクエストへの対応
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * ハッシュタグとメンションをリンクに変換
 */
function formatPostContent($content) {
    // ハッシュタグをリンクに変換
    $content = preg_replace(
        '/#(\w+)/u',
        '<a href="https://x.com/hashtag/$1" target="_blank" rel="noopener">#$1</a>',
        $content
    );
    
    // メンションをリンクに変換
    $content = preg_replace(
        '/@(\w+)/u',
        '<a href="https://x.com/$1" target="_blank" rel="noopener">@$1</a>',
        $content
    );
    
    return $content;
}

/**
 * 日時フォーマット
 */
function formatDateTime($datetime) {
    if (empty($datetime)) {
        return '';
    }
    return date('Y/m/d H:i:s', strtotime($datetime));
}