<?php

const DB_HOST = 'j-10rds.c7ai6w8ievto.us-east-1.rds.amazonaws.com'; // ホスト名
const DB_PORT = 3306; // ポート番号
const DB_NAME = 'users'; // データベース名
const DB_USER = 'admin'; // ユーザー名
const DB_PASSWORD = '5gpAwwQBuuFgXpr'; // パスワード

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    echo 'ERROR: Could not connect.'.$e->getMessage()."\n";
    exit();
}