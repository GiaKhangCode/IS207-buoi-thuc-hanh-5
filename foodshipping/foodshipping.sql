CREATE DATABASE IF NOT EXISTS foodshipping CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE foodshipping;

CREATE TABLE KHACHHANG (
    MAKH CHAR(5) PRIMARY KEY,
    HO VARCHAR(60) NOT NULL,
    TEN VARCHAR(30) NOT NULL,
    NGAYSINH DATE,
    DIACHI VARCHAR(255) NOT NULL,
    SDT VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE MONAN (
    MAMA CHAR(4) PRIMARY KEY,
    TENMA VARCHAR(100) NOT NULL,
    DONGIA DECIMAL(10, 0) NOT NULL CHECK (DONGIA >= 0),
    LOAIMA ENUM('Món ăn', 'Topping món ăn', 'Nước uống', 'Topping nước uống', 'Khuyến mãi') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE HOADON (
    MAHD VARCHAR(20) PRIMARY KEY,
    MAKH CHAR(5) NOT NULL,
    THT DECIMAL(15, 0) NOT NULL,
    NGAYHD DATE NOT NULL,
    GHICHU VARCHAR(500) NOT NULL DEFAULT '',
    FOREIGN KEY (MAKH) REFERENCES KHACHHANG (MAKH)
) ENGINE=InnoDB;

CREATE TABLE CTHD (
    MACT INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    MAHD VARCHAR(20) NOT NULL,
    MAMA CHAR(4) NOT NULL,
    MAK CHAR(4),
    SL INT UNSIGNED NOT NULL CHECK (SL BETWEEN 1 AND 999),
    DONGIA DECIMAL(10, 0) NOT NULL,
    FOREIGN KEY (MAHD) REFERENCES HOADON (MAHD) ON DELETE CASCADE,
    FOREIGN KEY (MAMA) REFERENCES MONAN (MAMA),
    FOREIGN KEY (MAK) REFERENCES MONAN (MAMA)
) ENGINE=InnoDB;

CREATE TABLE ACCOUNT (
    USERNAME VARCHAR(40) PRIMARY KEY,
    MAKH CHAR(5),
    PASSWORD VARCHAR(255) NOT NULL,
    QUANLY BOOLEAN NOT NULL DEFAULT FALSE,
    CHECK ((QUANLY = TRUE AND MAKH IS NULL) OR (QUANLY = FALSE AND MAKH IS NOT NULL)),
    FOREIGN KEY (MAKH) REFERENCES KHACHHANG (MAKH)
) ENGINE=InnoDB;

INSERT INTO KHACHHANG VALUES
    ('KH001', 'Nguyễn Văn', 'An', '2004-08-19', '12 Tô Vĩnh Ký, Quận 12, TP.HCM', '0900000001'),
    ('KH002', 'Trần Ngọc', 'Mai', '2004-10-21', '25 Võ Thị Sáu, Quận 3, TP.HCM', '0900000002');

INSERT INTO MONAN VALUES
    ('NU01', 'Trà mãng cầu', 30000, 'Nước uống'),
    ('NU02', 'Trà chanh giã tay', 20000, 'Nước uống'),
    ('MA01', 'Mì tôm thanh long (trộn)', 30000, 'Món ăn'),
    ('MA02', 'Bánh đồng xu phô mai', 30000, 'Món ăn'),
    ('TN01', 'Trân châu trắng', 5000, 'Topping nước uống'),
    ('TN02', 'Trân châu đen', 5000, 'Topping nước uống'),
    ('TN03', 'Thạch rau câu', 5000, 'Topping nước uống'),
    ('TA01', 'Tóp mỡ', 15000, 'Topping món ăn'),
    ('TA02', 'Thanh long trắng', 5000, 'Topping món ăn'),
    ('KM01', 'Ly cầu vồng (KM)', 0, 'Khuyến mãi');

-- Mật khẩu dùng thử của cả ba tài khoản là 123456.
INSERT INTO ACCOUNT (USERNAME, MAKH, PASSWORD, QUANLY) VALUES
    ('kh001', 'KH001', '$2y$12$VQACfy7kcBZv76xpoyp03ePV/7cJr..T9NwbvYewG5PnzQY7OFMTa', FALSE),
    ('kh002', 'KH002', '$2y$12$hIosek5wnbJJATcAmMWelensGVqCeP06ncnMMtUKWOUyBhKUzk9ze', FALSE),
    ('quanly', NULL, '$2y$12$VQACfy7kcBZv76xpoyp03ePV/7cJr..T9NwbvYewG5PnzQY7OFMTa', TRUE);

INSERT INTO HOADON VALUES
    ('HD111', 'KH001', 495000, '2021-12-11', 'Nước ít đá, giảm ngọt. Gọi trước khi giao.'),
    ('HD112', 'KH001', 35000, '2021-12-11', ''),
    ('HD418', 'KH001', 60000, '2021-12-25', ''),
    ('HD419', 'KH002', 30000, '2021-12-25', '');

INSERT INTO CTHD (MAHD, MAMA, MAK, SL, DONGIA) VALUES
    ('HD111', 'NU01', NULL, 5, 30000),
    ('HD111', 'TN01', 'NU01', 2, 5000),
    ('HD111', 'TN02', 'NU01', 3, 5000),
    ('HD111', 'NU02', NULL, 5, 20000),
    ('HD111', 'TN03', 'NU02', 1, 5000),
    ('HD111', 'MA01', NULL, 3, 30000),
    ('HD111', 'TA01', 'MA01', 2, 15000),
    ('HD111', 'TA02', 'MA01', 1, 5000),
    ('HD111', 'MA02', NULL, 3, 30000),
    ('HD111', 'KM01', NULL, 5, 0),
    ('HD112', 'MA01', NULL, 1, 30000),
    ('HD112', 'TA02', 'MA01', 1, 5000),
    ('HD418', 'NU01', NULL, 2, 30000),
    ('HD419', 'MA02', NULL, 1, 30000);
