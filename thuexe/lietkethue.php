<?php
require 'chung.php';
dauTrang('Liệt kê xe thuê chưa trả');
?>
<form id="form-liet-ke" class="bo-loc">
    <label>Chọn ngày <input type="date" name="ngay" value="<?= date('Y-m-d') ?>" required></label>
    <button type="submit">Xem</button>
</form>
<p id="thong-bao" class="thong-bao" role="status"></p>
<div class="bang-cuon">
    <table>
        <thead><tr><th scope="col">STT</th><th scope="col">Họ tên khách hàng</th><th scope="col">Số xe</th><th scope="col">Tên xe</th></tr></thead>
        <tbody id="ket-qua"></tbody>
    </table>
</div>
<script src="ajax.js"></script>
<script src="lietke.js"></script>
<?php cuoiTrang(); ?>
