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
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding-left: 20px;
            font-size: 18px;
            color: #000000;
            cursor: pointer;
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
            margin-left: 200px; /* サイドバーの幅と一致させる */
            padding: 20px;
            width: calc(100% - 200px); /* サイドバーを除いた幅を確保 */
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
            max-width: 600px; /* 少し幅を狭く */
            padding: 12px; /* 少し内側の余白を減らす */
            font-size: 16px; /* フォントサイズを少し小さく */
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .photo-container {
            width: 100%;
            max-width: 600px; /* 写真エリアの幅を狭く */
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
            height: 300px; /* 高さを少し減らす */
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
        /* ←ボタン */
        .back-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            font-size: 24px;
            color: #007bff;
            text-decoration: none;
            background: transparent;
            border: none;
            cursor: pointer;
            z-index: 1001;
        }

        .back-btn:hover {
            color: #0056b3;
        }

        /* ポップアップテキスト */
        .back-btn-tooltip {
            position: fixed;
            top: 50px;
            left: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-in-out;
            z-index: 1000;
        }

        .back-btn:hover + .back-btn-tooltip {
            opacity: 1;
            pointer-events: auto;


        }

        /* 絞り込みメニュー */
        .filter-modal {
            display: none;
            position: absolute;
            top: 320px;
            left: 200px;
            width: 200px;
            background-color: #ffffff;
            padding: 10px;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            border-radius: 12px;
        }

        .filter-item {
            margin: 5px 0;
            font-size: 14px;
            color: #000000;
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
        <div class="menu-item" id="filter-toggle">絞り込み</div>
        <div class="filter-modal" id="filter-modal">
            <div class="filter-item">絞り込み1</div>
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

    <script>
        const filterToggle = document.getElementById('filter-toggle');
        const filterModal = document.getElementById('filter-modal');

        filterToggle.addEventListener('click', () => {
            const isVisible = filterModal.style.display === 'block';
            filterModal.style.display = isVisible ? 'none' : 'block';
        });
    </script>
    <!-- ログイン画面に戻るボタン -->
<a href="login-page.html" class="back-btn">&larr;</a>
<div class="back-btn-tooltip">写真一覧画面に戻る</div>

</body>
</html>
