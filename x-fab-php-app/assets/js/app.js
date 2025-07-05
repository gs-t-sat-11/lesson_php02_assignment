// フィード閲覧ページのJavaScript

let currentPage = 1;
let totalPages = 1;
let currentSearch = '';
let currentSort = 'desc';

/**
 * ページ読み込み時の初期化
 */
document.addEventListener('DOMContentLoaded', () => {
    // イベントリスナーの設定
    document.getElementById('search-form').addEventListener('submit', handleSearch);
    document.getElementById('sort-select').addEventListener('change', handleSortChange);
    
    // URLパラメータから初期値を設定
    const urlParams = new URLSearchParams(window.location.search);
    const page = parseInt(urlParams.get('page')) || 1;
    const search = urlParams.get('q') || '';
    const sort = urlParams.get('sort') || 'desc';
    
    currentPage = page;
    currentSearch = search;
    currentSort = sort;
    
    document.getElementById('search-input').value = search;
    document.getElementById('sort-select').value = sort;
    
    // 初回データ読み込み
    loadFeeds();
});

/**
 * 検索フォームの処理
 */
function handleSearch(e) {
    e.preventDefault();
    currentSearch = document.getElementById('search-input').value;
    currentPage = 1;
    updateURL();
    loadFeeds();
}

/**
 * ソート変更の処理
 */
function handleSortChange() {
    currentSort = document.getElementById('sort-select').value;
    currentPage = 1;
    updateURL();
    loadFeeds();
}

/**
 * URLを更新
 */
function updateURL() {
    const params = new URLSearchParams();
    if (currentPage > 1) params.set('page', currentPage);
    if (currentSearch) params.set('q', currentSearch);
    if (currentSort !== 'desc') params.set('sort', currentSort);
    
    const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.history.pushState({}, '', url);
}

/**
 * フィードデータを読み込み
 */
async function loadFeeds() {
    const container = document.getElementById('feeds-container');
    container.innerHTML = '<div class="loading">読み込み中...</div>';
    
    try {
        const params = new URLSearchParams({
            page: currentPage,
            q: currentSearch,
            sort: currentSort
        });
        
        const response = await fetch(`/api/list.php?${params}`);
        const data = await response.json();
        
        if (data.success) {
            displayFeeds(data.result.feeds);
            updatePagination(data.result.pagination);
        } else {
            throw new Error(data.result.message || 'データの取得に失敗しました');
        }
    } catch (error) {
        container.innerHTML = `<div class="error-message">エラー: ${error.message}</div>`;
    }
}

/**
 * フィードを表示
 */
function displayFeeds(feeds) {
    const container = document.getElementById('feeds-container');
    
    if (feeds.length === 0) {
        if (currentSearch) {
            container.innerHTML = `
                <div class="empty-state">
                    <h2>検索結果が見つかりませんでした</h2>
                    <p>「${escapeHtml(currentSearch)}」に一致するフィードはありません</p>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <h2>フィードがありません</h2>
                    <p>Chrome拡張機能を使ってフィードを保存してください</p>
                </div>
            `;
        }
        return;
    }
    
    container.innerHTML = feeds.map(feed => createFeedCard(feed)).join('');
}

/**
 * フィードカードのHTMLを作成
 */
function createFeedCard(feed) {
    const postDate = formatDateTime(feed.post_date || feed.created_at);
    const feedData = feed.feed_data || {};
    
    let mediaHtml = '';
    if (feedData.images && feedData.images.length > 0) {
        mediaHtml += '<div class="media-container">';
        feedData.images.forEach(imageUrl => {
            mediaHtml += `
                <div class="media-item">
                    <img src="${escapeHtml(imageUrl)}" alt="画像" loading="lazy">
                </div>
            `;
        });
        mediaHtml += '</div>';
    }
    
    if (feedData.videos && feedData.videos.length > 0) {
        mediaHtml += '<div class="media-container">';
        feedData.videos.forEach(videoUrl => {
            mediaHtml += `
                <div class="media-item">
                    <video controls>
                        <source src="${escapeHtml(videoUrl)}" type="video/mp4">
                        お使いのブラウザは動画タグをサポートしていません。
                    </video>
                </div>
            `;
        });
        mediaHtml += '</div>';
    }
    
    let quotedHtml = '';
    if (feedData.quoted_tweet) {
        quotedHtml = `
            <div class="quoted-tweet">
                <div class="quoted-user">${escapeHtml(feedData.quoted_tweet.user_name || '')}</div>
                <div class="quoted-content">${formatPostContent(feedData.quoted_tweet.content || '')}</div>
            </div>
        `;
    }
    
    const userIconHtml = feed.user_icon_url 
        ? `<img src="${escapeHtml(feed.user_icon_url)}" alt="${escapeHtml(feed.user_name)}" class="user-icon">`
        : `<div class="user-icon" style="background-color: #e1e8ed;"></div>`;
    
    return `
        <div class="feed-card" data-feed-id="${feed.id}">
            <div class="feed-header">
                ${userIconHtml}
                <div class="user-info">
                    <a href="${escapeHtml(feed.feed_url)}" target="_blank" class="user-name">${escapeHtml(feed.user_name)}</a>
                    <div class="post-date">${postDate}</div>
                </div>
                <div class="feed-actions">
                    <a href="${escapeHtml(feed.feed_url)}" target="_blank" class="feed-link">表示</a>
                    <button class="delete-button" onclick="deleteFeed(${feed.id})">削除</button>
                </div>
            </div>
            <div class="post-content">${formatPostContent(feed.post_content)}</div>
            ${mediaHtml}
            ${quotedHtml}
        </div>
    `;
}

/**
 * ページネーションを更新
 */
function updatePagination(pagination) {
    totalPages = pagination.total_pages;
    const container = document.getElementById('pagination-container');
    
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    
    // 前へボタン
    html += `<button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>< 前へ</button>`;
    
    // ページ番号
    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);
    
    if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }
    
    if (startPage > 1) {
        html += '<button onclick="goToPage(1)">1</button>';
        if (startPage > 2) html += '<span>...</span>';
    }
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            html += `<span class="current-page">${i}</span>`;
        } else {
            html += `<button onclick="goToPage(${i})">${i}</button>`;
        }
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span>...</span>';
        html += `<button onclick="goToPage(${totalPages})">${totalPages}</button>`;
    }
    
    // 次へボタン
    html += `<button onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>次へ ></button>`;
    
    html += '</div>';
    container.innerHTML = html;
}

/**
 * ページ移動
 */
function goToPage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    updateURL();
    loadFeeds();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * フィード削除
 */
async function deleteFeed(id) {
    if (!confirm('このフィードを削除しますか？')) {
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const response = await fetch('/api/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                csrf_token: csrfToken
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // カードをフェードアウトして削除
            const card = document.querySelector(`[data-feed-id="${id}"]`);
            if (card) {
                card.style.transition = 'opacity 0.3s';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    // 残りのカードがない場合は再読み込み
                    if (document.querySelectorAll('.feed-card').length === 0) {
                        loadFeeds();
                    }
                }, 300);
            }
        } else {
            alert('削除に失敗しました: ' + data.result.message);
        }
    } catch (error) {
        alert('エラーが発生しました: ' + error.message);
    }
}

/**
 * HTMLエスケープ
 */
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * 投稿内容のフォーマット
 */
function formatPostContent(content) {
    let formatted = escapeHtml(content);
    
    // ハッシュタグをリンクに変換
    formatted = formatted.replace(/#(\w+)/g, '<a href="https://x.com/hashtag/$1" target="_blank" rel="noopener">#$1</a>');
    
    // メンションをリンクに変換
    formatted = formatted.replace(/@(\w+)/g, '<a href="https://x.com/$1" target="_blank" rel="noopener">@$1</a>');
    
    return formatted;
}

/**
 * 日時フォーマット
 */
function formatDateTime(datetime) {
    if (!datetime) return '';
    const date = new Date(datetime);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${year}/${month}/${day} ${hours}:${minutes}:${seconds}`;
}