<?php
// 最小限のテストページ
header('Content-Type: text/plain');
echo "PHP is working!\n";
echo "Port: " . ($_SERVER['SERVER_PORT'] ?? 'unknown') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n";

// PDO MySQL拡張機能の確認
echo "\nPDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'Not loaded') . "\n";

// 環境変数の確認
echo "\nEnvironment Variables:\n";
echo "ENV: " . (getenv('ENV') ?: 'not set') . "\n";
echo "DB_HOST: " . (getenv('DB_HOST') ? 'set' : 'not set') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'not set') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'not set') . "\n";