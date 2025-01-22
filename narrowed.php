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

if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
} else {
    $user_id = ''; // セッションにidがない場合のデフォルト値
}
$pdo = connectDB();
$err_msg = '';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

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
// データベースから写真一覧を取得

// 絞り込み条件を取得
$filter_conditions = [];

if (!empty($_POST['camera_maker'])) {
    $filter_conditions[] = 'カメラメーカー: ' . implode(', ', $_POST['camera_maker']);
}

if (!empty($_POST['camera_model'])) {
    $filter_conditions[] = 'カメラモデル: ' . implode(', ', $_POST['camera_model']);
}

if (!empty($_POST['date_range'])) {
    $filter_conditions[] = '撮影日: ' . implode(', ', $_POST['date_range']);
}

if (!empty($_POST['lens_model'])) {
    $filter_conditions[] = 'レンズモデル: ' . implode(', ', $_POST['lens_model']);
}

if (!empty($_POST['ApertureFNumber'])) {
    $filter_conditions[] = '絞り値: ' . implode(', ', $_POST['ApertureFNumber']);
}

if (!empty($_POST['exposure_time'])) {
    $filter_conditions[] = '露出時間: ' . implode(', ', $_POST['exposure_time']);
}

if (!empty($_POST['focal_length'])) {
    $filter_conditions[] = '焦点距離: ' . implode(', ', $_POST['focal_length']);
}

// フィルタ条件を関数化
function addFilterCondition($field, $values, &$sql, &$params) {
    if (!empty($values)) {
        $placeholders = implode(',', array_map(fn($key) => ":{$field}_$key", array_keys($values)));
        $sql .= " AND $field IN ($placeholders)";
        foreach ($values as $key => $value) {
            $params[":{$field}_$key"] = $value;
        }
    }
}

// クエリ構築
$sql = "SELECT id, file_name, image_content FROM j10images WHERE u_id = :u_id";
$params = [':u_id' => $user_id];

// 絞り込み条件の適用
addFilterCondition('IFD_Make', $_POST['camera_maker'] ?? [], $sql, $params);
addFilterCondition('IFD_Model', $_POST['camera_model'] ?? [], $sql, $params);
addFilterCondition('LensModel', $_POST['lens_model'] ?? [], $sql, $params);
addFilterCondition('ApertureFNumber', $_POST['ApertureFNumber'] ?? [], $sql, $params);
addFilterCondition('ExposureTime', $_POST['exposure_time'] ?? [], $sql, $params);
addFilterCondition('FocalLength', $_POST['focal_length'] ?? [], $sql, $params);
// クエリ実行
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        .menu-title {
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
            font-size: 20px;
            color: #000000;
            cursor: pointer;
            transition: color 0.3s;
        }

        .menu-item:hover {
            color: #007bff;
        }

        .menu-item a {
            text-decoration: none;
            color: inherit;
        }
        .menu-item.logout {
            margin-top: auto;
        }
        
        .menu-item.logout a {
            font-size: 18px;
            color: red;
            padding: 10px;
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

        .add-photo-btn {
            position: fixed;
            top: 20px; /* 上からの距離 */
            right: 20px; /* 右からの距離 */
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            z-index: 1000; /* 他の要素の上に配置 */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .add-photo-btn:hover {
            background-color: #0056b3;
        }

        /* メインコンテンツ */
        .main-content {
            margin-left: 200px;
            padding: 20px;
            width: calc(100% - 200px); /* サイドバーの幅を引いた幅を設定 */
            margin-top: 60px;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 横に3枚表示 */
            gap: 15px;
            margin-top: 20px;
        }

        .photo-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 3; /* アスペクト比を固定 */
            overflow: hidden;
            background-color: #f4f4f4;
        }

        .photo-thumb {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        .photo-item {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }


        .photo-item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .photo-title {
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
        }

        .photo-title a {
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
        .photo-info {
            display: flex;
            align-items: center;
            justify-content: space-between; /* タイトルとボタンを左右に配置 */
            gap: 10px; /* 必要に応じて調整 */
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
        .delete-btn {
            font-size: 14px;
            color: red;
            text-decoration: none;
            background-color: transparent;
            border: 1px solid red;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        } 
        .sort-container {
            margin-bottom: 10px; /* マージンを小さくする */
            width: 200px; /* 幅をサイドバーに合わせる */
        }

        .sort-header {
            cursor: pointer;
            padding: 5px; /* パディングを小さくする */
            background-color: #007bff;
            color: white;
            border-radius: 3px;
            text-align: left;
            font-size: 12px; /* フォントサイズを小さくする */
            transition: background-color 0.3s, color 0.3s;
            width: 100%; /* 幅を親要素に合わせる */
            box-sizing: border-box; /* パディングを含めて幅を計算 */
        }

        .sort-header:hover {
            background-color: #0056b3;
        }

        .sort-content {
            display: none;
            margin-top: 5px; /* マージンを小さくする */
        }

        .sort-container.open .sort-content {
            display: block;
        }
                /* 並び替えリストのスタイル */
        .sort-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sort-list li {
            margin: 3px 0; /* 項目間の間隔を狭くする */
        }

        .sort-list a {
            display: block;
            padding: 3px 8px; /* パディングを小さくする */
            background-color: #ffffff;
            color: #007bff;
            border: 1px solid #007bff;
            border-radius: 3px;
            transition: background-color 0.3s, color 0.3s;
            text-align: left; /* 左揃えに変更 */
            font-size: 12px;
            width: 100%; /* 幅を親要素に合わせる */
            box-sizing: border-box; /* パディングを含めて幅を計算 */
        }

        .sort-list a:hover {
            background-color: #007bff;
            color: #ffffff;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sortHeader = document.querySelector('.sort-header');
            const sortContainer = document.querySelector('.sort-container');

            sortHeader.addEventListener('click', function () {
                sortContainer.classList.toggle('open');
            });
        });
    </script>
</head>
<body>

<!-- サイドバー -->
<div class="sidebar" id="sidebar">
    <div class="menu-title">photos</div>
    <div class="menu-item">
        <a href="#">写真</a>
    </div>
    <div class="filter-conditions">
    <h4>絞り込み条件</h4>
    <ul>
        <?php if (!empty($filter_conditions)): ?>
            <?php foreach ($filter_conditions as $condition): ?>
                <li><?= htmlspecialchars($condition, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>絞り込み条件なし</li>
        <?php endif; ?>
    </ul>
</div>

    <div class="menu-item logout">
        <a href="logout.php">ログアウト</a>
    </div>

</div>

<!-- 写真追加ボタン -->
<form action="upload.php" method="get" style="display: inline;">
    <button type="submit" class="add-photo-btn">写真を追加</button>
</form>
<!-- メインコンテンツ -->
<div class="main-content">
    <main>
    <div class="photo-grid" id="photoGrid">
    <?php
    // データベースから写真を表示

    if ($photos) {
        foreach ($photos as $row) {
            // 表示するタイトルを決定 (image_title が NULL なら file_name を使用)
            $display_title = isset($row["image_title"]) && $row["image_title"] !== null ? $row["image_title"] : $row["file_name"];
            echo '<div class="photo-item">';
            // 写真詳細ページへのリンクを追加
            echo '<a href="detail.php?id=' . htmlspecialchars($row["id"]) . '">';
            echo '<div class="photo-wrapper">';
            echo '<img src="data:image/jpeg;base64,' . base64_encode($row["image_content"]) . '" alt="' . htmlspecialchars($row["file_name"], ENT_QUOTES, 'UTF-8') . '" class="photo-thumb">';
            echo '</div>';
            echo '</a>';
            echo '<div class="photo-info">';
            echo '<p class="photo-title">' . htmlspecialchars($display_title, ENT_QUOTES, 'UTF-8') . '</p>';
            // 削除ボタンを追加
            echo '<a href="?delete=' . htmlspecialchars($row["id"]) . '" class="delete-btn" onclick="return confirm(\'本当に削除しますか？\')">削除</a>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>写真がありません。</p>';
    }
    ?>
</div>

</div>
</main>
</body>
</html>
