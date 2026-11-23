<?php
declare(strict_types=1);

require __DIR__ . '/connect.php';

header('Content-Type: application/json; charset=utf-8');

function reply(bool $success, string $message, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(false, 'Yêu cầu không hợp lệ.', 405);
}

$masp    = trim((string)($_POST['masp'] ?? ''));
$tensp   = trim((string)($_POST['tensp'] ?? ''));
$nuocsx  = trim((string)($_POST['nuocsx'] ?? ''));
$dvt     = trim((string)($_POST['dvt'] ?? ''));
$gia     = $_POST['gia'] ?? '';
$loaisp  = trim((string)($_POST['loaisp'] ?? ''));
$hinhanh = trim((string)($_POST['hinhanh'] ?? ''));

if ($masp === '' || $tensp === '' || $nuocsx === '' || $dvt === '' || $loaisp === '' || $hinhanh === ''
    || filter_var($gia, FILTER_VALIDATE_INT) === false || (int)$gia < 0) {
    reply(false, 'Thêm sản phẩm không thành công! Vui lòng nhập đầy đủ và đúng thông tin.', 400);
}
$gia = (int)$gia;

try {
    // Prepared statement: tham số được bind riêng, tránh SQL injection.
    $stmt = $db->prepare(
        'INSERT INTO sanpham (MASP, TENSP, DVT, NUOCSX, GIA, HINHANH, LOAISP) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('ssssiss', $masp, $tensp, $dvt, $nuocsx, $gia, $hinhanh, $loaisp);
    $stmt->execute();
    $stmt->close();
    reply(true, 'Thêm sản phẩm thành công!');
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        reply(false, 'Thêm không thành công! Mã sản phẩm đã tồn tại.', 409);
    }
    reply(false, 'Thêm sản phẩm không thành công!', 500);
}
