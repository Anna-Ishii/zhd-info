document.addEventListener('DOMContentLoaded', function () {
    const modalBtns = document.querySelectorAll('.export-modal-btn');
    const closelBtn = document.getElementById('ExportCloselBtn');
    const allPageBtn = document.getElementById('ExportAllPageBtn');
    const dispPageBtn = document.getElementById('ExportDispPageBtn');
    const modal = document.getElementById('exportModal');

    if (modalBtns.length > 0 && modal && closelBtn &&  allPageBtn && dispPageBtn) {
        modalBtns.forEach((btn) => {
            btn.addEventListener('click', function () {
                modal.style.display = 'flex';
            });
        });

        closelBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
        allPageBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
        dispPageBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }
});