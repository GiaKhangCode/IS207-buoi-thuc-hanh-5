<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $connect = new mysqli(
        getenv('THUEXE_DB_HOST') ?: 'localhost',
        getenv('THUEXE_DB_USER') ?: 'root',
        getenv('THUEXE_DB_PASSWORD') ?: '',
        'thuexe'
    );
    $connect->set_charset('utf8mb4');
} catch (mysqli_sql_exception $loi) {
    error_log($loi->getMessage());
    http_response_code(500);
    $thongBao = 'Không kết nối được CSDL thuexe. Kiểm tra connect.php và nhập thuexe.sql.';
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['loi' => $thongBao], JSON_UNESCAPED_UNICODE));
    }
    exit($thongBao);
}
