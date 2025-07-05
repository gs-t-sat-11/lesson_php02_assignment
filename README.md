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

## ファイル整理について

### デプロイ関連ファイル

本プロジェクトでは、Cloud Runへのデプロイ過程で複数の試行錯誤を行いました。最終的に使用するファイルのみを残し、以下のファイルを整理しました：

#### 削除したファイル：
- `Dockerfile.cloudrun`, `Dockerfile.final`, `Dockerfile.gcp`, `Dockerfile.production`, `Dockerfile.simple` → `Dockerfile`に統合
- `deploy.sh`, `deploy_cloudrun_only.sh` → 不要なデプロイスクリプト
- `x-fab-php-app/deploy-final.sh`, `x-fab-php-app/start.sh` → 不要なスクリプト

#### 残したファイル：
- `x-fab-php-app/Dockerfile` - Cloud Run用の最終的なDockerfile
- `deploy_with_existing_sql.sh` - 既存のCloud SQLインスタンスを使用するデプロイスクリプト（.gitignoreで管理）

## Cloud Run + Cloud SQLへのデプロイ

### 前提条件

- Google Cloud SDKがインストール済み
- Google Cloudプロジェクトが作成済み
- 請求先アカウントが設定済み

### デプロイ手順

#### 1. Google Cloud SDKのセットアップ

```bash
# Macの場合
brew install --cask google-cloud-sdk

# ログイン
gcloud auth login
```

#### 2. デプロイ手順

##### 既存のCloud SQLインスタンスを使用する場合

```bash
# 1. 環境変数ファイルの準備
cp .env.deploy.example .env.deploy

# 2. .env.deployを編集して実際の値を設定
#    PROJECT_ID, CLOUD_SQL_INSTANCE, CONNECTION_NAME等を設定

# 3. デプロイスクリプトの実行
./deploy_with_existing_sql.sh

# 4. プロンプトに従ってパスワードを入力
```

新規でセットアップする場合は、[DEPLOY_GUIDE.md](DEPLOY_GUIDE.md)を参照してください。

#### 3. データベーステーブルの作成

スクリプトの指示に従い、別のターミナルで：

```bash
# Cloud SQL Proxyのダウンロード（初回のみ）
curl -o cloud_sql_proxy https://dl.google.com/cloudsql/cloud_sql_proxy.darwin.amd64
chmod +x cloud_sql_proxy

# Proxyの起動（CONNECTION_NAMEはスクリプトが表示）
./cloud_sql_proxy -instances=CONNECTION_NAME=tcp:3307

# 別のターミナルでSQLを実行
mysql -h 127.0.0.1 -P 3307 -u x_fab_user -p x_fab_db < x-fab-php-app/setup.sql
```

#### 4. Chrome拡張機能の更新

1. デプロイ完了後に表示されるCloud RunのURLをコピー
2. `x-fab-chrome-extension/content.js`のAPI_URLを更新：
   ```javascript
   const API_URL = 'https://YOUR-CLOUD-RUN-URL/api/save.php';
   ```
3. Chrome拡張機能を再読み込み

### デプロイ後の確認

- アプリケーション: `https://YOUR-CLOUD-RUN-URL`
- フィード一覧: `https://YOUR-CLOUD-RUN-URL/feeds`
- APIデバッグ: `https://YOUR-CLOUD-RUN-URL/api/debug.php`

### 料金の目安

- Cloud SQL (db-f1-micro): 約$15/月
- Cloud Run: リクエスト数に応じて（無料枠あり）

### リソースの削除

使用を終了する場合：

```bash
# Cloud Runサービスの削除
gcloud run services delete x-fab-php-app --region=asia-northeast1

# Cloud SQLインスタンスの削除
gcloud sql instances delete x-fab-db
```

詳細は[DEPLOY_GUIDE.md](DEPLOY_GUIDE.md)を参照してください。

## 今後の開発予定

- ユーザー認証機能
- エクスポート機能
- 統計情報表示