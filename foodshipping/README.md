# Food Shipping

## Tài khoản mẫu

| Tên đăng nhập | Mật khẩu | Chức năng |
| --- | --- | --- |
| `kh001`, `kh002` | `123456` | Đặt món, xem đơn của mình |
| `quanly` | `123456` | Xem và xóa đơn của các khách hàng |

## Cách chạy

1. Bật MySQL hoặc MariaDB. Trong thư mục `foodshipping`, nhập dữ liệu mẫu một lần:

   ```bash
   mysql -u root -p < foodshipping.sql
   ```

2. Kiểm tra thông tin kết nối trong `connect.php`. Mặc định là `localhost`, user `root`, mật khẩu trống. Sau đó chạy:

   ```bash
   php -S 127.0.0.1:8000
   ```

3. Mở [http://127.0.0.1:8000](http://127.0.0.1:8000).
