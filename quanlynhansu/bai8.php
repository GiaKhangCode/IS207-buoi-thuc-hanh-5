<?php
include "connect.php";
$thongbao = "";
$selected_macn = "";

// Xử lý khi người dùng nhấn nút "Cập nhật"
if (isset($_POST['btnCapNhat'])) {
    $macn = trim($_POST['macn'] ?? '');
    $tencn = trim($_POST['tencn'] ?? '');
    $diachi = trim($_POST['diachi'] ?? '');
    $mact = trim($_POST['mact'] ?? '');
    $selected_macn = $macn;

    if ($macn === "" || $tencn === "" || $mact === "") {
        $thongbao = "<p style='color:red;'>Vui lòng nhập đầy đủ Tên chi nhánh và Công ty!</p>";
    } else {
        $stmt = $connect->prepare("UPDATE CHINHANH SET TenChiNhanh = ?, DiaChi = ?, MaCongTy = ? WHERE MaChiNhanh = ?");
        $stmt->bind_param("ssss", $tencn, $diachi, $mact, $macn);

        if ($stmt->execute()) {
            $thongbao = "<p style='color:green;'>Cập nhật thông tin chi nhánh thành công!</p>";
        } else {
            $thongbao = "<p style='color:red;'>Cập nhật thất bại: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . "</p>";
        }
        $stmt->close();
    }
}

// Truy vấn danh sách chi nhánh từ CSDL
$chinhanh_list = [];
$sql_cn = "SELECT MaChiNhanh, TenChiNhanh, DiaChi, MaCongTy FROM CHINHANH ORDER BY MaChiNhanh";
$result_cn = $connect->query($sql_cn);
if ($result_cn && $result_cn->num_rows > 0) {
    while ($row = $result_cn->fetch_assoc()) {
        $chinhanh_list[$row['MaChiNhanh']] = $row;
    }
}

// Xác định chi nhánh đang được chọn (mặc định là chi nhánh đầu tiên)
if ($selected_macn === "" || !isset($chinhanh_list[$selected_macn])) {
    $selected_macn = array_key_first($chinhanh_list) ?? "";
}
$current_cn = $selected_macn !== "" ? $chinhanh_list[$selected_macn] : [
    'MaChiNhanh' => '',
    'TenChiNhanh' => '',
    'DiaChi' => '',
    'MaCongTy' => ''
];

// Truy vấn danh sách công ty từ CSDL
$congty_list = [];
$sql_ct = "SELECT MaCongTy, TenCongTy FROM CONGTY ORDER BY MaCongTy";
$result_ct = $connect->query($sql_ct);
if ($result_ct && $result_ct->num_rows > 0) {
    while ($row = $result_ct->fetch_assoc()) {
        $congty_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài 8 - Sửa thông tin chi nhánh</title>
    <style>
        table {
            border-collapse: collapse;
            background-color: #ffccff;
            width: 400px;
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
            width: 220px;
            padding: 3px;
            box-sizing: border-box;
        }
        input[readonly] {
            background-color: #f0f0f0;
            color: #555;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <table border="1">
        <tr>
            <th colspan="2">Sửa thông tin chi nhánh</th>
        </tr>
        <tr>
            <td>Tên chi nhánh</td>
            <td>
                <select name="chon_macn" id="chon_macn" onchange="loadChiNhanh()">
                    <?php
                    if (!empty($chinhanh_list)) {
                        foreach ($chinhanh_list as $ma => $cn) {
                            $sel = ($ma === $selected_macn) ? "selected" : "";
                            echo "<option value='" . htmlspecialchars($ma, ENT_QUOTES, 'UTF-8') . "' $sel>"
                                . htmlspecialchars($cn['TenChiNhanh'], ENT_QUOTES, 'UTF-8') . "</option>";
                        }
                    } else {
                        echo "<option value=''>-- Chưa có chi nhánh --</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Mã chi nhánh</td>
            <td>
                <input type="text" name="macn" id="macn"
                       value="<?php echo htmlspecialchars($current_cn['MaChiNhanh'], ENT_QUOTES, 'UTF-8'); ?>"
                       readonly required>
            </td>
        </tr>
        <tr>
            <td>Tên chi nhánh</td>
            <td>
                <input type="text" name="tencn" id="tencn"
                       value="<?php echo htmlspecialchars($current_cn['TenChiNhanh'], ENT_QUOTES, 'UTF-8'); ?>"
                       required>
            </td>
        </tr>
        <tr>
            <td>Địa chỉ</td>
            <td>
                <input type="text" name="diachi" id="diachi"
                       value="<?php echo htmlspecialchars($current_cn['DiaChi'], ENT_QUOTES, 'UTF-8'); ?>">
            </td>
        </tr>
        <tr>
            <td>Tên công ty</td>
            <td>
                <select name="mact" id="mact">
                    <?php
                    if (!empty($congty_list)) {
                        foreach ($congty_list as $ct) {
                            $sel = ($ct['MaCongTy'] === $current_cn['MaCongTy']) ? "selected" : "";
                            echo "<option value='" . htmlspecialchars($ct['MaCongTy'], ENT_QUOTES, 'UTF-8') . "' $sel>"
                                . htmlspecialchars($ct['TenCongTy'], ENT_QUOTES, 'UTF-8') . "</option>";
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
                <input type="submit" name="btnCapNhat" value="Cập nhật">
            </td>
        </tr>
    </table>
</form>

<!-- Hiển thị thông báo kết quả -->
<?php echo $thongbao; ?>

<script>
    const chiNhanhData = <?php echo json_encode($chinhanh_list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;

    function loadChiNhanh() {
        const select = document.getElementById("chon_macn");
        const ma = select.value;
        const cn = chiNhanhData[ma];
        if (!cn) return;

        document.getElementById("macn").value = cn.MaChiNhanh || "";
        document.getElementById("tencn").value = cn.TenChiNhanh || "";
        document.getElementById("diachi").value = cn.DiaChi || "";
        document.getElementById("mact").value = cn.MaCongTy || "";
    }
</script>

</body>
</html>
<?php
$connect->close();
?>

