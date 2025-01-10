<?php
// データベース接続
$host = 'localhost';
$dbname = 'j10img'; // データベース名
$username = 'root'; // ユーザー名
$password = 'root'; // パスワード

$conn = new mysqli($host, $username, $password, $dbname);

// 接続確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// クエリの実行
$sql = "SELECT id, Title, userName, FileName, MimeType, T_FileType, T_MimeType, IFD_Make, IFD_Model, IFD_Software, 
                IFD_DateTime, ExposureTime, ApertureFNumber, ISOSpeedRatings, DateTimeOriginal, FocalLength, 
                ColorSpace, ExposureMode, WhiteBalance, LensModel, Tag1, Tag2, Tag3, image_content, created_at
        FROM j10images WHERE id = 9"; // 例えば、idが1の画像を取得
$result = $conn->query($sql);

// データを取得
$imageData = null;
if ($result->num_rows > 0) {
    $imageData = $result->fetch_assoc();
}
?>
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
            padding-left: 20px;
            font-size: 18px;
            color: #000000;
            cursor: pointer;
            text-align: left;
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
            margin-top: 10px; /* ここで上下の位置を調整 */
            padding: 20px;
            max-width: 700px; /* 最大幅を調整 */
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

        .photo-placeholder img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }

        /* 詳細情報のスタイル */
        .photo-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 2列に設定 */
            gap: 10px; /* 列間の余白 */
            width: 100%;
        }

        .photo-details span {
        display: inline-block; /* インラインブロック要素にして幅を設定できるようにする */
        width: 300px; /* 幅を指定 */
        text-overflow: ellipsis; /* 省略記号を表示 */
        padding-right: 10px; /* 右側に余白を追加 */
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
            background-color: #cc0000;
        }
    </style>
</head>
<body>
    <!-- サイドバー -->
    <div class="sidebar">
        <div class="menu-title">photos</div>
        <div class="menu-item"><a href="photos.php">写真</a></div>
        <div class="menu-item"><a href="albums.php">アルバム</a></div>
        <div class="menu-item"><a href="tags-page.html">タグ作成</a></div>
        <div class="menu-item"><a href="sort-page.html">並べ替え</a></div>
        <div class="menu-item"><a href="account-page.html">アカウント詳細</a></div>
    </div>

    <!-- メインエリア -->
    <main class="main-area">
        <div class="photo-container">
            <div class="photo-placeholder">
                <!-- データベースから取得した画像を表示 -->
                <?php if ($imageData): ?>
                    <img src="https://j10s3.s3.us-east-1.amazonaws.com/<?php echo htmlspecialchars($imageData['FileName']); ?>" alt="写真">
                <?php else: ?>
                    <p>画像が見つかりません。</p>
                <?php endif; ?>
            </div>
            <div class="photo-details">
                <!-- 取得した情報を表示 -->
                <?php if ($imageData): ?>
                    <span>カメラ: <?php echo htmlspecialchars($imageData['IFD_Make']); ?> <?php echo htmlspecialchars($imageData['IFD_Model']); ?></span>
                    <span>露出: <?php echo htmlspecialchars($imageData['ExposureTime']); ?> <?php echo htmlspecialchars($imageData['ApertureFNumber']); ?> ISO <?php echo htmlspecialchars($imageData['ISOSpeedRatings']); ?></span>
                    <span>レンズ: <?php echo htmlspecialchars($imageData['LensModel']); ?></span>

                    <?php
                    // 焦点距離の変換処理
                    $focalLength = isset($imageData['FocalLength']) ? $imageData['FocalLength'] : ''; // FocalLengthが存在しない場合は空文字を設定

                    // 焦点距離が空でない場合、分数形式（例: 220/1）から変換
                    if (!empty($focalLength) && strpos($focalLength, '/') !== false) {
                        list($numerator, $denominator) = explode('/', $focalLength);
                        $focalLength = $numerator; // 分子だけを使用
                    }

                    // 最終的にmmを追加
                    $focalLength .= 'mm';
                    ?>

                    <span>焦点距離: <?php echo htmlspecialchars($focalLength); ?></span>
                    <span>撮影日時: <?php echo htmlspecialchars($imageData['DateTimeOriginal']); ?></span>

                    <?php
                    // タグを配列として定義
                    $tags = [
                        'Tag1' => $imageData['Tag1'] ?? null,
                        'Tag2' => $imageData['Tag2'] ?? null,
                        'Tag3' => $imageData['Tag3'] ?? null,
                        // 必要に応じてさらに追加
                    ];

                    // タグをループして表示
                    $tagDisplay = ''; // 空の文字列で初期化

                    foreach ($tags as $key => $tag) {
                        if (!empty($tag) && $tag != 0) {
                            // タグをかぎかっこで囲んで表示し、スペースで区切り
                            $tagDisplay .= "<span>{" . htmlspecialchars($key) . ": " . htmlspecialchars($tag) . "}</span> ";
                        }
                    }

                    echo rtrim($tagDisplay); // 末尾の余分なスペースを削除
                    ?>

                <?php else: ?>
                    <p>詳細情報が見つかりません。</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="title-text"><?php echo htmlspecialchars($imageData['Title'] ?? 'タイトル'); ?></div>
        <div class="memo-text">メモの内容をここに表示</div>
        <!-- メモの下にボタンを配置 -->
        <div class="photo-buttons">
            <button class="photo-button1">編集</button>
            <button class="photo-button2">削除</button>
        </div>
    </main>
</body>
</html>