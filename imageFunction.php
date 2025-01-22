<?php
define('DB_HOST', 'j-10rds.c7ai6w8ievto.us-east-1.rds.amazonaws.com'); // ホスト名
define('DB_PORT', 3306); // ポート番号
define('DB_NAME', 'J-10'); // データベース名
define('DB_USER', 'admin'); // ユーザー名
define('DB_PASSWORD', '5gpAwwQBuuFgXpr'); // パスワード

function connectDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        return $pdo;
    } catch (PDOException $e) {
        exit('接続失敗: ' . $e->getMessage());
    }
}
?>