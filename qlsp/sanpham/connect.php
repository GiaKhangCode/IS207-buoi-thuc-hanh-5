<?php
declare(strict_types=1);

// Thông tin kết nối MySQL mặc định của XAMPP.
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'qlsp';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = new mysqli($servername, $username, $password, $dbname);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    exit('Không thể kết nối CSDL. Hãy kiểm tra MySQL và cấu hình trong connect.php.');
}
