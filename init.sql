SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================================
-- 1. CSDL QUẢN LÝ NHÂN SỰ (Dùng cho Mục III Bài 1-4 & Mục IV Bài 1-8)
-- Tạo cả quanlynhansu và quanlynhanvien để tương thích với tất cả các đoạn mã trong tài liệu
-- =========================================================================
CREATE DATABASE IF NOT EXISTS `quanlynhansu` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `quanlynhansu`;

CREATE TABLE IF NOT EXISTS `CONGTY` (
    `MaCongTy` VARCHAR(40) PRIMARY KEY,
    `TenCongTy` VARCHAR(100) NOT NULL,
    `DiaChi` VARCHAR(150)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `CHINHANH` (
    `MaChiNhanh` VARCHAR(40) PRIMARY KEY,
    `TenChiNhanh` VARCHAR(100) NOT NULL,
    `DiaChi` VARCHAR(150),
    `MaCongTy` VARCHAR(40),
    CONSTRAINT `fk_cn_ct` FOREIGN KEY (`MaCongTy`) REFERENCES `CONGTY`(`MaCongTy`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `PHONGBAN` (
    `MaPhong` VARCHAR(40) PRIMARY KEY,
    `TenPhong` VARCHAR(100) NOT NULL,
    `MaChiNhanh` VARCHAR(40),
    CONSTRAINT `fk_pb_cn` FOREIGN KEY (`MaChiNhanh`) REFERENCES `CHINHANH`(`MaChiNhanh`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `NHANVIEN` (
    `MaNhanVien` VARCHAR(40) PRIMARY KEY,
    `TenNhanVien` VARCHAR(100) NOT NULL,
    `LuongThang` FLOAT,
    `GioiTinh` TINYINT(4), -- 1: Nam, 0: Nữ
    `MaPhong` VARCHAR(40),
    CONSTRAINT `fk_nv_pb` FOREIGN KEY (`MaPhong`) REFERENCES `PHONGBAN`(`MaPhong`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Dữ liệu mẫu khớp với hình minh họa trong PDF
INSERT INTO `CONGTY` VALUES 
('CT01', 'Viễn Thông A', '12 Tô Vĩnh Ký, Q.12, TP.HCM'),
('CT02', 'FPT Software', 'Khu Công Nghệ Cao, TP. Thủ Đức');

INSERT INTO `CHINHANH` VALUES 
('CN01', 'Thành Phố Hồ Chí Minh', '38 An Dương Vương, Q.5, TP.HCM', 'CT01'),
('CN02', 'Hà Nội', '15 Cầu Giấy, Hà Nội', 'CT01'),
('CN03', 'Đà Nẵng', '20 Nguyễn Văn Linh, Đà Nẵng', 'CT01');

INSERT INTO `PHONGBAN` VALUES 
('PB01', 'Phòng kế toán', 'CN01'),
('PB02', 'Phòng kỹ thuật', 'CN01'),
('PB03', 'Phòng kinh doanh', 'CN01');

INSERT INTO `NHANVIEN` VALUES 
('NV01', 'Nguyễn Xuân Tùng', 12000000, 1, 'PB01'),
('NV02', 'Nguyễn Xuân Tú', 10000000, 1, 'PB02'),
('NV03', 'Nguyễn Xuân Tâm', 15000000, 0, 'PB03');

CREATE DATABASE IF NOT EXISTS `quanlynhanvien` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `quanlynhanvien`;

CREATE TABLE IF NOT EXISTS `CONGTY` LIKE `quanlynhansu`.`CONGTY`;
INSERT INTO `CONGTY` SELECT * FROM `quanlynhansu`.`CONGTY`;

CREATE TABLE IF NOT EXISTS `CHINHANH` LIKE `quanlynhansu`.`CHINHANH`;
INSERT INTO `CHINHANH` SELECT * FROM `quanlynhansu`.`CHINHANH`;

CREATE TABLE IF NOT EXISTS `PHONGBAN` LIKE `quanlynhansu`.`PHONGBAN`;
INSERT INTO `PHONGBAN` SELECT * FROM `quanlynhansu`.`PHONGBAN`;

CREATE TABLE IF NOT EXISTS `NHANVIEN` LIKE `quanlynhansu`.`NHANVIEN`;
INSERT INTO `NHANVIEN` SELECT * FROM `quanlynhansu`.`NHANVIEN`;


-- =========================================================================
-- 2. CSDL QUẢN LÝ SẢN PHẨM & GIỎ HÀNG (Dùng cho Mục III Bài 5-10 & Mục IV Bài 10)
-- =========================================================================
CREATE DATABASE IF NOT EXISTS `qlsp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `qlsp`;

CREATE TABLE IF NOT EXISTS `login` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `sanpham` (
    `MASP` CHAR(4) PRIMARY KEY,
    `TENSP` VARCHAR(50) NOT NULL,
    `DVT` VARCHAR(50),
    `NUOCSX` VARCHAR(20),
    `GIA` INT(11),
    `HINHANH` VARCHAR(255),
    `LOAISP` VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `hoadon` (
    `SOHD` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `NGHD` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `HOTENKH` VARCHAR(50),
    `SDT` CHAR(15),
    `EMAIL` VARCHAR(50),
    `DIACHI` VARCHAR(100),
    `TINH` VARCHAR(50),
    `QUAN` VARCHAR(50),
    `PHUONG` VARCHAR(50),
    `THANHTOAN` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cthd` (
    `SOHD` INT(11),
    `MASP` CHAR(4),
    `SL` INT(11),
    `DONGIA` INT(11),
    PRIMARY KEY (`SOHD`, `MASP`),
    CONSTRAINT `fk_cthd_hd` FOREIGN KEY (`SOHD`) REFERENCES `hoadon`(`SOHD`) ON DELETE CASCADE,
    CONSTRAINT `fk_cthd_sp` FOREIGN KEY (`MASP`) REFERENCES `sanpham`(`MASP`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Dữ liệu mẫu
INSERT INTO `login` (`username`, `password`) VALUES 
('test', '1234'),
('admin', 'admin');

INSERT INTO `sanpham` VALUES 
('SP01', 'But bi', 'cay', 'Viet Nam', 5000, 'butbi.jpg', 'Sản phẩm bút'),
('SP02', 'But chi', 'cay', 'Viet Nam', 3000, 'butchi.jpg', 'Sản phẩm bút'),
('SP03', 'Phan viet bang', 'hop', 'Viet Nam', 5000, 'phanvietbang.jpg', 'Sản phẩm phấn'),
('SP04', 'Phan khong bui', 'hop', 'Nhat Ban', 7000, 'phankhongbui.jpg', 'Sản phẩm phấn'),
('SP05', 'Bang trang', 'cai', 'Viet Nam', 50000, 'bangtrang.jpg', 'Sản phẩm bảng'),
('SP06', 'Tap 200 trang', 'quyen', 'Viet Nam', 12000, 'tap.jpg', 'Sản phẩm tập');


-- =========================================================================
-- 3. CSDL FOOD SHIPPING (Dùng cho Mục IV Bài 9)
-- =========================================================================
CREATE DATABASE IF NOT EXISTS `food_shipping` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `food_shipping`;

CREATE TABLE IF NOT EXISTS `KHACHHANG` (
    `MAKH` VARCHAR(20) PRIMARY KEY,
    `HO` VARCHAR(50),
    `TEN` VARCHAR(50),
    `NGAYSINH` DATE,
    `DIACHI` VARCHAR(150),
    `SDT` VARCHAR(15)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `ACCOUNT` (
    `USERNAME` VARCHAR(50) PRIMARY KEY,
    `MAKH` VARCHAR(20),
    `PASSWORD` VARCHAR(50) NOT NULL,
    CONSTRAINT `fk_acc_kh` FOREIGN KEY (`MAKH`) REFERENCES `KHACHHANG`(`MAKH`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `MONAN` (
    `MAMA` VARCHAR(20) PRIMARY KEY,
    `TENMA` VARCHAR(100) NOT NULL,
    `DONGIA` DECIMAL(12, 0),
    `LOAIMA` VARCHAR(50) -- Món ăn, Topping món ăn, Nước uống, Topping nước uống, Khuyến mãi
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `HOADON` (
    `MAHD` VARCHAR(20) PRIMARY KEY,
    `MAKH` VARCHAR(20),
    `THT` DECIMAL(12, 0),
    `NGAYHD` DATE,
    CONSTRAINT `fk_fs_hd_kh` FOREIGN KEY (`MAKH`) REFERENCES `KHACHHANG`(`MAKH`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `CTHD` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `MAHD` VARCHAR(20),
    `MAMA` VARCHAR(20),
    `MAK` VARCHAR(20) NULL, -- Mã món ăn kèm (dùng cho topping)
    `SL` INT,
    CONSTRAINT `fk_fs_cthd_hd` FOREIGN KEY (`MAHD`) REFERENCES `HOADON`(`MAHD`) ON DELETE CASCADE,
    CONSTRAINT `fk_fs_cthd_ma` FOREIGN KEY (`MAMA`) REFERENCES `MONAN`(`MAMA`)
) ENGINE=InnoDB;

-- Dữ liệu mẫu
INSERT INTO `KHACHHANG` VALUES 
('KH001', 'Nguyen Van', 'A', '2004-05-15', 'KTX Khu B, ĐHQG TP.HCM', '0901234567'),
('KH002', 'Tran Thi', 'B', '2005-08-20', 'Linh Trung, Thủ Đức', '0987654321');

INSERT INTO `ACCOUNT` VALUES 
('nguyenvana', 'KH001', '123456'),
('tranthib', 'KH002', '123456');

INSERT INTO `MONAN` VALUES 
('MA01', 'Trà mãng cầu', 30000, 'Nước uống'),
('MA02', 'Trà chanh giã tay', 20000, 'Nước uống'),
('MA03', 'Mì tôm thanh long (trộn)', 30000, 'Món ăn'),
('MA04', 'Bánh đồng xu phô mai', 30000, 'Món ăn'),
('TP01', 'Trân châu trắng', 5000, 'Topping nước uống'),
('TP02', 'Trân châu đen', 5000, 'Topping nước uống'),
('TP03', 'Thạch rau câu', 5000, 'Topping nước uống'),
('TP04', 'Tóp mỡ', 15000, 'Topping món ăn'),
('KM01', 'Ly cầu vồng (KM)', 0, 'Khuyến mãi');


-- =========================================================================
-- 4. CSDL CHO THUÊ XE TỰ LÁI (Dùng cho Mục IV Bài 11)
-- =========================================================================
CREATE DATABASE IF NOT EXISTS `thuexe` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `thuexe`;

CREATE TABLE IF NOT EXISTS `KHACHHANG` (
    `MAKH` VARCHAR(20) PRIMARY KEY,
    `TENKH` VARCHAR(100) NOT NULL,
    `SDT` VARCHAR(15),
    `DIACHI` VARCHAR(150)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `XE` (
    `SOXE` VARCHAR(20) PRIMARY KEY,
    `TENXE` VARCHAR(50) NOT NULL,
    `HANGXE` VARCHAR(50),
    `SOCHO` VARCHAR(10),
    `NAMSX` VARCHAR(10),
    `DGTHUE` FLOAT,
    `TINHTRANG` TINYINT(1) DEFAULT 0 -- 0: Chưa thuê, 1: Đang cho thuê
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `THUE` (
    `MAT` VARCHAR(20) PRIMARY KEY,
    `MAKH` VARCHAR(20),
    `SOXE` VARCHAR(20),
    `NGAYTHUE` DATE,
    `NGAYTRA` DATE NULL,
    `GIATHUE` FLOAT DEFAULT 0,
    CONSTRAINT `fk_thue_kh` FOREIGN KEY (`MAKH`) REFERENCES `KHACHHANG`(`MAKH`),
    CONSTRAINT `fk_thue_xe` FOREIGN KEY (`SOXE`) REFERENCES `XE`(`SOXE`)
) ENGINE=InnoDB;

-- Dữ liệu mẫu
INSERT INTO `KHACHHANG` VALUES 
('KH01', 'Nguyễn Anh X', '0911222333', 'Quận 1, TP.HCM'),
('KH02', 'Mai Anh Đến', '0944555666', 'TP. Thủ Đức, TP.HCM');

INSERT INTO `XE` VALUES 
('51E-123.45', 'Forester', 'Subaru', '5', '2022', 1000000, 0),
('51A-678.90', 'Vios', 'Toyota', '5', '2021', 900000, 0),
('51B-111.22', 'CRV', 'Honda', '7', '2021', 1100000, 1),
('51B-333.44', 'Seltos', 'KIA', '7', '2020', 800000, 1);

INSERT INTO `THUE` VALUES 
('T01', 'KH01', '51B-111.22', '2026-11-20', NULL, 0),
('T02', 'KH02', '51B-333.44', '2026-11-21', NULL, 0);

SET FOREIGN_KEY_CHECKS = 1;