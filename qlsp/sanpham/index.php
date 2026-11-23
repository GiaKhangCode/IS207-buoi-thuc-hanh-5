<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Danh sách sản phẩm | Bài 6</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="page-shell">
        <section class="catalog" aria-label="Danh mục sản phẩm">
            <div class="filter-row">
                <label class="sr-only" for="category">Loại sản phẩm</label>
                <select id="category" name="category">
                    <option value="">Chọn sản phẩm</option>
                </select>
            </div>

            <div id="status" class="status" role="status" aria-live="polite"></div>
            <div id="products" class="product-grid" aria-live="polite"></div>
        </section>
    </main>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="app.js"></script>
</body>
</html>
