<?php
declare(strict_types=1);

require __DIR__ . '/connect.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $action = $_GET['action'] ?? '';

    if ($action === 'categories') {
        // Thứ tự các loại sản phẩm theo đúng combobox trong hình mẫu.
        $categories = ['Bút', 'Phấn', 'Bảng', 'Tập'];
        echo json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    if ($action === 'products') {
        $category = trim((string)($_GET['category'] ?? ''));
        if ($category === '' || $category === '*') {
            $result = $db->query('SELECT MASP, TENSP, DVT, NUOCSX, GIA, HINHANH, LOAISP FROM sanpham ORDER BY LOAISP, MASP');
        } else {
            $statement = $db->prepare('SELECT MASP, TENSP, DVT, NUOCSX, GIA, HINHANH, LOAISP FROM sanpham WHERE LOAISP = ? ORDER BY MASP');
            $statement->bind_param('s', $category);
            $statement->execute();
            $result = $statement->get_result();
        }

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $row['GIA'] = (int)$row['GIA'];
            $products[] = $row;
        }
        echo json_encode($products, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Yêu cầu không hợp lệ.'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['error' => 'Không tải được dữ liệu sản phẩm. Hãy kiểm tra CSDL đã được khởi tạo.'], JSON_UNESCAPED_UNICODE);
}
