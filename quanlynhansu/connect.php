<?php
$connect = new mysqli('db', 'root', 'root', 'quanlynhansu');
if ($connect->connect_errno) {
    die("Lỗi kết nối CSDL: " . $connect->connect_error);
}
$connect->set_charset('utf8mb4');
?>