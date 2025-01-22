<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'imageFunction.php';
session_start();

// セッションからユーザーIDを取得
$user_id = $_SESSION['id'] ?? '';

// データベース接続
$pdo = connectDB();
if ($pdo === false) {
    die("データベースに接続できませんでした。");
}

// 共通関数: フィルタオプションを取得
function getDistinctOptions($pdo, $column, $user_id) {
    $sql = "SELECT DISTINCT $column FROM j10images WHERE $column NOT IN ('N/A', '----') AND u_id = :u_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':u_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// フィルタオプションの取得
$exposure_options = getDistinctOptions($pdo, 'ExposureTime', $user_id);
$focal_options = getDistinctOptions($pdo, 'FocalLength', $user_id);
$lens_options = getDistinctOptions($pdo, 'LensModel', $user_id);
$f_values = getDistinctOptions($pdo, 'ApertureFNumber', $user_id);
$camera_makers = getDistinctOptions($pdo, 'IFD_Make', $user_id);
$camera_models = getDistinctOptions($pdo, 'IFD_Model', $user_id);

// ソート: 必要な場合のみ
usort($exposure_options, function($b, $a) {
    $toDecimal = function($fraction) {
        if (strpos($fraction, '/') !== false) {
            [$numerator, $denominator] = explode('/', $fraction);
            return floatval($numerator) / floatval($denominator);
        }
        return floatval($fraction);
    };
    return $toDecimal($a) <=> $toDecimal($b);
});
// 焦点距離を小さい順にソート
usort($focal_options, function($a, $b) {
    return floatval($a) - floatval($b);
});
// F値を小さい順にソート
usort($f_values, function($a, $b) {
    $a_value = floatval(str_replace('f/', '', $a));
    $b_value = floatval(str_replace('f/', '', $b));
    return $a_value <=> $b_value;
});
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>絞り込み</title>
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
            width: 100%;
            height: 100%;
        }
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

        .main-area {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            padding: 20px;
        }

        .filter-section {
            width: 100%;
            max-width: 800px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .filter-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .filter-item {
            margin-bottom: 20px;
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
            padding: 10px;
        }

        .button {
            padding: 8px 12px;
            font-size: 14px;
            color: #ffffff;
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
            background-color: #cc0000;
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

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function validateForm() {
        const dateStart = new Date(document.getElementById('date_start').value);
        const dateEnd = new Date(document.getElementById('date_end').value);

        if (dateStart && dateEnd && dateStart > dateEnd) {
            alert('終了日は開始日より後の日付を選択してください。');
            return false;
        }
        return true;
    }

    </script>
</head>
<body>
<main class="main-area">
<form onsubmit="return validateForm()" action="narrowed.php" method="POST">
        <div class="filter-section">
            <div class="filter-title">絞り込み項目</div>
            <div class="filter-item">
    <label>カメラ (メーカー)</label>
    <div class="checkbox-group">
        <?php
        foreach ($camera_makers as $maker) {
            echo '<label><input type="checkbox" name="camera_maker[]" value="' . htmlspecialchars($maker) . '">' . htmlspecialchars($maker) . '</label>';
        }
        ?>
    </div>
</div>

<div class="filter-item">
    <label>カメラ (型番)</label>
    <div class="checkbox-group">
        <?php foreach ($camera_models as $model): ?>
            <label><input type="checkbox" name="camera_model[]" value="<?php echo htmlspecialchars($model); ?>"><?php echo htmlspecialchars($model); ?></label>
        <?php endforeach; ?>
    </div>
</div>
     <!-- レンズ (型番) -->
            <div class="filter-item">
                <label>レンズ (型番)</label>
                <div class="checkbox-group">
                    <?php
                    foreach ($lens_options as $lens) {
                        echo '<label><input type="checkbox" name="lens_model[]" value="' . $lens . '">' . $lens . '</label>';
                    }
                    ?>
                </div>
            </div>

            <!-- F値範囲 -->
            <div class="filter-item">
                <label>F値</label>
                <div class="checkbox-group">
                    <?php 
                    // F値を小さい順にソートして表示
                    usort($f_values, function($a, $b) {
                        $a_value = floatval(str_replace('f/', '', $a)); // "f/" を削除して数値化
                        $b_value = floatval(str_replace('f/', '', $b)); // "f/" を削除して数値化
                        return $a_value <=> $b_value; // 数値として比較
                    });                    
                    foreach ($f_values as $f_value): ?>
                        <label><input type="checkbox" name="ApertureFNumber[]" value="<?php echo htmlspecialchars($f_value); ?>"><?php echo htmlspecialchars($f_value); ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-item">
            <label>シャッタースピード</label>
            <div class="checkbox-group">
                <?php foreach ($exposure_options as $exposure_time): ?>
                    <label>
                        <input type="checkbox" name="exposure_time[]" value="<?php echo htmlspecialchars($exposure_time); ?>">
                        <?php echo htmlspecialchars($exposure_time); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
                <!-- 焦点距離 -->
            <div class="filter-item">
                <label>焦点距離</label>
                <div class="checkbox-group">
                    <?php
                    foreach ($focal_options as $focal_length) {
                        echo '<label><input type="checkbox" name="focal_length[]" value="' . htmlspecialchars($focal_length) . '">' . htmlspecialchars($focal_length) . 'mm</label>';
                    }
                    ?>                    
                </div>
            </div>
                <script>
                function updateOptions(selectId1, selectId2, compareDirection) {
                const select1 = document.getElementById(selectId1);
                const select2 = document.getElementById(selectId2);
                const value1 = parseFloat(select1.value);

                for (let option of select2.options) {
                    option.disabled = compareDirection === 'min'
                        ? parseFloat(option.value) < value1
                        : parseFloat(option.value) > value1;
                }

                if ((compareDirection === 'min' && parseFloat(select2.value) < value1) ||
                    (compareDirection === 'max' && parseFloat(select2.value) > value1)) {
                    select2.value = value1;
                }
            }

                </script>
                <!-- 日付範囲 -->
            <div class="filter-item">
                <label for="date_start">撮影日 (開始)</label>
                <input type="date" name="date_start" id="date_start" onchange="document.getElementById('date_end').min = this.value;">

                <label for="date_end">撮影日 (終了)</label>
                <input type="date" name="date_end" id="date_end" onchange="document.getElementById('date_start').max = this.value;">
            </div>
        </div>


            <!-- ボタン -->
            <a href="photos.php" class="back-button">戻る</a>
            <div class="filter-buttons">
                <button type="reset" class="button button-reset">リセット</button>
                <button type="submit" class="button button-apply">適用</button>
            </div>
        </form>
    </main>
    <script>
     <input type="date" name="date_start" id="date_start" max="">
     <input type="date" name="date_end" id="date_end" min="">

    </script>
</body>
</html>
<?php
// Close the database connection
$pdo = null;
?>