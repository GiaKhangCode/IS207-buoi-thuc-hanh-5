const formTra = document.querySelector('#form-tra');
const chonXe = document.querySelector('#xe-tra');
const nutTra = formTra.querySelector('button');
let lanTai = 0;
let dangLuu = false;

function chonLuotThue() {
    const luaChon = chonXe.selectedOptions[0];
    document.querySelector('#so-xe').value = luaChon?.dataset.soxe || '';
    document.querySelector('#ngay-thue').value = luaChon?.dataset.ngaythue || '';
    document.querySelector('#ngay-tra').min = luaChon?.dataset.ngaythue || '';
}

async function taiXeTra() {
    const lanNay = ++lanTai;
    chonXe.replaceChildren(new Option('Đang tải...', ''));
    chonXe.disabled = true;
    nutTra.disabled = true;
    chonLuotThue();

    try {
        const duLieu = await goiApi('danhsach', { makh: formTra.elements.makh.value });
        if (lanNay !== lanTai) {
            return;
        }
        chonXe.replaceChildren();
        for (const xe of duLieu.dangThue) {
            const luaChon = new Option(xe.SOXE + ' - ' + xe.TENXE, xe.MAT);
            luaChon.dataset.soxe = xe.SOXE;
            luaChon.dataset.ngaythue = xe.NGAYTHUE;
            chonXe.append(luaChon);
        }
        if (!duLieu.dangThue.length) {
            chonXe.add(new Option('Khách hàng chưa thuê xe', ''));
        }
        chonXe.disabled = false;
        nutTra.disabled = !duLieu.dangThue.length;
        chonLuotThue();
    } catch (loi) {
        if (lanNay !== lanTai) {
            return;
        }
        chonXe.replaceChildren(new Option('Chưa tải được xe', ''));
        hienThongBao(loi.message, true);
    }
}

formTra.elements.makh.addEventListener('change', () => {
    hienThongBao('');
    taiXeTra();
});
chonXe.addEventListener('change', chonLuotThue);
formTra.addEventListener('submit', async suKien => {
    suKien.preventDefault();
    if (dangLuu || !formTra.reportValidity()) {
        return;
    }
    const thamSo = new FormData(formTra);
    dangLuu = true;
    Array.from(formTra.elements).forEach(control => { control.disabled = true; });
    hienThongBao('Đang lưu...');

    try {
        const duLieu = await goiApi('tra', thamSo, true);
        hienThongBao(duLieu.thongBao);
    } catch (loi) {
        hienThongBao(loi.message, true);
    } finally {
        Array.from(formTra.elements).forEach(control => { control.disabled = false; });
        await taiXeTra();
        dangLuu = false;
    }
});

taiXeTra();
