<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <style>
        /* 共通のスタイル */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f4f4;
            color: #000000;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh; /* 高さを画面いっぱいにする */
            overflow: hidden; /* スクロールを抑制 */
        }

        /* サイドバー */
        .sidebar {
            width: 200px;
            background-color: #ffffff;
            position: relative;
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
            padding-left: 20px; 
            font-size: 18px;
            color: #000000;
            cursor: pointer;
            text-align: left; /* 左揃えに設定 */
        }


        .menu-item a {
            text-decoration: none;
            color: inherit;
        }

        .menu-item:hover {
            color: #007bff;
        }

        /* 絞り込みメニュー */
        #filter-toggle {
            display: none;
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
        }

        #filter-toggle:checked + .filter-modal {
            display: block;
        }

        /* アルバム追加ボタン */
        .add-photo-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .add-photo-btn:hover {
            background-color: #0056b3;
        }

        /* メインコンテンツ */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-top: 80px; /* 上にスペースを追加 */
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
            align-items: flex-start;
            overflow-y: auto;
        }

        /* アルバムアイテム */
        .album-item {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px;
            width: 250px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-align: center;
        }

        .album-item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5px;
        }

        .album-thumb {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .album-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0 0;
            color: #333;
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
        
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="menu-title">photos</div>
        <div class="menu-item">
            <a href="photos.php">写真</a>
        </div>
        <div class="menu-item">
            <a href="albums.php">アルバム</a>
        </div>
        <div class="menu-item">
            <a href="tags-page.html">タグ作成</a>
        </div>
        <div class="menu-item">
            <a href="sort-page.html">並べ替え</a>
        </div>
        <!-- 絞り込みメニュー -->
        <label for="filter-toggle" class="menu-item">絞り込み</label>

        <!-- 絞り込みポップアップ -->
        <input type="checkbox" id="filter-toggle">
        <div class="filter-modal">
            <div class="filter-item">絞り込み1</div>
            <div class="filter-item">絞り込み2</div>
            <div class="filter-item">絞り込み3</div>
        </div>

        <div class="menu-item">
            <a href="account-page.html">アカウント詳細</a>
        </div>
    </div>

    <form action="upload.php" method="get" style="display: inline;">
        <button type="submit" class="add-photo-btn">アルバムを追加</button>
    </form>

    <div class="main-content">
        <div class="album-item">
            <div class="photo-grid">
                <img src="https://via.placeholder.com/150" alt="写真1" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真2" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真3" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真4" class="album-thumb">
            </div>
            <div class="album-info">
                <p class="album-title">アルバム1</p>
            </div>
        </div>
        <div class="album-item">
            <div class="photo-grid">
                <img src="https://via.placeholder.com/150" alt="写真1" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真2" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真3" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真4" class="album-thumb">
            </div>
            <div class="album-info">
                <p class="album-title">アルバム2</p>
            </div>
        </div>
    </div>

</body>
</html>