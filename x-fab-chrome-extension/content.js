// X Feed Saver - Content Script

// APIのURL（本番環境では適切なURLに変更）
const API_URL = 'https://x-fab-php-app-riicjqaxya-an.a.run.app/api/save.php';

// 保存ボタンのスタイル
const buttonStyles = `
  margin-left: 8px;
  padding: 4px 8px;
  background-color: #1d9bf0;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
  z-index: 10;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
`;

// 既に処理済みのツイートを追跡
const processedTweets = new Set();

/**
 * フィード要素から情報を抽出
 */
function extractFeedData(article) {
  try {
    // ユーザー情報の取得
    const userLink = article.querySelector('a[href*="/status/"]');
    const feedUrl = userLink ? `https://x.com${userLink.getAttribute('href')}` : '';
    
    // ユーザー名の取得（複数の可能性のあるセレクタを試す）
    let userName = '';
    const userNameElement = article.querySelector('[data-testid="User-Name"] span') || 
                           article.querySelector('div[dir="ltr"] > span') ||
                           article.querySelector('a[role="link"] span');
    if (userNameElement) {
      userName = userNameElement.textContent.trim();
    }
    
    // ユーザーアイコンの取得
    let userIconUrl = '';
    const avatarImg = article.querySelector('img[src*="profile_images"]');
    if (avatarImg) {
      userIconUrl = avatarImg.src;
    }
    
    // 投稿内容の取得
    let postContent = '';
    const tweetTextElement = article.querySelector('[data-testid="tweetText"]') ||
                            article.querySelector('[lang]');
    if (tweetTextElement) {
      postContent = tweetTextElement.textContent.trim();
    }
    
    // 投稿日時の取得
    let postDate = '';
    const timeElement = article.querySelector('time');
    if (timeElement) {
      postDate = timeElement.getAttribute('datetime');
    }
    
    // 画像URLの取得
    const images = [];
    const imageElements = article.querySelectorAll('img[src*="media"]');
    imageElements.forEach(img => {
      const src = img.src;
      if (src && !images.includes(src)) {
        images.push(src);
      }
    });
    
    // 動画URLの取得（動画のサムネイルから推測）
    const videos = [];
    const videoElements = article.querySelectorAll('video');
    videoElements.forEach(video => {
      const src = video.src || video.querySelector('source')?.src;
      if (src && !videos.includes(src)) {
        videos.push(src);
      }
    });
    
    // 引用ツイートの取得
    let quotedTweet = null;
    const quotedElement = article.querySelector('[data-testid="quoteTweet"]') ||
                         article.querySelector('div[role="blockquote"]');
    if (quotedElement) {
      const quotedUserElement = quotedElement.querySelector('span');
      const quotedTextElement = quotedElement.querySelector('[lang]');
      const quotedLinkElement = quotedElement.querySelector('a[href*="/status/"]');
      
      if (quotedUserElement && quotedTextElement) {
        quotedTweet = {
          url: quotedLinkElement ? `https://x.com${quotedLinkElement.getAttribute('href')}` : '',
          user_name: quotedUserElement.textContent.trim(),
          content: quotedTextElement.textContent.trim()
        };
      }
    }
    
    return {
      feed_url: feedUrl,
      user_name: userName,
      user_icon_url: userIconUrl,
      post_content: postContent,
      post_date: postDate,
      images: images,
      videos: videos,
      quoted_tweet: quotedTweet
    };
  } catch (error) {
    console.error('データ抽出エラー:', error);
    return null;
  }
}

/**
 * 保存ボタンをクリックした時の処理
 */
async function saveFeed(button, feedData) {
  // ボタンの状態を変更
  button.textContent = '送信中...';
  button.disabled = true;
  button.style.backgroundColor = '#8b98a5';
  
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(feedData)
    });
    
    const result = await response.json();
    
    if (result.success) {
      button.textContent = '送信完了';
      button.style.backgroundColor = '#1d9b01';
      
      // 3秒後に元に戻す
      setTimeout(() => {
        button.textContent = '保存';
        button.style.backgroundColor = '#1d9bf0';
        button.disabled = false;
      }, 3000);
    } else {
      throw new Error(result.result.message || '保存に失敗しました');
    }
  } catch (error) {
    button.textContent = '送信失敗';
    button.style.backgroundColor = '#e0245e';
    console.error('保存エラー:', error);
    
    // 3秒後に元に戻す
    setTimeout(() => {
      button.textContent = '保存';
      button.style.backgroundColor = '#1d9bf0';
      button.disabled = false;
    }, 3000);
  }
}

/**
 * 保存ボタンを追加
 */
function addSaveButton(article) {
  // 既にボタンが追加されているかチェック
  if (article.querySelector('.x-feed-save-button')) {
    return;
  }
  
  // アクションバーを探す（いいね、リツイートボタンがある場所）
  const actionBar = article.querySelector('[role="group"]');
  if (!actionBar) {
    // アクションバーが見つからない場合は、時間情報の隣に追加
    const timeElement = article.querySelector('time');
    if (timeElement && timeElement.parentElement) {
      const button = createSaveButton(article);
      timeElement.parentElement.appendChild(button);
    }
    return;
  }
  
  // ボタンを作成してアクションバーに追加
  const button = createSaveButton(article);
  actionBar.appendChild(button);
}

/**
 * 保存ボタンを作成
 */
function createSaveButton(article) {
  const button = document.createElement('button');
  button.className = 'x-feed-save-button';
  button.textContent = '保存';
  button.style.cssText = buttonStyles;
  
  // クリックイベントを追加
  button.addEventListener('click', async (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    const feedData = extractFeedData(article);
    if (feedData && feedData.feed_url && feedData.user_name && feedData.post_content) {
      await saveFeed(button, feedData);
    } else {
      alert('フィード情報の取得に失敗しました');
    }
  });
  
  return button;
}

/**
 * ページの監視と処理
 */
function observePage() {
  // 定期的に全てのツイートをチェック
  const checkAndAddButtons = () => {
    // より幅広いセレクタでツイートを探す
    const articles = document.querySelectorAll('article');
    articles.forEach(article => {
      // ツイートの特徴を持つarticleのみ処理
      const hasTime = article.querySelector('time');
      const hasText = article.querySelector('[data-testid="tweetText"], [lang], div[dir="auto"]');
      if (hasTime && hasText) {
        addSaveButton(article);
      }
    });
  };
  
  // 初回実行
  setTimeout(checkAndAddButtons, 1000);
  
  // 新しく追加されるツイートを監視
  const observer = new MutationObserver((mutations) => {
    // 変更があったらチェック
    checkAndAddButtons();
  });
  
  // 監視を開始
  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
  
  // スクロールイベントでもチェック
  let scrollTimeout;
  window.addEventListener('scroll', () => {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(checkAndAddButtons, 500);
  });
}

// ページ読み込み後に実行
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', observePage);
} else {
  observePage();
}

// URLが変更された時（SPAのナビゲーション）にも再実行
let lastUrl = location.href;
new MutationObserver(() => {
  const url = location.href;
  if (url !== lastUrl) {
    lastUrl = url;
    processedTweets.clear();
    setTimeout(observePage, 1000);
  }
}).observe(document, {subtree: true, childList: true});