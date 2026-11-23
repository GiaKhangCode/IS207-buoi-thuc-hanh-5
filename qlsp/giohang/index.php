<?php
declare(strict_types=1);

require __DIR__ . '/session.php';

$gioHang = $_SESSION['giohang'];
session_write_close();
$tongTien = 0;
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="../sanpham/style.css">
</head>
<body>
    <main class="page-shell">
        <h1>Giỏ hàng</h1>
        <?php if (!$gioHang): ?>
            <p>Giỏ hàng đang trống.</p>
        <?php else: ?>
            <div class="cart-table-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col">Đơn vị</th>
                            <th scope="col">Số lượng</th>
                            <th scope="col">Đơn giá</th>
                            <th scope="col">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gioHang as $sanPham): ?>
                            <?php
                            $thanhTien = $sanPham['GIA'] * $sanPham['SL'];
                            $tongTien += $thanhTien;
                            ?>
                            <tr>
                                <th scope="row"><?= htmlspecialchars($sanPham['TENSP'], ENT_QUOTES, 'UTF-8') ?></th>
                                <td><?= htmlspecialchars($sanPham['DVT'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="number"><?= $sanPham['SL'] ?></td>
                                <td class="number"><?= number_format($sanPham['GIA'], 0, ',', '.') ?></td>
                                <td class="number"><?= number_format($thanhTien, 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="row" colspan="4">Tổng cộng</th>
                            <td class="number"><?= number_format($tongTien, 0, ',', '.') ?> đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
        <p><a href="../sanpham/">Tiếp tục mua hàng</a></p>
    </main>
</body>
</html>
