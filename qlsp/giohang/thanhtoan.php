<?php
declare(strict_types=1);

require __DIR__ . '/session.php';

$gioHang = $_SESSION['giohang'];
$token = $_SESSION['giohang_token'];
session_write_close();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function tien(int $so): string
{
    return number_format($so, 0, ',', '.') . ' đ';
}

$tongTien = 0;
$soMatHang = count($gioHang);
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thanh toán đơn hàng</title>
    <meta name="cart-token" content="<?= e($token) ?>">
    <link rel="stylesheet" href="thanhtoan.css">
</head>
<body>
    <main class="checkout-page">
        <div class="checkout-nav">
            <a href="index.php">&lt; Quay lại giỏ hàng</a>
            <a href="../sanpham/">Tiếp tục mua hàng</a>
        </div>

        <div id="checkout-message" class="checkout-message" role="status" aria-live="polite"></div>

        <?php if (empty($gioHang)): ?>
            <div class="empty-box">
                <p>Giỏ hàng hiện đang trống. Vui lòng chọn sản phẩm trước khi thanh toán.</p>
                <a class="btn-back" href="../sanpham/">&lt; Quay lại danh sách sản phẩm</a>
            </div>
        <?php else: ?>
            <div id="checkout-container" class="checkout-layout">
                <section class="checkout-card form-card">
                    <form id="checkout-form" novalidate>
                        <div class="form-grid">
                            <div class="form-col">
                                <h2>Thông tin giao hàng</h2>

                                <div class="field">
                                    <label for="hoten"><span class="icon" aria-hidden="true">&#128100;</span> Họ tên</label>
                                    <input type="text" id="hoten" name="hoten" maxlength="50" placeholder="Họ tên" required>
                                </div>

                                <div class="field">
                                    <label for="sdt"><span class="icon" aria-hidden="true">&#9993;</span> Số điện thoại</label>
                                    <input type="tel" id="sdt" name="sdt" maxlength="15" placeholder="09xxxxxxxx" required>
                                </div>

                                <div class="field">
                                    <label for="email"><span class="icon" aria-hidden="true">&#9993;</span> Email</label>
                                    <input type="email" id="email" name="email" maxlength="50" placeholder="abc@example.com" required>
                                </div>
                            </div>

                            <div class="form-col">
                                <h2>Thông tin nhận hàng</h2>

                                <div class="field">
                                    <label for="diachi"><span class="icon" aria-hidden="true">&#128179;</span> Địa chỉ nhận hàng</label>
                                    <input type="text" id="diachi" name="diachi" maxlength="100" placeholder="13 abc xyz" required>
                                </div>

                                <div class="field">
                                    <label for="tinh"><span class="icon" aria-hidden="true">&#127963;</span> Tỉnh/Thành phố</label>
                                    <select id="tinh" name="tinh" required>
                                        <option value="">Vui lòng chọn tỉnh/thành phố</option>
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="quan"><span class="icon" aria-hidden="true">&#127963;</span> Quận/Huyện</label>
                                    <select id="quan" name="quan" required disabled>
                                        <option value="">Vui lòng chọn quận/huyện</option>
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="phuong"><span class="icon" aria-hidden="true">&#127963;</span> Phường/Xã</label>
                                    <select id="phuong" name="phuong" required disabled>
                                        <option value="">Vui lòng chọn phường/xã</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="btn-submit" class="btn-checkout">Thanh toán</button>
                    </form>
                </section>

                <aside class="checkout-card summary-card">
                    <div class="summary-header">
                        <span>Cart</span>
                        <span>SL</span>
                        <span class="cart-badge">&#128722; <strong id="summary-count"><?= $soMatHang ?></strong></span>
                    </div>

                    <div id="summary-items" class="summary-items">
                        <?php foreach ($gioHang as $ma => $sp): ?>
                            <?php
                            $thanhTien = (int) $sp['GIA'] * (int) $sp['SL'];
                            $tongTien += $thanhTien;
                            ?>
                            <div class="summary-row">
                                <a class="item-name" href="index.php"><?= e((string) $sp['TENSP']) ?></a>
                                <span class="item-qty"><?= (int) $sp['SL'] ?></span>
                                <span class="item-price"><?= tien($thanhTien) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="summary-divider">

                    <div class="summary-total">
                        <span>Total</span>
                        <strong id="summary-total"><?= tien($tongTien) ?></strong>
                    </div>
                </aside>
            </div>

            <div id="order-success" class="success-box" hidden>
                <h2>Đặt hàng và thanh toán thành công!</h2>
                <p>Mã hóa đơn của bạn: <strong id="order-id"></strong></p>
                <p>Tổng tiền đã thanh toán: <strong id="order-total"></strong></p>
                <div class="success-actions">
                    <a class="btn-back" href="../sanpham/">&lt; Tiếp tục mua hàng</a>
                    <a class="btn-secondary" href="index.php">Xem giỏ hàng</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="thanhtoan.js"></script>
</body>
</html>

