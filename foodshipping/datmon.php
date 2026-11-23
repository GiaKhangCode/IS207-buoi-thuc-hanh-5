<?php
require 'chung.php';
$taiKhoan = yeuCauDangNhap();

if ($taiKhoan['QUANLY']) {
    header('Location: donhang.php');
    exit;
}

require 'connect.php';

$monChinh = [];
$topping = ['Topping món ăn' => [], 'Topping nước uống' => []];
$loaiTopping = ['Món ăn' => 'Topping món ăn', 'Nước uống' => 'Topping nước uống'];

$ketQua = $connect->query(
    "SELECT MAMA, TENMA, DONGIA, LOAIMA FROM MONAN
     ORDER BY FIELD(LOAIMA, 'Nước uống', 'Món ăn', 'Khuyến mãi', 'Topping nước uống', 'Topping món ăn'), MAMA"
);

while ($mon = $ketQua->fetch_assoc()) {
    if (isset($topping[$mon['LOAIMA']])) {
        $topping[$mon['LOAIMA']][] = $mon;
    } else {
        $monChinh[] = $mon;
    }
}

$loi = '';
$ghiChu = '';
$soLuongNhap = [];
$tongTien = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    kiemTraToken();

    $ghiChu = trim((string) filter_input(INPUT_POST, 'ghichu'));
    $soLuongNhap = $_POST['sl'] ?? [];
    $chiTiet = [];
    $coMonChinh = false;

    foreach ($monChinh as $mon) {
        $maMon = $mon['MAMA'];
        $loaiKem = $loaiTopping[$mon['LOAIMA']] ?? '';
        $cacDong = array_merge([$mon], $topping[$loaiKem] ?? []);
        $daChonMon = false;

        foreach ($cacDong as $dong) {
            $ma = $dong['MAMA'];
            $giaTri = $soLuongNhap[$maMon][$ma] ?? '';
            if ($giaTri === '') {
                continue;
            }

            if (!is_string($giaTri) || !ctype_digit($giaTri) || (int) $giaTri > 999) {
                $loi = 'Số lượng phải là số nguyên từ 0 đến 999.';
                break 2;
            }
            $sl = (int) $giaTri;
            if ($sl === 0) {
                continue;
            }

            $maKem = null;
            if ($ma === $maMon) {
                $daChonMon = true;
                $coMonChinh = true;
            } else {
                if (!$daChonMon) {
                    $loi = 'Vui lòng chọn ' . $mon['TENMA'] . ' trước khi chọn topping cho món này.';
                    break 2;
                }
                $maKem = $maMon;
            }

            $donGia = (int) $dong['DONGIA'];
            $tongTien += $donGia * $sl;
            $chiTiet[] = [$ma, $maKem, $sl, $donGia];
        }
    }

    if ($loi === '' && !$coMonChinh) {
        $loi = 'Vui lòng chọn ít nhất một món.';
    }
    if ($loi === '' && mb_strlen($ghiChu) > 500) {
        $loi = 'Ghi chú tối đa 500 ký tự.';
    }

    if ($loi === '') {
        $maHD = 'HD' . strtoupper(bin2hex(random_bytes(6)));
        $maKH = $taiKhoan['MAKH'];
        $ngayHD = date('Y-m-d');

        $connect->begin_transaction();
        try {
            $stmt = $connect->prepare(
                'INSERT INTO HOADON (MAHD, MAKH, THT, NGAYHD, GHICHU) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('ssiss', $maHD, $maKH, $tongTien, $ngayHD, $ghiChu);
            $stmt->execute();
            $stmt->close();

            $stmt = $connect->prepare(
                'INSERT INTO CTHD (MAHD, MAMA, MAK, SL, DONGIA) VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($chiTiet as [$ma, $maKem, $sl, $donGia]) {
                $stmt->bind_param('sssii', $maHD, $ma, $maKem, $sl, $donGia);
                $stmt->execute();
            }
            $stmt->close();
            $connect->commit();
            $connect->close();

            $_SESSION['donVuaDat'] = $maHD;
            header('Location: hoadon.php?mahd=' . rawurlencode($maHD), true, 303);
            exit;
        } catch (mysqli_sql_exception $ngoaiLe) {
            $connect->rollback();
            error_log($ngoaiLe->getMessage());
            $loi = 'Chưa lưu được đơn hàng. Vui lòng thử lại.';
        }
    }
}
$connect->close();

dauTrang('Đặt món');
?>

<p>Khách hàng <strong><?= h($taiKhoan['HOTEN']) ?></strong></p>
<?php if ($loi): ?>
    <p class="loi" role="alert"><?= h($loi) ?></p>
<?php endif; ?>

<form action="datmon.php" method="post" id="form-dat-mon">
    <input type="hidden" name="token" value="<?= h($_SESSION['token']) ?>">
    <div class="bang-cuon">
        <table>
            <thead>
                <tr><th scope="col">Món ăn / Nước uống</th><th scope="col">Đơn giá</th><th scope="col">Số lượng</th><th scope="col">Tạm tính</th></tr>
            </thead>
            <?php foreach ($monChinh as $mon): ?>
                <?php
                $maMon = $mon['MAMA'];
                $loaiKem = $loaiTopping[$mon['LOAIMA']] ?? '';
                $cacDong = array_merge([$mon], $topping[$loaiKem] ?? []);
                ?>
                <tbody>
                    <?php foreach ($cacDong as $dong): ?>
                        <?php
                        $ma = $dong['MAMA'];
                        $laTopping = $ma !== $maMon;
                        $giaTri = $soLuongNhap[$maMon][$ma] ?? '0';
                        $giaTri = is_scalar($giaTri) ? $giaTri : '0';
                        $tenNhan = 'Số lượng ' . $dong['TENMA'];
                        if ($laTopping) {
                            $tenNhan .= ' cho ' . $mon['TENMA'];
                        }
                        ?>
                        <tr class="<?= $laTopping ? 'topping' : 'mon-chinh' ?>">
                            <th scope="row"><?= h($dong['TENMA']) ?></th>
                            <td class="so"><?= tien($dong['DONGIA']) ?> đ</td>
                            <td class="so">
                                <input type="number" name="sl[<?= h($maMon) ?>][<?= h($ma) ?>]"
                                    min="0" max="999" step="1" value="<?= h($giaTri) ?>"
                                    data-gia="<?= h($dong['DONGIA']) ?>" aria-label="<?= h($tenNhan) ?>">
                            </td>
                            <td class="so" data-tam-tinh>0 đ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php endforeach; ?>
            <tfoot>
                <tr><th scope="row" colspan="3">Tổng tạm tính</th><td class="so" id="tong-tien">0 đ</td></tr>
            </tfoot>
        </table>
    </div>
    <div class="dat-hang">
        <label for="ghi-chu">Ghi chú giao hàng</label>
        <textarea id="ghi-chu" name="ghichu" rows="3" maxlength="500"><?= h($ghiChu) ?></textarea>
        <button type="submit">Đặt</button>
    </div>
</form>

<script>
    const formDatMon = document.querySelector('#form-dat-mon');
    const cacDong = formDatMon.querySelectorAll('tbody tr');
    const dinhDangSo = new Intl.NumberFormat('vi-VN');

    function tinhTien() {
        let tong = 0;
        for (const dong of cacDong) {
            const oSoLuong = dong.querySelector('input');
            const soLuong = oSoLuong.validity.valid ? (oSoLuong.valueAsNumber || 0) : 0;
            const tamTinh = soLuong * Number(oSoLuong.dataset.gia);
            dong.querySelector('[data-tam-tinh]').textContent = dinhDangSo.format(tamTinh) + ' đ';
            tong += tamTinh;
        }
        document.querySelector('#tong-tien').textContent = dinhDangSo.format(tong) + ' đ';
    }

    formDatMon.addEventListener('input', tinhTien);
    tinhTien();
</script>

<?php cuoiTrang(); ?>
