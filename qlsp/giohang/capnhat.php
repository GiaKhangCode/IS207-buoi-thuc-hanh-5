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

$hanhDong = (string) filter_input(INPUT_POST, 'action');
$maSP = trim((string) filter_input(INPUT_POST, 'masp'));

if (!isset($_SESSION['giohang'][$maSP])) {
    baoLoi(404, 'Sản phẩm không còn trong giỏ hàng.');
}

if ($hanhDong === 'update') {
    $soLuong = filter_input(INPUT_POST, 'soluong', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 9999],
    ]);
    if (!$soLuong) {
        baoLoi(400, 'Số lượng phải từ 1 đến 9.999.');
    }
    $_SESSION['giohang'][$maSP]['SL'] = $soLuong;
} elseif ($hanhDong === 'delete') {
    unset($_SESSION['giohang'][$maSP]);
} else {
    baoLoi(400, 'Yêu cầu không hợp lệ.');
}

$tong = 0;
foreach ($_SESSION['giohang'] as $sp) {
    $tong += $sp['GIA'] * $sp['SL'];
}

$ketQua = [
    'count' => count($_SESSION['giohang']),
    'total' => $tong,
];
if ($hanhDong === 'update') {
    $ketQua['lineTotal'] = $_SESSION['giohang'][$maSP]['GIA'] * $_SESSION['giohang'][$maSP]['SL'];
    $ketQua['qty'] = $_SESSION['giohang'][$maSP]['SL'];
}
session_write_close();

echo json_encode($ketQua);
