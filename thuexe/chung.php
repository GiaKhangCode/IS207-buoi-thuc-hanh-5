<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
$_SESSION['thuexe_token'] ??= bin2hex(random_bytes(32));
$token = $_SESSION['thuexe_token'];
session_write_close();

function h($giaTri)
{
    return htmlspecialchars((string) $giaTri, ENT_QUOTES, 'UTF-8');
}

function tien($giaTri)
{
    return rtrim(rtrim(number_format((float) $giaTri, 2, ',', '.'), '0'), ',');
}

function nhap($ten, $macDinh = '')
{
    $giaTri = $_POST[$ten] ?? $_GET[$ten] ?? $macDinh;
    if (!is_string($giaTri)) {
        throw new InvalidArgumentException('Dữ liệu nhập không hợp lệ.');
    }
    return trim($giaTri);
}

function kiemTraNgay($ngay)
{
    $doiTuong = DateTimeImmutable::createFromFormat('!Y-m-d', $ngay);
    if (!$doiTuong || $doiTuong->format('Y-m-d') !== $ngay) {
        throw new InvalidArgumentException('Vui lòng nhập ngày hợp lệ.');
    }
    return $doiTuong;
}

function kiemTraToken()
{
    global $token;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($token, nhap('token'))) {
        throw new InvalidArgumentException('Phiên làm việc đã thay đổi. Vui lòng tải lại trang.');
    }
}

function dauTrang($tieuDe)
{
    global $token;
    ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= h($token) ?>">
    <title><?= h($tieuDe) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <nav aria-label="Điều hướng">
        <a href="themxe.php">Thêm xe</a>
        <a href="traxe.php">Trả xe</a>
        <a href="thuexe.php">Thuê xe</a>
        <a href="lietkethue.php">Xe chưa trả</a>
        <a href="xetrackhach.php">Xe đã trả</a>
    </nav>
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
