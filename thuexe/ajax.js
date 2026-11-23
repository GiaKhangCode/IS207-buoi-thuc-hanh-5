async function goiApi(tacVu, thamSo, ghiDuLieu = false) {
    const duLieu = new URLSearchParams(thamSo);
    duLieu.set('tacvu', tacVu);
    const cauHinh = { headers: { Accept: 'application/json' } };
    let url = 'api.php?' + duLieu.toString();

    if (ghiDuLieu) {
        duLieu.set('token', document.querySelector('meta[name="csrf-token"]').content);
        cauHinh.method = 'POST';
        cauHinh.body = duLieu;
        url = 'api.php';
    }

    const phanHoi = await fetch(url, cauHinh);
    if (!phanHoi.headers.get('content-type')?.includes('application/json')) {
        throw new Error('Máy chủ chưa trả về dữ liệu hợp lệ. Kiểm tra PHP và MySQL.');
    }
    const ketQua = await phanHoi.json();
    if (!phanHoi.ok || ketQua.loi) {
        throw new Error(ketQua.loi || 'Không tải được dữ liệu.');
    }
    return ketQua;
}

function hienThongBao(noiDung, laLoi = false) {
    const thongBao = document.querySelector('#thong-bao');
    thongBao.textContent = noiDung;
    thongBao.classList.toggle('loi', laLoi);
}

function taoDong(bang, cacGiaTri, cotTien = -1) {
    const dong = bang.insertRow();
    cacGiaTri.forEach((giaTri, viTri) => {
        const o = dong.insertCell();
        o.textContent = giaTri;
        if (viTri === cotTien) {
            o.className = 'so';
        }
    });
    return dong;
}

function dongRong(bang, soCot, noiDung = 'Không có dữ liệu.') {
    bang.replaceChildren();
    const o = bang.insertRow().insertCell();
    o.colSpan = soCot;
    o.className = 'rong';
    o.textContent = noiDung;
}

const dinhDangTien = new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 2 });
