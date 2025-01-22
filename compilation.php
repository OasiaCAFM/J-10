<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'imageFunction.php';

$pdo = connectDB();

// 接続チェック

if ($pdo === false) {
    die("データベース接続に失敗しました。");
}

$id = $_GET['id'] ?? null;
$imageData = null;
if ($id) {
    $sql = "SELECT * FROM j10images WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $imageData = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $ifd_make = $_POST['ifd_make'] ?? '';
    $ifd_model = $_POST['ifd_model'] ?? '';
    $exposure_time = $_POST['exposure_time'] ?? '';
    $aperture_fnumber = $_POST['aperture_fnumber'] ?? '';
    $iso_speed_ratings = $_POST['iso_speed_ratings'] ?? '';
    $lens_model = $_POST['lens_model'] ?? '';
    $focal_length = $_POST['focal_length'] ?? '';
    $datetime_original_date = $_POST['datetime_original_date'] ?? '';
    $datetime_original_time = $_POST['datetime_original_time'] ?? '';
    $datetime_original = $datetime_original_date . ' ' . $datetime_original_time;
    $tag1 = $_POST['tag1'] ?? '';
    $tag2 = $_POST['tag2'] ?? '';
    $tag3 = $_POST['tag3'] ?? '';

    $sql = "UPDATE j10images SET 
                image_title = :image_title, 
                IFD_Make = :ifd_make, 
                IFD_Model = :ifd_model, 
                ExposureTime = :exposure_time, 
                ApertureFNumber = :aperture_fnumber, 
                ISOSpeedRatings = :iso_speed_ratings, 
                LensModel = :lens_model, 
                FocalLength = :focal_length, 
                DateTimeOriginal = :datetime_original
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':image_title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':ifd_make', $ifd_make, PDO::PARAM_STR);
    $stmt->bindValue(':ifd_model', $ifd_model, PDO::PARAM_STR);
    $stmt->bindValue(':exposure_time', $exposure_time, PDO::PARAM_STR);
    $stmt->bindValue(':aperture_fnumber', $aperture_fnumber, PDO::PARAM_STR);
    $stmt->bindValue(':iso_speed_ratings', $iso_speed_ratings, PDO::PARAM_STR);
    $stmt->bindValue(':lens_model', $lens_model, PDO::PARAM_STR);
    $stmt->bindValue(':focal_length', $focal_length, PDO::PARAM_STR);
    $stmt->bindValue(':datetime_original', $datetime_original, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: detail.php?id=" . $id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>編集</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f4f4;
            color: #000000;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
        }
        .form-group {
            margin-bottom: 10px; /* テキストフィールドの間隔を10pxに設定 */
        }

        .main-area {
            margin-top: 30px;
            padding: 20px;
            max-width: 800px;
            width: 100%;
            box-sizing: border-box;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .filter-section {
            margin-bottom: 20px;
        }

        .filter-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .filter-item {
            margin-bottom: 15px;
        }

        .filter-item label {
            font-size: 14px;
            margin-right: 10px;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .range-inputs {
            display: flex;
            gap: 5px;
        }

        .filter-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .button {
            padding: 8px 12px;
            font-size: 14px;
            background-color: #007bff;
            color:rgb(255, 255, 255);
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .button-apply {
            background-color: #007bff;
        }

        .button-apply:hover {
            background-color: #0056b3;
        }

        .button-reset {
            background-color: #ff0000;
        }

        .button-reset:hover {
            background-color:hsl(0, 100.00%, 40.00%);
        }
        .back-button {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <a href="detail.php?id=<?php echo htmlspecialchars($id); ?>" class="back-button">戻る</a>
    <main class="main-area">
        <?php if ($imageData): ?>
            <form action="compilation.php?id=<?php echo htmlspecialchars($id); ?>" method="POST">
                <div class="form-group">
                    <label for="title">タイトル</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($imageData['image_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="ifd_make">カメラメーカー</label>
                    <input type="text" id="ifd_make" name="ifd_make" value="<?php echo htmlspecialchars($imageData['IFD_Make']); ?>">
                </div>
                <div class="form-group">
                    <label for="ifd_model">カメラモデル</label>
                    <input type="text" id="ifd_model" name="ifd_model" value="<?php echo htmlspecialchars($imageData['IFD_Model']); ?>">
                </div>
                <div class="form-group">
                    <label for="exposure_time">露出時間</label>
                    <input type="text" id="exposure_time" name="exposure_time" value="<?php echo htmlspecialchars($imageData['ExposureTime']); ?>">
                </div>
                <div class="form-group">
                    <label for="aperture_fnumber">絞り値</label>
                    <input type="text" id="aperture_fnumber" name="aperture_fnumber" value="<?php echo htmlspecialchars($imageData['ApertureFNumber']); ?>">
                </div>
                <div class="form-group">
                    <label for="iso_speed_ratings">ISO感度</label>
                    <input type="text" id="iso_speed_ratings" name="iso_speed_ratings" value="<?php echo htmlspecialchars($imageData['ISOSpeedRatings']); ?>">
                </div>
                <div class="form-group">
                    <label for="lens_model">レンズモデル</label>
                    <input type="text" id="lens_model" name="lens_model" value="<?php echo htmlspecialchars($imageData['LensModel']); ?>">
                </div>
                <div class="form-group">
                    <label for="focal_length">焦点距離</label>
                    <input type="text" id="focal_length" name="focal_length" value="<?php echo htmlspecialchars($imageData['FocalLength']); ?>">
                </div>
                <div class="form-group">
                    <label for="datetime_original_date">撮影日</label>
                    <input type="date" id="datetime_original_date" name="datetime_original_date" value="<?php echo htmlspecialchars(explode(' ', $imageData['DateTimeOriginal'])[0]); ?>">
                </div>
                <div class="form-group">
                    <label for="datetime_original_time">撮影時間</label>
                    <input type="time" id="datetime_original_time" name="datetime_original_time" value="<?php echo htmlspecialchars(explode(' ', $imageData['DateTimeOriginal'])[1]); ?>">
                </div>
                <button type="submit" class="button">保存</button>
            </form>
        <?php else: ?>
            <p>編集するデータが見つかりません。</p>
        <?php endif; ?>
        
    </main>
</body>
</html>