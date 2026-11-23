<?php
require 'chung.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require 'connect.php';

$giaoDich = false;

try {
    $tacVu = nhap('tacvu');
    $duLieu = [];

    switch ($tacVu) {
        case 'danhsach':
            $maKH = nhap('makh');
            $connect->begin_transaction(MYSQLI_TRANS_START_READ_ONLY);
            $giaoDich = true;
            $duLieu['chuaThue'] = $connect->query(
                'SELECT SOXE, TENXE, HANGXE, NAMSX, SOCHO, DGTHUE FROM XE
                 WHERE TINHTRANG = 0 ORDER BY SOXE'
            )->fetch_all(MYSQLI_ASSOC);
            $stmt = $connect->prepare(
                'SELECT t.MAT, x.SOXE, x.TENXE, x.HANGXE, x.NAMSX, x.SOCHO, x.DGTHUE, t.NGAYTHUE
                 FROM THUE t JOIN XE x ON x.SOXE = t.SOXE
                 WHERE t.MAKH = ? AND t.NGAYTRA IS NULL ORDER BY t.NGAYTHUE, t.MAT'
            );
            $stmt->bind_param('s', $maKH);
            $stmt->execute();
            $duLieu['dangThue'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $connect->commit();
            $giaoDich = false;
            break;

        case 'chuatra':
            $ngay = nhap('ngay');
            kiemTraNgay($ngay);
            $stmt = $connect->prepare(
                'SELECT k.TENKH, x.SOXE, x.TENXE
                 FROM THUE t JOIN KHACHHANG k ON k.MAKH = t.MAKH JOIN XE x ON x.SOXE = t.SOXE
                 WHERE t.NGAYTHUE = ? AND t.NGAYTRA IS NULL ORDER BY k.TENKH, x.SOXE'
            );
            $stmt->bind_param('s', $ngay);
            $stmt->execute();
            $duLieu['cacXe'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            break;

        case 'datra':
            $maKH = nhap('makh');
            $tuNgay = nhap('tungay');
            $denNgay = nhap('denngay');
            kiemTraNgay($tuNgay);
            kiemTraNgay($denNgay);
            if ($tuNgay > $denNgay) {
                throw new InvalidArgumentException('Từ ngày phải nhỏ hơn hoặc bằng Đến ngày.');
            }
            $stmt = $connect->prepare(
                'SELECT x.SOXE, x.TENXE, t.GIATHUE
                 FROM THUE t JOIN XE x ON x.SOXE = t.SOXE
                 WHERE t.MAKH = ? AND t.NGAYTRA BETWEEN ? AND ?
                 ORDER BY t.NGAYTRA, t.MAT'
            );
            $stmt->bind_param('sss', $maKH, $tuNgay, $denNgay);
            $stmt->execute();
            $duLieu['cacXe'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            break;

        case 'thue':
        case 'huy':
        case 'tra':
            kiemTraToken();
            $maKH = nhap('makh');
            $soXe = nhap('soxe');
            $maThue = nhap('mat');
            $ngayThue = nhap('ngaythue');
            $ngayTra = nhap('ngaytra');

            if ($tacVu === 'thue') {
                kiemTraNgay($ngayThue);
            }
            if ($tacVu === 'tra') {
                kiemTraNgay($ngayTra);
            }

            $connect->begin_transaction();
            $giaoDich = true;

            // Khóa cùng dòng xe trước mỗi thao tác để tránh hai người thuê cùng lúc.
            $stmt = $connect->prepare('SELECT TINHTRANG, DGTHUE FROM XE WHERE SOXE = ? FOR UPDATE');
            $stmt->bind_param('s', $soXe);
            $stmt->execute();
            $xe = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$xe) {
                throw new InvalidArgumentException('Không tìm thấy xe.');
            }

            if ($tacVu === 'thue') {
                if ($xe['TINHTRANG']) {
                    throw new RuntimeException('Xe vừa được người khác thuê. Vui lòng tải lại danh sách.');
                }
                $stmt = $connect->prepare('SELECT MAKH FROM KHACHHANG WHERE MAKH = ?');
                $stmt->bind_param('s', $maKH);
                $stmt->execute();
                $khachHang = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$khachHang) {
                    throw new InvalidArgumentException('Vui lòng chọn khách hàng hợp lệ.');
                }

                $stmt = $connect->prepare(
                    'SELECT NGAYTRA FROM THUE WHERE SOXE = ? ORDER BY NGAYTRA DESC LIMIT 1'
                );
                $stmt->bind_param('s', $soXe);
                $stmt->execute();
                $lanTruoc = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($lanTruoc && $ngayThue < $lanTruoc['NGAYTRA']) {
                    throw new InvalidArgumentException('Ngày thuê phải từ ngày trả xe gần nhất trở đi.');
                }

                $maThue = 'T' . strtoupper(bin2hex(random_bytes(8)));
                $stmt = $connect->prepare(
                    'INSERT INTO THUE (MAT, MAKH, SOXE, NGAYTHUE) VALUES (?, ?, ?, ?)'
                );
                $stmt->bind_param('ssss', $maThue, $maKH, $soXe, $ngayThue);
                $stmt->execute();
                $stmt->close();
                $tinhTrang = 1;
                $duLieu['thongBao'] = 'Thuê xe thành công.';
            } else {
                $stmt = $connect->prepare(
                    'SELECT NGAYTHUE FROM THUE
                     WHERE MAT = ? AND MAKH = ? AND SOXE = ? AND NGAYTRA IS NULL FOR UPDATE'
                );
                $stmt->bind_param('sss', $maThue, $maKH, $soXe);
                $stmt->execute();
                $luotThue = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$luotThue || !$xe['TINHTRANG']) {
                    throw new RuntimeException('Lượt thuê đã thay đổi. Vui lòng tải lại danh sách.');
                }

                if ($tacVu === 'huy') {
                    $stmt = $connect->prepare('DELETE FROM THUE WHERE MAT = ?');
                    $stmt->bind_param('s', $maThue);
                    $stmt->execute();
                    $stmt->close();
                    $duLieu['thongBao'] = 'Đã hủy thuê xe.';
                } else {
                    if ($ngayTra < $luotThue['NGAYTHUE']) {
                        throw new InvalidArgumentException('Ngày trả phải từ ngày thuê trở đi.');
                    }
                    $tuNgay = kiemTraNgay($luotThue['NGAYTHUE']);
                    $denNgay = kiemTraNgay($ngayTra);
                    $soNgay = max(1, (int) $tuNgay->diff($denNgay)->days);

                    // MySQL tính bằng DECIMAL để giữ chính xác số tiền.
                    $stmt = $connect->prepare(
                        'UPDATE THUE SET NGAYTRA = ?, GIATHUE = ? * CAST(? AS DECIMAL(12, 2)) WHERE MAT = ?'
                    );
                    $stmt->bind_param('siss', $ngayTra, $soNgay, $xe['DGTHUE'], $maThue);
                    $stmt->execute();
                    $stmt->close();
                    $stmt = $connect->prepare('SELECT GIATHUE FROM THUE WHERE MAT = ?');
                    $stmt->bind_param('s', $maThue);
                    $stmt->execute();
                    $giaThue = $stmt->get_result()->fetch_assoc()['GIATHUE'];
                    $stmt->close();
                    $duLieu['thongBao'] = 'Trả xe thành công. Số ngày thuê: ' . $soNgay
                        . '. Tiền thuê: ' . tien($giaThue) . ' đồng.';
                }
                $tinhTrang = 0;
            }

            $stmt = $connect->prepare('UPDATE XE SET TINHTRANG = ? WHERE SOXE = ?');
            $stmt->bind_param('is', $tinhTrang, $soXe);
            $stmt->execute();
            $stmt->close();
            $connect->commit();
            $giaoDich = false;
            break;

        default:
            throw new InvalidArgumentException('Yêu cầu không hợp lệ.');
    }

    echo json_encode($duLieu, JSON_UNESCAPED_UNICODE);
} catch (Throwable $loi) {
    if ($giaoDich) {
        $connect->rollback();
    }
    $thongBao = 'Chưa thực hiện được thao tác. Vui lòng thử lại.';
    http_response_code(500);
    if ($loi instanceof InvalidArgumentException) {
        http_response_code(422);
        $thongBao = $loi->getMessage();
    } elseif ($loi instanceof RuntimeException && !($loi instanceof mysqli_sql_exception)) {
        http_response_code(409);
        $thongBao = $loi->getMessage();
    } else {
        error_log($loi->getMessage());
    }
    echo json_encode(['loi' => $thongBao], JSON_UNESCAPED_UNICODE);
}
$connect->close();
