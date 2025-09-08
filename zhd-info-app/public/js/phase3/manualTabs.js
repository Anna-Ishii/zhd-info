document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------
    // Swiper 初期化（タイプ切替＋検索用スライダー）
    // -------------------------------
    let manualSwiper = new Swiper(".manual__recent__swiper", {
        direction: "horizontal",
        loop: false,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        slidesPerView: "auto",
        grabCursor: true,
        spaceBetween: 8,
        freeMode: { enabled: true },
    });

    const typeTabs = document.querySelectorAll(".manual__tabs__tab");
    const sliderWrapper = document.querySelector(".manual__recent__list");
    const searchInput = document.querySelector("#filter");
    const searchBtn = document.querySelector(".manual__search__btn");

    // タイプ切替＋検索共通処理
    function fetchTypeAndSearch(type, keyword = "") {
        let url = `/manual/filter-by-type?type=${type}`;
        if (keyword) url += `&keyword=${encodeURIComponent(keyword)}`;

        fetch(url)
            .then(res => res.text())
            .then(html => {
                sliderWrapper.innerHTML = html;

                // Swiper 再初期化
                if (manualSwiper) manualSwiper.destroy(true, true);
                manualSwiper = new Swiper(".manual__recent__swiper", {
                    direction: "horizontal",
                    loop: false,
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev",
                    },
                    slidesPerView: "auto",
                    grabCursor: true,
                    spaceBetween: 8,
                    freeMode: { enabled: true },
                });
            });
    }

    // タイプタブクリック
    typeTabs.forEach(tab => {
        tab.addEventListener("click", function () {
            typeTabs.forEach(t => t.classList.remove("active"));
            this.classList.add("active");
            fetchTypeAndSearch(this.dataset.type, searchInput.value);
        });
    });

    // 検索（Enter）
    searchInput.addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            const activeType = document.querySelector(".manual__tabs__tab.active").dataset.type;
            fetchTypeAndSearch(activeType, searchInput.value);
        }
    });

    // 検索ボタンクリック
    searchBtn.addEventListener("click", function () {
        const activeType = document.querySelector(".manual__tabs__tab.active").dataset.type;
        fetchTypeAndSearch(activeType, searchInput.value);
    });

    // -------------------------------
    // カテゴリー切替（大カテゴリー＋中カテゴリー）
    // -------------------------------
    const level1Tabs = document.querySelectorAll(".manual__category__item");
    const level2Wrapper = document.querySelector(".manual__category__tab");
    const categoryWrapper = document.querySelector(".manual__list");

    function fetchCategoryManual(level2Id) {
        fetch(`/manual/filter-by-category?level2_id=${level2Id}`)
            .then(res => res.text())
            .then(html => categoryWrapper.innerHTML = html);
    }

    function setupLevel2Tabs(level2s) {
        level2Wrapper.innerHTML = "";
        if (!level2s || level2s.length === 0) {
            categoryWrapper.innerHTML = "<p>該当するマニュアルはありません。</p>";
            return;
        }

        level2s.forEach((level2, index) => {
            const p = document.createElement("p");
            p.className = "manual__category__tab__item";
            p.dataset.id = level2.id;
            p.textContent = level2.name;
            if (index === 0) p.classList.add("active"); // ← 最初の子をactiveに
            level2Wrapper.appendChild(p);
        });

        // 最初の子カテゴリーでマニュアルを取得
        fetchCategoryManual(level2s[0].id);
    }

    // 大カテゴリークリック
    level1Tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            level1Tabs.forEach(t => t.classList.remove("active"));
            this.classList.add("active");

            // 中カテゴリーとマニュアルをリセット
            level2Wrapper.innerHTML = "";
            categoryWrapper.innerHTML = "<p>該当するマニュアルはありません。</p>";

            const level1Id = this.dataset.id;
            fetch(`/manual/level2-by-level1?level1_id=${level1Id}`)
                .then(res => res.json())
                .then(level2s => setupLevel2Tabs(level2s));
        });
    });

    // 中カテゴリークリック（委譲）
    level2Wrapper.addEventListener("click", function (e) {
        const tab = e.target.closest(".manual__category__tab__item");
        if (!tab) return;

        level2Wrapper.querySelectorAll(".manual__category__tab__item").forEach(t => t.classList.remove("active"));
        tab.classList.add("active");

        fetchCategoryManual(tab.dataset.id);
    });

    // ページロード時の初期化
    const initialLevel1 = document.querySelector(".manual__category__item.active");
    if (initialLevel1) {
        fetch(`/manual/level2-by-level1?level1_id=${initialLevel1.dataset.id}`)
            .then(res => res.json())
            .then(level2s => setupLevel2Tabs(level2s));
    }
});






