# Lab 5 - Phát triển ứng dụng Web (IS207.R11)

PHP + MySQL + AJAX. Đề bài: `BTTH 4 PHP_MySQL_AJAX 2.pdf`.

| Thư mục        | CSDL         | Nội dung                                         |
| -------------- | ------------ | ------------------------------------------------ |
| `qlsp/login`   | qlsp         | Mục III Bài 5: đăng nhập bằng AJAX               |
| `qlsp/sanpham` | qlsp         | Mục III Bài 6, 7; Mục IV Bài 10 (Live Search)    |
| `qlsp/giohang` | qlsp         | Mục III Bài 8, 9, 10: giỏ hàng, thanh toán       |
| `quanlynhansu` | quanlynhansu | Mục IV Bài 1-8 (và Mục III Bài 1-4 nếu thêm vào) |
| `foodshipping` | riêng        | Mục IV Bài 9                                     |
| `thuexe`       | riêng        | Mục IV Bài 11                                    |
| `sql`          |              | File .sql export của từng CSDL                   |

## Chạy môi trường bằng Docker

```bash
docker compose up -d --build   # khởi động
docker compose down            # dừng (giữ dữ liệu)
docker compose down -v         # dừng và xóa sạch CSDL, lần sau chạy lại file trong sql/
```

| Dịch vụ | Địa chỉ |
|---|---|
| Web (PHP 8.2 + Apache) | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |
| MySQL 8.0 (từ máy, ví dụ MySQL Workbench) | `localhost:3307`, user `root`, không mật khẩu |

Trong code PHP, host CSDL là `db` (không phải `localhost`):

```php
$connect = new mysqli('db', 'root', '', 'qlsp');
```

File `.sql` trong `sql/` chỉ tự chạy khi CSDL khởi tạo lần đầu, theo thứ tự tên file.
Thêm file mới thì chạy `docker compose down -v` rồi `up` lại, hoặc import bằng phpMyAdmin.
