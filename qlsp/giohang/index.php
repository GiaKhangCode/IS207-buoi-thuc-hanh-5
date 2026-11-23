<?php
declare(strict_types=1);

require __DIR__ . '/session.php';

$gioHang = $_SESSION['giohang'];
$token = $_SESSION['giohang_token'];
session_write_close();

// Session của Bài 8 không lưu hình ảnh, nên đọc HINHANH từ CSDL theo mã sản phẩm.
$hinhAnh = [];
if ($gioHang) {
    require __DIR__ . '/../sanpham/connect.php';
    $dsMa = array_map('strval', array_keys($gioHang));
    $dauHoi = implode(',', array_fill(0, count($dsMa), '?'));
    $statement = $db->prepare("SELECT MASP, HINHANH FROM sanpham WHERE MASP IN ($dauHoi)");
    $statement->bind_param(str_repeat('s', count($dsMa)), ...$dsMa);
    $statement->execute();
    foreach ($statement->get_result() as $dong) {
        $hinhAnh[$dong['MASP']] = $dong['HINHANH'];
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function tien(int $so): string
{
    return number_format($so, 0, ',', '.') . ' đ';
}

$tongTien = 0;
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý giỏ hàng</title>
    <meta name="cart-token" content="<?= e($token) ?>">
    <link rel="stylesheet" href="giohang.css">
</head>
<body>
    <main class="cart-page">
        <h1>Quản lý giỏ hàng</h1>

        <div id="message" class="message" role="status" aria-live="polite"></div>

        <p id="empty" class="empty"<?= $gioHang ? ' hidden' : '' ?>>Giỏ hàng đang trống.</p>

        <div id="cart"<?= $gioHang ? '' : ' hidden' ?>>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th scope="col" colspan="2">Tên sản phẩm</th>
                        <th scope="col">Giá</th>
                        <th scope="col">Số lượng</th>
                        <th scope="col">Thành tiền</th>
                        <th scope="col"><span class="sr-only">Thao tác</span></th>
                    </tr>
                </thead>
                <tbody id="cart-body">
                    <?php foreach ($gioHang as $ma => $sp): ?>
                        <?php
                        $ma = (string) $ma;
                        $thanhTien = $sp['GIA'] * $sp['SL'];
                        $tongTien += $thanhTien;
                        $hinh = $hinhAnh[$ma] ?? '';
                        ?>
                        <tr data-masp="<?= e($ma) ?>" data-gia="<?= $sp['GIA'] ?>">
                            <td class="thumb">
                                <?php if ($hinh !== ''): ?>
                                    <img src="../sanpham/assets/<?= rawurlencode($hinh) ?>" alt="<?= e($sp['TENSP']) ?>">
                                <?php endif; ?>
                            </td>
                            <td class="name"><?= e($sp['TENSP']) ?></td>
                            <td class="price"><?= tien($sp['GIA']) ?></td>
                            <td>
                                <input class="qty" type="number" min="1" max="9999" value="<?= $sp['SL'] ?>"
                                       aria-label="Số lượng <?= e($sp['TENSP']) ?>">
                            </td>
                            <td class="line-total"><?= tien($thanhTien) ?></td>
                            <td class="actions">
                                <button type="button" class="btn btn-edit" title="Cập nhật số lượng" aria-label="Cập nhật số lượng">&#9998;</button>
                                <button type="button" class="btn btn-del" title="Xóa khỏi giỏ hàng" aria-label="Xóa khỏi giỏ hàng">&#128465;</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-footer">
                <a class="btn btn-continue" href="../sanpham/">&lt; Tiếp tục mua hàng</a>
                <span class="total">Tổng tiền <strong id="total"><?= tien($tongTien) ?></strong></span>
                <a class="btn btn-pay" href="thanhtoan.php">Thanh toán &gt;</a>
            </div>
        </div>

        <p id="back-empty" class="back-empty"<?= $gioHang ? ' hidden' : '' ?>>
            <a class="btn btn-continue" href="../sanpham/">&lt; Tiếp tục mua hàng</a>
        </p>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="giohang.js"></script>
</body>
</html>
