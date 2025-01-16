<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "j10img";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = intval($_GET['id']);
$sql = "SELECT image_content FROM j10images WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($image_content);
$stmt->fetch();
$stmt->close();
$conn->close();

header("Content-type: image/jpeg");
echo $image_content;
?>