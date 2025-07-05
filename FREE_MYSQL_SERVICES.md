# 無料で使えるMySQLサービス

Cloud SQLが使用できない場合、以下の無料MySQLサービスを利用できます。

## 1. PlanetScale (推奨)

**特徴:**
- 無料プラン: 5GB ストレージ、10億行読み取り/月
- 自動スケーリング
- 東京リージョンあり
- SSL接続標準

**セットアップ:**
1. https://planetscale.com にアクセス
2. アカウント作成
3. データベース作成（東京リージョン選択）
4. 接続情報を取得

**接続例:**
```
Host: xxx.ap-northeast-1.psdb.cloud
Port: 3306
Database: x_fab_db
Username: xxx
Password: pscale_pw_xxx
SSL: 必須
```

## 2. Aiven

**特徴:**
- 無料トライアル: $300クレジット
- 東京リージョンあり
- 高性能

**セットアップ:**
1. https://aiven.io にアクセス
2. アカウント作成
3. MySQLサービス作成
4. 接続情報を取得

## 3. Railway

**特徴:**
- 無料プラン: $5クレジット/月
- 簡単セットアップ
- 自動バックアップ

**セットアップ:**
1. https://railway.app にアクセス
2. GitHubでログイン
3. New Project → Database → MySQL
4. 接続情報を取得

## 4. Neon (PostgreSQL)

MySQLではありませんが、無料で使いやすい：

**特徴:**
- 無料プラン: 3GB
- 自動スケーリング
- サーバーレス

**注意:** PostgreSQLなので、PDOのDSNを変更する必要があります。

## セットアップ手順

### 1. PlanetScaleの場合

```bash
# PlanetScale CLIのインストール（オプション）
brew install planetscale/tap/pscale

# データベース作成後、Webコンソールでsetup.sqlを実行
# または、CLIで接続
pscale shell x_fab_db main
```

### 2. 接続情報の設定

選んだサービスの接続情報を使う場合、環境変数として設定するか、デプロイ時に直接指定します。

外部MySQLサービスを使用する場合は、Cloud Runの環境変数に接続情報を設定：

```bash
gcloud run services update x-fab-php-app \
  --region=asia-northeast1 \
  --update-env-vars="DB_HOST=xxx.ap-northeast-1.psdb.cloud,DB_PORT=3306,DB_NAME=x_fab_db,DB_USER=your_username,DB_PASS=your_password,ENV=production"
```

## SSL接続の対応

PlanetScale等はSSL接続が必須です。`includes/db.php`を更新：

```php
$this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    PDO::MYSQL_ATTR_SSL_CA => true,  // SSL有効化
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
]);
```

## トラブルシューティング

### 接続エラー

1. ファイアウォール設定を確認
2. SSL接続が必要か確認
3. 接続文字列のフォーマットを確認

### パフォーマンス

- 無料プランは制限があるため、本番環境では有料プランを検討
- キャッシュの活用を検討