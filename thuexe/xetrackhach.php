<?php
require 'chung.php';
require 'connect.php';
$khachHang = $connect->query('SELECT MAKH, TENKH FROM KHACHHANG ORDER BY MAKH')->fetch_all(MYSQLI_ASSOC);
$connect->close();
dauTrang('Xe thuê đã trả của khách hàng');
?>
<form id="form-da-tra">
    <div class="bo-loc">
        <label>Từ ngày <input type="date" name="tungay" value="<?= date('Y-m-01') ?>" required></label>
        <label>Đến ngày <input type="date" name="denngay" value="<?= date('Y-m-d') ?>" required></label>
    </div>
    <div class="bo-loc">
        <label>Họ tên khách hàng
            <select name="makh" id="khach-hang" required>
                <?php foreach ($khachHang as $khach): ?>
                    <option value="<?= h($khach['MAKH']) ?>"><?= h($khach['TENKH']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Xem</button>
    </div>
</form>
<p id="thong-bao" class="thong-bao" role="status"></p>
<div class="bang-cuon">
    <table>
        <thead><tr><th scope="col">STT</th><th scope="col">Số xe</th><th scope="col">Tên xe</th><th scope="col">Giá thuê</th></tr></thead>
        <tbody id="ket-qua"></tbody>
    </table>
</div>
<script src="ajax.js"></script>
<script src="lietke.js"></script>
<?php cuoiTrang(); ?>
