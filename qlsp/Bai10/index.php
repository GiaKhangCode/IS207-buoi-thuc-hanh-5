<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION['bai10_csrf'])) {
    $_SESSION['bai10_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['bai10_csrf'];
session_write_close();
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
    <title>Bài 10 - AJAX: sản phẩm và đơn hàng</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
</head>
<body>
<main class="container">
    <header class="site-header">
        <div>
            <p class="eyebrow">IS207 • Thực hành phần IV • Bài 10</p>
            <h1>Quản lý sản phẩm &amp; đơn hàng</h1>
            <p class="subheading">Live Search, sửa/xóa bằng AJAX và phân trang</p>
        </div>
        <nav class="links" aria-label="Liên kết chức năng">
            <a href="../sanpham/">Danh sách mua hàng</a>
            <a href="../sanpham/sanpham.php">Thêm sản phẩm</a>
            <a href="../giohang/">Giỏ hàng</a>
        </nav>
    </header>

    <div class="tabs" role="tablist" aria-label="Nội dung quản lý">
        <button id="product-tab" type="button" class="tab active" role="tab" aria-selected="true" aria-controls="product-panel">Sản phẩm (Câu 1, 2, 4)</button>
        <button id="order-tab" type="button" class="tab" role="tab" aria-selected="false" aria-controls="order-panel">Đơn hàng (Câu 3)</button>
    </div>

    <div id="message" class="message" role="status" aria-live="polite" hidden></div>

    <section id="product-panel" class="panel" role="tabpanel" aria-labelledby="product-tab">
        <div class="panel-header">
            <div><h2>Danh sách sản phẩm</h2><p>Nhập từng ký tự vào ô tìm kiếm để lọc ngay.</p></div>
            <label class="search-field">Tìm theo tên sản phẩm
                <input id="product-search" type="search" placeholder="Search sản phẩm..." autocomplete="off">
            </label>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Mã SP</th><th>Tên sản phẩm</th><th>Loại</th><th>ĐVT</th><th>Giá</th><th>Chức năng</th></tr></thead>
                <tbody id="product-body"><tr><td colspan="6">Đang tải sản phẩm...</td></tr></tbody>
            </table>
        </div>
        <div class="pager"><span id="product-info"></span><div id="product-pagination" class="pagination"></div></div>

        <section id="product-editor" class="editor" hidden>
            <div class="editor-heading"><h3>Sửa thông tin sản phẩm</h3><button type="button" id="product-close" class="plain-btn">Đóng</button></div>
            <form id="product-form" class="fields">
                <label>Mã sản phẩm<input name="masp" readonly></label>
                <label>Tên sản phẩm<input name="tensp" maxlength="50" required></label>
                <label>Đơn vị tính<input name="dvt" maxlength="50" required></label>
                <label>Nước sản xuất<input name="nuocsx" maxlength="20" required></label>
                <label>Giá (VNĐ)<input name="gia" type="number" min="0" max="2147483647" step="1" required></label>
                <label>Loại sản phẩm<input name="loaisp" maxlength="20" required></label>
                <label>Hình ảnh (tên file)<input name="hinhanh" maxlength="40"></label>
                <div class="form-actions"><button class="primary" type="submit">Lưu sản phẩm</button></div>
            </form>
        </section>
    </section>

    <section id="order-panel" class="panel" role="tabpanel" aria-labelledby="order-tab" hidden>
        <div class="panel-header">
            <div><h2>Danh sách đơn hàng</h2><p>Sửa thông tin giao hàng, chi tiết đơn hàng hoặc xóa hóa đơn.</p></div>
            <label class="search-field">Tìm đơn hàng
                <input id="order-search" type="search" placeholder="Mã đơn hoặc tên khách..." autocomplete="off">
            </label>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Số HĐ</th><th>Khách hàng</th><th>Ngày HĐ</th><th>Mặt hàng</th><th>Tổng tiền</th><th>Chức năng</th></tr></thead>
                <tbody id="order-body"><tr><td colspan="6">Đang tải đơn hàng...</td></tr></tbody>
            </table>
        </div>
        <div class="pager"><span id="order-info"></span><div id="order-pagination" class="pagination"></div></div>

        <section id="order-editor" class="editor" hidden>
            <div class="editor-heading"><h3>Thông tin đơn hàng <span id="order-title"></span></h3><button type="button" id="order-close" class="plain-btn">Đóng</button></div>
            <form id="order-form" class="fields">
                <label>Số hóa đơn<input name="sohd" readonly></label>
                <label>Ngày hóa đơn<input name="nghd" type="datetime-local" required></label>
                <label>Họ tên khách hàng<input name="hotenkh" maxlength="50" required></label>
                <label>Số điện thoại<input name="sdt" maxlength="15" required></label>
                <label>Email<input name="email" type="email" maxlength="50" required></label>
                <label>Địa chỉ<input name="diachi" maxlength="100" required></label>
                <label>Tỉnh/Thành phố<input name="tinh" maxlength="50" required></label>
                <label>Quận/Huyện<input name="quan" maxlength="50" required></label>
                <label>Phường/Xã<input name="phuong" maxlength="50" required></label>
                <label>Trạng thái thanh toán
                    <select name="thanhtoan"><option value="0">Chưa thanh toán</option><option value="1">Đã thanh toán</option></select>
                </label>
                <div class="form-actions"><button type="submit" class="primary">Cập nhật đơn hàng</button></div>
            </form>
            <h4>Chi tiết sản phẩm trong đơn</h4>
            <p class="hint">Sửa số lượng/đơn giá rồi chọn Lưu từng dòng. Đơn giá là giá ghi nhận tại lúc đặt hàng.</p>
            <div class="table-wrap"><table>
                <thead><tr><th>Mã SP</th><th>Tên sản phẩm</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th><th>Chức năng</th></tr></thead>
                <tbody id="order-lines"></tbody>
            </table></div>
            <p id="order-total" class="order-total"></p>
        </section>
    </section>

    <footer>Trang thực hành trên cơ sở dữ liệu <code>qlsp</code> hiện có. Các thao tác sửa/xóa ảnh hưởng dữ liệu thật trong MySQL.</footer>
</main>
</body>
</html>
