const formThue = document.querySelector('#form-thue');
const bangChuaThue = document.querySelector('#xe-chua-thue');
const bangDangThue = document.querySelector('#xe-dang-thue');
let lanTai = 0;
let dangLuu = false;

function hienXe(bang, cacXe, tacVu, nhanNut) {
    bang.replaceChildren();
    if (!cacXe.length) {
        dongRong(bang, 7);
        return;
    }

    for (const xe of cacXe) {
        const dong = taoDong(bang, [
            xe.SOXE, xe.TENXE, xe.HANGXE, xe.NAMSX, xe.SOCHO, dinhDangTien.format(xe.DGTHUE),
        ], 5);
        const oNut = dong.insertCell();
        oNut.className = 'chuc-nang';
        const nut = document.createElement('button');
        nut.type = 'button';
        nut.textContent = nhanNut;
        nut.dataset.tacvu = tacVu;
        nut.dataset.soxe = xe.SOXE;
        nut.dataset.mat = xe.MAT || '';
        oNut.append(nut);
    }
}

async function taiXe() {
    const lanNay = ++lanTai;
    dongRong(bangChuaThue, 7, 'Đang tải...');
    dongRong(bangDangThue, 7, 'Đang tải...');
    if (!formThue.elements.makh.value) {
        dongRong(bangChuaThue, 7, 'Chưa có khách hàng.');
        dongRong(bangDangThue, 7);
        return;
    }

    try {
        const duLieu = await goiApi('danhsach', { makh: formThue.elements.makh.value });
        if (lanNay !== lanTai) {
            return;
        }
        hienXe(bangChuaThue, duLieu.chuaThue, 'thue', 'Thuê');
        hienXe(bangDangThue, duLieu.dangThue, 'huy', 'Không thuê');
    } catch (loi) {
        if (lanNay !== lanTai) {
            return;
        }
        dongRong(bangChuaThue, 7, 'Chưa tải được danh sách.');
        dongRong(bangDangThue, 7, 'Chưa tải được danh sách.');
        hienThongBao(loi.message, true);
    }
}

formThue.addEventListener('submit', suKien => suKien.preventDefault());
formThue.elements.makh.addEventListener('change', () => {
    hienThongBao('');
    taiXe();
});

document.querySelector('main').addEventListener('click', async suKien => {
    const nut = suKien.target.closest('button[data-tacvu]');
    if (!nut || dangLuu) {
        return;
    }
    if (nut.dataset.tacvu === 'thue' && !formThue.reportValidity()) {
        return;
    }

    const thamSo = new FormData(formThue);
    thamSo.set('soxe', nut.dataset.soxe);
    thamSo.set('mat', nut.dataset.mat);
    dangLuu = true;
    const cacControl = document.querySelectorAll('main input, main select, main button');
    cacControl.forEach(control => { control.disabled = true; });
    hienThongBao('Đang lưu...');

    try {
        const duLieu = await goiApi(nut.dataset.tacvu, thamSo, true);
        hienThongBao(duLieu.thongBao);
    } catch (loi) {
        hienThongBao(loi.message, true);
    } finally {
        await taiXe();
        cacControl.forEach(control => { control.disabled = false; });
        dangLuu = false;
    }
});

taiXe();
