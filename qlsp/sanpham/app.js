$(document).ready(function () {
  const $categorySelect = $("#category");
  const $productsContainer = $("#products");
  const $statusBox = $("#status");

  function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, function (character) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[character];
    });
  }

  function formatPrice(price) {
    return "Giá: " + Number(price || 0);
  }

  function loadCategories() {
    $.ajax({
      url: "api.php",
      method: "GET",
      data: { action: "categories" },
      dataType: "json",
      success: function (categories) {
        let options = '<option value="">Chọn sản phẩm</option>';
        options += '<option value="*">Chọn tất cả sản phẩm</option>';

        $.each(categories, function (_, category) {
          options +=
            '<option value="' +
            escapeHtml(category) +
            '">Sản phẩm ' +
            escapeHtml(category.toLowerCase()) +
            "</option>";
        });

        $categorySelect.html(options);
        $statusBox.text("Chọn một loại sản phẩm để bắt đầu.");
      },
      error: function () {
        $categorySelect.html(
          '<option value="">Không tải được danh mục</option>',
        );
        $statusBox
          .text(
            "Không kết nối được CSDL. Hãy import database.sql và kiểm tra connect.php.",
          )
          .addClass("is-error");
      },
    });
  }

  function productCard(product) {
    const image = product.HINHANH
      ? "assets/" + encodeURIComponent(product.HINHANH)
      : "";
    const imageMarkup = image
      ? '<img src="' +
        image +
        '" alt="' +
        escapeHtml(product.TENSP) +
        '" loading="lazy">'
      : '<span class="image-fallback" aria-hidden="true">✎</span>';

    return (
      '<article class="product-card">' +
      '<div class="product-image">' +
      imageMarkup +
      "</div>" +
      '<div class="product-info">' +
      "<h2>" +
      escapeHtml(product.TENSP) +
      "</h2>" +
      '<p class="unit">Đơn vị tính: <strong>' +
      escapeHtml(product.DVT) +
      "</strong></p>" +
      '<p class="price">' +
      formatPrice(product.GIA) +
      "</p>" +
      '<form class="buy-row">' +
      '<label for="qty-' +
      escapeHtml(product.MASP) +
      '">Số lượng</label>' +
      '<input id="qty-' +
      escapeHtml(product.MASP) +
      '" type="number" min="1" value="1" aria-label="Số lượng ' +
      escapeHtml(product.TENSP) +
      '">' +
      '<button type="button">Chọn mua</button>' +
      "</form></div></article>"
    );
  }

  function loadProducts(category) {
    $productsContainer.empty();
    $statusBox.removeClass("is-error");

    if (!category) {
      $statusBox.text("Chọn một loại sản phẩm để bắt đầu.");
      return;
    }

    $statusBox.text("Đang tải sản phẩm…");

    $.ajax({
      url: "api.php",
      method: "GET",
      data: { action: "products", category: category },
      dataType: "json",
      success: function (products) {
        if (!products.length) {
          $statusBox.text("Chưa có sản phẩm trong loại này.");
          return;
        }

        $statusBox.text(products.length + " sản phẩm");
        $productsContainer.html(
          $.map(products, function (product) {
            return productCard(product);
          }).join(""),
        );
      },
      error: function () {
        $statusBox
          .text("Không tải được sản phẩm. Hãy kiểm tra CSDL và thử lại.")
          .addClass("is-error");
      },
    });
  }

  $categorySelect.on("change", function () {
    loadProducts($(this).val());
  });

  loadCategories();
});
