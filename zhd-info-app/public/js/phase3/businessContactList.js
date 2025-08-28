document.addEventListener('DOMContentLoaded', function () {
    // アコーディオンメニューの機能
    const accordionHeaders = document.querySelectorAll('.achieve__content__list__name');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function () {
            const content = this.nextElementSibling;
            const toggle = this.querySelector('.achieve__content__list__name__toggle');

            // 現在の状態を確認
            const isOpen = content.classList.contains('is-open');

            // クリックされたアコーディオンのみ開閉
            if (isOpen) {
                // 開いている場合は閉じる
                content.classList.remove('is-open');
                if (toggle) {
                    toggle.classList.remove('is-open');
                }
            } else {
                // 閉じている場合は開く
                content.classList.add('is-open');
                if (toggle) {
                    toggle.classList.add('is-open');
                }
            }
        });
    });
});