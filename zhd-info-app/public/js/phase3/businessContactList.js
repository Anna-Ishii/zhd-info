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


    function truncateByChars(el, max = 30) {
        const original = (el.textContent || '').trim();
        if (!original) return;

        let graphemes;
        if (typeof Intl !== 'undefined' && 'Segmenter' in Intl) {
            const seg = new Intl.Segmenter(undefined, { granularity: 'grapheme' });
            graphemes = Array.from(seg.segment(original), s => s.segment);
        } else {
            graphemes = Array.from(original);
        }

        if (graphemes.length > max) {
            el.textContent = graphemes.slice(0, max).join('') + '…';
        }
    }

    const apply = el => {
        const n = parseInt(el.getAttribute('data-truncate'), 10) || 30;
        truncateByChars(el, n);
    };

    document.querySelectorAll('[data-truncate]').forEach(apply);
});