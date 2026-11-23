<?php
// File connect.php dùng chung cho các bài tập
$servername = "db";
$username = "root";
$password = "root";

// Mặc định kết nối CSDL quanlynhansu (dùng cho bài 1-4)
$dbname = "quanlynhansu";

$connect = new mysqli($servername, $username, $password, $dbname);

if ($connect->connect_error) {
    die("Lỗi kết nối CSDL: " . $connect->connect_error);
}

$connect->set_charset("utf8");
?>
