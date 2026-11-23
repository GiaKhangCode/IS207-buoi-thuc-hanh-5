<?php
require_once __DIR__ . '/connect.php';

$thongbao = '';
$maPhong = '';
$tenPhong = '';
$maChiNhanh = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnThem'])) {
    $maPhong = trim($_POST['maPhong'] ?? '');
    $tenPhong = trim($_POST['tenPhong'] ?? '');
    $maChiNhanh = trim($_POST['maChiNhanh'] ?? '');

    if ($maPhong === '' || $tenPhong === '' || $maChiNhanh === '') {
        $thongbao = 'Vui lòng nhập đầy đủ thông tin.';
    } else {
        $stmt = $connect->prepare('INSERT INTO PHONGBAN (MaPhong, TenPhong, MaChiNhanh) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $maPhong, $tenPhong, $maChiNhanh);
        if ($stmt->execute()) {
            $thongbao = 'Thêm phòng ban thành công!';
            $maPhong = $tenPhong = '';
        } else {
            $thongbao = $stmt->errno === 1062
                ? 'Mã phòng ban đã tồn tại.'
                : 'Không thể thêm phòng ban: ' . $stmt->error;
        }
        $stmt->close();
    }
}

$resultChiNhanh = $connect->query('SELECT MaChiNhanh, TenChiNhanh FROM CHINHANH ORDER BY TenChiNhanh');
$chiNhanh = $resultChiNhanh ? $resultChiNhanh->fetch_all(MYSQLI_ASSOC) : [];
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bài 3 - Thêm phòng ban</title>
    <style>
        body { font-family: "Times New Roman", serif; }
        .form-box { width: 280px; margin: 28px auto; padding: 3px; border: 3px ridge #8888ff; background: #e8b9e8; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #a7eded; font-size: 16px; }
        td { padding: 5px 2px; }
        input[type="text"], select { box-sizing: border-box; width: 145px; }
        .actions { text-align: center; }
        .message { text-align: center; margin: 12px auto; }
    </style>
</head>
<body>
<form class="form-box" method="post">
    <table>
        <tr><th colspan="2">Thêm phòng ban</th></tr>
        <tr><td><label for="maPhong">Mã phòng ban</label></td><td><input id="maPhong" name="maPhong" type="text" value="<?= e($maPhong) ?>" required></td></tr>
        <tr><td><label for="tenPhong">Tên phòng ban</label></td><td><input id="tenPhong" name="tenPhong" type="text" value="<?= e($tenPhong) ?>" required></td></tr>
        <tr><td><label for="maChiNhanh">Tên chi nhánh</label></td><td>
            <select id="maChiNhanh" name="maChiNhanh" required>
                <?php if (!$chiNhanh): ?>
                    <option value="">-- Chưa có chi nhánh --</option>
                <?php else: foreach ($chiNhanh as $cn): ?>
                    <option value="<?= e($cn['MaChiNhanh']) ?>" <?= $maChiNhanh === $cn['MaChiNhanh'] ? 'selected' : '' ?>><?= e($cn['TenChiNhanh']) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </td></tr>
        <tr><td class="actions" colspan="2"><input type="submit" name="btnThem" value="Thêm"> <input type="reset" value="Reset"></td></tr>
    </table>
</form>
<?php if ($thongbao !== ''): ?><p class="message"><?= e($thongbao) ?></p><?php endif; ?>
</body>
</html>
<?php $connect->close(); ?>
