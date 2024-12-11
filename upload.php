<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>画像アップロード</title>

    <div class="button001">
        <a href="#" onclick="history.back()">戻る</a>      
    </div>



    <style>
        .navbar {
            background-color: #f8f9fa; /* 背景色を薄い青色に設定 */
        }
        .navbar-brand {
            font-size: 2.5rem; /* フォントサイズを大きく設定 */
            color: #333; /* フォントカラーを濃い灰色に設定 */
        }
        #image-preview {
            display: block; /* プレビュー領域を常に表示 */
            width: 100%; /* 幅をフォーム内に収める */
            max-width: 300px; /* プレビューの最大幅 */
            height: 200px; /* 高さを固定 */
            object-fit: contain; /* 縦横比を維持しつつ領域内に収める */
            margin-top: 10px; /* 他の要素と間隔を設定 */
            border: none; /* 枠線を消去 */
            background-color: #f8f9fa; /* 背景色を設定 */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-light">
    <h2><a href="#" class="navbar-brand">Photos</a></h2>
</nav>
<hr>

<script>
    // 画像プレビュー表示
    const previewImage = () => {
        const fileInput = document.getElementById("file-input");
        const imagePreview = document.getElementById("image-preview");

        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
            };
            reader.readAsDataURL(fileInput.files[0]);
        } else {
            imagePreview.src = ""; // ファイルが選択されていない場合はリセット
        }
    };
</script>

<div class="container mt-4">
    <!-- フォーム -->
    <form
        action=""
        method="post"
        enctype="multipart/form-data"
        id="upload-form"
        class="bg-light p-4 rounded shadow-sm"
    >
        <div class="form-group">
            <label for="file-input">画像ファイルを選択:</label>
            <input
                type="file"
                name="image"
                id="file-input"
                accept="image/*"
                class="form-control"
                onchange="previewImage()"
                required
            />
        </div>

        <div class="form-group">
            <!-- プレビュー領域 -->
            <img
                id="image-preview"
            />
        </div>

        <div class="form-group">
            <label for="title-input">写真のタイトル:</label>
            <input
                type="text"
                name="title"
                id="title-input"
                class="form-control"
                placeholder="タイトルを入力してください"
            />
        </div>

        <div class="form-group">
            <textarea name="memo" rows="4" cols="40" class="form-control" placeholder="メモを入力してください"></textarea>
        </div>
        <button type="submit" name="upload" class="btn btn-primary">送信</button>
    </form>

    <!-- メッセージ表示 -->
    <?php if (isset($message)): ?>
        <div class="alert alert-info mt-3"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
</div>

<?php
// データベース接続設定
$dsn = "mysql:host=localhost;dbname=images;charset=utf8";
$username = "root";
$password = "root";

try {
    $dbh = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    die("データベース接続失敗: " . $e->getMessage());
}

// 画像アップロード処理
if (isset($_POST['upload'])) {
    $title = $_POST['title'];
    $image = uniqid(mt_rand(), true);
    $image .= '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file = "images/$image";

    // データベース登録クエリ
    $sql = "INSERT INTO images (title, name) VALUES (:title, :image)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':image', $image, PDO::PARAM_STR);

    // ファイルアップロードと検証
    if (!empty($_FILES['image']['name'])) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file) && exif_imagetype($file)) {
            $stmt->execute();
            $message = "画像とタイトルをアップロードしました。";
        } else {
            $message = "画像ファイルではありません。";
        }
    } else {
        $message = "ファイルが選択されていません。";
    }
}
?>
</body>
</html>
