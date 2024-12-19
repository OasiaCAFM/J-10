<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iPhone風写真一覧 - 絞り込み</title>
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

        .radio-group {
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
    </style>
</head>
<body>
    <main class="main-area">
        <div class="filter-section">
            <div class="filter-title">絞り込み項目</div>

            <!-- カメラ (メーカー) -->
            <div class="filter-item">
                <label>カメラ (メーカー)</label>
                <div class="radio-group">
                    <label><input type="radio" name="camera-maker" value="canon">Canon</label>
                    <label><input type="radio" name="camera-maker" value="nikon">Nikon</label>
                    <label><input type="radio" name="camera-maker" value="sony">Sony</label>
                </div>
            </div>

            <!-- カメラ (型番) -->
            <div class="filter-item">
                <label>カメラ (型番)</label>
                <div class="radio-group">
                    <label><input type="radio" name="camera-model" value="eos-5d">EOS 5D</label>
                    <label><input type="radio" name="camera-model" value="d850">D850</label>
                    <label><input type="radio" name="camera-model" value="alpha-a7">α7</label>
                </div>
            </div>

            <!-- レンズ (メーカー) -->
            <div class="filter-item">
                <label>レンズ (メーカー)</label>
                <div class="radio-group">
                    <label><input type="radio" name="lens-maker" value="canon">Canon</label>
                    <label><input type="radio" name="lens-maker" value="nikon">Nikon</label>
                    <label><input type="radio" name="lens-maker" value="sony">Sony</label>
                </div>
            </div>

            <!-- レンズ (型番) -->
            <div class="filter-item">
                <label>レンズ (型番)</label>
                <div class="radio-group">
                    <label><input type="radio" name="lens-model" value="ef24-70mm">EF 24-70mm</label>
                    <label><input type="radio" name="lens-model" value="afs-50mm">AF-S 50mm</label>
                    <label><input type="radio" name="lens-model" value="gm-85mm">GM 85mm</label>
                </div>
            </div>

            <!-- タグ -->
            <div class="filter-item">
                <label>タグ</label>
                <div class="radio-group">
                    <label><input type="radio" name="tags" value="landscape">風景</label>
                    <label><input type="radio" name="tags" value="portrait">人物</label>
                </div>
            </div>

            <!-- F値範囲 -->
            <div class="filter-item">
                <label>F値範囲（明るさ）</label>
                <div class="radio-group">
                    <label><input type="radio" name="f-value" value="1.8">1.8以下</label>
                    <label><input type="radio" name="f-value" value="4.0">4.0以下</label>
                    <label><input type="radio" name="f-value" value="8.0">8以下</label>
                    <label><input type="radio" name="f-value" value="11.0">11以下</label>
                    <label><input type="radio" name="f-value" value="32.0">32以下</label>
                </div>
            </div>

            <!-- 焦点距離範囲 -->
            <div class="filter-item">
                <label>焦点距離範囲 (mm)</label>
                <div class="range-inputs">
                    <input type="number" id="focal-length-min" placeholder="18" min="18" max="600">
                    <span>–</span>
                    <input type="number" id="focal-length-max" placeholder="600" min="18" max="600">
                </div>
            </div>

            <!-- 日付範囲 -->
            <div class="filter-item">
                <label for="date-start">撮影日 (開始)</label>
                <input type="date" id="date-start">

                <label for="date-end">撮影日 (終了)</label>
                <input type="date" id="date-end">
            </div>
        </div>

        <!-- ボタン -->
        <div class="filter-buttons">
            <button class="button button-reset">リセット</button>
            <button class="button button-apply">適用</button>
        </div>
    </main>
</body>
</html>
