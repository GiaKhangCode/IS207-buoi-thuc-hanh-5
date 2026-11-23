<?php
require 'chung.php';

$loi = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    kiemTraToken();

    if (isset($_POST['dangxuat'])) {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: index.php', true, 303);
        exit;
    }

    require 'connect.php';

    $username = trim((string) filter_input(INPUT_POST, 'username'));
    $password = (string) filter_input(INPUT_POST, 'password');
    $stmt = $connect->prepare(
        'SELECT a.MAKH, a.PASSWORD, a.QUANLY, CONCAT(k.HO, " ", k.TEN) AS HOTEN
         FROM ACCOUNT a LEFT JOIN KHACHHANG k ON k.MAKH = a.MAKH
         WHERE a.USERNAME = ?'
    );
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $taiKhoan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $connect->close();

    if ($taiKhoan && password_verify($password, $taiKhoan['PASSWORD'])) {
        session_regenerate_id(true);
        $_SESSION['token'] = bin2hex(random_bytes(32));
        $_SESSION['taikhoan'] = [
            'MAKH' => $taiKhoan['MAKH'],
            'HOTEN' => $taiKhoan['HOTEN'],
            'QUANLY' => (bool) $taiKhoan['QUANLY'],
        ];
        header('Location: ' . ($taiKhoan['QUANLY'] ? 'donhang.php' : 'datmon.php'), true, 303);
        exit;
    }

    $loi = 'Tên đăng nhập hoặc mật khẩu không đúng.';
}

if (isset($_SESSION['taikhoan'])) {
    header('Location: ' . ($_SESSION['taikhoan']['QUANLY'] ? 'donhang.php' : 'datmon.php'));
    exit;
}

dauTrang('Đăng nhập', 'trang-dang-nhap');
?>

<?php if ($loi): ?>
    <p class="loi" role="alert"><?= h($loi) ?></p>
<?php endif; ?>

<form method="post" action="index.php" class="dang-nhap khung">
    <input type="hidden" name="token" value="<?= h($_SESSION['token']) ?>">
    <label for="username">Tên đăng nhập</label>
    <input id="username" name="username" value="<?= h($username) ?>" autocomplete="username" maxlength="40" required>
    <label for="password">Mật khẩu</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>
    <button type="submit">Đăng nhập</button>
</form>

<?php cuoiTrang(); ?>
