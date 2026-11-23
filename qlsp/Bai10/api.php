<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(bool $ok, array $payload = [], int $status = 200): never
{
    http_response_code($status);
    echo json_encode(['success' => $ok] + $payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function postString(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

function validLength(string $value, int $max): bool
{
    return (function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value)) <= $max;
}

function positiveId(mixed $value): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        respond(false, ['error' => 'Mã hóa đơn không hợp lệ.'], 400);
    }
    return $id;
}

function wholeNumber(mixed $value, int $min, int $max, string $field): int
{
    $number = filter_var($value, FILTER_VALIDATE_INT);
    if ($number === false || $number < $min || $number > $max) {
        respond(false, ['error' => "$field phải là số nguyên trong khoảng $min đến $max."], 400);
    }
    return $number;
}

function requirePostAndToken(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(false, ['error' => 'Phương thức không được hỗ trợ.'], 405);
    }
    session_start();
    $expected = $_SESSION['bai10_csrf'] ?? '';
    $actual = postString('token');
    session_write_close();
    if ($expected === '' || !hash_equals($expected, $actual)) {
        respond(false, ['error' => 'Phiên làm việc không hợp lệ. Hãy tải lại trang.'], 403);
    }
}

try {
    require __DIR__ . '/../sanpham/connect.php'; // $db là mysqli của nhóm
    $action = (string)($_REQUEST['action'] ?? '');

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        switch ($action) {
            case 'products':
                $q = trim((string)($_GET['q'] ?? ''));
                if (!validLength($q, 100)) respond(false, ['error' => 'Từ khóa quá dài.'], 400);
                $page = max(1, min(100000, (int)($_GET['page'] ?? 1)));
                $pageSize = 6;
                $like = '%' . $q . '%';
                $counter = $db->prepare('SELECT COUNT(*) AS n FROM sanpham WHERE TENSP LIKE ?');
                $counter->bind_param('s', $like);
                $counter->execute();
                $total = (int)$counter->get_result()->fetch_assoc()['n'];
                $counter->close();
                $pages = max(1, (int)ceil($total / $pageSize));
                $page = min($page, $pages);
                $offset = ($page - 1) * $pageSize;
                $stmt = $db->prepare('SELECT MASP, TENSP, DVT, NUOCSX, GIA, HINHANH, LOAISP FROM sanpham WHERE TENSP LIKE ? ORDER BY MASP LIMIT ? OFFSET ?');
                $stmt->bind_param('sii', $like, $pageSize, $offset);
                $stmt->execute();
                $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                respond(true, ['data' => $data, 'pagination' => ['page' => $page, 'per_page' => $pageSize, 'total' => $total, 'total_pages' => $pages]]);

            case 'product':
                $masp = trim((string)($_GET['masp'] ?? ''));
                $stmt = $db->prepare('SELECT MASP, TENSP, DVT, NUOCSX, GIA, HINHANH, LOAISP FROM sanpham WHERE MASP = ?');
                $stmt->bind_param('s', $masp);
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$product) respond(false, ['error' => 'Không tìm thấy sản phẩm.'], 404);
                respond(true, ['data' => $product]);

            case 'orders':
                $q = trim((string)($_GET['q'] ?? ''));
                if (!validLength($q, 100)) respond(false, ['error' => 'Từ khóa quá dài.'], 400);
                $page = max(1, min(100000, (int)($_GET['page'] ?? 1)));
                $pageSize = 6;
                $like = '%' . $q . '%';
                $counter = $db->prepare('SELECT COUNT(*) AS n FROM hoadon WHERE CAST(SOHD AS CHAR) LIKE ? OR HOTENKH LIKE ?');
                $counter->bind_param('ss', $like, $like);
                $counter->execute();
                $total = (int)$counter->get_result()->fetch_assoc()['n'];
                $counter->close();
                $pages = max(1, (int)ceil($total / $pageSize));
                $page = min($page, $pages);
                $offset = ($page - 1) * $pageSize;
                $stmt = $db->prepare(
                    'SELECT h.SOHD, h.NGHD, h.HOTENKH, h.THANHTOAN, COUNT(c.MASP) AS so_mat_hang,
                            COALESCE(SUM(c.SL * c.DONGIA),0) AS tong_tien
                     FROM hoadon h LEFT JOIN cthd c ON c.SOHD = h.SOHD
                     WHERE CAST(h.SOHD AS CHAR) LIKE ? OR h.HOTENKH LIKE ?
                     GROUP BY h.SOHD, h.NGHD, h.HOTENKH, h.THANHTOAN
                     ORDER BY h.SOHD DESC LIMIT ? OFFSET ?'
                );
                $stmt->bind_param('ssii', $like, $like, $pageSize, $offset);
                $stmt->execute();
                $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                respond(true, ['data' => $data, 'pagination' => ['page' => $page, 'per_page' => $pageSize, 'total' => $total, 'total_pages' => $pages]]);

            case 'order':
                $sohd = positiveId($_GET['sohd'] ?? null);
                $stmt = $db->prepare('SELECT SOHD, NGHD, HOTENKH, SDT, EMAIL, DIACHI, TINH, QUAN, PHUONG, THANHTOAN FROM hoadon WHERE SOHD = ?');
                $stmt->bind_param('i', $sohd);
                $stmt->execute();
                $order = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$order) respond(false, ['error' => 'Không tìm thấy đơn hàng.'], 404);
                $stmt = $db->prepare(
                    'SELECT c.MASP, COALESCE(s.TENSP, c.MASP) AS TENSP, c.SL, c.DONGIA, (c.SL * c.DONGIA) AS THANHTIEN
                     FROM cthd c LEFT JOIN sanpham s ON s.MASP = c.MASP WHERE c.SOHD = ? ORDER BY c.MASP'
                );
                $stmt->bind_param('i', $sohd);
                $stmt->execute();
                $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                respond(true, ['data' => $order, 'items' => $items]);
        }
        respond(false, ['error' => 'API GET không hợp lệ.'], 400);
    }

    requirePostAndToken();
    switch ($action) {
        case 'update_product':
            $masp = postString('masp');
            $tensp = postString('tensp');
            $dvt = postString('dvt');
            $nuocsx = postString('nuocsx');
            $loaisp = postString('loaisp');
            $hinhanh = postString('hinhanh');
            $gia = wholeNumber($_POST['gia'] ?? null, 0, 2147483647, 'Giá');
            if ($masp === '' || $tensp === '' || $dvt === '' || $nuocsx === '' || $loaisp === '' ||
                !validLength($masp, 4) || !validLength($tensp, 50) || !validLength($dvt, 50) ||
                !validLength($nuocsx, 20) || !validLength($loaisp, 20) || !validLength($hinhanh, 40)) {
                respond(false, ['error' => 'Dữ liệu sản phẩm thiếu hoặc vượt độ dài cho phép.'], 400);
            }
            $existsStmt = $db->prepare('SELECT 1 FROM sanpham WHERE MASP = ?');
            $existsStmt->bind_param('s', $masp);
            $existsStmt->execute();
            $productExists = $existsStmt->get_result()->num_rows > 0;
            $existsStmt->close();
            if (!$productExists) respond(false, ['error' => 'Không tìm thấy sản phẩm để cập nhật.'], 404);
            $stmt = $db->prepare('UPDATE sanpham SET TENSP=?, DVT=?, NUOCSX=?, GIA=?, HINHANH=?, LOAISP=? WHERE MASP=?');
            $stmt->bind_param('sssisss', $tensp, $dvt, $nuocsx, $gia, $hinhanh, $loaisp, $masp);
            $stmt->execute();
            $stmt->close();
            respond(true, ['message' => 'Cập nhật sản phẩm thành công.']);

        case 'delete_product':
            $masp = postString('masp');
            if ($masp === '') respond(false, ['error' => 'Mã sản phẩm trống.'], 400);
            $db->begin_transaction();
            // Khóa dòng cha để ngăn phát sinh chi tiết hóa đơn mới trong lúc kiểm tra và xóa.
            $parent = $db->prepare('SELECT MASP FROM sanpham WHERE MASP = ? FOR UPDATE');
            $parent->bind_param('s', $masp);
            $parent->execute();
            $exists = $parent->get_result()->num_rows > 0;
            $parent->close();
            if (!$exists) {
                $db->rollback();
                respond(false, ['error' => 'Sản phẩm không tồn tại.'], 404);
            }
            $stmt = $db->prepare('SELECT 1 FROM cthd WHERE MASP = ? LIMIT 1');
            $stmt->bind_param('s', $masp);
            $stmt->execute();
            $hasOrder = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            if ($hasOrder) {
                $db->rollback();
                respond(false, ['error' => 'Sản phẩm đã xuất hiện trong hóa đơn, không xóa để tránh mất lịch sử đặt hàng.'], 409);
            }
            $stmt = $db->prepare('DELETE FROM sanpham WHERE MASP = ?');
            $stmt->bind_param('s', $masp);
            $stmt->execute();
            $removed = $stmt->affected_rows;
            $stmt->close();
            $db->commit();
            if ($removed === 0) respond(false, ['error' => 'Sản phẩm không tồn tại.'], 404);
            respond(true, ['message' => 'Xóa sản phẩm thành công.']);

        case 'update_order':
            $sohd = positiveId($_POST['sohd'] ?? null);
            $dateInput = postString('nghd');
            $d = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $dateInput);
            if (!$d || $d->format('Y-m-d\TH:i') !== $dateInput) {
                respond(false, ['error' => 'Ngày hóa đơn không hợp lệ.'], 400);
            }
            $nghd = $d->format('Y-m-d H:i:s');
            $hotenkh = postString('hotenkh');
            $sdt = postString('sdt');
            $email = postString('email');
            $diachi = postString('diachi');
            $tinh = postString('tinh');
            $quan = postString('quan');
            $phuong = postString('phuong');
            $thanhtoan = wholeNumber($_POST['thanhtoan'] ?? null, 0, 1, 'Trạng thái thanh toán');
            if ($hotenkh === '' || $sdt === '' || $email === '' || $diachi === '' || $tinh === '' || $quan === '' || $phuong === '' ||
                !validLength($hotenkh, 50) || !validLength($sdt, 15) || !validLength($email, 50) ||
                !validLength($diachi, 100) || !validLength($tinh, 50) || !validLength($quan, 50) || !validLength($phuong, 50) ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9+\-\s]{8,15}$/', $sdt)) {
                respond(false, ['error' => 'Thông tin đơn hàng không hợp lệ. Kiểm tra dữ liệu, email và số điện thoại.'], 400);
            }
            $stmt = $db->prepare('SELECT 1 FROM hoadon WHERE SOHD = ?');
            $stmt->bind_param('i', $sohd);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            if (!$exists) respond(false, ['error' => 'Hóa đơn không tồn tại.'], 404);
            $stmt = $db->prepare('UPDATE hoadon SET NGHD=?, HOTENKH=?, SDT=?, EMAIL=?, DIACHI=?, TINH=?, QUAN=?, PHUONG=?, THANHTOAN=? WHERE SOHD=?');
            $stmt->bind_param('ssssssssii', $nghd, $hotenkh, $sdt, $email, $diachi, $tinh, $quan, $phuong, $thanhtoan, $sohd);
            $stmt->execute();
            $stmt->close();
            respond(true, ['message' => 'Cập nhật đơn hàng thành công.']);

        case 'delete_order':
            $sohd = positiveId($_POST['sohd'] ?? null);
            $db->begin_transaction();
            $stmt = $db->prepare('DELETE FROM cthd WHERE SOHD = ?');
            $stmt->bind_param('i', $sohd);
            $stmt->execute();
            $stmt->close();
            $stmt = $db->prepare('DELETE FROM hoadon WHERE SOHD = ?');
            $stmt->bind_param('i', $sohd);
            $stmt->execute();
            $removed = $stmt->affected_rows;
            $stmt->close();
            if ($removed === 0) {
                $db->rollback();
                respond(false, ['error' => 'Không tìm thấy hóa đơn.'], 404);
            }
            $db->commit();
            respond(true, ['message' => 'Xóa hóa đơn và chi tiết hóa đơn thành công.']);

        case 'update_order_item':
            $sohd = positiveId($_POST['sohd'] ?? null);
            $masp = postString('masp');
            $sl = wholeNumber($_POST['sl'] ?? null, 1, 9999, 'Số lượng');
            $dongia = wholeNumber($_POST['dongia'] ?? null, 0, 2147483647, 'Đơn giá');
            if ($masp === '') respond(false, ['error' => 'Mã sản phẩm không hợp lệ.'], 400);
            $existsStmt = $db->prepare('SELECT 1 FROM cthd WHERE SOHD = ? AND MASP = ?');
            $existsStmt->bind_param('is', $sohd, $masp);
            $existsStmt->execute();
            $itemExists = $existsStmt->get_result()->num_rows > 0;
            $existsStmt->close();
            if (!$itemExists) respond(false, ['error' => 'Không tìm thấy dòng sản phẩm trong hóa đơn.'], 404);
            $stmt = $db->prepare('UPDATE cthd SET SL = ?, DONGIA = ? WHERE SOHD = ? AND MASP = ?');
            $stmt->bind_param('iiis', $sl, $dongia, $sohd, $masp);
            $stmt->execute();
            $stmt->close();
            respond(true, ['message' => 'Cập nhật chi tiết đơn hàng thành công.']);

        case 'delete_order_item':
            $sohd = positiveId($_POST['sohd'] ?? null);
            $masp = postString('masp');
            $db->begin_transaction();
            $stmt = $db->prepare('SELECT MASP FROM cthd WHERE SOHD = ? FOR UPDATE');
            $stmt->bind_param('i', $sohd);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if (count($rows) <= 1) {
                $db->rollback();
                respond(false, ['error' => 'Không thể xóa dòng cuối. Hãy xóa toàn bộ hóa đơn nếu không muốn giữ đơn này.'], 400);
            }
            $stmt = $db->prepare('DELETE FROM cthd WHERE SOHD = ? AND MASP = ?');
            $stmt->bind_param('is', $sohd, $masp);
            $stmt->execute();
            $removed = $stmt->affected_rows;
            $stmt->close();
            if ($removed === 0) {
                $db->rollback();
                respond(false, ['error' => 'Không tìm thấy sản phẩm trong hóa đơn.'], 404);
            }
            $db->commit();
            respond(true, ['message' => 'Xóa sản phẩm khỏi hóa đơn thành công.']);
    }

    respond(false, ['error' => 'API POST không hợp lệ.'], 400);
} catch (mysqli_sql_exception $exception) {
    if (isset($db) && $db instanceof mysqli) {
        try { $db->rollback(); } catch (Throwable $ignored) {}
    }
    error_log('Bai 10 MySQL: ' . $exception->getMessage());
    respond(false, ['error' => 'Lỗi truy vấn MySQL. Kiểm tra CSDL và dữ liệu liên quan.'], 500);
} catch (Throwable $exception) {
    error_log('Bai 10: ' . $exception->getMessage());
    respond(false, ['error' => 'Có lỗi xảy ra khi xử lý yêu cầu.'], 500);
}
