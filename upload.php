<?php
ob_start(); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'imageFunction.php';
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$pdo = connectDB();

if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    
} else {
    echo "ログインしてください";
    $user_id = ''; // セッションにnameがない場合のデフォルト値
}
// エラーメッセージの初期化
$error_message = '';

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

function generateThumbnail($sourcePath, $maxWidth, $maxHeight) {
    list($originalWidth, $originalHeight, $imageType) = getimagesize($sourcePath);

    // アスペクト比を維持してリサイズ
    $aspectRatio = $originalWidth / $originalHeight;
    if ($originalWidth > $originalHeight) {
        $newWidth = $maxWidth;
        $newHeight = $maxWidth / $aspectRatio;
    } else {
        $newHeight = $maxHeight;
        $newWidth = $maxHeight * $aspectRatio;
    }

    // 元画像を読み込む
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        default:
            throw new Exception("対応していない画像形式です。");
    }

    // リサイズ後の空の画像を作成
    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

    // 元画像をリサイズしてコピー
    imagecopyresampled(
        $thumbnail,
        $sourceImage,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $originalWidth,
        $originalHeight
    );

    // 一時ファイルにサムネイルを保存
    $tempPath = tempnam(sys_get_temp_dir(), 'thumb');
    imagejpeg($thumbnail, $tempPath, 75); // 圧縮率75でJPEGに変換

    // メモリを解放
    imagedestroy($sourceImage);
    imagedestroy($thumbnail);

    return $tempPath;
}


// ファイルがアップロードされているか確認
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['photo']['name']);
        $file_path = $_FILES['photo']['tmp_name'];

        // ファイルの拡張子チェック
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            $error_message = "許可されていないファイル形式です。jpg, jpeg, pngのいずれかのファイルをアップロードしてください。";
        } else {
            try {
                $thumbnailPath = generateThumbnail($file_path, 500, 500); // 最大幅300px, 高さ300px
                
                // サムネイルデータを取得
                $content = file_get_contents($thumbnailPath);
                unlink($thumbnailPath); // サムネイルの一時ファイルを削除

                // S3にファイルをアップロード
                $result = $s3Client->putObject([
                    'Bucket' => $bucket,
                    'Key' => $file_name,
                    'SourceFile' => $file_path,
                    'ACL' => 'public-read', // 公開アクセスを設定
                ]);
    
                $file_url = $result['ObjectURL'];

                $imageData = file_get_contents($file_url);
                if ($imageData === false) {
                    die("画像データの取得に失敗しました。");
                }

                // メモリ上にストリームを作成
                $imageStream = fopen('data://image/jpeg;base64,' . base64_encode($imageData), 'rb');
                if ($imageStream === false) {
                    die("画像ストリームの作成に失敗しました。");
                }

                // EXIF情報を読み取る
                $exif_data = @exif_read_data($imageStream, 0, true);
                // 焦点距離の計算
                $focal_length_raw = $exif_data['EXIF']['FocalLength'] ?? 'N/A';
                $focal_length_calculated = 'N/A'; // デフォルト値

                if ($focal_length_raw !== 'N/A' && strpos($focal_length_raw, '/') !== false) {
                    list($numerator, $denominator) = explode('/', $focal_length_raw);
                    if (is_numeric($numerator) && is_numeric($denominator) && $denominator != 0) {
                        $focal_length_calculated = round($numerator / $denominator, 2); // 小数第2位まで計算
                    }
                }
                // ストリームを閉じる
                fclose($imageStream);
    
                if ($exif_data === false) {
                    $exif_data = [];
                }
                

                // データベースにファイル情報を保存
                $sql = "INSERT INTO j10images (u_id, image_title, file_name, MimeType, T_FileType, T_MimeType, IFD_Make, IFD_Model, IFD_Software, IFD_DateTime, ExposureTime, ApertureFNumber, ISOSpeedRatings, DateTimeOriginal, FocalLength, ColorSpace, ExposureMode, WhiteBalance, LensModel, image_content, image_url, created_at) 
                        VALUES (:u_id, :image_title, :file_name, :MimeType, :T_FileType, :T_MimeType, :IFD_Make, :IFD_Model, :IFD_Software, :IFD_DateTime, :ExposureTime, :ApertureFNumber, :ISOSpeedRatings, :DateTimeOriginal, :FocalLength, :ColorSpace, :ExposureMode, :WhiteBalance, :LensModel, :image_content, :image_url, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':u_id', $user_id, PDO::PARAM_INT);
                $stmt->bindValue(':image_title', $_POST['title'] ?? NULL, PDO::PARAM_STR);
                $stmt->bindValue(':file_name', $file_name, PDO::PARAM_STR);
                $stmt->bindValue(':MimeType', $exif_data['FILE']['MimeType'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':T_FileType', $exif_data['COMPUTED']['Thumbnail.FileType'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':T_MimeType', $exif_data['COMPUTED']['Thumbnail.MimeType'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':IFD_Make', $exif_data['IFD0']['Make'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':IFD_Model', $exif_data['IFD0']['Model'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':IFD_Software', $exif_data['IFD0']['Software'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':IFD_DateTime', $exif_data['IFD0']['DateTime'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':ExposureTime', $exif_data['EXIF']['ExposureTime'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':ApertureFNumber', $exif_data['COMPUTED']['ApertureFNumber'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':ISOSpeedRatings', $exif_data['EXIF']['ISOSpeedRatings'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':DateTimeOriginal', $exif_data['EXIF']['DateTimeOriginal'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':FocalLength', $focal_length_calculated, PDO::PARAM_STR);
                $stmt->bindValue(':ColorSpace', $exif_data['EXIF']['ColorSpace'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':ExposureMode', $exif_data['EXIF']['ExposureMode'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':WhiteBalance', $exif_data['EXIF']['WhiteBalance'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':LensModel', $exif_data['EXIF']['UndefinedTag:0xA434'] ?? 'N/A', PDO::PARAM_STR);
                $stmt->bindValue(':image_content', $content, PDO::PARAM_LOB);
                $stmt->bindValue(':image_url', $file_url, PDO::PARAM_STR);
                $stmt->execute();
                header('Location: photos.php');
    exit;
            } catch (AwsException $e) {
                $error_message = "ファイルのアップロードに失敗しました。エラー: " . $e->getMessage();
            }
        }
    } else {
        $error_message = "ファイルがアップロードされていません。";
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f7;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .form-container {
            width: 100%;
            max-width: 600px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            font-size: 26px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }

        .form-container input[type="text"],
        .form-container input[type="file"] {
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #d1d1d6;
            border-radius: 12px;
            font-size: 18px;
            background-color: #f5f5f7;
            color: #333;
        }

        .form-container button {
            padding: 15px 30px;
            background-color: #007aff;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 18px;
        }

        .form-container button:hover {
            background-color: #0051a8;
        }

        #error-message {
            color: red;
            margin-bottom: 10px;
        }

        /* Apple風の戻るボタン */
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 18px;
            color: #007aff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: transparent;
            border: none;
            padding: 10px;
            border-radius: 50%;
        }

        .back-btn:hover {
            background-color: #f0f0f5;
            border-radius: 50%;
        }

        .back-btn:before {
            content: '←'; /* 矢印アイコン */
            font-size: 24px;
            color: #007aff;
            margin-right: 8px;
        }

        /* 写真一覧リンク */
        .photos-link {
            font-size: 20px;
            color: #007aff;
            text-decoration: none;
            margin-top: 20px;
        }

        .photos-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<!-- Apple風の戻るボタン -->
<a href="photos.php" class="back-btn"><span>写真一覧</span></a>
<div class="form-container">
    <h2>写真をアップロード</h2>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <label for="title">写真タイトル（任意、最大32文字）</label>
        <input type="text" id="title" name="titlea" maxlength="32">
        
        <?php if (!empty($error_message)): ?>
            <p id="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        
        <label for="photo">写真を選択</label>
        <input type="file" id="photo" name="photo" accept="image/*" required>

        <button type="submit">アップロード</button>
    </form>
</div>
</body>
</html>
