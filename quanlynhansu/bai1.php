<?php
// Muc IV - Bai 1: Xem va cap nhat thong tin nhan vien.
session_start();
require_once __DIR__ . '/connect.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Token bao ve cac thao tac thay doi du lieu qua POST.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$thongBao = $_SESSION['thongBao'] ?? '';
$laLoi = $_SESSION['laLoi'] ?? false;
unset($_SESSION['thongBao'], $_SESSION['laLoi']);

$maDangXem = trim($_GET['view'] ?? '');
$giaTriNhap = null;

// Phong ban lay truc tiep tu CSDL, dung cho combobox trong form cap nhat.
$resultPB = $connect->query('SELECT MaPhong, TenPhong FROM PHONGBAN ORDER BY TenPhong');
$phongBan = $resultPB->fetch_all(MYSQLI_ASSOC);
$maPhongHopLe = array_column($phongBan, 'MaPhong');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenGui = $_POST['csrf_token'] ?? '';

    if (!is_string($tokenGui) || !hash_equals($csrfToken, $tokenGui)) {
        $thongBao = 'Phiên biểu mẫu không hợp lệ. Hãy tải lại trang.';
        $laLoi = true;
    } elseif (isset($_POST['btnCapNhat'])) {
        // MaNhanVien duoc lay tu URL, KHONG cho cap nhat ma nhan vien.
        $tenNV = trim($_POST['tenNV'] ?? '');
        $luong = trim($_POST['luong'] ?? '');
        $gioiTinh = isset($_POST['gioiTinh']) ? 1 : 0; // Chon = Nam, khong chon = Nu.
        $maPhong = trim($_POST['maPhong'] ?? '');

        // Giu lai du lieu nguoi dung vua nhap neu validation that bai.
        $giaTriNhap = [
            'TenNhanVien' => $tenNV,
            'LuongThang' => $luong,
            'GioiTinh' => $gioiTinh,
            'MaPhong' => $maPhong
        ];

        if ($maDangXem === '' || $tenNV === '' || $luong === '' || $maPhong === '') {
            $thongBao = 'Vui lòng nhập đầy đủ thông tin.';
            $laLoi = true;
        } elseif (!is_numeric($luong) || !is_finite((float)$luong) || (float)$luong < 0) {
            $thongBao = 'Lương phải là một số không âm.';
            $laLoi = true;
        } elseif (!in_array($maPhong, $maPhongHopLe, true)) {
            $thongBao = 'Phòng ban không hợp lệ.';
            $laLoi = true;
        } else {
            try {
                // Chua co nhan vien thi khong duoc thong bao cap nhat thanh cong.
                $check = $connect->prepare('SELECT 1 FROM NHANVIEN WHERE MaNhanVien = ?');
                $check->bind_param('s', $maDangXem);
                $check->execute();
                $tonTai = $check->get_result()->num_rows > 0;
                $check->close();

                if (!$tonTai) {
                    $thongBao = 'Không tìm thấy nhân viên cần cập nhật.';
                    $laLoi = true;
                } else {
                    $luongSo = (float)$luong;
                    $stmt = $connect->prepare(
                        'UPDATE NHANVIEN
                         SET TenNhanVien = ?, LuongThang = ?, GioiTinh = ?, MaPhong = ?
                         WHERE MaNhanVien = ?'
                    );
                    $stmt->bind_param('sdiss', $tenNV, $luongSo, $gioiTinh, $maPhong, $maDangXem);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION['thongBao'] = 'Cập nhật thông tin nhân viên thành công!';
                    $_SESSION['laLoi'] = false;
                    header('Location: bai1.php?view=' . rawurlencode($maDangXem));
                    exit;
                }
            } catch (mysqli_sql_exception $ex) {
                $thongBao = 'Cập nhật thất bại: ' . $ex->getMessage();
                $laLoi = true;
            }
        }
    } elseif (isset($_POST['btnXoa'])) {
        // Giữ lại chức năng Xoa cua Bai 4 muc III.
        $maXoa = trim($_POST['maXoa'] ?? '');
        if ($maXoa === '') {
            $thongBao = 'Mã nhân viên cần xóa không hợp lệ.';
            $laLoi = true;
        } else {
            try {
                $stmt = $connect->prepare('DELETE FROM NHANVIEN WHERE MaNhanVien = ?');
                $stmt->bind_param('s', $maXoa);
                $stmt->execute();
                $soDongXoa = $stmt->affected_rows;
                $stmt->close();

                $_SESSION['thongBao'] = $soDongXoa > 0
                    ? 'Xóa nhân viên thành công!'
                    : 'Không tìm thấy nhân viên cần xóa.';
                $_SESSION['laLoi'] = $soDongXoa === 0;
                header('Location: bai1.php');
                exit;
            } catch (mysqli_sql_exception $ex) {
                $thongBao = 'Xóa thất bại: ' . $ex->getMessage();
                $laLoi = true;
            }
        }
    }
}

if ($maDangXem !== '') {
    // Xem du lieu cua DUNG nhan vien tu URL ?view=NV01.
    $stmt = $connect->prepare(
        'SELECT MaNhanVien, TenNhanVien, LuongThang, GioiTinh, MaPhong
         FROM NHANVIEN WHERE MaNhanVien = ?'
    );
    $stmt->bind_param('s', $maDangXem);
    $stmt->execute();
    $nhanVien = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($nhanVien && $giaTriNhap !== null) {
        $nhanVien = array_merge($nhanVien, $giaTriNhap);
    }
} else {
    // Danh sach nhan vien: giu cot Xoa va them lien ket View.
    $resultNV = $connect->query(
        'SELECT MaNhanVien, TenNhanVien, LuongThang, GioiTinh, MaPhong
         FROM NHANVIEN ORDER BY MaNhanVien'
    );
    $danhSachNV = $resultNV->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bài 1 - Xem và cập nhật nhân viên</title>
    <style>
        body { font-family: "Times New Roman", serif; font-size: 17px; margin: 30px; }
        table { border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px 10px; }
        th { background: #ccffff; }
        .danh-sach td { background: #ffddff; }
        .sua-nhan-vien { background: #fff; }
        .sua-nhan-vien td { padding: 3px 6px; }
        .sua-nhan-vien input[type="text"], .sua-nhan-vien select {
            box-sizing: border-box; width: 200px; font-family: inherit; font-size: 16px;
        }
        .sua-nhan-vien input[readonly] { background: #eee; color: #555; }
        .nut { text-align: center; }
        .xoa-form { display: inline; }
        .xoa-form button { border: 0; padding: 0; color: #c00; text-decoration: underline;
            font-family: inherit; font-size: inherit; cursor: pointer; background: transparent; }
        .thong-bao { margin: 12px 0; color: green; }
        .loi { color: red; }
        .quay-lai { display: inline-block; margin-bottom: 15px; }
    </style>
</head>
<body>

<?php if ($maDangXem !== ''): ?>
    <a class="quay-lai" href="bai1.php">&larr; Quay lại danh sách nhân viên</a>

    <?php if ($thongBao !== ''): ?>
        <p class="thong-bao <?= $laLoi ? 'loi' : '' ?>"><?= e($thongBao) ?></p>
    <?php endif; ?>

    <?php if (!$nhanVien): ?>
        <p class="loi">Không tìm thấy nhân viên có mã <?= e($maDangXem) ?>.</p>
    <?php else: ?>
        <form method="post" action="bai1.php?view=<?= rawurlencode($nhanVien['MaNhanVien']) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <table class="sua-nhan-vien">
                <tr>
                    <td><label for="maNV">Mã nhân viên</label></td>
                    <td><input type="text" id="maNV" name="maNV"
                        value="<?= e($nhanVien['MaNhanVien']) ?>" readonly></td>
                </tr>
                <tr>
                    <td><label for="tenNV">Tên nhân viên</label></td>
                    <td><input type="text" id="tenNV" name="tenNV"
                        value="<?= e($nhanVien['TenNhanVien']) ?>" required></td>
                </tr>
                <tr>
                    <td><label for="luong">Lương tháng</label></td>
                    <td><input type="text" id="luong" name="luong" inputmode="decimal"
                        value="<?= e($nhanVien['LuongThang']) ?>" required></td>
                </tr>
                <tr>
                    <td><label for="gioiTinh">Giới tính</label></td>
                    <td><input type="checkbox" id="gioiTinh" name="gioiTinh" value="1"
                        <?= (int)$nhanVien['GioiTinh'] === 1 ? 'checked' : '' ?>> (chọn = Nam)</td>
                </tr>
                <tr>
                    <td><label for="maPhong">Mã phòng ban</label></td>
                    <td>
                        <select id="maPhong" name="maPhong" required>
                            <?php foreach ($phongBan as $pb): ?>
                                <option value="<?= e($pb['MaPhong']) ?>"
                                    <?= $nhanVien['MaPhong'] === $pb['MaPhong'] ? 'selected' : '' ?>>
                                    <?= e($pb['MaPhong']) ?> - <?= e($pb['TenPhong']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr><td class="nut" colspan="2">
                    <input type="submit" name="btnCapNhat" value="Cập nhật"
                        <?= !$phongBan ? 'disabled' : '' ?>>
                </td></tr>
            </table>
        </form>
    <?php endif; ?>

<?php else: ?>
    <h3>Danh sách nhân viên</h3>
    <?php if ($thongBao !== ''): ?>
        <p class="thong-bao <?= $laLoi ? 'loi' : '' ?>"><?= e($thongBao) ?></p>
    <?php endif; ?>

    <table class="danh-sach">
        <tr>
            <th>STT</th>
            <th>Mã nhân viên</th>
            <th>Tên nhân viên</th>
            <th>Chức năng</th>
        </tr>
        <?php if (!$danhSachNV): ?>
            <tr><td colspan="4">Không có nhân viên nào.</td></tr>
        <?php else: foreach ($danhSachNV as $i => $nv): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($nv['MaNhanVien']) ?></td>
                <td><?= e($nv['TenNhanVien']) ?></td>
                <td>
                    <a href="bai1.php?view=<?= rawurlencode($nv['MaNhanVien']) ?>">View</a>
                    |
                    <form class="xoa-form" method="post" action="bai1.php"
                        onsubmit="return confirm('Bạn chắc chắn muốn xóa nhân viên này?');">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="maXoa" value="<?= e($nv['MaNhanVien']) ?>">
                        <button type="submit" name="btnXoa" value="1">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </table>
<?php endif; ?>

</body>
</html>
<?php $connect->close(); ?>
