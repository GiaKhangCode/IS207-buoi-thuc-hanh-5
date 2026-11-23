<?php
require_once __DIR__ . '/connect.php';

function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

$thongbao = '';
$maChiNhanh = '';
$maPhong = '';
$maNV = '';
$tenNV = '';
$luong = '';
$gioiTinh = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnThem'])) {
    $maChiNhanh = trim($_POST['maChiNhanh'] ?? '');
    $maPhong = trim($_POST['maPhong'] ?? '');
    $maNV = trim($_POST['maNV'] ?? '');
    $tenNV = trim($_POST['tenNV'] ?? '');
    $luong = trim($_POST['luong'] ?? '');
    // Checkbox được chọn -> Nam (1), không chọn -> Nữ (0).
    $gioiTinh = isset($_POST['gioiTinh']);
    $gt = $gioiTinh ? 1 : 0;

    if ($maChiNhanh === '' || $maPhong === '' || $maNV === '' || $tenNV === '' || $luong === '') {
        $thongbao = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif (!is_numeric($luong) || (float)$luong < 0) {
        $thongbao = 'Lương phải là số không âm.';
    } else {
        try {
            // Phòng ban phải thuộc đúng chi nhánh đã chọn.
            $check = $connect->prepare('SELECT 1 FROM PHONGBAN WHERE MaPhong = ? AND MaChiNhanh = ?');
            $check->bind_param('ss', $maPhong, $maChiNhanh);
            $check->execute();
            $hopLe = $check->get_result()->num_rows > 0;
            $check->close();

            if (!$hopLe) {
                $thongbao = 'Phòng ban được chọn không thuộc chi nhánh này.';
            } else {
                $luongSo = (float)$luong;
                $stmt = $connect->prepare(
                    'INSERT INTO NHANVIEN (MaNhanVien, TenNhanVien, LuongThang, GioiTinh, MaPhong) VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('ssdis', $maNV, $tenNV, $luongSo, $gt, $maPhong);
                $stmt->execute();
                $stmt->close();
                $thongbao = 'Thêm nhân viên thành công!';
                $maNV = $tenNV = $luong = '';
                $gioiTinh = false;
            }
        } catch (mysqli_sql_exception $ex) {
            $thongbao = $ex->getCode() === 1062
                ? 'Mã nhân viên đã tồn tại.'
                : 'Không thể thêm nhân viên: ' . $ex->getMessage();
        }
    }
}

$resultCN = $connect->query('SELECT MaChiNhanh, TenChiNhanh FROM CHINHANH ORDER BY TenChiNhanh');
$chiNhanh = $resultCN ? $resultCN->fetch_all(MYSQLI_ASSOC) : [];
$resultPB = $connect->query('SELECT MaPhong, TenPhong, MaChiNhanh FROM PHONGBAN ORDER BY TenPhong');
$phongBan = $resultPB ? $resultPB->fetch_all(MYSQLI_ASSOC) : [];

if ($maChiNhanh === '' && $chiNhanh) {
    $maChiNhanh = $chiNhanh[0]['MaChiNhanh'];
}
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bài 4 - Thêm nhân viên</title>
    <style>
        body { font-family: "Times New Roman", serif; }
        .form-box { width: 360px; margin: 28px auto; padding: 3px; border: 3px ridge #8888ff; background: #ffccff; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #ccffff; font-size: 16px; }
        td { padding: 5px 4px; }
        input[type="text"], select { box-sizing: border-box; width: 190px; }
        .actions { text-align: center; }
        .message { text-align: center; margin: 12px auto; }
    </style>
</head>
<body>
<form id="frm" class="form-box" method="post">
    <table>
        <tr><th colspan="2">Thêm nhân viên</th></tr>
        <tr>
            <td><label for="maChiNhanh">Tên chi nhánh</label></td>
            <td>
                <select id="maChiNhanh" name="maChiNhanh" required>
                    <?php if (!$chiNhanh): ?>
                        <option value="">-- Chưa có chi nhánh --</option>
                    <?php else: foreach ($chiNhanh as $cn): ?>
                        <option value="<?= e($cn['MaChiNhanh']) ?>" <?= $maChiNhanh === $cn['MaChiNhanh'] ? 'selected' : '' ?>><?= e($cn['TenChiNhanh']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="maPhong">Tên phòng ban</label></td>
            <td><select id="maPhong" name="maPhong" required></select></td>
        </tr>
        <tr><td><label for="maNV">Mã nhân viên</label></td><td><input id="maNV" name="maNV" type="text" value="<?= e($maNV) ?>" required></td></tr>
        <tr><td><label for="tenNV">Tên nhân viên</label></td><td><input id="tenNV" name="tenNV" type="text" value="<?= e($tenNV) ?>" required></td></tr>
        <tr><td><label for="luong">Lương</label></td><td><input id="luong" name="luong" type="text" inputmode="decimal" value="<?= e($luong) ?>" required></td></tr>
        <tr><td><label for="gioiTinh">Giới tính</label></td><td><input id="gioiTinh" name="gioiTinh" type="checkbox" value="1" <?= $gioiTinh ? 'checked' : '' ?>> (chọn = Nam)</td></tr>
        <tr><td class="actions" colspan="2"><input type="submit" name="btnThem" value="Thêm"> <input type="reset" value="Reset"></td></tr>
    </table>
</form>
<?php if ($thongbao !== ''): ?><p class="message"><?= e($thongbao) ?></p><?php endif; ?>

<script>
    // Phòng ban chỉ hiện những phòng thuộc chi nhánh đang chọn.
    const phongBan = <?= json_encode($phongBan, $jsonFlags) ?>;
    const chonSan = <?= json_encode($maPhong, $jsonFlags) ?>;
    const selChiNhanh = document.getElementById('maChiNhanh');
    const selPhong = document.getElementById('maPhong');

    function napPhongBan(chon) {
        selPhong.innerHTML = '';
        const ds = phongBan.filter(p => p.MaChiNhanh === selChiNhanh.value);
        if (ds.length === 0) {
            selPhong.add(new Option('-- Chưa có phòng ban --', ''));
            return;
        }
        ds.forEach(p => selPhong.add(new Option(p.TenPhong, p.MaPhong, false, p.MaPhong === chon)));
    }

    selChiNhanh.addEventListener('change', () => napPhongBan(''));
    // Reset phải nạp lại danh sách phòng ban theo chi nhánh mặc định sau khi form được đặt lại.
    document.getElementById('frm').addEventListener('reset', () => setTimeout(() => napPhongBan(''), 0));
    napPhongBan(chonSan);
</script>
</body>
</html>
<?php $connect->close(); ?>
