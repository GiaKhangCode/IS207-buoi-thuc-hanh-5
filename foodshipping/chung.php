<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
$_SESSION['token'] ??= bin2hex(random_bytes(32));

function h($giaTri)
{
    return htmlspecialchars((string) $giaTri, ENT_QUOTES, 'UTF-8');
}

function tien($giaTri)
{
    return number_format((int) $giaTri, 0, ',', '.');
}

function ngay($giaTri)
{
    return date('d/m/Y', strtotime($giaTri));
}

function yeuCauDangNhap()
{
    if (empty($_SESSION['taikhoan'])) {
        header('Location: index.php');
        exit;
    }

    return $_SESSION['taikhoan'];
}

function kiemTraToken()
{
    $token = $_POST['token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['token'], $token)) {
        http_response_code(400);
        exit('Phiên làm việc đã thay đổi. Quay lại trang trước và tải lại trang.');
    }
}

function dauTrang($tieuDe, $lop = '')
{
    $taiKhoan = $_SESSION['taikhoan'] ?? null;
    ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($tieuDe) ?> · Food Shipping</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="<?= h($lop) ?>">
    <?php if ($taiKhoan): ?>
        <header>
            <nav aria-label="Điều hướng">
                <?php if (!$taiKhoan['QUANLY']): ?>
                    <a href="datmon.php">Đặt món</a>
                <?php endif; ?>
                <a href="donhang.php">Đơn hàng</a>
                <form action="index.php" method="post">
                    <input type="hidden" name="token" value="<?= h($_SESSION['token']) ?>">
                    <button type="submit" name="dangxuat" value="1" class="lien-ket">Đăng xuất</button>
                </form>
            </nav>
        </header>
    <?php endif; ?>
    <h1><?= h($tieuDe) ?></h1>
    <?php
}

function cuoiTrang()
{
    ?>
</main>
</body>
</html>
    <?php
}
