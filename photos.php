<?php
// データベース接続設定
$dsn = 'mysql:host=localhost;dbname=images';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 写真データを取得するクエリ
    $stmt = $pdo->prepare("SELECT * FROM images");
    $stmt->execute();
    $photos = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "データベース接続エラー: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* 共通のスタイル */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f4f4;  /* ライトテーマの背景色 */
            color: #000000;  /* ライトテーマ時の文字色 */
            margin: 0;
            padding: 0;
            display: flex;
            transition: background-color 0.3s, color 0.3s;
        }

        a {
        text-decoration: none;  /* すべてのリンクの下線を消す */
        }

        /* サイドバー */
        .sidebar {
            width: 200px;
            background-color: #ffffff;  /* ライトテーマ時のサイドバー背景色 */
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
            transition: background-color 0.3s;
        }

        .menu-imageTitle {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #000000;  /* ライトテーマ時の文字色 */
        }

        .menu-item {
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding-left: 20px;
            font-size: 18px;
            color: #000000;  /* ライトテーマ時の文字色 */
            cursor: pointer;
            transition: color 0.3s;
        }

        .menu-item:hover {
            color: #007bff;  /* サイドバーのアイテムにカーソルを当てた時の色 */
        }

        .menu-item a {
            text-decoration: none;
            color: inherit;
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
            display: block;
            opacity: 1;
            pointer-events: auto;
            transition: opacity 0.3s ease;
        }

        .filter-item {
            margin: 5px 0;
            font-size: 14px;
            color: #000000;  /* ライトテーマ時の文字色 */
        }

        /* 絞り込みメニューの項目にカーソルを合わせたとき */
        .filter-item:hover {
            color: #007bff;  /* カーソルを合わせたときに青くなる */
        }

        /* 写真追加ボタン */
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
            margin-left: 200px;
            padding: 20px;
            width: 100%;
            margin-top: 60px;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 横に3枚表示 */
            gap: 15px;
            margin-top: 20px;
        }

        .photo-grid .photo-wrapper {
            position: relative;
            width: 100%;
            padding-top: 66.67%; /* 縦:横 = 2:3 */
            overflow: hidden;
            background-color: #f4f4f4; /* 背景色を指定して余白を目立たせない */
        }

        .photo-grid img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 100%;
            max-height: 100%;
            object-fit: contain; /* 画像が枠全体に収まるように表示 */
        }


        .photo-item {
            background-color: #ffffff;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            text-align: center;
        }

        .photo-item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .photo-thumb {
            width: 100%;
            height: 100%;
            object-fit: fill; /* 枠全体を埋めるように画像を表示 */
            transition: transform 0.2s ease-in-out;
        }


        .photo-imageTitle {
            font-size: 16px;
            font-weight: 600;
            padding: 10px;
            text-align: center;
            color: #333;
            margin: 0;
        }

        .photo-imageTitle a {
            color: inherit;  /* 親要素の色を引き継ぐ */
        }


        /* フルスクリーンポップアップ */
        .photo-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .photo-modal img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            transition: transform 0.2s ease;
        }

        .photo-modal:target {
            display: flex;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            color: white;
            text-decoration: none;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            padding: 5px 8px;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .close-btn:hover {
            background-color: rgba(0, 0, 0, 0.8);
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
    </style>
</head>
<body>

<!-- サイドバー -->
<div class="sidebar" image_id="sidebar">
    <div class="menu-imageTitle">photos</div>
    <div class="menu-item">
        写真</a>
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
    <div class="menu-item filter-container">
        <span>絞り込み</span>
        <div class="filter-modal">
            <a href="narrowed.php" class="filter-item">絞り込み1</a>
            <a href="narrowed.php" class="filter-item">絞り込み2</a>
            <a href="narrowed.php" class="filter-item">絞り込み3</a>
        </div>
    </div>

    <div class="menu-item">
        <a href="account-page.html">アカウント詳細</a>
    </div>
</div>

<!-- 写真追加ボタン -->
<form action="upload.php" method="get" style="display: inline;">
    <button type="submit" class="add-photo-btn">写真を追加</button>
</form>

<!-- メインコンテンツ -->
<div class="main-content">
    <div class="photo-grid">
        <?php foreach ($photos as $photo): ?>
            <div class="photo-item">
                <a href="photo-details.php?image_id=<?= htmlspecialchars($photo['image_id']) ?>">  <!-- リンク追加 -->
                    <div class="photo-wrapper">
                        <img src="image.php?id=<?= htmlspecialchars($photo['image_id']) ?>" class="photo-thumb">
                    </div>
                    <div class="photo-info">
                        <p class="photo-imageTitle"><?= htmlspecialchars($photo['imageTitle']) ?></p>
                    </div>
                </a>
            </div>

        <?php endforeach; ?>
    </div>


    <?php foreach ($photos as $photo): ?>
        <div image_id="photo<?= htmlspecialchars($photo['image_id']) ?>" class="photo-modal">
            <a href="#" class="close-btn">&times;</a>
            <!-- 修正: $photo['image_id'] を使用 -->
            <img src="image.php?id=<?= htmlspecialchars($photo['image_id']) ?>" class="mr-3">
        </div>
    <?php endforeach; ?>
</div>


</body>
</html>