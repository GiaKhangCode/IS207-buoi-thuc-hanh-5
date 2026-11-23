const formLietKe = document.querySelector('#form-liet-ke, #form-da-tra');
const bangKetQua = document.querySelector('#ket-qua');
const daTra = formLietKe.id === 'form-da-tra';
let lanTai = 0;

async function taiDanhSach() {
    if (!formLietKe.reportValidity()) {
        return;
    }
    const lanNay = ++lanTai;
    hienThongBao('');
    dongRong(bangKetQua, 4, 'Đang tải...');

    try {
        const duLieu = await goiApi(daTra ? 'datra' : 'chuatra', new FormData(formLietKe));
        if (lanNay !== lanTai) {
            return;
        }
        bangKetQua.replaceChildren();
        if (!duLieu.cacXe.length) {
            dongRong(bangKetQua, 4);
        }
        duLieu.cacXe.forEach((xe, viTri) => {
            const cacGiaTri = daTra
                ? [viTri + 1, xe.SOXE, xe.TENXE, dinhDangTien.format(xe.GIATHUE)]
                : [viTri + 1, xe.TENKH, xe.SOXE, xe.TENXE];
            taoDong(bangKetQua, cacGiaTri, daTra ? 3 : -1);
        });
    } catch (loi) {
        if (lanNay !== lanTai) {
            return;
        }
        dongRong(bangKetQua, 4, 'Chưa tải được danh sách.');
        hienThongBao(loi.message, true);
    }
}

formLietKe.addEventListener('submit', suKien => {
    suKien.preventDefault();
    taiDanhSach();
});
formLietKe.addEventListener('keydown', suKien => {
    if (suKien.key === 'Enter' && suKien.target.matches('input')) {
        suKien.preventDefault();
        formLietKe.requestSubmit();
    }
});
if (daTra) {
    formLietKe.addEventListener('change', taiDanhSach);
}
taiDanhSach();
