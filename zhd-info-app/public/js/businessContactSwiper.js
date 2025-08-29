document.addEventListener("DOMContentLoaded", function () {
    const swiper = new Swiper(".business-contact__recent__swiper", {
        direction: "horizontal",
        loop: false,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        slidesPerView: "auto",
        grabCursor: true,
        spaceBetween: 8,
        freeMode: {
            enabled: true,
        },
    });

    // スライド数を取得して設定を決定
    const detailSwiperElement = document.querySelector(
        ".business-contact__detail__swiper"
    );
    const slideCount = detailSwiperElement
        ? detailSwiperElement.querySelectorAll(".swiper-slide").length
        : 0;

    const swiperDetail = new Swiper(".business-contact__detail__swiper", {
        direction: "horizontal",
        loop: false,
        navigation: {
            nextEl: ".swiper-detail-button-next",
            prevEl: ".swiper-detail-button-prev",
        },
        slidesPerView: 1,
        breakpoints: {
            900: {
                slidesPerView: 2,
                centeredSlides: slideCount === 1,
            },
        },
        on: {
            init: function () {
                // 初期化時にページネーションを設定
                updatePagination.call(this);
            },
            slideChange: function () {
                // スライド変更時にページネーションを更新
                updatePagination.call(this);
            },
        },
    });

    // ページネーション更新関数
    function updatePagination() {
        const slides = this.slides;
        const totalSlides = slides.length;
        const currentSlideIndex = this.activeIndex;
        const slidesPerView = this.params.slidesPerView;

        slides.forEach((slide, index) => {
            const currentSlideSpan = slide.querySelector(".current-slide");
            const totalSlidesSpan = slide.querySelector(".total-slides");

            if (currentSlideSpan) {
                // 現在表示されているスライドの番号を計算
                if (slidesPerView === 2) {
                    // 2つ同時表示の場合、表示されているスライドの番号を設定
                    if (index === currentSlideIndex) {
                        currentSlideSpan.textContent = currentSlideIndex + 1;
                    } else if (index === currentSlideIndex + 1) {
                        currentSlideSpan.textContent = currentSlideIndex + 2;
                    }
                } else {
                    // 1つ表示の場合
                    currentSlideSpan.textContent = currentSlideIndex + 1;
                }
            }
            if (totalSlidesSpan) {
                totalSlidesSpan.textContent = totalSlides;
            }
        });
    }
});
