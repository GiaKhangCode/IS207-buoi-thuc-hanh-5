<?php
require 'chung.php';
require 'connect.php';
$khachHang = $connect->query('SELECT MAKH, TENKH FROM KHACHHANG ORDER BY MAKH')->fetch_all(MYSQLI_ASSOC);
$connect->close();
dauTrang('Thuê xe');
?>
<form id="form-thue" class="bo-loc">
    <label>Họ tên khách hàng
        <select name="makh" id="khach-hang" required>
            <?php foreach ($khachHang as $khach): ?>
                <option value="<?= h($khach['MAKH']) ?>"><?= h($khach['TENKH']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Ngày thuê <input type="date" name="ngaythue" value="<?= date('Y-m-d') ?>" required></label>
</form>
<p id="thong-bao" class="thong-bao" role="status"></p>
<div class="bang-cuon">
    <table>
        <caption>Danh sách các xe chưa thuê</caption>
        <thead>
            <tr><th scope="col">Số xe</th><th scope="col">Tên xe</th><th scope="col">Hãng xe</th><th scope="col">Năm sản xuất</th><th scope="col">Số chỗ</th><th scope="col">Đơn giá thuê</th><th scope="col">Chọn thuê</th></tr>
        </thead>
        <tbody id="xe-chua-thue"></tbody>
    </table>
</div>
<div class="bang-cuon">
    <table>
        <caption>Danh sách các xe đang thuê</caption>
        <thead>
            <tr><th scope="col">Số xe</th><th scope="col">Tên xe</th><th scope="col">Hãng xe</th><th scope="col">Năm sản xuất</th><th scope="col">Số chỗ</th><th scope="col">Đơn giá thuê</th><th scope="col">Chọn thuê</th></tr>
        </thead>
        <tbody id="xe-dang-thue"></tbody>
    </table>
</div>
<script src="ajax.js"></script>
<script src="thuexe.js"></script>
<?php cuoiTrang(); ?>
