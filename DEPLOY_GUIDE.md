# Cloud Run & Cloud SQL デプロイガイド

## 前提条件

1. Google Cloud SDKがインストール済み
2. Google Cloudプロジェクトが作成済み
3. 請求先アカウントが設定済み

## デプロイ手順

### 1. Google Cloud SDKのインストール（未インストールの場合）

```bash
# Homebrewを使用
brew install --cask google-cloud-sdk

# または公式サイトからダウンロード
# https://cloud.google.com/sdk/docs/install
```

### 2. 認証

```bash
gcloud auth login
```

### 3. デプロイ手順

#### 新規デプロイの場合

新規のCloud SQLインスタンスを作成する場合は、適切なスクリプトを作成するか、手動でセットアップしてください。

#### 既存のCloud SQLインスタンスを使用する場合

```bash
# 1. 環境変数ファイルの準備
cp .env.deploy.example .env.deploy

# 2. .env.deployを編集して実際の値を設定
# PROJECT_ID, CLOUD_SQL_INSTANCE, CONNECTION_NAME等

# 3. x-fab-php-appディレクトリに移動
cd x-fab-php-app

# 4. デプロイスクリプトの実行
../deploy_with_existing_sql.sh
```

スクリプトが以下を自動的に実行します：
- 必要なAPIの有効化
- データベースとユーザーの作成
- Cloud Buildを使用したイメージのビルド
- Cloud Runへのデプロイ

### 4. データベーステーブルの作成

`deploy_with_existing_sql.sh`を使用する場合、スクリプトがCloud SQL Proxyのダウンロードと起動を自動的に行います。

手動で実行する場合：

```bash
# setup.sqlの実行
mysql -h 127.0.0.1 -P 3307 -u x_fab_user -p x_fab_db < x-fab-php-app/setup.sql
```

### 5. Chrome拡張機能の更新

本番環境用のURLは既に設定済みの場合があります：
- **現在の本番環境URL**: `https://x-fab-php-app-riicjqaxya-an.a.run.app`

新しいデプロイを行った場合：
1. `x-fab-chrome-extension/content.js`を開く
2. API_URLを更新：
   ```javascript
   const API_URL = 'https://YOUR_CLOUD_RUN_URL/api/save.php';
   ```
3. Chrome拡張機能を再読み込み

## 環境変数の設定

### Cloud Run環境変数

デプロイ後に環境変数を更新する場合：

```bash
gcloud run services update x-fab-php-app \
  --region=asia-northeast1 \
  --update-env-vars="DB_HOST=/cloudsql/CONNECTION_NAME,DB_NAME=x_fab_db,DB_USER=x_fab_user,DB_PASS=YOUR_PASSWORD,ENV=production"
```

### ローカル開発環境への切り替え

`.env`ファイルを使用してローカル環境に戻す：

```bash
cd x-fab-php-app
cp .env.example .env
# .envファイルを編集
```

Chrome拡張機能のAPI URLもローカルに戻す：
```javascript
const API_URL = 'http://localhost/x-fab-php-app/api/save.php';
```

## トラブルシューティング

### Cloud SQL接続エラー

1. Cloud SQL Admin APIが有効か確認
2. Cloud Runサービスアカウントに適切な権限があるか確認
3. 接続名が正しいか確認

### デプロイエラー

1. プロジェクトIDが正しいか確認
2. 請求先アカウントが設定されているか確認
3. APIが有効になっているか確認

### パフォーマンスの最適化

必要に応じて以下を調整：

```bash
# インスタンスサイズの変更
gcloud sql instances patch x-fab-db --tier=db-g1-small

# Cloud Runのメモリ増加
gcloud run services update x-fab-php-app --memory=1Gi
```

## セキュリティの強化

1. **Cloud SQL**
   - プライベートIPの使用を検討
   - 定期的なバックアップの設定

2. **Cloud Run**
   - 認証を有効にする場合は`--no-allow-unauthenticated`を使用
   - Cloud Armorでの保護を検討

3. **シークレット管理**
   - Secret Managerの使用を検討

## 料金の目安

- Cloud SQL (db-f1-micro): 約$15/月
- Cloud Run: リクエスト数に応じて（無料枠あり）
- ストレージ: 使用量に応じて

## 削除方法

リソースを削除する場合：

```bash
# Cloud Runサービスの削除
gcloud run services delete x-fab-php-app --region=asia-northeast1

# Cloud SQLインスタンスの削除
gcloud sql instances delete x-fab-db

# Container Registryのイメージ削除
gcloud container images delete gcr.io/PROJECT_ID/x-fab-php-app
```