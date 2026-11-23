<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $connect = new mysqli(
        getenv('FOOD_DB_HOST') ?: 'localhost',
        getenv('FOOD_DB_USER') ?: 'root',
        getenv('FOOD_DB_PASSWORD') ?: '',
        'foodshipping'
    );
    $connect->set_charset('utf8mb4');
} catch (mysqli_sql_exception $loi) {
    error_log($loi->getMessage());
    http_response_code(500);
    exit('Không kết nối được CSDL foodshipping. Kiểm tra cấu hình connect.php và nhập foodshipping.sql.');
}
