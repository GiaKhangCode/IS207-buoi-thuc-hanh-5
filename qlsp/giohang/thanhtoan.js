$(document).ready(function () {
  const cartToken = $('meta[name="cart-token"]').attr("content") || "";
  const $form = $("#checkout-form");
  const $message = $("#checkout-message");
  const $tinh = $("#tinh");
  const $quan = $("#quan");
  const $phuong = $("#phuong");
  const $btnSubmit = $("#btn-submit");

  // Dữ liệu địa giới hành chính phổ biến để chọn Tỉnh -> Quận/Huyện -> Phường/Xã
  const diaGioi = {
    "TP. Hồ Chí Minh": {
      "TP. Thủ Đức": ["Phường Linh Trung", "Phường Linh Xuân", "Phường Hiệp Phú", "Phường Tăng Nhơn Phú A"],
      "Quận 1": ["Phường Bến Nghé", "Phường Bến Thành", "Phường Đa Kao", "Phường Tân Định"],
      "Quận 5": ["Phường 1", "Phường 4", "Phường 8", "Phường 11"],
      "Quận 10": ["Phường 1", "Phường 12", "Phường 14", "Phường 15"],
      "Quận Bình Thạnh": ["Phường 1", "Phường 19", "Phường 25", "Phường 26"],
    },
    "Hà Nội": {
      "Quận Cầu Giấy": ["Phường Dịch Vọng", "Phường Mai Dịch", "Phường Nghĩa Tân", "Phường Trung Hòa"],
      "Quận Hoàn Kiếm": ["Phường Hàng Bạc", "Phường Hàng Đào", "Phường Tràng Tiền"],
      "Quận Ba Đình": ["Phường Điện Biên", "Phường Kim Mã", "Phường Ngọc Khánh"],
      "Quận Đống Đa": ["Phường Láng Hạ", "Phường Ô Chợ Dừa", "Phường Trung Liệt"],
    },
    "Đà Nẵng": {
      "Quận Hải Châu": ["Phường Hải Châu 1", "Phường Thạch Thang", "Phường Thuận Phước"],
      "Quận Thanh Khê": ["Phường An Khê", "Phường Chính Gián", "Phường Tân Chính"],
      "Quận Sơn Trà": ["Phường An Hải Bắc", "Phường Mân Thái", "Phường Phước Mỹ"],
    },
    "Cần Thơ": {
      "Quận Ninh Kiều": ["Phường An Hòa", "Phường Cái Khế", "Phường Xuân Khánh"],
      "Quận Bình Thủy": ["Phường An Thới", "Phường Bình Thủy", "Phường Long Tuyền"],
    },
    "Bình Dương": {
      "TP. Dĩ An": ["Phường Đông Hòa", "Phường Dĩ An", "Phường Tân Đông Hiệp"],
      "TP. Thủ Dầu Một": ["Phường Hiệp Thành", "Phường Phú Cường", "Phường Phú Lợi"],
    },
    "Đồng Nai": {
      "TP. Biên Hòa": ["Phường Quyết Thắng", "Phường Tân Hiệp", "Phường Trảng Dài"],
      "TP. Long Khánh": ["Phường Xuân An", "Phường Xuân Bình", "Phường Xuân Hòa"],
    },
  };

  function formatMoney(value) {
    return Number(value).toLocaleString("vi-VN") + " đ";
  }

  function showMessage(text, isError) {
    if (!text) {
      $message.removeClass("is-visible is-error is-success").text("");
      return;
    }
    $message
      .text(text)
      .addClass("is-visible")
      .toggleClass("is-error", Boolean(isError))
      .toggleClass("is-success", !isError);
  }

  // Đổ danh sách Tỉnh/Thành phố
  Object.keys(diaGioi).forEach(function (tenTinh) {
    $tinh.append($("<option>").val(tenTinh).text(tenTinh));
  });

  $tinh.on("change", function () {
    const tenTinh = $(this).val();
    $quan.empty().append('<option value="">Vui lòng chọn quận/huyện</option>');
    $phuong.empty().append('<option value="">Vui lòng chọn phường/xã</option>').prop("disabled", true);

    if (!tenTinh || !diaGioi[tenTinh]) {
      $quan.prop("disabled", true);
      return;
    }

    Object.keys(diaGioi[tenTinh]).forEach(function (tenQuan) {
      $quan.append($("<option>").val(tenQuan).text(tenQuan));
    });
    $quan.prop("disabled", false);
  });

  $quan.on("change", function () {
    const tenTinh = $tinh.val();
    const tenQuan = $(this).val();
    $phuong.empty().append('<option value="">Vui lòng chọn phường/xã</option>');

    if (!tenTinh || !tenQuan || !diaGioi[tenTinh]?.[tenQuan]) {
      $phuong.prop("disabled", true);
      return;
    }

    diaGioi[tenTinh][tenQuan].forEach(function (tenPhuong) {
      $phuong.append($("<option>").val(tenPhuong).text(tenPhuong));
    });
    $phuong.prop("disabled", false);
  });

  // Gửi thông tin thanh toán bằng AJAX
  $form.on("submit", function (event) {
    event.preventDefault();
    showMessage("");

    const payload = {
      token: cartToken,
      hoten: $.trim($("#hoten").val()),
      sdt: $.trim($("#sdt").val()),
      email: $.trim($("#email").val()),
      diachi: $.trim($("#diachi").val()),
      tinh: $tinh.val(),
      quan: $quan.val(),
      phuong: $phuong.val(),
    };

    if (
      !payload.hoten ||
      !payload.sdt ||
      !payload.email ||
      !payload.diachi ||
      !payload.tinh ||
      !payload.quan ||
      !payload.phuong
    ) {
      showMessage("Vui lòng điền đầy đủ tất cả thông tin thanh toán.", true);
      return;
    }

    $btnSubmit.prop("disabled", true).text("Đang xử lý...");

    $.ajax({
      url: "xuly_thanhtoan.php",
      method: "POST",
      dataType: "json",
      data: payload,
    })
      .done(function (result) {
        $btnSubmit.text("Đã thanh toán");
        showMessage(result.message || "Thanh toán thành công!", false);
        $("#order-id").text("#" + result.sohd);
        $("#order-total").text(formatMoney(result.total));
        $("#checkout-container").prop("hidden", true);
        $("#order-success").prop("hidden", false);
      })
      .fail(function (request) {
        const err = request.responseJSON?.error || "Không thể thanh toán đơn hàng. Vui lòng thử lại.";
        showMessage(err, true);
        $btnSubmit.prop("disabled", false).text("Thanh toán");
      });
  });
});

