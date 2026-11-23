$(document).ready(function () {
  const $body = $("#cart-body");
  const $message = $("#message");
  const cartToken = $('meta[name="cart-token"]').attr("content");

  function formatMoney(value) {
    return Number(value).toLocaleString("vi-VN") + " đ";
  }

  function showMessage(text, isError) {
    $message.text(text).toggleClass("is-error", Boolean(isError));
  }

  function showEmptyState() {
    $("#cart").prop("hidden", true);
    $("#empty, #back-empty").prop("hidden", false);
  }

  function sendToServer(data) {
    return $.ajax({
      url: "capnhat.php",
      method: "POST",
      dataType: "json",
      data: $.extend({ token: cartToken }, data),
    });
  }

  function errorText(request) {
    return request.responseJSON?.error || "Không cập nhật được giỏ hàng.";
  }

  // Tính lại thành tiền của dòng và tổng tiền ngay trên trình duyệt (phản hồi tức thì).
  function recalcLocal() {
    let total = 0;
    $body.find("tr").each(function () {
      const $row = $(this);
      const qty = parseInt($row.find(".qty").val(), 10);
      if (qty >= 1) {
        const lineTotal = qty * Number($row.data("gia"));
        $row.find(".line-total").text(formatMoney(lineTotal));
        total += lineTotal;
      } else {
        total += Number($row.find(".qty").data("saved")) * Number($row.data("gia"));
      }
    });
    $("#total").text(formatMoney(total));
  }

  $body.find(".qty").each(function () {
    $(this).data("saved", $(this).val());
  });

  function saveQuantity($row) {
    const $qty = $row.find(".qty");
    const qty = parseInt($qty.val(), 10);

    if (!(qty >= 1 && qty <= 9999)) {
      showMessage("Số lượng phải từ 1 đến 9.999.", true);
      $qty.val($qty.data("saved"));
      recalcLocal();
      return;
    }

    sendToServer({ action: "update", masp: $row.data("masp"), soluong: qty })
      .done(function (result) {
        $qty.data("saved", result.qty).val(result.qty);
        $row.find(".line-total").text(formatMoney(result.lineTotal));
        $("#total").text(formatMoney(result.total));
        showMessage("");
      })
      .fail(function (request) {
        showMessage(errorText(request), true);
        $qty.val($qty.data("saved"));
        recalcLocal();
      });
  }

  // Đổi số lượng: thành tiền và tổng tiền đổi theo ngay, rồi lưu vào Session.
  $body.on("input", ".qty", recalcLocal);
  $body.on("change", ".qty", function () {
    saveQuantity($(this).closest("tr"));
  });
  $body.on("click", ".btn-edit", function () {
    saveQuantity($(this).closest("tr"));
  });

  $body.on("click", ".btn-del", function () {
    const $row = $(this).closest("tr");
    const name = $row.find(".name").text();
    if (!window.confirm('Xóa "' + name + '" khỏi giỏ hàng?')) {
      return;
    }

    sendToServer({ action: "delete", masp: $row.data("masp") })
      .done(function (result) {
        $row.remove();
        $("#total").text(formatMoney(result.total));
        showMessage("Đã xóa sản phẩm khỏi giỏ hàng.");
        if (result.count === 0) {
          showEmptyState();
        }
      })
      .fail(function (request) {
        showMessage(errorText(request), true);
      });
  });
});
