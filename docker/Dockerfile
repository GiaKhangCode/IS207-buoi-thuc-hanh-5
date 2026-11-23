FROM php:8.2-apache

# mysqli (dùng trong đề) và pdo_mysql (cho PrepareStatement nếu muốn dùng PDO)
RUN docker-php-ext-install mysqli pdo_mysql

# Hiện lỗi PHP khi làm lab
RUN { \
      echo "display_errors=On"; \
      echo "error_reporting=E_ALL"; \
      echo "date.timezone=Asia/Ho_Chi_Minh"; \
      echo "default_charset=UTF-8"; \
    } > /usr/local/etc/php/conf.d/lab.ini

# Cho phép duyệt thư mục để bấm vào từng bài ở http://localhost:8080
RUN printf '<Directory /var/www/html>\n  Options Indexes FollowSymLinks\n  AllowOverride All\n</Directory>\n' > /etc/apache2/conf-enabled/lab.conf
