<?php
require 'chung.php';
require 'connect.php';
$khachHang = $connect->query('SELECT MAKH, TENKH FROM KHACHHANG ORDER BY MAKH')->fetch_all(MYSQLI_ASSOC);
$connect->close();
dauTrang('Thông tin trả xe');
?>
<form id="form-tra" class="form-nhap">
    <label for="khach-hang">Họ tên khách hàng</label>
    <select id="khach-hang" name="makh" required>
        <?php foreach ($khachHang as $khach): ?>
            <option value="<?= h($khach['MAKH']) ?>"><?= h($khach['TENKH']) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="xe-tra">Số xe</label>
    <select id="xe-tra" name="mat" required></select>
    <input type="hidden" name="soxe" id="so-xe">
    <label for="ngay-thue">Ngày thuê</label>
    <input id="ngay-thue" type="date" readonly>
    <label for="ngay-tra">Ngày trả</label>
    <input id="ngay-tra" type="date" name="ngaytra" value="<?= date('Y-m-d') ?>" required>
    <button type="submit">Trả xe</button>
</form>
<p id="thong-bao" class="thong-bao" role="status"></p>
<script src="ajax.js"></script>
<script src="traxe.js"></script>
<?php cuoiTrang(); ?>
