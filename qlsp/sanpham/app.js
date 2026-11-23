$(document).ready(function () {
  const $categorySelect = $("#category");
  const $productsContainer = $("#products");
  const $statusBox = $("#status");
  const $cartCount = $("#cart-count");
  const $cartStatus = $("#cart-status");
  const cartToken = $('meta[name="cart-token"]').attr("content");

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
      '<form class="buy-row" data-masp="' +
      escapeHtml(product.MASP) +
      '">' +
      '<label for="qty-' +
      escapeHtml(product.MASP) +
      '">Số lượng</label>' +
      '<input id="qty-' +
      escapeHtml(product.MASP) +
      '" name="soluong" type="number" min="1" max="9999" value="1" required aria-label="Số lượng ' +
      escapeHtml(product.TENSP) +
      '">' +
      '<button type="submit">Chọn mua</button>' +
      "</form></div></article>"
    );
  }

  function loadProducts(category) {
    $productsContainer.empty();
    $statusBox.removeClass("is-error is-empty");

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
          $statusBox.text("Chưa có sản phẩm trong loại này.").addClass("is-empty");
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

  $productsContainer.on("submit", ".buy-row", function (event) {
    event.preventDefault();
    const $form = $(this);
    const $button = $form.find("button");
    if ($button.prop("disabled")) {
      return;
    }

    $button.prop("disabled", true);
    $cartStatus.removeClass("is-error").empty();

    $.ajax({
      url: "../giohang/them.php",
      method: "POST",
      dataType: "json",
      data: {
        masp: $form.attr("data-masp"),
        soluong: $form.find('[name="soluong"]')[0].valueAsNumber,
        token: cartToken,
      },
      success: function (cart) {
        // Các phản hồi AJAX có thể đến không theo thứ tự gửi.
        $cartCount.text(Math.max(Number($cartCount.text()), cart.count));
      },
      error: function (request) {
        $cartStatus
          .text(request.responseJSON?.error || "Không thêm được sản phẩm vào giỏ hàng.")
          .addClass("is-error");
      },
      complete: function () {
        $button.prop("disabled", false);
      },
    });
  });

  loadCategories();
});
