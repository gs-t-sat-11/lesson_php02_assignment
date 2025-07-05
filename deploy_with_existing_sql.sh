#!/bin/bash

# 既存のCloud SQLインスタンスを使用したCloud Runデプロイスクリプト

set -e

# MySQLクライアントのパスを追加
export PATH="/opt/homebrew/opt/mysql-client/bin:$PATH"

# 色の定義
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== X Feed Saver - Cloud Run デプロイ (既存Cloud SQL使用) ===${NC}"
echo ""

# 環境変数ファイルの読み込み
if [ -f .env.deploy ]; then
    echo -e "${GREEN}.env.deploy ファイルを読み込み中...${NC}"
    set -a
    source .env.deploy
    set +a
else
    echo -e "${RED}エラー: .env.deploy ファイルが見つかりません${NC}"
    echo "1. .env.deploy.example を .env.deploy にコピーしてください:"
    echo "   cp .env.deploy.example .env.deploy"
    echo "2. .env.deploy ファイルを編集して、実際の値を設定してください"
    exit 1
fi

# 必須変数のチェック
required_vars=("PROJECT_ID" "REGION" "CLOUD_SQL_INSTANCE" "CONNECTION_NAME" "DB_NAME" "DB_USER" "SERVICE_NAME")
for var in "${required_vars[@]}"; do
    if [ -z "${!var}" ]; then
        echo -e "${RED}エラー: $var が設定されていません${NC}"
        echo ".env.deploy ファイルを確認してください"
        exit 1
    fi
done

echo -e "${YELLOW}プロジェクト: ${PROJECT_ID}${NC}"
echo -e "${YELLOW}Cloud SQLインスタンス: ${CLOUD_SQL_INSTANCE}${NC}"
echo -e "${YELLOW}リージョン: ${REGION}${NC}"
echo ""

# gcloudの設定
echo -e "${BLUE}1. gcloud設定${NC}"
gcloud config set project $PROJECT_ID

# 必要なAPIの有効化
echo -e "${BLUE}2. 必要なAPIを有効化${NC}"
gcloud services enable \
    run.googleapis.com \
    cloudbuild.googleapis.com \
    containerregistry.googleapis.com

# データベースの作成
echo -e "${BLUE}3. データベースの作成${NC}"
echo "Cloud SQL Proxyを使用してデータベースを作成します..."

# Cloud SQL Proxyのダウンロード（未インストールの場合）
if [ ! -f ./cloud_sql_proxy ]; then
    echo "Cloud SQL Proxyをダウンロード中..."
    curl -o cloud_sql_proxy https://dl.google.com/cloudsql/cloud_sql_proxy.darwin.amd64
    chmod +x cloud_sql_proxy
fi

# Cloud SQL Proxyを起動（バックグラウンド）
echo "Cloud SQL Proxyを起動中..."
./cloud_sql_proxy -instances=$CONNECTION_NAME=tcp:3307 &
PROXY_PID=$!
sleep 5

# データベースとユーザーの作成
echo -e "${BLUE}4. データベースとユーザーの作成${NC}"
read -s -p "新しいデータベースユーザーのパスワードを入力してください: " DB_PASSWORD
echo ""

# rootパスワードの取得
if [ -n "$CLOUD_SQL_ROOT_PASSWORD" ]; then
    ROOT_PASSWORD="$CLOUD_SQL_ROOT_PASSWORD"
else
    read -s -p "Cloud SQL rootパスワードを入力してください: " ROOT_PASSWORD
    echo ""
fi

# rootでログインしてデータベースとユーザーを作成
mysql -h 127.0.0.1 -P 3307 -u root -p$ROOT_PASSWORD << EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'%';
FLUSH PRIVILEGES;
EOF

echo -e "${GREEN}✓ データベースとユーザーを作成しました${NC}"

# テーブルの作成
echo -e "${BLUE}5. テーブルの作成${NC}"
mysql -h 127.0.0.1 -P 3307 -u $DB_USER -p$DB_PASSWORD $DB_NAME < x-fab-php-app/setup.sql

echo -e "${GREEN}✓ テーブルを作成しました${NC}"

# Cloud SQL Proxyを停止
kill $PROXY_PID 2>/dev/null || true

# .env.productionの更新
echo -e "${BLUE}6. 本番環境設定の作成${NC}"
cd x-fab-php-app
cat > .env.production << EOF
DB_HOST=/cloudsql/$CONNECTION_NAME
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASS=$DB_PASSWORD
DB_PORT=3306
ENV=production
EOF

# Cloud Buildの設定ファイルを作成
echo -e "${BLUE}7. Cloud Build設定ファイルの作成${NC}"
cat > cloudbuild-deploy.yaml << EOF
steps:
  # Dockerイメージのビルド（x86_64アーキテクチャ）
  - name: 'gcr.io/cloud-builders/docker'
    args: ['build', '-f', 'Dockerfile', '-t', 'gcr.io/$PROJECT_ID/$SERVICE_NAME', '.']
  
  # イメージをプッシュ
  - name: 'gcr.io/cloud-builders/docker'
    args: ['push', 'gcr.io/$PROJECT_ID/$SERVICE_NAME']

images:
  - 'gcr.io/$PROJECT_ID/$SERVICE_NAME'
EOF

# Cloud Buildでビルド
echo -e "${BLUE}8. Cloud Buildでイメージをビルド${NC}"
gcloud builds submit --config cloudbuild-deploy.yaml

# Cloud Runへデプロイ
echo -e "${BLUE}9. Cloud Runへデプロイ${NC}"
gcloud run deploy $SERVICE_NAME \
    --image gcr.io/$PROJECT_ID/$SERVICE_NAME \
    --region $REGION \
    --platform managed \
    --allow-unauthenticated \
    --add-cloudsql-instances $CONNECTION_NAME \
    --set-env-vars "DB_HOST=/cloudsql/$CONNECTION_NAME,DB_NAME=$DB_NAME,DB_USER=$DB_USER,DB_PASS=$DB_PASSWORD,ENV=production" \
    --port 8080 \
    --memory 1Gi \
    --timeout 300 \
    --max-instances 10

# Cloud RunサービスのURLを取得
SERVICE_URL=$(gcloud run services describe $SERVICE_NAME --region=$REGION --format="value(status.url)")

# クリーンアップ
rm -f .env.production cloudbuild-deploy.yaml
cd ..

echo ""
echo -e "${GREEN}========== デプロイ完了 ==========${NC}"
echo ""
echo -e "${GREEN}サービスURL: $SERVICE_URL${NC}"
echo ""
echo "アクセスURL:"
echo "- アプリケーション: $SERVICE_URL"
echo "- フィード一覧: $SERVICE_URL/feeds"
echo "- APIデバッグ: $SERVICE_URL/api/debug.php"
echo ""
echo -e "${YELLOW}Chrome拡張機能の更新:${NC}"
echo "x-fab-chrome-extension/content.jsのAPI_URLを以下に更新してください:"
echo "const API_URL = '$SERVICE_URL/api/save.php';"
echo ""
echo -e "${GREEN}Cloud SQL接続情報:${NC}"
echo "インスタンス: $CLOUD_SQL_INSTANCE"
echo "接続名: $CONNECTION_NAME"
echo "データベース: $DB_NAME"
echo "ユーザー: $DB_USER"
echo ""
echo -e "${YELLOW}注意事項:${NC}"
echo "- Cloud SQLの料金が発生します（Enterprise サンドボックス）"
echo "- 使用しない時はインスタンスを停止することをお勧めします"
echo ""