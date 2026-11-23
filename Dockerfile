FROM php:8.2-apache

# Cài đặt extension mysqli và pdo_mysql để kết nối MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Sử dụng file cấu hình php.ini-development để hiển thị đầy đủ thông báo lỗi phục vụ học tập
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

# Bật mod_rewrite của Apache
RUN a2enmod rewrite
