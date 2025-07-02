# X Feed Saver

X（旧Twitter）のフィードを保存・管理するためのChrome拡張機能とPHPアプリケーションです。

## 機能

- Chrome拡張機能でXのフィードに「保存」ボタンを追加
- フィード情報（URL、ユーザー名、投稿内容、画像、動画、引用ツイート）をデータベースに保存
- 保存したフィードの閲覧・検索・削除機能
- レスポンシブデザイン対応

## 必要な環境

- XAMPP（Apache + MySQL + PHP）
- Google Chrome（拡張機能の開発者モード）
- phpMyAdmin（データベース管理）

## セットアップ手順

### 1. プロジェクトの配置

#### 自動配置スクリプトを使用する場合（Mac）

配置スクリプトを使用すると、自動的にXAMPPに必要なファイルがコピーされます：

```bash
# プロジェクトディレクトリに移動
cd /path/to/lesson_php02_assignment

# スクリプトを実行
./deploy_to_xampp.sh
```

スクリプトが以下を自動的に実行します：
- XAMPPのインストール確認
- ファイルのコピー
- パーミッションの設定
- .envファイルの作成

#### 手動で配置する場合

XAMPPのhtdocsディレクトリに`x-fab-php-app`フォルダを配置します：

```bash
# Windowsの場合
C:\xampp\htdocs\x-fab-php-app

# Macの場合
/Applications/XAMPP/htdocs/x-fab-php-app
```

### 2. データベースのセットアップ

1. XAMPPを起動し、ApacheとMySQLを開始
2. ブラウザで http://localhost/phpmyadmin にアクセス
3. 左サイドバーで「新規作成」をクリックするか、トップメニューの「SQL」タブを開く
4. 以下のSQLを実行（`x-fab-php-app/setup.sql`の内容と同じ）：

```sql
CREATE DATABASE IF NOT EXISTS x_fab_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE x_fab_db;

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
```

### 3. 環境設定

1. `x-fab-php-app/.env.example`を`.env`にコピー（既に作成済み）
2. 必要に応じて`.env`ファイルを編集：

```
DB_HOST=localhost
DB_NAME=x_fab_db
DB_USER=root
DB_PASS=
DB_PORT=3306
ENV=development
API_URL=http://localhost/x-fab-php-app/api
```

### 4. 動作確認

1. データベース接続テスト：
   ```
   http://localhost/x-fab-php-app/test_db.php
   ```

2. APIデバッグページ：
   ```
   http://localhost/x-fab-php-app/api/debug.php
   ```

3. フィード閲覧ページ：
   ```
   http://localhost/x-fab-php-app/feeds
   ```

### 5. Chrome拡張機能のインストール

1. Chromeで `chrome://extensions/` を開く
2. 右上の「デベロッパーモード」をONにする
3. 「パッケージ化されていない拡張機能を読み込む」をクリック
4. `x-fab-chrome-extension`フォルダを選択
5. 拡張機能が有効になったことを確認

### 6. 使い方

1. https://x.com にアクセス
2. 各フィードの右上に「保存」ボタンが表示される
3. ボタンをクリックすると、フィード情報がデータベースに保存される
4. http://localhost/x-fab-php-app/feeds で保存したフィードを閲覧

## ディレクトリ構造

```
lesson_php02_assignment/
├── x-fab-chrome-extension/     # Chrome拡張機能
│   ├── manifest.json          # 拡張機能の設定
│   ├── content.js             # コンテンツスクリプト
│   └── icons/                 # アイコンファイル
│
├── x-fab-php-app/             # PHPアプリケーション
│   ├── api/                   # APIエンドポイント
│   │   ├── save.php          # フィード保存
│   │   ├── list.php          # 一覧取得
│   │   ├── delete.php        # 削除
│   │   └── debug.php         # デバッグページ
│   ├── includes/              # 共通処理
│   ├── assets/                # CSS/JS
│   ├── .env                   # 環境変数
│   └── index.php              # 閲覧ページ
│
├── docs/                      # ドキュメント
├── deploy_to_xampp.sh         # XAMPP配置スクリプト（Mac用）
└── README.md                  # このファイル
```

## トラブルシューティング

### データベース接続エラー

- XAMPPのMySQLが起動しているか確認
- `.env`ファイルの設定が正しいか確認
- `test_db.php`でエラー詳細を確認

### phpMyAdminでのエラー

#### Error: #1046 データベースが選択されていません

このエラーが出た場合は、以下の手順で解決：

1. phpMyAdminのトップページから「SQL」タブを開く
2. `setup.sql`の内容を全てコピー＆ペースト（`USE x_fab_db;`を含む）
3. 実行

または、詳細な手順は `docs/phpmyadmin_setup_guide.md` を参照してください。

### Chrome拡張機能が動作しない

- デベロッパーモードがONになっているか確認
- 拡張機能のエラーログを確認（拡張機能ページの「エラー」ボタン）
- ページをリロードして再試行

### APIエラー

- `/api/debug.php`でAPIの動作を確認
- ブラウザの開発者ツールでネットワークエラーを確認
- PHPのエラーログを確認

## 今後の開発予定

- Cloud Run + Cloud SQLへのデプロイ対応
- ユーザー認証機能
- エクスポート機能
- 統計情報表示