<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>写真詳細</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <!-- ヘッダー -->
        <header class="header">
            <h1>photos</h1>
            <h2>タイトル</h2>
        </header>

        <div class="content">
            <!-- サイドメニュー -->
            <aside class="sidebar">
                <!-- 戻るボタン -->
                <div class="back-button">
                    <a href="previous-page.html"> <!-- 戻る先のページ -->
                        &lt; 戻る
                    </a>
                </div>

                <!-- 写真メニュー -->
                <div class="menu-item">
                    <a href="photo-page.html"> <!-- 写真ページへのリンク -->
                        <img src="icon-photo.png" alt="写真アイコン">
                        <span>写真</span>
                    </a>
                </div>

                <!-- アルバムメニュー -->
                <div class="menu-item">
                    <a href="album-page.html"> <!-- アルバムページへのリンク -->
                        <img src="icon-album.png" alt="アルバムアイコン">
                        <span>アルバム</span>
                    </a>
                </div>
            </aside>

            <!-- メインエリア -->
            <main class="main-area">
                <input type="text" class="title-input" placeholder="タイトル">
                <div class="photo-container">
                    <div class="photo-placeholder">
                        <!-- 写真エリア -->
                    </div>
                    <div class="photo-details">
                        <span>Canon ESO Kiss X10i</span>
                        <span>55mm f/8.0 1/4s ISO5000</span>
                        <span>EF-S55-250mm f/4-5.6 IS STM</span>
                        <span>20:27:50 2024.12.25</span>
                    </div>
                </div>
                <input type="text" class="memo-input" placeholder="メモ">
            </main>
        </div>
    </div>
</body>
</html>
