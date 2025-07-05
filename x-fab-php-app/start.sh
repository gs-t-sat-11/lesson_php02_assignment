#!/bin/sh
# Cloud Run用のスタートスクリプト

# PORT環境変数が設定されていない場合はデフォルト8080
if [ -z "$PORT" ]; then
    export PORT=8080
fi

echo "Starting Apache on port $PORT..."

# Apache設定を動的に更新
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/000-default.conf

# Apacheを起動
exec apache2-foreground