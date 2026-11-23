<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function traLoiLoi(int $ma, string $noiDung): void
{
    http_response_code($ma);
    echo json_encode(['success' => false, 'error' => $noiDung], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    traLoiLoi(405, 'Yêu cầu phải dùng phương thức POST.');
}

require __DIR__ . '/session.php';

$token = (string) filter_input(INPUT_POST, 'token');
if (!hash_equals($_SESSION['giohang_token'], $token)) {
    traLoiLoi(403, 'Phiên làm việc đã thay đổi. Vui lòng tải lại trang.');
}

$gioHang = $_SESSION['giohang'];
if (empty($gioHang)) {
    traLoiLoi(400, 'Giỏ hàng đang trống, không thể thanh toán.');
}

$hoTen = trim((string) filter_input(INPUT_POST, 'hoten'));
$sdt = trim((string) filter_input(INPUT_POST, 'sdt'));
$email = trim((string) filter_input(INPUT_POST, 'email'));
$diaChi = trim((string) filter_input(INPUT_POST, 'diachi'));
$tinh = trim((string) filter_input(INPUT_POST, 'tinh'));
$quan = trim((string) filter_input(INPUT_POST, 'quan'));
$phuong = trim((string) filter_input(INPUT_POST, 'phuong'));

if ($hoTen === '' || $sdt === '' || $email === '' || $diaChi === '' || $tinh === '' || $quan === '' || $phuong === '') {
    traLoiLoi(400, 'Vui lòng nhập đầy đủ tất cả thông tin giao hàng và nhận hàng.');
}

if (!preg_match('/^[0-9+\-\s]{8,15}$/', $sdt)) {
    traLoiLoi(400, 'Số điện thoại không hợp lệ (8-15 chữ số).');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    traLoiLoi(400, 'Địa chỉ email không hợp lệ.');
}

/**
 * Kết nối CSDL qlsp tương thích cả Docker (root/root hoặc root/'') lẫn XAMPP (localhost).
 */
function ketNoiQlsp(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $configs = [
        ['db', 'root', 'root', 'qlsp'],
        ['db', 'root', '', 'qlsp'],
        ['localhost', 'root', '', 'qlsp'],
        ['localhost', 'root', 'root', 'qlsp'],
    ];

    $lastError = null;
    foreach ($configs as [$host, $user, $pass, $dbname]) {
        try {
            $conn = new mysqli($host, $user, $pass, $dbname);
            $conn->set_charset('utf8mb4');
            return $conn;
        } catch (mysqli_sql_exception $e) {
            $lastError = $e;
        }
    }

    if ($lastError) {
        error_log($lastError->getMessage());
    }
    traLoiLoi(500, 'Không thể kết nối CSDL qlsp.');
    exit;
}

$db = ketNoiQlsp();

try {
    $db->begin_transaction();

    $thanhToan = 1;
    $stmtHd = $db->prepare(
        'INSERT INTO hoadon (NGHD, HOTENKH, SDT, EMAIL, DIACHI, TINH, QUAN, PHUONG, THANHTOAN)
         VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmtHd->bind_param('sssssssi', $hoTen, $sdt, $email, $diaChi, $tinh, $quan, $phuong, $thanhToan);
    $stmtHd->execute();
    $soHd = (int) $db->insert_id;
    $stmtHd->close();

    $stmtCthd = $db->prepare(
        'INSERT INTO cthd (SOHD, MASP, SL, DONGIA) VALUES (?, ?, ?, ?)'
    );

    $tongTien = 0;
    foreach ($gioHang as $ma => $sp) {
        $maSp = (string) ($sp['MASP'] ?? $ma);
        $soLuong = (int) ($sp['SL'] ?? 0);
        $donGia = (int) ($sp['GIA'] ?? 0);

        if ($maSp === '' || $soLuong < 1) {
            continue;
        }

        $stmtCthd->bind_param('isii', $soHd, $maSp, $soLuong, $donGia);
        $stmtCthd->execute();
        $tongTien += $soLuong * $donGia;
    }
    $stmtCthd->close();

    $db->commit();
} catch (mysqli_sql_exception $exception) {
    $db->rollback();
    error_log($exception->getMessage());
    traLoiLoi(500, 'Không thể lưu đơn hàng xuống CSDL. Vui lòng thử lại.');
} finally {
    $db->close();
}

// Xóa giỏ hàng trong Session sau khi thanh toán thành công
$_SESSION['giohang'] = [];
session_write_close();

echo json_encode([
    'success' => true,
    'sohd' => $soHd,
    'total' => $tongTien,
    'message' => 'Thanh toán đơn hàng thành công!',
], JSON_UNESCAPED_UNICODE);

