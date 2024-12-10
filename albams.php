<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アルバム表示</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f0f0f5;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .main-content {
            margin: 20px auto;
            width: 80%;
        }

        .album-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 2列に設定 */
            gap: 15px;
        }

        .album-item {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
        }

        .album-item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 2列に設定 */
            gap: 5px;
            padding: 10px;
        }

        .album-thumb {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .album-info {
            padding: 10px;
            text-align: center;
        }

        .album-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .add-album-btn {
            display: block;
            margin: 20px auto;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .add-album-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="main-content">
    <h1 style="font-size: 24px; font-weight: bold; text-align: center;">アルバム</h1>
    <div class="album-grid" id="albumGrid">
        <!-- アルバム1 -->
        <div class="album-item">
            <div class="photo-grid">
                <img src="https://via.placeholder.com/150" alt="写真1" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真2" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真3" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真4" class="album-thumb">
            </div>
            <div class="album-info">
                <p class="album-title">アルバム1</p>
            </div>
        </div>
    </div>
    <button class="add-album-btn" onclick="addAlbum()">アルバムを追加</button>
</div>

<script>
    function addAlbum() {
        const albumGrid = document.getElementById('albumGrid');
        const newAlbum = document.createElement('div');
        newAlbum.className = 'album-item';
        newAlbum.innerHTML = `
            <div class="photo-grid">
                <img src="https://via.placeholder.com/150" alt="写真1" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真2" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真3" class="album-thumb">
                <img src="https://via.placeholder.com/150" alt="写真4" class="album-thumb">
            </div>
            <div class="album-info">
                <p class="album-title">新しいアルバム</p>
            </div>
        `;
        albumGrid.appendChild(newAlbum);
    }
</script>

</body>
</html>
