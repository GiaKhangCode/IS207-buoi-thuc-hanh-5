<?php
require_once __DIR__ . '/connect.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Lấy danh sách tất cả công ty để hiển thị vào combobox
$sql_ct = "SELECT MaCongTy, TenCongTy FROM CONGTY ORDER BY MaCongTy";
$result_ct = $connect->query($sql_ct);
$danhSachCongTy = $result_ct ? $result_ct->fetch_all(MYSQLI_ASSOC) : [];

$selected_ct = $_POST['mact'] ?? '';
$danhSachChiNhanh = [];
$daLietKe = false;

// Xử lý khi nhấn nút "Liệt kê"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnLietKe'])) {
    $daLietKe = true;
    if ($selected_ct !== '') {
        $stmt = $connect->prepare("SELECT MaChiNhanh, TenChiNhanh FROM CHINHANH WHERE MaCongTy = ? ORDER BY MaChiNhanh");
        $stmt->bind_param("s", $selected_ct);
        $stmt->execute();
        $res = $stmt->get_result();
        $danhSachChiNhanh = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài 5 - Liệt kê chi nhánh của công ty</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 18px;
            margin: 30px;
        }
        .form-row {
            margin-bottom: 12px;
        }
        select {
            font-family: "Times New Roman", serif;
            font-size: 16px;
            padding: 3px 6px;
            min-width: 170px;
        }
        input[type="submit"] {
            font-family: "Times New Roman", serif;
            font-size: 16px;
            padding: 2px 14px;
            cursor: pointer;
        }
        .table-title {
            font-weight: bold;
            font-size: 18px;
            margin: 20px 0 8px 0;
        }
        table {
            border-collapse: collapse;
            font-family: "Times New Roman", serif;
            font-size: 16px;
            min-width: 320px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            background-color: #ccffff;
            padding: 6px 12px;
            font-weight: bold;
            text-align: center;
        }
        td {
            background-color: #d2a0e0; /* Tone màu tím theo mẫu trong đề bài */
            padding: 6px 12px;
        }
        .col-stt {
            text-align: center;
            width: 50px;
        }
        .col-ten {
            text-align: left;
        }
        .empty-msg {
            color: #555;
            font-style: italic;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <div class="form-row">
        <label for="mact">Tên công ty: </label>
        <select name="mact" id="mact" required>
            <?php if (!empty($danhSachCongTy)): ?>
                <?php foreach ($danhSachCongTy as $ct): ?>
                    <option value="<?= e($ct['MaCongTy']) ?>" <?= ($ct['MaCongTy'] === $selected_ct) ? 'selected' : '' ?>>
                        <?= e($ct['TenCongTy']) ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="">-- Chưa có công ty --</option>
            <?php endif; ?>
        </select>
    </div>

    <div class="form-row">
        <input type="submit" name="btnLietKe" value="Liệt kê">
    </div>
</form>

<?php if ($daLietKe): ?>
    <div class="table-title">Danh sách các chi nhánh</div>
    <?php if (!empty($danhSachChiNhanh)): ?>
        <table>
            <tr>
                <th class="col-stt">STT</th>
                <th class="col-ten">Tên chi nhánh</th>
            </tr>
            <?php foreach ($danhSachChiNhanh as $index => $cn): ?>
                <tr>
                    <td class="col-stt"><?= $index + 1 ?></td>
                    <td class="col-ten"><?= e($cn['TenChiNhanh']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p class="empty-msg">Công ty này hiện chưa có chi nhánh nào.</p>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
<?php
$connect->close();
?>
