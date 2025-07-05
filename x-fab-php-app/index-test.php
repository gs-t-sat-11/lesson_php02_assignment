<?php
// 最小限のテストページ（ルートとして使用）
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>X Feed Saver - Test</title>
</head>
<body>
    <h1>X Feed Saver - Cloud Run Test</h1>
    <p>Status: Working!</p>
    <p>Port: <?php echo $_SERVER['SERVER_PORT'] ?? 'unknown'; ?></p>
    <p>PHP Version: <?php echo phpversion(); ?></p>
    <p>Time: <?php echo date('Y-m-d H:i:s'); ?></p>
    
    <h2>Environment Check</h2>
    <ul>
        <li>PDO MySQL: <?php echo extension_loaded('pdo_mysql') ? '✓ Loaded' : '✗ Not loaded'; ?></li>
        <li>DB_HOST: <?php echo getenv('DB_HOST') ? '✓ Set' : '✗ Not set'; ?></li>
        <li>DB_NAME: <?php echo getenv('DB_NAME') ?: 'Not set'; ?></li>
        <li>ENV: <?php echo getenv('ENV') ?: 'Not set'; ?></li>
    </ul>
    
    <p><a href="/feeds">Go to Feeds</a> | <a href="/api/health.php">Health Check</a></p>
</body>
</html>