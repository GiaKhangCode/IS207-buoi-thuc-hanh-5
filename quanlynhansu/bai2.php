<?php
include "connect.php";
$thongbao = "";

// Xử lý khi người dùng nhấn nút "Thêm"
if (isset($_POST['btnThem'])) {
    $macn = trim($_POST['macn']);
    $tencn = trim($_POST['tencn']);
    $diachi = trim($_POST['diachi']);
    $mact = $_POST['mact'];

    if ($macn == "" || $tencn == "" || $mact == "") {
        $thongbao = "<p style='color:red;'>Vui lòng nhập đầy đủ Mã chi nhánh, Tên chi nhánh và Công ty!</p>";
    } else {
        $stmt = $connect->prepare("INSERT INTO CHINHANH (MaChiNhanh, TenChiNhanh, DiaChi, MaCongTy) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $macn, $tencn, $diachi, $mact);

        if ($stmt->execute()) {
            $thongbao = "<p style='color:green;'>Thêm chi nhánh thành công!</p>";
        } else {
            $thongbao = "<p style='color:red;'>Thêm thất bại: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Truy vấn danh sách công ty để đổ vào thẻ <select>
$sql_ct = "SELECT MaCongTy, TenCongTy FROM CONGTY";
$result_ct = $connect->query($sql_ct);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài 2 - Thêm chi nhánh</title>
    <style>
        table {
            border-collapse: collapse;
            background-color: #ffccff; 
            width: 380px;
            font-family: "Times New Roman", serif;
        }
        th {
            background-color: #ccffff; 
            padding: 6px;
            font-size: 18px;
        }
        td {
            padding: 6px 10px;
        }
        input[type="text"], select {
            width: 210px;
            padding: 3px;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <table border="1">
        <tr>
            <th colspan="2">Thêm chi nhánh</th>
        </tr>
        <tr>
            <td>Mã chi nhánh</td>
            <td><input type="text" name="macn" placeholder="VD: CN04" required></td>
        </tr>
        <tr>
            <td>Tên chi nhánh</td>
            <td><input type="text" name="tencn" placeholder="VD: Cần Thơ" required></td>
        </tr>
        <tr>
            <td>Địa chỉ</td>
            <td><input type="text" name="diachi" placeholder="VD: 38 An Dương Vương"></td>
        </tr>
        <tr>
            <td>Tên công ty</td>
            <td>
                <select name="mact">
                    <?php
                    if ($result_ct && $result_ct->num_rows > 0) {
                        while ($row = $result_ct->fetch_assoc()) {
                            // Hiển thị TenCongTy cho người dùng chọn, nhưng value gửi đi là MaCongTy
                            echo "<option value='" . $row['MaCongTy'] . "'>" . $row['TenCongTy'] . "</option>";
                        }
                    } else {
                        echo "<option value=''>-- Chưa có công ty --</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input type="submit" name="btnThem" value="Thêm">
                <input type="reset" value="Reset">
            </td>
        </tr>
    </table>
</form>

<!-- Hiển thị thông báo kết quả -->
<?php echo $thongbao; ?>

</body>
</html>
<?php
$connect->close();
?>