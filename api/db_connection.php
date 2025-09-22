<?php
// db_connection.php
// 設定資料庫連線參數
$servername = "13.230.122.24"; // Or your host, e.g., "localhost"
$username = "root"; // Your database username
$password = "new_secure_password"; // Your database password
$dbname = "delly_dougan"; // Your database name

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 設定連線的字元集為 utf8mb4，以支援中文
$conn->set_charset("utf8mb4");
?>
