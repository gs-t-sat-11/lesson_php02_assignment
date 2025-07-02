-- phpMyAdminで実行する場合の手順：
-- 1. phpMyAdminにアクセス
-- 2. 左サイドバーの「新規作成」をクリック、またはトップメニューの「SQL」タブを開く
-- 3. このファイルの内容を全てコピー＆ペーストして実行

-- データベースの作成
CREATE DATABASE IF NOT EXISTS x_fab_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 作成したデータベースを選択
USE x_fab_db;

-- x_feedsテーブルの作成
CREATE TABLE IF NOT EXISTS x_feeds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feed_url VARCHAR(500) NOT NULL,
    user_name VARCHAR(200) NOT NULL,
    user_icon_url VARCHAR(500),
    post_content TEXT NOT NULL,
    post_date DATETIME,
    feed_data JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- テーブルの確認（オプション）
SHOW TABLES;
DESCRIBE x_feeds;