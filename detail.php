<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iPhone風写真一覧</title>
    <style>
        /* 共通のスタイル */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f4f4;
            color: #000000;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center; /* 画面の中央に配置 */
            align-items: flex-start; /* 上に揃える */
            height: 100vh; /* 高さを100vhに */
        }

        /* サイドバー */
        .sidebar {
            width: 200px;
            background-color: #ffffff;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding-top: 20px;
            text-align: center;
        }

        .menu-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #000000;
        }

        .menu-item {
            margin: 20px 0;
            font-size: 18px;
            color: #000000;
        }

        .menu-item a {
            text-decoration: none;
            color: inherit;
        }

        .menu-item:hover {
            color: #007bff;
        }

        /* メインエリア */
        .main-area {
            margin-left: 220px; /* サイドバーの幅分だけ左にずらす */
            margin-top: 30px; /* ここで上下の位置を調整 */
            padding: 20px;
            max-width: 800px; /* 最大幅を設定して中央揃え */
            width: 100%; /* 100%に設定して制限内で幅を調整 */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center; /* 中央に揃える */
        }

        .title-text, .memo-text {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .photo-container {
            width: 100%;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .photo-placeholder {
            width: 100%;
            position: relative;
            padding-top: 50%; /* 4:3 の比率 */
            background-color: #ddd;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .photo-placeholder::after {
            content: "";
            display: block;
            padding-top: 0;
        }

        /* 詳細情報のスタイル */
        .photo-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 2列に設定 */
            gap: 10px; /* 列間の余白 */
            width: 100%;
        }

        .photo-details span {
            font-size: 14px;
            color: #555;
        }

        /* 絞り込みメニュー */
        #filter-toggle {
            display: none;
        }

        /* 絞り込みメニュー（ポップアップ） */
        .filter-modal {
            display: block;
            position: absolute;
            top: 340px; /* 下に少し移動 */
            left: 200px;
            width: 200px;
            background-color: #ffffff;
            padding: 10px;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease, pointer-events 0s linear 0.3s;
            border-radius: 12px;
        }

        #filter-toggle:checked + .filter-modal {
            opacity: 1;
            pointer-events: auto;
            transition: opacity 0.3s ease;
        }

        .filter-item {
            margin: 5px 0;
            font-size: 14px;
            color: #000000;
        }

        .filter-item:hover {
            color: #007bff;
            cursor: pointer;
        }

        /* ←ボタン */
        .back-btn {
            position: fixed;
            top: 30px; /* 下に少し移動 */
            left: 10px;
            font-size: 24px;
            color: #007bff;
            text-decoration: none;
        }

        .back-btn:hover {
            color: #0056b3;
        }

        /* ボタンのスタイル */
        .photo-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px; /* メモの下に余白を作る */
        }

        .photo-button1 {
            padding: 8px 12px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .photo-button1:hover {
            background-color: #0056b3;
        }

        .photo-button2 {
            padding: 8px 12px;
            background-color: #ff0000;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .photo-button2:hover {
            background-color: #ff0000;
        }
    </style>
</head>
<body>
    <!-- サイドバー -->
    <div class="sidebar">
        <div class="menu-title">photos</div>
        <div class="menu-item"><a href="photos-page.html">写真</a></div>
        <div class="menu-item"><a href="albums-page.html">アルバム</a></div>
        <div class="menu-item"><a href="tags-page.html">タグ作成</a></div>
        <div class="menu-item"><a href="sort-page.html">並べ替え</a></div>
        <!-- 絞り込みメニュー -->
        <label for="filter-toggle" class="menu-item">絞り込み</label>

        <!-- 絞り込みポップアップ -->
        <input type="checkbox" id="filter-toggle">
        <div class="filter-modal">
            <a href="narrowed.php" class="filter-item">絞り込み1</a>
            <div class="filter-item">絞り込み2</div>
            <div class="filter-item">絞り込み3</div>
        </div>
        <div class="menu-item"><a href="account-page.html">アカウント詳細</a></div>
    </div>

    <!-- メインエリア -->
    <main class="main-area">
        <div class="photo-container">
            <div class="photo-placeholder">写真エリア</div>
            <div class="photo-details">
                <span>Canon ESO Kiss X10i</span>
                <span>55mm f/8.0 1/4s ISO5000</span>
                <span>EF-S55-250mm f/4-5.6 IS STM</span>
                <span>20:27:50 2024.12.25</span>
            </div>
        </div>
        <div class="title-text">写真のタイトル</div>
        <div class="memo-text">メモの内容をここに表示</div>
        
        <!-- メモの下にボタンを配置 -->
        <div class="photo-buttons">
            <button class="photo-button1">編集</button>
            <button class="photo-button2">削除</button>
        </div>
    </main>

    <!-- ログイン画面に戻るボタン -->
    <a href="login-page.html" class="back-btn">&larr;</a>
</body>
</html>
