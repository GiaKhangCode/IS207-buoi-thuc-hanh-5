<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm | Bài 7</title>
    <style>
        body { font-family: "Times New Roman", serif; max-width: 640px; margin: 0 auto; padding: 16px; }
        label { font-weight: bold; }
        .field { margin-bottom: 14px; }
        .field input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #aaa; border-radius: 4px; }
        .actions { display: flex; gap: 40px; padding-left: 20px; }
        .actions button { width: 110px; padding: 10px; border: 0; border-radius: 8px; color: #fff; font-size: 18px; cursor: pointer; }
        #btn-them { background: #0a6a9b; }
        #btn-reset { background: #9a9a35; }
    </style>
</head>
<body>
    <h1>Thêm sản phẩm</h1>
    <form id="form-sp">
        <div class="field">
            <label for="masp">Mã sản phẩm</label><br>
            <input type="text" id="masp" name="masp" maxlength="4" placeholder="XX00" required>
        </div>
        <div class="field">
            <label for="tensp">Tên sản phẩm</label><br>
            <input type="text" id="tensp" name="tensp" maxlength="50" placeholder="Bút chì" required>
        </div>
        <div class="field">
            <label for="nuocsx">Nước sản xuất</label><br>
            <input type="text" id="nuocsx" name="nuocsx" maxlength="20" placeholder="Việt Nam" required>
        </div>
        <div class="field">
            <label for="dvt">Đơn vị tính</label><br>
            <input type="text" id="dvt" name="dvt" maxlength="50" placeholder="Cây" required>
        </div>
        <div class="field">
            <label for="gia">Giá</label><br>
            <input type="number" id="gia" name="gia" min="0" placeholder="30000" required>
        </div>
        <div class="field">
            <label for="loaisp">Loại sản phẩm</label><br>
            <input type="text" id="loaisp" name="loaisp" maxlength="20" placeholder="Bút" required>
        </div>
        <div class="field">
            <label for="hinhanh">Hình ảnh</label><br>
            <input type="text" id="hinhanh" name="hinhanh" maxlength="40" placeholder="Link hình ảnh" required>
        </div>
        <div class="actions">
            <button type="submit" id="btn-them">Thêm</button>
            <button type="reset" id="btn-reset">Reset</button>
        </div>
    </form>

    <script>
        const form = document.getElementById("form-sp");

        form.addEventListener("submit", function (e) {
            e.preventDefault();

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "themsp.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;

                let res = null;
                try {
                    res = JSON.parse(xhr.responseText);
                } catch (err) {}

                alert(res ? res.message : "Thêm sản phẩm không thành công!");
                if (res && res.success) {
                    form.reset();
                }
            };

            // FormData -> chuỗi urlencoded (masp=...&tensp=...)
            xhr.send(new URLSearchParams(new FormData(form)).toString());
        });
    </script>
</body>
</html>
