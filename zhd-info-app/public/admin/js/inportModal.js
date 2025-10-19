document.addEventListener('DOMContentLoaded', function () {
    const modalBtns = document.querySelectorAll('.inport-modal-btn');
    const closelBtn = document.getElementById('inportCloselBtn');
    const openBtn = document.getElementById('inportOpenBtn');
    const modal = document.getElementById('inportModal');

    if (modalBtns.length > 0 && modal && closelBtn &&  openBtn) {
        modalBtns.forEach((btn) => {
            btn.addEventListener('click', function () {
                modal.style.display = 'flex';
            });
        });

        closelBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
        openBtn.addEventListener('click', function () {
            const fileName = this.getAttribute('data-file');
            if (fileName) {
                window.location.href = fileName + ".html";
            } else {
                modal.style.display = 'none';
            }
        });
    }
});