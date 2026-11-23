<?php
require 'chung.php';
require 'connect.php';

$xe = ['soxe' => '', 'tenxe' => '', 'hangxe' => '', 'socho' => '', 'namsx' => '', 'dgthue' => ''];
$thongBao = '';
$laLoi = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        kiemTraToken();
        foreach ($xe as $ten => $giaTri) {
            $xe[$ten] = nhap($ten);
        }
        if ($xe['soxe'] === '' || mb_strlen($xe['soxe']) > 20
            || $xe['tenxe'] === '' || mb_strlen($xe['tenxe']) > 100
            || $xe['hangxe'] === '' || mb_strlen($xe['hangxe']) > 50) {
            throw new InvalidArgumentException('Nhập số xe, tên xe và hãng xe đúng độ dài.');
        }
        if (!ctype_digit($xe['socho']) || (int) $xe['socho'] < 1 || (int) $xe['socho'] > 99) {
            throw new InvalidArgumentException('Số chỗ phải là số nguyên từ 1 đến 99.');
        }
        if (!preg_match('/^[0-9]{4}$/', $xe['namsx']) || (int) $xe['namsx'] < 1886
            || (int) $xe['namsx'] > (int) date('Y') + 1) {
            throw new InvalidArgumentException('Năm sản xuất không hợp lệ.');
        }
        if (!preg_match('/^[0-9]{1,10}(\.[0-9]{1,2})?$/', $xe['dgthue']) || (float) $xe['dgthue'] <= 0) {
            throw new InvalidArgumentException('Đơn giá phải lớn hơn 0, tối đa 10 chữ số và 2 số lẻ.');
        }

        $stmt = $connect->prepare(
            'INSERT INTO XE (SOXE, TENXE, HANGXE, SOCHO, NAMSX, DGTHUE, TINHTRANG)
             VALUES (?, ?, ?, ?, ?, ?, 0)'
        );
        $stmt->bind_param('ssssss', $xe['soxe'], $xe['tenxe'], $xe['hangxe'], $xe['socho'], $xe['namsx'], $xe['dgthue']);
        $stmt->execute();
        $stmt->close();
        $connect->close();
        header('Location: themxe.php?thanhcong=1', true, 303);
        exit;
    } catch (InvalidArgumentException $loi) {
        $thongBao = $loi->getMessage();
        $laLoi = true;
    } catch (mysqli_sql_exception $loi) {
        error_log($loi->getMessage());
        $thongBao = $loi->getCode() === 1062 ? 'Số xe đã tồn tại.' : 'Chưa thêm được xe. Vui lòng thử lại.';
        $laLoi = true;
    }
} elseif (isset($_GET['thanhcong'])) {
    $thongBao = 'Thêm xe thành công. Tình trạng xe là chưa thuê.';
}
$connect->close();
dauTrang('Thêm xe');
?>
<p class="thong-bao <?= $laLoi ? 'loi' : '' ?>" role="status"><?= h($thongBao) ?></p>
<form action="themxe.php" method="post" class="form-nhap">
    <input type="hidden" name="token" value="<?= h($token) ?>">
    <label for="soxe">Số xe</label>
    <input id="soxe" name="soxe" maxlength="20" value="<?= h($xe['soxe']) ?>" required>
    <label for="tenxe">Tên xe</label>
    <input id="tenxe" name="tenxe" maxlength="100" value="<?= h($xe['tenxe']) ?>" required>
    <label for="hangxe">Hãng xe</label>
    <input id="hangxe" name="hangxe" maxlength="50" value="<?= h($xe['hangxe']) ?>" required>
    <label for="socho">Số chỗ</label>
    <input id="socho" name="socho" type="number" min="1" max="99" step="1" value="<?= h($xe['socho']) ?>" required>
    <label for="namsx">Năm sản xuất</label>
    <input id="namsx" name="namsx" type="number" min="1886" max="<?= (int) date('Y') + 1 ?>" step="1" value="<?= h($xe['namsx']) ?>" required>
    <label for="dgthue">Đơn giá thuê</label>
    <input id="dgthue" name="dgthue" type="number" min="0.01" max="9999999999.99" step="0.01" value="<?= h($xe['dgthue']) ?>" required>
    <button type="submit">Thêm</button>
</form>
<?php cuoiTrang(); ?>
