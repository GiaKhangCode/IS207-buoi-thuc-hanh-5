<?php
declare(strict_types=1);

// Tự động tương thích Docker (root/root hoặc root/'') và XAMPP (localhost)
$configs = [
    ['db', 'root', 'root', 'qlsp'],
    ['db', 'root', '', 'qlsp'],
    ['localhost', 'root', '', 'qlsp'],
    ['localhost', 'root', 'root', 'qlsp'],
];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$db = null;
foreach ($configs as [$servername, $username, $password, $dbname]) {
    try {
        $db = new mysqli($servername, $username, $password, $dbname);
        $db->set_charset('utf8mb4');
        break;
    } catch (mysqli_sql_exception $exception) {
        $db = null;
    }
}

if (!$db) {
    http_response_code(500);
    exit('Không thể kết nối CSDL. Hãy kiểm tra MySQL và cấu hình trong connect.php.');
}
