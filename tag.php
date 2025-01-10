<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "tags_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("データベース接続に失敗しました: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tag'])) {
    $tag = $conn->real_escape_string(trim($_POST['tag']));
    if (!empty($tag)) {
        $sql = "INSERT INTO tags (name) VALUES ('$tag')";
        $conn->query($sql);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_tags'])) {
    foreach ($_POST['delete_tags'] as $tagToDelete) {
        $tagToDelete = $conn->real_escape_string(trim($tagToDelete));
        $sql = "DELETE FROM tags WHERE name = '$tagToDelete'";
        $conn->query($sql);
    }
}

if (isset($_POST['delete_mode'])) {
    $_SESSION['delete_mode'] = !($_SESSION['delete_mode'] ?? false);
}

$sql = "SELECT name FROM tags";
$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>タグ作成</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>


        body {
            background-color: #f9f9f9;
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-inline input, .form-inline button {
            margin-right: 10px;
        }
        .tag-item {
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border-radius: 20px;
            margin: 5px;
            font-size: 14px;
        }
        .delete-mode {
            text-align: center;
            margin-top: 20px;
        }
        .checkbox-group label {
            display: inline-block;
            margin: 5px;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .checkbox-group input {
            margin-right: 5px;
        }


    </style>
</head>
<body>


<nav class="navbar navbar-left">
    <div class="button001">
        <a href="photos.php">戻る</a>    
    </div>
</nav>
<hr>



<div class="container">
    <h1>タグ作成</h1>

    <!-- タグ追加フォーム -->
    <form class="form-inline" method="POST" action="">
        <div class="form-group">
            <input type="text" name="tag" class="form-control" placeholder="新しいタグを入力" required>
        </div>
        <button type="submit" class="btn btn-primary">追加</button>
    </form>

    <!-- タグ削除モードの切り替え -->
    <div class="delete-mode">
        <form method="POST" action="">
            <button type="submit" class="btn btn-danger" name="delete_mode">
                <?= isset($_SESSION['delete_mode']) && $_SESSION['delete_mode'] ? '削除モードを解除' : '削除モードを有効化' ?>
            </button>
        </form>
    </div>

    <!-- 削除モードのタグリスト -->
    <?php if (isset($_SESSION['delete_mode']) && $_SESSION['delete_mode']): ?>
        <form method="POST" action="" class="checkbox-group mt-4">
            <h5>削除するタグを選択:</h5>
            <?php while ($row = $result->fetch_assoc()): ?>
                <label>
                    <input type="checkbox" name="delete_tags[]" value="<?= htmlspecialchars($row['name']) ?>">
                    <?= htmlspecialchars($row['name']) ?>
                </label>
            <?php endwhile; ?>
            <button type="submit" class="btn btn-danger mt-3">削除</button>
        </form>
    <?php else: ?>
        <!-- タグ一覧表示 -->
        <div class="tag-list mt-4">
            <h5>タグ一覧:</h5>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <span class="tag-item"><?= htmlspecialchars($row['name']) ?></span>
                <?php endwhile; ?>
            <?php else: ?>
                <p>まだタグがありません。</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
