<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function baoLoi(int $ma, string $noiDung): void
{
    http_response_code($ma);
    echo json_encode(['error' => $noiDung], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    baoLoi(405, 'Yêu cầu phải dùng phương thức POST.');
}

require __DIR__ . '/session.php';

$token = (string) filter_input(INPUT_POST, 'token');
if (!hash_equals($_SESSION['giohang_token'], $token)) {
    baoLoi(403, 'Phiên giỏ hàng đã thay đổi. Hãy tải lại trang.');
}

$maSP = trim((string) filter_input(INPUT_POST, 'masp'));
$soLuong = filter_input(INPUT_POST, 'soluong', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 9999],
]);

if ($maSP === '' || !$soLuong) {
    baoLoi(400, 'Hãy chọn sản phẩm và nhập số lượng từ 1 đến 9.999.');
}

require __DIR__ . '/../sanpham/connect.php';

try {
    $statement = $db->prepare('SELECT MASP, TENSP, DVT, GIA FROM sanpham WHERE MASP = ?');
    $statement->bind_param('s', $maSP);
    $statement->execute();
    $sanPham = $statement->get_result()->fetch_assoc();
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    baoLoi(500, 'Không đọc được thông tin sản phẩm.');
}

if (!$sanPham) {
    baoLoi(404, 'Sản phẩm không còn trong danh sách.');
}

$maSP = $sanPham['MASP'];
$sanPham['GIA'] = (int) $sanPham['GIA'];
$soLuongMoi = ($_SESSION['giohang'][$maSP]['SL'] ?? 0) + $soLuong;
if ($soLuongMoi > 9999) {
    baoLoi(400, 'Mỗi sản phẩm trong giỏ có tối đa 9.999 đơn vị.');
}

$_SESSION['giohang'][$maSP] ??= $sanPham;
$_SESSION['giohang'][$maSP]['SL'] = $soLuongMoi;
$cartCount = count($_SESSION['giohang']);
session_write_close();

echo json_encode(['count' => $cartCount]);
