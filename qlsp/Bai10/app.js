"use strict";

const $ = (selector) => document.querySelector(selector);
const csrfToken = $('meta[name="csrf-token"]').content;
const state = { products: { page: 1, q: "" }, orders: { page: 1, q: "" }, currentOrder: null };
let activeProductRequest = 0;
let activeOrderRequest = 0;

function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>"']/g, (c) =>
    ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);
}
function price(value) {
  return Number(value || 0).toLocaleString("vi-VN") + " đ";
}
function notify(message, isError = false) {
  const el = $("#message");
  el.hidden = false;
  el.textContent = message;
  el.className = "message " + (isError ? "error" : "success");
  el.scrollIntoView({ behavior: "smooth", block: "nearest" });
}
function clearNotice() { $("#message").hidden = true; }
function debounce(fn, milliseconds = 250) {
  let timeout;
  return (...args) => { clearTimeout(timeout); timeout = setTimeout(() => fn(...args), milliseconds); };
}
async function request(action, data = null) {
  const isPost = data !== null;
  const options = isPost
    ? { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
        body: new URLSearchParams({ action, token: csrfToken, ...data }).toString() }
    : { method: "GET" };
  const url = isPost ? "api.php" : `api.php?action=${encodeURIComponent(action)}`;
  let response;
  try {
    response = await fetch(url, options);
  } catch {
    throw new Error("Không thể kết nối server. Hãy kiểm tra Docker/Apache và thử lại.");
  }
  let json;
  try { json = await response.json(); }
  catch { throw new Error("Server không trả về JSON. Hãy kiểm tra PHP/MySQL."); }
  if (!response.ok || !json.success) throw new Error(json.error || "Thao tác thất bại.");
  return json;
}
async function getData(action, params = {}) {
  const url = new URL("api.php", window.location.href);
  url.searchParams.set("action", action);
  Object.entries(params).forEach(([key, value]) => url.searchParams.set(key, String(value)));
  let response;
  try { response = await fetch(url); }
  catch { throw new Error("Không kết nối được server."); }
  let json;
  try { json = await response.json(); }
  catch { throw new Error("Phản hồi từ server không phải JSON."); }
  if (!response.ok || !json.success) throw new Error(json.error || "Không tải được dữ liệu.");
  return json;
}

function showTab(target) {
  const isProduct = target === "products";
  $("#product-panel").hidden = !isProduct;
  $("#order-panel").hidden = isProduct;
  $("#product-tab").classList.toggle("active", isProduct);
  $("#order-tab").classList.toggle("active", !isProduct);
  $("#product-tab").setAttribute("aria-selected", String(isProduct));
  $("#order-tab").setAttribute("aria-selected", String(!isProduct));
  clearNotice();
  if (isProduct) loadProducts(); else loadOrders();
}
$("#product-tab").addEventListener("click", () => showTab("products"));
$("#order-tab").addEventListener("click", () => showTab("orders"));

function pagination(container, pagination, type) {
  container.replaceChildren();
  const { page, total, total_pages, per_page } = pagination;
  $(`#${type}-info`).textContent = total
    ? `Hiển thị ${(page - 1) * per_page + 1}–${Math.min(page * per_page, total)} / ${total} dòng`
    : "Không có dữ liệu";
  if (total_pages <= 1) return;

  const add = (label, targetPage, disabled, current = false) => {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = label;
    button.disabled = disabled;
    button.className = "page-button" + (current ? " current" : "");
    if (current) button.setAttribute("aria-current", "page");
    button.addEventListener("click", () => {
      state[type].page = targetPage;
      if (type === "products") loadProducts(); else loadOrders();
    });
    container.append(button);
  };
  add("‹", Math.max(1, page - 1), page === 1);
  for (let p = Math.max(1, page - 2); p <= Math.min(total_pages, page + 2); p++) {
    add(String(p), p, p === page, p === page);
  }
  add("›", Math.min(total_pages, page + 1), page === total_pages);
}

// Câu 1 + Câu 4: Tìm theo từng ký tự, có phân trang ở phía MySQL.
async function loadProducts() {
  const version = ++activeProductRequest;
  $("#product-body").innerHTML = '<tr><td colspan="6">Đang tải...</td></tr>';
  try {
    const result = await getData("products", state.products);
    if (version !== activeProductRequest) return;
    state.products.page = result.pagination.page;
    $("#product-body").innerHTML = result.data.length ? result.data.map((p) => `
      <tr>
        <td>${escapeHtml(p.MASP)}</td>
        <td>${escapeHtml(p.TENSP)}</td>
        <td>${escapeHtml(p.LOAISP)}</td>
        <td>${escapeHtml(p.DVT)}</td>
        <td class="num">${price(p.GIA)}</td>
        <td class="actions">
          <button type="button" class="small edit" data-operation="edit-product" data-id="${escapeHtml(p.MASP)}">Sửa</button>
          <button type="button" class="small danger" data-operation="delete-product" data-id="${escapeHtml(p.MASP)}">Xóa</button>
        </td>
      </tr>`).join("") : '<tr><td colspan="6">Không tìm thấy sản phẩm phù hợp.</td></tr>';
    pagination($("#product-pagination"), result.pagination, "products");
  } catch (error) {
    if (version !== activeProductRequest) return;
    $("#product-body").innerHTML = '<tr><td colspan="6">Không tải được dữ liệu sản phẩm.</td></tr>';
    notify(error.message, true);
  }
}
$("#product-search").addEventListener("input", debounce((event) => {
  state.products.q = event.target.value;
  state.products.page = 1;
  loadProducts();
}));

// Câu 2: Sửa / xóa sản phẩm bằng AJAX.
$("#product-body").addEventListener("click", async (event) => {
  const button = event.target.closest("button[data-operation]");
  if (!button) return;
  const id = button.dataset.id;
  if (button.dataset.operation === "edit-product") {
    try {
      const { data } = await getData("product", { masp: id });
      const form = $("#product-form");
      const fields = { masp: "MASP", tensp: "TENSP", dvt: "DVT", nuocsx: "NUOCSX", gia: "GIA", loaisp: "LOAISP", hinhanh: "HINHANH" };
      Object.entries(fields).forEach(([field, dbName]) => { form.elements[field].value = data[dbName] ?? ""; });
      $("#product-editor").hidden = false;
      $("#product-editor").scrollIntoView({ behavior: "smooth", block: "nearest" });
    } catch (error) { notify(error.message, true); }
    return;
  }
  if (!confirm(`Bạn có chắc muốn xóa sản phẩm ${id}?`)) return;
  button.disabled = true;
  try {
    const result = await request("delete_product", { masp: id });
    if ($("#product-form").elements.masp.value === id) $("#product-editor").hidden = true;
    await loadProducts();
    notify(result.message);
  } catch (error) { notify(error.message, true); }
  finally { button.disabled = false; }
});
$("#product-close").addEventListener("click", () => { $("#product-editor").hidden = true; });
$("#product-form").addEventListener("submit", async (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  if (!form.reportValidity()) return;
  const button = form.querySelector('[type="submit"]');
  button.disabled = true;
  try {
    const result = await request("update_product", Object.fromEntries(new FormData(form)));
    $("#product-editor").hidden = true;
    await loadProducts();
    notify(result.message);
  } catch (error) { notify(error.message, true); }
  finally { button.disabled = false; }
});

// Câu 3: Sửa / xóa hóa đơn và cả từng dòng chi tiết đặt hàng.
async function loadOrders() {
  const version = ++activeOrderRequest;
  $("#order-body").innerHTML = '<tr><td colspan="6">Đang tải...</td></tr>';
  try {
    const result = await getData("orders", state.orders);
    if (version !== activeOrderRequest) return;
    state.orders.page = result.pagination.page;
    $("#order-body").innerHTML = result.data.length ? result.data.map((o) => `
      <tr>
        <td>#${escapeHtml(o.SOHD)}</td>
        <td>${escapeHtml(o.HOTENKH)}</td>
        <td>${escapeHtml(o.NGHD)}</td>
        <td class="num">${escapeHtml(o.so_mat_hang)}</td>
        <td class="num">${price(o.tong_tien)}</td>
        <td class="actions">
          <button type="button" class="small edit" data-operation="edit-order" data-id="${escapeHtml(o.SOHD)}">Sửa</button>
          <button type="button" class="small danger" data-operation="delete-order" data-id="${escapeHtml(o.SOHD)}">Xóa</button>
        </td>
      </tr>`).join("") : '<tr><td colspan="6">Chưa có đơn hàng. Hãy đặt hàng qua trang giỏ hàng để thử câu 3.</td></tr>';
    pagination($("#order-pagination"), result.pagination, "orders");
  } catch (error) {
    if (version !== activeOrderRequest) return;
    $("#order-body").innerHTML = '<tr><td colspan="6">Không tải được danh sách đơn hàng.</td></tr>';
    notify(error.message, true);
  }
}
$("#order-search").addEventListener("input", debounce((event) => {
  state.orders.q = event.target.value;
  state.orders.page = 1;
  loadOrders();
}));

async function openOrder(sohd) {
  const result = await getData("order", { sohd });
  const order = result.data;
  state.currentOrder = Number(order.SOHD);
  const form = $("#order-form");
  const fields = { sohd: "SOHD", hotenkh: "HOTENKH", sdt: "SDT", email: "EMAIL", diachi: "DIACHI", tinh: "TINH", quan: "QUAN", phuong: "PHUONG", thanhtoan: "THANHTOAN" };
  Object.entries(fields).forEach(([field, dbName]) => { form.elements[field].value = order[dbName] ?? ""; });
  form.elements.nghd.value = String(order.NGHD).replace(" ", "T").slice(0, 16);
  $("#order-title").textContent = "#" + order.SOHD;
  $("#order-lines").innerHTML = result.items.length ? result.items.map((line) => `
    <tr data-masp="${escapeHtml(line.MASP)}">
      <td>${escapeHtml(line.MASP)}</td>
      <td>${escapeHtml(line.TENSP)}</td>
      <td><input class="inline-number item-qty" type="number" min="1" max="9999" step="1" value="${escapeHtml(line.SL)}" aria-label="Số lượng ${escapeHtml(line.TENSP)}"></td>
      <td><input class="inline-number item-price" type="number" min="0" max="2147483647" step="1" value="${escapeHtml(line.DONGIA)}" aria-label="Đơn giá ${escapeHtml(line.TENSP)}"></td>
      <td class="num">${price(line.THANHTIEN)}</td>
      <td class="actions">
        <button type="button" class="small edit" data-operation="save-line">Lưu</button>
        <button type="button" class="small danger" data-operation="delete-line">Xóa</button>
      </td>
    </tr>`).join("") : '<tr><td colspan="6">Đơn hàng chưa có sản phẩm.</td></tr>';
  const total = result.items.reduce((sum, line) => sum + Number(line.THANHTIEN), 0);
  $("#order-total").textContent = "Tổng tiền: " + price(total);
  $("#order-editor").hidden = false;
  $("#order-editor").scrollIntoView({ behavior: "smooth", block: "nearest" });
}
$("#order-body").addEventListener("click", async (event) => {
  const button = event.target.closest("button[data-operation]");
  if (!button) return;
  const id = button.dataset.id;
  if (button.dataset.operation === "edit-order") {
    try { await openOrder(id); }
    catch (error) { notify(error.message, true); }
    return;
  }
  if (!confirm(`Xóa hóa đơn #${id} và toàn bộ chi tiết hóa đơn?`)) return;
  button.disabled = true;
  try {
    const result = await request("delete_order", { sohd: id });
    if (state.currentOrder === Number(id)) { state.currentOrder = null; $("#order-editor").hidden = true; }
    await loadOrders();
    notify(result.message);
  } catch (error) { notify(error.message, true); }
  finally { button.disabled = false; }
});
$("#order-close").addEventListener("click", () => { $("#order-editor").hidden = true; state.currentOrder = null; });
$("#order-form").addEventListener("submit", async (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  if (!form.reportValidity()) return;
  const button = form.querySelector('[type="submit"]');
  button.disabled = true;
  try {
    const result = await request("update_order", Object.fromEntries(new FormData(form)));
    await loadOrders();
    await openOrder(state.currentOrder);
    notify(result.message);
  } catch (error) { notify(error.message, true); }
  finally { button.disabled = false; }
});
$("#order-lines").addEventListener("click", async (event) => {
  const button = event.target.closest("button[data-operation]");
  if (!button) return;
  const row = button.closest("tr[data-masp]");
  const masp = row.dataset.masp;
  const sohd = state.currentOrder;
  const operation = button.dataset.operation;
  const data = { sohd, masp };
  if (operation === "delete-line") {
    if (!confirm(`Xóa sản phẩm ${masp} khỏi hóa đơn #${sohd}?`)) return;
  } else {
    const sl = row.querySelector(".item-qty");
    const dongia = row.querySelector(".item-price");
    if (!sl.reportValidity() || !dongia.reportValidity()) return;
    data.sl = sl.value;
    data.dongia = dongia.value;
  }
  button.disabled = true;
  try {
    const result = await request(operation === "delete-line" ? "delete_order_item" : "update_order_item", data);
    await openOrder(sohd);
    await loadOrders();
    notify(result.message);
  } catch (error) { notify(error.message, true); }
  finally { button.disabled = false; }
});

loadProducts();
