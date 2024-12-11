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
            margin-left: 200px;
            padding: 20px;
            width: calc(100% - 200px);
            background-color: #f4f4f4;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .title-input,
        .memo-input {
            width: 100%;
            max-width: 600px;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .photo-container {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .photo-placeholder {
            width: 100%;
            height: 300px;
            background-color: #ddd;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
        }

        .photo-details span {
            display: block;
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
            top: 320px;
            left: 200px;
            width: 200px;
            background-color: #ffffff;  /* ライトテーマに合わせた背景色 */
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
        color: #000000;  /* ライトテーマ時の文字色 */
        border-bottom: none;  /* 下線を消す */
        }

        

        /* 絞り込み詳細にカーソルを合わせたときに青色 */
        .filter-item:hover {
            color: #007bff;  /* 絞り込み詳細のカーソルホバー時の色 */
            cursor: pointer;
        }

        /* ←ボタン */
        .back-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            font-size: 24px;
            color: #007bff;
            text-decoration: none;
        }

        .back-btn:hover {
            color: #0056b3;
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
        <input type="text" class="title-input" placeholder="タイトル">
        <div class="photo-container">
            <div class="photo-placeholder">写真エリア</div>
            <div class="photo-details">
                <span>Canon ESO Kiss X10i</span>
                <span>55mm f/8.0 1/4s ISO5000</span>
                <span>EF-S55-250mm f/4-5.6 IS STM</span>
                <span>20:27:50 2024.12.25</span>
            </div>
        </div>
        <input type="text" class="memo-input" placeholder="メモ">
    </main>

    <!-- ログイン画面に戻るボタン -->
    <a href="login-page.html" class="back-btn">&larr;</a>
</body>
</html>
