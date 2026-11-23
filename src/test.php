<?php
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Kiểm tra kết nối Docker PHP & MySQL</h2>";

// 1. Kiểm tra CSDL quanlynhansu
$conn1 = new mysqli('db', 'root', 'root', 'quanlynhansu');
if ($conn1->connect_error) {
    echo "<p style='color:red;'>❌ Kết nối 'quanlynhansu' thất bại: " . $conn1->connect_error . "</p>";
} else {
    echo "<p style='color:green;'>✅ Kết nối 'quanlynhansu' thành công!</p>";
    $conn1->close();
}

// 2. Kiểm tra CSDL qlsp (Dùng cho Bài 5)
$conn2 = new mysqli('db', 'root', 'root', 'qlsp');
if ($conn2->connect_error) {
    echo "<p style='color:red;'>❌ Kết nối 'qlsp' thất bại: " . $conn2->connect_error . "</p>";
} else {
    echo "<p style='color:green;'>✅ Kết nối 'qlsp' thành công!</p>";
    
    // Kiểm tra bảng login
    $result = $conn2->query("SELECT * FROM login");
    if ($result) {
        echo "<p style='color:green;'>✅ Bảng 'login' đã sẵn sàng! Số tài khoản hiện có: " . $result->num_rows . "</p>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>Username: <strong>" . htmlspecialchars($row['username']) . "</strong> | Password: <strong>" . htmlspecialchars($row['password']) . "</strong></li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>❌ Không tìm thấy bảng login: " . $conn2->error . "</p>";
    }
    $conn2->close();
}

echo "<hr><p><a href='login.html'>👉 Đến trang Bài 5 (login.html)</a> | <a href='index.php'>Về trang chủ</a></p>";
?>
