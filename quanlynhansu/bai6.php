<?php
require_once __DIR__ . '/connect.php';

function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

$thongbao = '';
$loi = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnXoa'])) {
    $maCongTy = trim($_POST['mact'] ?? '');

    if ($maCongTy === '') {
        $thongbao = 'Vui lòng chọn công ty cần xóa.';
        $loi = true;
    } else {
        $connect->begin_transaction();
        try {
            $stmt = $connect->prepare(
                'DELETE nv FROM NHANVIEN nv
                 JOIN PHONGBAN pb ON nv.MaPhong = pb.MaPhong
                 JOIN CHINHANH cn ON pb.MaChiNhanh = cn.MaChiNhanh
                 WHERE cn.MaCongTy = ?');
            $stmt->bind_param('s', $maCongTy);
            $stmt->execute();
            $soNV = $stmt->affected_rows;
            $stmt->close();

            $stmt = $connect->prepare(
                'DELETE pb FROM PHONGBAN pb
                 JOIN CHINHANH cn ON pb.MaChiNhanh = cn.MaChiNhanh
                 WHERE cn.MaCongTy = ?');
            $stmt->bind_param('s', $maCongTy);
            $stmt->execute();
            $soPB = $stmt->affected_rows;
            $stmt->close();

            $stmt = $connect->prepare('DELETE FROM CHINHANH WHERE MaCongTy = ?');
            $stmt->bind_param('s', $maCongTy);
            $stmt->execute();
            $soCN = $stmt->affected_rows;
            $stmt->close();

            $stmt = $connect->prepare('DELETE FROM CONGTY WHERE MaCongTy = ?');
            $stmt->bind_param('s', $maCongTy);
            $stmt->execute();
            $soCT = $stmt->affected_rows;
            $stmt->close();

            if ($soCT === 0) {
                $connect->rollback();
                $thongbao = 'Công ty không tồn tại hoặc đã bị xóa.';
                $loi = true;
            } else {
                $connect->commit();
                $thongbao = "Xóa thành công công ty $maCongTy cùng $soCN chi nhánh, $soPB phòng ban, $soNV nhân viên.";
            }
        } catch (mysqli_sql_exception $ex) {
            $connect->rollback();
            $thongbao = 'Xóa không thành công: ' . $ex->getMessage();
            $loi = true;
        }
    }
}

$resultCT = $connect->query('SELECT MaCongTy, TenCongTy FROM CONGTY ORDER BY TenCongTy');
$congTy = $resultCT ? $resultCT->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bài 6 - Xóa công ty</title>
    <style>
        body { font-family: "Times New Roman", serif; font-size: 18px; }
        .box { display: inline-block; border: 1px solid #000; padding: 6px 10px 12px; }
        select { font-size: 16px; padding: 2px; min-width: 150px; }
        .actions { margin-top: 14px; }
        .ok { color: green; }
        .err { color: red; }
    </style>
</head>
<body>
<form class="box" method="post"
      onsubmit="return confirm('Xóa công ty này sẽ xóa luôn tất cả chi nhánh, phòng ban và nhân viên thuộc công ty. Bạn chắc chắn?');">
    <label for="mact">Tên công ty:</label>
    <select id="mact" name="mact" required>
        <?php if (!$congTy): ?>
            <option value="">-- Chưa có công ty --</option>
        <?php else: foreach ($congTy as $ct): ?>
            <option value="<?= e($ct['MaCongTy']) ?>"><?= e($ct['TenCongTy']) ?></option>
        <?php endforeach; endif; ?>
    </select>
    <div class="actions">
        <input type="submit" name="btnXoa" value="Xóa" <?= $congTy ? '' : 'disabled' ?>>
    </div>
</form>

<?php if ($thongbao !== ''): ?>
    <p class="<?= $loi ? 'err' : 'ok' ?>"><?= e($thongbao) ?></p>
<?php endif; ?>
</body>
</html>
<?php $connect->close(); ?>
