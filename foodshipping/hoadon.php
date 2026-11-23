<?php
require 'chung.php';
$taiKhoan = yeuCauDangNhap();
require 'connect.php';

$maHD = (string) filter_input(INPUT_GET, 'mahd');
$sql = 'SELECT hd.MAHD, hd.MAKH, hd.NGAYHD, hd.THT, hd.GHICHU,
               CONCAT(kh.HO, " ", kh.TEN) AS HOTEN, kh.DIACHI, kh.SDT,
               ct.MAMA, ct.MAK, ct.SL, ct.DONGIA, m.TENMA
        FROM HOADON hd
        JOIN KHACHHANG kh ON kh.MAKH = hd.MAKH
        LEFT JOIN CTHD ct ON ct.MAHD = hd.MAHD
        LEFT JOIN MONAN m ON m.MAMA = ct.MAMA
        WHERE hd.MAHD = ?';

if ($taiKhoan['QUANLY']) {
    $stmt = $connect->prepare($sql . ' ORDER BY ct.MACT');
    $stmt->bind_param('s', $maHD);
} else {
    $stmt = $connect->prepare($sql . ' AND hd.MAKH = ? ORDER BY ct.MACT');
    $stmt->bind_param('ss', $maHD, $taiKhoan['MAKH']);
}
$stmt->execute();
$cacDong = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$connect->close();

$hoaDon = $cacDong[0] ?? null;
if (!$hoaDon) {
    http_response_code(404);
    dauTrang('Hóa đơn');
    echo '<p>Không tìm thấy hóa đơn.</p>';
    cuoiTrang();
    exit;
}

$monChinh = [];
$topping = [];
foreach ($cacDong as $dong) {
    if ($dong['MAMA'] === null) {
        continue;
    }
    if ($dong['MAK'] === null) {
        $monChinh[] = $dong;
    } else {
        $topping[$dong['MAK']][] = $dong;
    }
}

$datThanhCong = ($_SESSION['donVuaDat'] ?? null) === $hoaDon['MAHD'];
if ($datThanhCong) {
    unset($_SESSION['donVuaDat']);
}
dauTrang('Chi tiết hóa đơn', 'trang-hoa-don');
?>

<?php if ($datThanhCong): ?>
    <p class="thong-bao" role="status">Đặt món thành công.</p>
<?php endif; ?>

<div class="khung">
    <dl class="thong-tin">
        <div><dt>Khách hàng:</dt> <dd><?= h($hoaDon['HOTEN']) ?></dd></div>
        <div><dt>Mã khách hàng:</dt> <dd><?= h($hoaDon['MAKH']) ?></dd></div>
        <div><dt>Mã đơn hàng:</dt> <dd><?= h($hoaDon['MAHD']) ?></dd></div>
        <div><dt>Ngày hóa đơn:</dt> <dd><?= ngay($hoaDon['NGAYHD']) ?></dd></div>
        <div><dt>Địa chỉ:</dt> <dd><?= h($hoaDon['DIACHI']) ?></dd></div>
        <div><dt>Số điện thoại:</dt> <dd><?= h($hoaDon['SDT']) ?></dd></div>
    </dl>

    <div class="bang-cuon">
        <table>
            <thead>
                <tr><th scope="col">STT</th><th scope="col">Món ăn</th><th scope="col">Số lượng</th><th scope="col">Đơn giá</th><th scope="col">Thành tiền</th></tr>
            </thead>
            <?php foreach ($monChinh as $viTri => $mon): ?>
                <?php $cacDongMon = array_merge([$mon], $topping[$mon['MAMA']] ?? []); ?>
                <tbody>
                    <?php foreach ($cacDongMon as $viTriKem => $dong): ?>
                        <tr class="<?= $viTriKem ? 'topping' : 'mon-chinh' ?>">
                            <td><?= $viTri + 1 ?><?= $viTriKem ? '.' . $viTriKem : '' ?></td>
                            <th scope="row"><?= h($dong['TENMA']) ?></th>
                            <td class="so"><?= h($dong['SL']) ?></td>
                            <td class="so"><?= tien($dong['DONGIA']) ?></td>
                            <td class="so"><?= tien((int) $dong['DONGIA'] * (int) $dong['SL']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php endforeach; ?>
        </table>
    </div>

    <?php if ($hoaDon['GHICHU'] !== ''): ?>
        <p class="ghi-chu">Ghi chú: <?= h($hoaDon['GHICHU']) ?></p>
    <?php endif; ?>
    <p class="tong-hoa-don">Tổng cộng: <?= tien($hoaDon['THT']) ?> đ</p>
</div>

<p><a href="donhang.php?makh=<?= rawurlencode($hoaDon['MAKH']) ?>">Về danh sách đơn hàng</a></p>
<?php cuoiTrang(); ?>
