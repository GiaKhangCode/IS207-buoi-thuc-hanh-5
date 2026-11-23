<?php
include "connect.php";

$result_ct = $connect->query("SELECT MaCongTy, TenCongTy FROM CONGTY");
$result_cn = $connect->query("SELECT MaChiNhanh, TenChiNhanh FROM CHINHANH");


$selected_ct = isset($_POST['mact']) ? $_POST['mact'] : '';
$selected_cn = isset($_POST['macn']) ? $_POST['macn'] : '';

$ketqua_html = "";

// Xử lý khi bấm nút "Liệt kê"
if (isset($_POST['btnLietKe'])) {
    if ($selected_ct != "" && $selected_cn != "") {
        // Kiểm tra xem chi nhánh đã chọn có thuộc công ty đã chọn hay không
        $stmt_check = $connect->prepare("SELECT MaChiNhanh FROM CHINHANH WHERE MaChiNhanh = ? AND MaCongTy = ?");
        $stmt_check->bind_param("ss", $selected_cn, $selected_ct);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            // Chi nhánh thuộc công ty -> Truy vấn danh sách phòng ban
            $stmt_pb = $connect->prepare("SELECT TenPhong FROM PHONGBAN WHERE MaChiNhanh = ?");
            $stmt_pb->bind_param("s", $selected_cn);
            $stmt_pb->execute();
            $res_pb = $stmt_pb->get_result();

            $ketqua_html .= "<h3>Danh sách các phòng ban trong chi nhánh</h3>";
            if ($res_pb->num_rows > 0) {
                $ketqua_html .= "<table border='1' cellspacing='0' cellpadding='5'>";
                $ketqua_html .= "<tr style='background-color:#ccffff;'><th>STT</th><th>Tên phòng ban</th></tr>";
                $stt = 1;
                while ($row = $res_pb->fetch_assoc()) {
                    $ketqua_html .= "<tr style='background-color:#ffccff;'>";
                    $ketqua_html .= "<td align='center'>" . $stt++ . "</td>";
                    $ketqua_html .= "<td>" . htmlspecialchars($row['TenPhong']) . "</td>";
                    $ketqua_html .= "</tr>";
                }
                $ketqua_html .= "</table>";
            } else {
                $ketqua_html .= "<p>Chi nhánh này hiện chưa có phòng ban nào.</p>";
            }
            $stmt_pb->close();
        } else {
            // Chi nhánh không thuộc công ty đã chọn
            $ketqua_html .= "<p style='color:red;'>Chi nhánh được chọn không thuộc công ty này!</p>";
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài 7 - Liệt kê phòng ban</title>
    <style>
        body { font-family: "Times New Roman", serif; }
        .form-row { margin-bottom: 10px; }
        select { padding: 3px; min-width: 180px; }
        table { border-collapse: collapse; min-width: 260px; }
    </style>
</head>
<body>

<form method="POST" action="">
    <div class="form-row">
        <label>Tên công ty: </label>
        <select name="mact">
            <?php
            if ($result_ct && $result_ct->num_rows > 0) {
                while ($row = $result_ct->fetch_assoc()) {
                    $sel = ($row['MaCongTy'] == $selected_ct) ? "selected" : "";
                    echo "<option value='{$row['MaCongTy']}' $sel>{$row['TenCongTy']}</option>";
                }
            }
            ?>
        </select>
    </div>

    <div class="form-row">
        <label>Tên chi nhánh: </label>
        <select name="macn">
            <?php
            if ($result_cn && $result_cn->num_rows > 0) {
                while ($row = $result_cn->fetch_assoc()) {
                    $sel = ($row['MaChiNhanh'] == $selected_cn) ? "selected" : "";
                    echo "<option value='{$row['MaChiNhanh']}' $sel>{$row['TenChiNhanh']}</option>";
                }
            }
            ?>
        </select>
    </div>

    <div class="form-row">
        <input type="submit" name="btnLietKe" value="Liệt kê">
    </div>
</form>

<!-- Hiển thị kết quả -->
<?php echo $ketqua_html; ?>

</body>
</html>
<?php
$connect->close();
?>