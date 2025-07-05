<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
    <title>X Feed Viewer - 保存したフィード一覧</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1>X Feed Viewer</h1>
                <div class="controls">
                    <form id="search-form" class="search-box">
                        <input 
                            type="text" 
                            id="search-input" 
                            name="q" 
                            placeholder="検索"
                            autocomplete="off"
                        >
                    </form>
                    <select id="sort-select" class="sort-select">
                        <option value="desc">新着順</option>
                        <option value="asc">古い順</option>
                    </select>
                    <button type="submit" form="search-form" class="search-button">検索</button>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div id="feeds-container" class="feeds-container">
                <div class="loading">読み込み中...</div>
            </div>
            <div id="pagination-container"></div>
        </div>
    </main>

    <script src="/assets/js/app.js"></script>
</body>
</html>