<?php
// Cấu hình kết nối MySQL trong Docker
// Server name là 'db' (tên service mysql trong docker-compose.yml)
$servername = "db";
$username = "root";
$password = "root";
$DBName = "qlsp";

// Tạo kết nối
$conn = mysqli_connect($servername, $username, $password, $DBName);

if (!$conn) {
    die("<p>Không thể connect CSDL</p><p>Error code: " . mysqli_connect_errno() . ": " . mysqli_connect_error() . "</p>");
}

mysqli_set_charset($conn, "utf8");

if (isset($_POST["name"]) && isset($_POST["pass"])) {
    $user = mysqli_real_escape_string($conn, $_POST["name"]);
    $pass = mysqli_real_escape_string($conn, $_POST["pass"]);

    $strSQL = "SELECT * FROM login WHERE username = '$user' AND password = '$pass'";
    $result = mysqli_query($conn, $strSQL);

    if ($result && mysqli_num_rows($result) > 0) {
        echo "Chúc mừng bạn đã đăng nhập thành công!!!";
    } else {
        echo "Đăng nhập không thành công. Vui lòng đăng nhập lại!!!";
    }
} else {
    echo "Thiếu thông tin đăng nhập!";
}

mysqli_close($conn);
?>
