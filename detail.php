<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
require_once 'imageFunction.php';
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Dotenv\Dotenv;

session_start();

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
// データベースに接続
$pdo = connectDB();
$err_msg = '';
// 接続チェック
if ($pdo === false) {
    die("データベースに接続できませんでした。");
}
$pdo = connectDB();
$err_msg = '';

// S3クライアントの作成
$s3Client = new S3Client([
    'region'      => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'key' => $_ENV['key'],
        'secret' => $_ENV['secret'],
    ],
]);

$bucket = 'j10s3';

// 削除処理
if (isset($_GET['delete'])) {
    $delete_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);

    if ($delete_id === false) {
        exit('無効なIDです。');
    }

    // 削除する写真のファイルパスを取得
    $sql = "SELECT image_url FROM j10images WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $delete_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $file_path = $result['image_url'];

        // ファイルを削除
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        // S3からファイルを削除
        try {
            $s3Client->deleteObject([
                'Bucket' => $bucket,
                'Key' => basename($file_path),
            ]);
        } catch (AwsException $e) {
            exit('S3からファイルを削除できませんでした: ' . $e->getMessage());
        }
        // データベースから写真情報を削除
        $sql = "DELETE FROM j10images WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $delete_id, PDO::PARAM_INT);
        $stmt->execute();
            // 削除後にページをリフレッシュ
        header("Location: photos.php");
        exit();
    } else {
        exit('写真が見つかりませんでした。');
        }
    }

// GETパラメータからIDを取得
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die("無効なIDが指定されました。");
}
// データを取得
$sql = "SELECT id, image_title, u_id, file_name, IFD_Make, IFD_Model, 
               IFD_DateTime, ExposureTime, ApertureFNumber, ISOSpeedRatings, DateTimeOriginal, FocalLength, 
               LensModel, Tag1, Tag2, Tag3, created_at, image_url
        FROM j10images WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$imageData = $stmt->fetch(PDO::FETCH_ASSOC);

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
            width: 120%;
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
            padding-top: 10px;
            border-top: 2px solid #ccc;
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

        .delete-btn {
            padding: 8px 12px;
            background-color: #ff0000;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .photo-title {
            margin-bottom: 10px; /* タイトルと詳細情報の間隔を10pxに設定 */
            font-size: 20px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <!-- サイドバー -->
    <div class="sidebar">
        <div class="menu-title">photos</div>
        <div class="menu-item"><a href="photos.php">写真</a></div>
        <div class="menu-item"><a href="narrowwing.php">並べ替え</a></div>
    </div>
 <!-- メインエリア -->
 <main class="main-area">
        <div class="photo-container">
            <div class="photo-placeholder">
                <?php if ($imageData): ?>
                    <img src="<?= htmlspecialchars($imageData['image_url']) ?>" alt="<?= htmlspecialchars($imageData['image_title']) ?>">
                <?php else: ?>
                    <p>画像が見つかりません。</p>
                <?php endif; ?>
            </div>
            <?php if ($imageData): ?>
                <div class="photo-title"><?= htmlspecialchars($imageData['image_title'] ?? '') ?></div>
            <?php endif; ?>
            <div class="photo-details">
                <?php if ($imageData): ?>
                    <span>カメラ: <?= htmlspecialchars($imageData['IFD_Model']) ?></span>
                    <span>露出: <?= htmlspecialchars($imageData['ExposureTime']) ?> <?= htmlspecialchars($imageData['ApertureFNumber']) ?> ISO <?= htmlspecialchars($imageData['ISOSpeedRatings']) ?></span>
                    <span>レンズ: <?= htmlspecialchars($imageData['LensModel']) ?></span>
                    <span>焦点距離: <?= htmlspecialchars(parseFocalLength($imageData['FocalLength'])) ?></span>
                    <span>撮影日時: <?= htmlspecialchars($imageData['DateTimeOriginal']) ?></span>
                <?php else: ?>
                    <p>詳細情報が見つかりません。</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="photo-buttons">
            <form action="compilation.php" method="GET">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($imageData['id']); ?>">
                <button type="submit" class="photo-button1">編集</button>
            </form>
            <a href="?delete=<?= htmlspecialchars($imageData['id']) ?>" class="delete-btn" onclick="return confirm('本当に削除しますか？')">削除</a>

        </div>
        </div>
    </main>
</body>
</html>

<?php
// 焦点距離を変換する関数
function parseFocalLength($focalLength) {
    if (empty($focalLength) || strpos($focalLength, '/') === false) {
        return $focalLength . 'mm';
    }
    list($numerator, $denominator) = explode('/', $focalLength);
    if (!is_numeric($numerator) || !is_numeric($denominator)) {
        return $focalLength . '';
    }
    return intval($numerator / max(1, $denominator)) . 'mm';
}
?>