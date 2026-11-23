CREATE DATABASE IF NOT EXISTS qlsp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qlsp.sanpham (
    MASP CHAR(4) NOT NULL,
    TENSP VARCHAR(50) NOT NULL,
    DVT VARCHAR(50) NOT NULL,
    NUOCSX VARCHAR(20) NOT NULL,
    GIA INT(11) NOT NULL,
    HINHANH VARCHAR(40) NOT NULL,
    LOAISP VARCHAR(20) NOT NULL,
    PRIMARY KEY (MASP)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO qlsp.sanpham (MASP, TENSP, DVT, NUOCSX, GIA, HINHANH, LOAISP) VALUES
('P001', 'Phan viet bang', 'hop', 'Nhật Bản', 5000, 'phan-viet-bang.jpg', 'Phấn'),
('P002', 'Phan khong bui', 'hop', 'Nhật Bản', 7000, 'phan-khong-bui.jpg', 'Phấn'),
('B001', 'Bút chì gỗ', 'cây', 'Việt Nam', 5000, 'but-chi-go.jpg', 'Bút'),
('B002', 'Bút bi xanh', 'cây', 'Việt Nam', 7000, 'but-bi-xanh.jpg', 'Bút'),
('B003', 'Bút bi xanh (hộp)', 'hộp', 'Việt Nam', 100000, 'but-bi-xanh.jpg', 'Bút'),
('B004', 'Bút bi xanh ngòi 0,7 mm', 'cây', 'Việt Nam', 20000, 'but-bi-xanh.jpg', 'Bút'),
('B005', 'Bút chì gỗ HB', 'cây', 'Việt Nam', 5000, 'but-chi-go.jpg', 'Bút'),
('B006', 'Bút chì gỗ 2B', 'cây', 'Việt Nam', 6000, 'but-chi-go.jpg', 'Bút'),
('B007', 'Bút chì gỗ 4B', 'cây', 'Việt Nam', 7000, 'but-chi-go.jpg', 'Bút'),
('B008', 'Bút chì gỗ 6B', 'cây', 'Việt Nam', 8000, 'but-chi-go.jpg', 'Bút')
ON DUPLICATE KEY UPDATE MASP = VALUES(MASP);
