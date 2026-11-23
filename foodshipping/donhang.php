<?php
require 'chung.php';
$taiKhoan = yeuCauDangNhap();
require 'connect.php';

$laQuanLy = $taiKhoan['QUANLY'];
$danhSachKH = [];

if ($laQuanLy) {
    $ketQua = $connect->query(
        'SELECT MAKH, CONCAT(HO, " ", TEN) AS HOTEN FROM KHACHHANG ORDER BY TEN, HO, MAKH'
    );
    while ($khachHang = $ketQua->fetch_assoc()) {
        $danhSachKH[$khachHang['MAKH']] = $khachHang['HOTEN'];
    }
} else {
    $danhSachKH[$taiKhoan['MAKH']] = $taiKhoan['HOTEN'];
}

$maKH = (string) filter_input(INPUT_GET, 'makh');
if (!isset($danhSachKH[$maKH])) {
    $maKH = array_key_first($danhSachKH) ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    kiemTraToken();
    $maHD = (string) filter_input(INPUT_POST, 'mahd');
    $stmt = $connect->prepare('DELETE FROM HOADON WHERE MAHD = ? AND MAKH = ?');
    $stmt->bind_param('ss', $maHD, $maKH);
    $stmt->execute();
    $_SESSION['thongbao'] = $stmt->affected_rows
        ? 'Đã xóa đơn hàng ' . $maHD . '.'
        : 'Không tìm thấy đơn hàng của khách hàng đang chọn.';
    $stmt->close();
    $connect->close();

    header('Location: donhang.php?makh=' . rawurlencode($maKH), true, 303);
    exit;
}

$hoaDon = [];
if ($maKH !== '') {
    $stmt = $connect->prepare('SELECT MAHD, NGAYHD FROM HOADON WHERE MAKH = ? ORDER BY NGAYHD DESC, MAHD');
    $stmt->bind_param('s', $maKH);
    $stmt->execute();
    $hoaDon = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$connect->close();

$thongBao = $_SESSION['thongbao'] ?? '';
unset($_SESSION['thongbao']);

dauTrang('Danh sách đơn hàng', 'trang-don-hang');
?>

<?php if ($thongBao): ?>
    <p class="thong-bao" role="status"><?= h($thongBao) ?></p>
<?php endif; ?>

<div class="khung">
    <?php if ($laQuanLy && $danhSachKH): ?>
        <form method="get" action="donhang.php" class="chon-khach">
            <label for="khach-hang">Khách hàng</label>
            <select id="khach-hang" name="makh" onchange="this.form.submit()">
                <?php foreach ($danhSachKH as $ma => $hoTen): ?>
                    <option value="<?= h($ma) ?>" <?= $ma === $maKH ? 'selected' : '' ?>><?= h($hoTen) ?></option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit">Xem</button></noscript>
        </form>
    <?php elseif (!$laQuanLy): ?>
        <p>Khách hàng: <?= h($taiKhoan['HOTEN']) ?></p>
    <?php endif; ?>

    <?php if ($maKH === ''): ?>
        <p>Chưa có khách hàng.</p>
    <?php else: ?>
        <p>Mã khách hàng: <?= h($maKH) ?></p>
        <div class="bang-cuon">
            <table>
                <thead>
                    <tr><th scope="col">STT</th><th scope="col">Đơn hàng</th><th scope="col">Ngày HĐ</th><th scope="col">Chức năng</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($hoaDon as $viTri => $don): ?>
                        <tr>
                            <td><?= $viTri + 1 ?></td>
                            <td><a href="hoadon.php?mahd=<?= rawurlencode($don['MAHD']) ?>"><?= h($don['MAHD']) ?></a></td>
                            <td><?= ngay($don['NGAYHD']) ?></td>
                            <td>
                                <form method="post" action="donhang.php?makh=<?= rawurlencode($maKH) ?>"
                                    onsubmit="return confirm('Xóa đơn hàng này?')">
                                    <input type="hidden" name="token" value="<?= h($_SESSION['token']) ?>">
                                    <input type="hidden" name="mahd" value="<?= h($don['MAHD']) ?>">
                                    <button class="lien-ket" type="submit" aria-label="Xóa đơn <?= h($don['MAHD']) ?>">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$hoaDon): ?>
                        <tr><td colspan="4">Khách hàng chưa có đơn hàng.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php cuoiTrang(); ?>
