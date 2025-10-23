$(function () {
    // タブ切替
    $(".manual__tabs__tab").on("click", function () {
        $(".manual__tabs__tab").removeClass("active");
        $(this).addClass("active");

        const type = $(this).data("type");
        const search = $("#filter").val();

        $.get(route("manual.fetch.type"), { type, search }, function (html) {
            $("#manual-recent").html(html);
        });
    });

    // 中カテゴリ切替
    $(".manual__category__tab__item").on("click", function () {
        $(".manual__category__tab__item").removeClass("active");
        $(this).addClass("active");

        const level2Id = $(this).data("id");
        const search = $("#filter").val();

        $.get(
            route("manual.fetch.category"),
            { category_level2: level2Id, search },
            function (html) {
                $("#manual-list").html(html);
            }
        );
    });

    // 検索
    $("#manual-search-form").on("submit", function (e) {
        e.preventDefault();
        const search = $("#filter").val();

        // 現在のタブ状態
        const type = $(".manual__tabs__tab.active").data("type");
        $.get(route("manual.fetch.type"), { type, search }, function (html) {
            $("#manual-recent").html(html);
        });

        // 現在のカテゴリ状態
        const level2Id = $(".manual__category__tab__item.active").data("id");
        $.get(
            route("manual.fetch.category"),
            { category_level2: level2Id, search },
            function (html) {
                $("#manual-list").html(html);
            }
        );
    });
});
