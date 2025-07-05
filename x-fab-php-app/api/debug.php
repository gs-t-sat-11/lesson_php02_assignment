<?php
session_start();
require_once dirname(__DIR__) . '/includes/functions.php';
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API デバッグページ</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 100px;
            font-family: monospace;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .response {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
            white-space: pre-wrap;
            font-family: monospace;
            overflow-x: auto;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            background-color: #e9ecef;
            border: none;
            border-radius: 4px 4px 0 0;
            cursor: pointer;
        }
        .tab.active {
            background-color: white;
            border-bottom: 2px solid white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>API デバッグページ</h1>
        
        <div class="tabs">
            <button class="tab active" onclick="showTab('save')">保存API</button>
            <button class="tab" onclick="showTab('list')">一覧API</button>
            <button class="tab" onclick="showTab('delete')">削除API</button>
        </div>
        
        <!-- 保存API -->
        <div id="save-tab" class="tab-content active">
            <div class="section">
                <h2>フィード保存API</h2>
                <form id="save-form">
                    <div class="form-group">
                        <label>フィードURL *</label>
                        <input type="text" name="feed_url" value="https://x.com/user/status/123456789" required>
                    </div>
                    <div class="form-group">
                        <label>ユーザー名 *</label>
                        <input type="text" name="user_name" value="テストユーザー" required>
                    </div>
                    <div class="form-group">
                        <label>ユーザーアイコンURL</label>
                        <input type="text" name="user_icon_url" value="https://example.com/icon.jpg">
                    </div>
                    <div class="form-group">
                        <label>投稿内容 *</label>
                        <textarea name="post_content" required>これはテスト投稿です。#テスト @mention</textarea>
                    </div>
                    <div class="form-group">
                        <label>投稿日時</label>
                        <input type="text" name="post_date" value="<?php echo date('Y-m-d H:i:s'); ?>">
                    </div>
                    <div class="form-group">
                        <label>画像URL（JSON配列）</label>
                        <textarea name="images">["https://example.com/image1.jpg", "https://example.com/image2.jpg"]</textarea>
                    </div>
                    <div class="form-group">
                        <label>動画URL（JSON配列）</label>
                        <textarea name="videos">["https://example.com/video1.mp4"]</textarea>
                    </div>
                    <div class="form-group">
                        <label>引用ツイート（JSON）</label>
                        <textarea name="quoted_tweet">{
  "url": "https://x.com/quoted/status/987654321",
  "user_name": "引用元ユーザー",
  "content": "引用元の内容"
}</textarea>
                    </div>
                    <button type="submit">送信</button>
                </form>
                <div id="save-response" class="response" style="display:none;"></div>
            </div>
        </div>
        
        <!-- 一覧API -->
        <div id="list-tab" class="tab-content">
            <div class="section">
                <h2>フィード一覧API</h2>
                <form id="list-form">
                    <div class="form-group">
                        <label>ページ番号</label>
                        <input type="number" name="page" value="1" min="1">
                    </div>
                    <div class="form-group">
                        <label>検索キーワード</label>
                        <input type="text" name="q" placeholder="ポスト内容またはユーザー名で検索">
                    </div>
                    <div class="form-group">
                        <label>ソート順</label>
                        <select name="sort">
                            <option value="desc">新着順</option>
                            <option value="asc">古い順</option>
                        </select>
                    </div>
                    <button type="submit">送信</button>
                </form>
                <div id="list-response" class="response" style="display:none;"></div>
            </div>
        </div>
        
        <!-- 削除API -->
        <div id="delete-tab" class="tab-content">
            <div class="section">
                <h2>フィード削除API</h2>
                <form id="delete-form">
                    <div class="form-group">
                        <label>フィードID *</label>
                        <input type="number" name="id" required>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <button type="submit">削除</button>
                </form>
                <div id="delete-response" class="response" style="display:none;"></div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // タブの切り替え
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            event.target.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        }
        
        // 保存APIフォーム
        document.getElementById('save-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = {
                feed_url: formData.get('feed_url'),
                user_name: formData.get('user_name'),
                user_icon_url: formData.get('user_icon_url'),
                post_content: formData.get('post_content'),
                post_date: formData.get('post_date')
            };
            
            try {
                data.images = JSON.parse(formData.get('images') || '[]');
                data.videos = JSON.parse(formData.get('videos') || '[]');
                data.quoted_tweet = JSON.parse(formData.get('quoted_tweet') || 'null');
            } catch (err) {
                alert('JSONの形式が正しくありません');
                return;
            }
            
            const response = await fetch('/api/save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            const responseDiv = document.getElementById('save-response');
            responseDiv.textContent = JSON.stringify(result, null, 2);
            responseDiv.className = 'response ' + (result.success ? 'success' : 'error');
            responseDiv.style.display = 'block';
        });
        
        // 一覧APIフォーム
        document.getElementById('list-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const params = new URLSearchParams({
                page: formData.get('page'),
                q: formData.get('q'),
                sort: formData.get('sort')
            });
            
            const response = await fetch('/api/list.php?' + params);
            const result = await response.json();
            
            const responseDiv = document.getElementById('list-response');
            responseDiv.textContent = JSON.stringify(result, null, 2);
            responseDiv.className = 'response ' + (result.success ? 'success' : 'error');
            responseDiv.style.display = 'block';
        });
        
        // 削除APIフォーム
        document.getElementById('delete-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!confirm('本当に削除しますか？')) {
                return;
            }
            
            const formData = new FormData(e.target);
            const data = {
                id: parseInt(formData.get('id')),
                csrf_token: formData.get('csrf_token')
            };
            
            const response = await fetch('/api/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            const responseDiv = document.getElementById('delete-response');
            responseDiv.textContent = JSON.stringify(result, null, 2);
            responseDiv.className = 'response ' + (result.success ? 'success' : 'error');
            responseDiv.style.display = 'block';
        });
    </script>
</body>
</html>