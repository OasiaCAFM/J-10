<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iPhone風写真一覧</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f0f0f5;
            margin: 0;
            padding: 0;
            display: flex;
        }
        /* サイドバー（ハンバーガーメニュー） */
        .sidebar {
            width: 60px;
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
            transition: width 0.3s ease;
        }
        .sidebar.closed {
            width: 60px;
        }
        .sidebar.open {
            width: 200px;
        }
        .menu-toggle {
            font-size: 30px;
            cursor: pointer;
            padding: 10px;
            margin-bottom: 10px;
            display: block;
        }
        .menu-item {
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding-left: 20px;
            font-size: 18px;
            color: #333;
            cursor: pointer;
            transition: color 0.3s;
            overflow: hidden;
        }
        .menu-item i {
            margin-right: 10px;
            font-size: 20px;
            width: 24px;
            height: 24px;
        }

        /* Apple風シンプルアイコンデザイン */
        .camera-icon {
            width: 24px;
            height: 24px;
            border: 2px solid black;
            border-radius: 6px;
            position: relative;
            background-color: #f9f9f9;
        }
        .camera-icon::before {
            content: "";
            display: block;
            width: 10px;
            height: 10px;
            background: black;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .folder-icon {
            width: 24px;
            height: 18px;
            border: 2px solid black;
            border-radius: 4px;
            background-color: #f9f9f9;
            position: relative;
        }
        .folder-icon::before {
            content: "";
            display: block;
            width: 10px;
            height: 4px;
            background-color: black;
            position: absolute;
            top: -6px;
            left: 3px;
            border-radius: 2px 2px 0 0;
        }

        /* 白黒カラーの立体的な段ボール型アイコン */
        .box-icon {
            width: 24px;
            height: 18px;
            background-color: #f0f0f0; /* 明るいグレーで箱のベース */
            border: 2px solid #333; /* 濃いグレーで枠線 */
            border-radius: 2px;
            position: relative;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2); /* 影をつけて立体感 */
        }
        .box-icon::before {
            content: "";
            display: block;
            width: 24px;
            height: 8px;
            background-color: #e0e0e0; /* 蓋部分を箱より少し暗く */
            position: absolute;
            top: -6px;
            left: 0;
            border-bottom: 2px solid #333; /* 枠線 */
            border-radius: 2px 2px 0 0;
            box-shadow: 0 -2px 3px rgba(0, 0, 0, 0.15); /* 蓋の影 */
        }
        .box-icon::after {
            content: "";
            display: block;
            width: 12px;
            height: 6px;
            background-color: #333; /* 蓋を開けたときの内側（濃いグレー） */
            position: absolute;
            top: -6px;
            left: 6px;
            box-shadow: inset 0 -2px 2px rgba(0, 0, 0, 0.2); /* 内側の影で深さを表現 */
        }

        .menu-item span {
            display: none;
        }
        .sidebar.open .menu-item span {
            display: inline-block;
        }
        .menu-item:hover {
            color: #007bff;
        }
        .main-content {
            margin-left: 80px;
            padding: 20px;
            width: 100%;
            transition: margin-left 0.3s ease;
        }
        .sidebar.open + .main-content {
            margin-left: 200px;
        }
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        .photo-item {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .photo-item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }
        .photo-thumb {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .photo-info {
            padding: 10px;
            text-align: center;
        }
        .photo-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0;
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
    </style>
</head>
<body>

<!-- サイドバー -->
<div class="sidebar closed" id="sidebar">
    <div class="menu-toggle" onclick="toggleSidebar()">☰</div>
    <div class="menu-item" onclick="toggleSubmenu('photoSubmenu')">
        <i class="camera-icon"></i><span>写真</span>
    </div>
    <div class="menu-item" onclick="toggleSubmenu('albumSubmenu')">
        <i class="folder-icon"></i><span>アルバム</span>
    </div>
    <div class="menu-item">
        <i class="box-icon"></i><span>絞り込み</span>
    </div>
</div>

<!-- 写真追加ボタン -->
<button class="add-photo-btn" onclick="addPhoto()">写真を追加</button>

<!-- メインコンテンツ -->
<div class="main-content">
<h1 style="font-size: 24px; font-weight: bold; text-align: center;">photos</h1>

    <div class="photo-grid" id="photoGrid">
        <!-- 写真1 -->
        <div class="photo-item">
            <img src="https://via.placeholder.com/150" alt="写真1" class="photo-thumb">
            <div class="photo-info">
                <p class="photo-title">写真1</p>
            </div>
        </div>
        <!-- 写真2 -->
        <div class="photo-item">
            <img src="https://via.placeholder.com/150" alt="写真2" class="photo-thumb">
            <div class="photo-info">
                <p class="photo-title">写真2</p>
            </div>
        </div>
        <!-- 写真3 -->
        <div class="photo-item">
            <img src="https://via.placeholder.com/150" alt="写真3" class="photo-thumb">
            <div class="photo-info">
                <p class="photo-title">写真3</p>
            </div>
        </div>
        <!-- 写真4 -->
        <div class="photo-item">
            <img src="https://via.placeholder.com/150" alt="写真4" class="photo-thumb">
            <div class="photo-info">
                <p class="photo-title">写真4</p>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        var sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('open');
    }
    function addPhoto() {
        var photoGrid = document.getElementById('photoGrid');
        var newPhoto = document.createElement('div');
        newPhoto.className = 'photo-item';
        newPhoto.innerHTML = `
            <img src="https://via.placeholder.com/150" alt="新しい写真" class="photo-thumb">
            <div class="photo-info">
                <p class="photo-title">新しい写真</p>
            </div>
        `;
        photoGrid.appendChild(newPhoto);
    }
</script>

</body>
</html>
