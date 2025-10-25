$(".editIcon").on("click", function (e) {
    e.preventDefault();
    var manual_id = $(this).closest("tr").data("manual_id");
    var targetUrl =
        window.location.origin + "/admin/manual/publish/edit/" + manual_id;
    window.location.href = targetUrl;
});

$(".StopBtn").on("click", function (e) {
    e.preventDefault();

    var targetElement = $(this).parents("tr");
    var manual_id = targetElement.attr("data-manual_id");

    // モーダルにmanual_idを設定
    $("#confirmModal").data("manual_id", manual_id).modal("show");

    // モーダルの移動ボタンのクリックイベント
    $(".confirmBtn").on("click", function () {
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var manual_id = $("#confirmModal").data("manual_id");

        let manuals = [];
        manuals.push(manual_id);

        fetch("/admin/manual/publish/stop", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                manual_id: manuals,
            }),
        })
            .then((response) => {
                if (response.ok) {
                    return response.json();
                } else {
                    return response.json().then((data) => {
                        throw new Error(data.message); // エラーメッセージをスロー
                    });
                }
            })
            .then((data) => {
                const message = data.message;
                // メッセージをモーダルに表示
                $("#completeModal .message-content").text(message);
                $("#completeModal").modal("show");

                // モーダルが閉じられたときにページをリロード
                $("#completeModal").on("hidden.bs.modal", function () {
                    window.location.reload();
                });
            })
            .catch((error) => {
                const message = error.message;
                // エラーメッセージをモーダに表示
                $("#completeModal .message-content").text(message);
                $("#completeModal").modal("show");

                // エラーの場合はリロードしない
                $("#completeModal").off("hidden.bs.modal");
            });

        // モーダルを閉じる
        $("#confirmModal").modal("hide");
    });

    // キャンセルボタンのクリックイベントはモーダル内で処理
    $(".cancelBtn").on("click", function () {
        $("#confirmModal").modal("hide");
    });
});

$(".label-title").each((i, dt) => {
    let video = $(dt).find("video");

    if (video.length) {
        video.on("loadedmetadata", function () {
            let duration = video[0]?.duration;
            let min = Math.floor(duration / 60);
            let sec = Math.floor(duration % 60);
            $(dt)
                .parent()
                .find(".label-movie-time")
                .text(`${min} 分 ${sec} 秒`);
        });
    }
});
$(window).on("load", function () {
    var d = new Date();
    /* datetimepicker */
    $.datetimepicker.setLocale("ja");

    $("#publishDateFrom").datetimepicker({
        format: "Y/m/d(D)",
        timepicker: false,
        onShow: function (ct) {
            this.setOptions({
                maxDate: jQuery("#publishDateTo").val()
                    ? jQuery("#publishDateTo").val()
                    : false,
            });
        },
        defaultDate: d,
    });
    $("#publishDateTo").datetimepicker({
        format: "Y/m/d(D)",
        timepicker: false,
        onShow: function (ct) {
            this.setOptions({
                minDate: jQuery("#publishDateFrom").val()
                    ? jQuery("#publishDateFrom").val()
                    : false,
            });
        },
        defaultDate: d,
    });
    $("#readedDateFrom").datetimepicker({
        format: "Y/m/d (D) H:i",
        onShow: function (ct) {
            this.setOptions({
                maxDate: jQuery("#readedDateTo").val()
                    ? jQuery("#readedDateTo").val()
                    : false,
            });
        },
        defaultDate: d,
    });
    $("#readedDateTo").datetimepicker({
        format: "Y/m/d (D) H:i",
        onShow: function (ct) {
            this.setOptions({
                minDate: jQuery("#readedDateFrom").val()
                    ? jQuery("#readedDateFrom").val()
                    : false,
            });
        },
        defaultDate: d,
    });
});

// 更新ボタンのクリックイベントにオーバーレイ表示
$(document).ready(function () {
    $("#updateViewRatesBtn").on("click", function () {
        var overlay = document.getElementById("overlay");
        overlay.style.display = "block";

        // ページが読み込まれたらオーバーレイを非表示にする
        $(window).on("load", function () {
            overlay.style.display = "none";
        });
    });
});

// CSVエクスポートボタンのクリックイベントにオーバーレイ表示
$(document).ready(function () {
    $(".exportBtn").on("click", function (e) {
        e.preventDefault(); // デフォルトのリンク動作を防ぐ
        var overlay = document.getElementById("overlay");
        overlay.style.display = "block";

        // エクスポート処理を実行
        var exportUrl = $(this).attr("href");
        var fileName = $(this).data("filename");
        fetch(exportUrl)
            .then((response) => {
                if (response.ok) {
                    return response.blob();
                } else {
                    throw new Error("エクスポートに失敗しました");
                }
            })
            .then((blob) => {
                // エクスポートが成功した場合、モーダルを閉じる
                $("#manualExportModal").modal("hide");
                overlay.style.display = "none";

                // ダウンロードを開始
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement("a");
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
            })
            .catch((error) => {
                alert(error.message);
                overlay.style.display = "none";
            });

        // ページがリロードされる前にオーバーレイを非表示にする
        window.onbeforeunload = function () {
            overlay.style.display = "none";
        };
    });
});

// ドロップダウンメニューを閉じる
document.addEventListener("click", function (event) {
    const dropdowns = document.querySelectorAll(".dropdown-menu");
    dropdowns.forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove("show");
        }
    });
});

$(function () {
    // --------------------------
    // DOM読み込み後に実行
    // --------------------------
    const $selectAllCategories = $("#selectAllCategories"); // カテゴリ全選択
    const $categories = $(".categoryCheck"); // カテゴリ個別
    const $categoryLabel = $("#selectedCategories"); // カテゴリ表示ラベル

    const $selectAllStatuses = $("#selectAllStatuses"); // 状態全選択
    const $statuses = $('input[name="status[]"]'); // 状態個別
    const $statusLabel = $("#selectedStatus"); // 状態表示ラベル

    const $form = $("form");

    // --------------------------
    // 初期表示（検索後の値を反映）
    // --------------------------
    updateCategoryLabel();
    updateStatusLabel();

    $selectAllCategories.prop(
        "checked",
        $categories.length === $categories.filter(":checked").length
    );
    $selectAllStatuses.prop(
        "checked",
        $statuses.length === $statuses.filter(":checked").length
    );

    // --------------------------
    // カテゴリ個別チェック変更
    // --------------------------
    $categories.on("change", function () {
        updateCategoryLabel();
        $selectAllCategories.prop(
            "checked",
            $categories.length === $categories.filter(":checked").length
        );
    });

    // カテゴリ全選択変更
    window.toggleAllCategories = function () {
        const checked = $selectAllCategories.is(":checked");
        $categories.prop("checked", checked);
        updateCategoryLabel();
    };
    $selectAllCategories.on("change", toggleAllCategories);

    function updateCategoryLabel() {
        const selected = $categories
            .filter(":checked")
            .map(function () {
                return $(this).next("label").text().trim();
            })
            .get();
        $categoryLabel.text(selected.length ? selected.join(", ") : "指定なし");
    }

    // --------------------------
    // 状態個別チェック変更
    // --------------------------
    $statuses.on("change", function () {
        updateStatusLabel();
        $selectAllStatuses.prop(
            "checked",
            $statuses.length === $statuses.filter(":checked").length
        );
    });

    // 状態全選択
    window.toggleAllStatuses = function () {
        const checked = $selectAllStatuses.is(":checked");
        $statuses.prop("checked", checked);
        updateStatusLabel();
    };

    function updateStatusLabel() {
        const selected = $statuses
            .filter(":checked")
            .map(function () {
                return $(this).next("label").text().trim();
            })
            .get();
        $statusLabel.text(selected.length ? selected.join(", ") : "指定なし");
    }

    // --------------------------
    // ページロード時にラベル更新
    // --------------------------
    function initLabels() {
        updateCategoryLabel();
        updateStatusLabel();
    }

    initLabels();
});

document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('.manual-list__search form');
  const keywordInput = document.querySelector('input[name="q"]');

  if (keywordInput && form) {
    keywordInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        form.submit();
      }
    });
  }
});

// 選択された状態を表示
function updateSelectedStatuses() {
    const selected = [];
    const checkboxes = document.querySelectorAll(
        'input[name="status[]"]:checked'
    );
    checkboxes.forEach((checkbox) => {
        selected.push(checkbox.nextElementSibling.textContent);
    });
    document.getElementById("selectedStatus").textContent =
        selected.length > 0 ? selected.join(", ") : "指定なし";

    // すべて選択チェックボックスの状態を更新
    const allCheckbox = document.getElementById("selectAllStatuses");
    const allCheckboxes = document.querySelectorAll('input[name="status[]"]');
    allCheckbox.checked = allCheckboxes.length === checkboxes.length;
}

// すべて選択チェックボックスのクリックイベント
document.addEventListener("DOMContentLoaded", updateSelectedStatuses);

function toggleAllStatuses() {
    const selectAllCheckbox = document.getElementById("selectAllStatuses");
    const checkboxes = document.querySelectorAll('input[name="status[]"]');
    checkboxes.forEach((checkbox) => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateSelectedStatuses();
}

$(document).ready(function () {
    // ページロード時に検索条件を削除
    sessionStorage.removeItem("searchParams");

    // クエリパラメータを取得して保存
    const params = new URLSearchParams(window.location.search);
    if (params.toString()) {
        sessionStorage.setItem("searchParams", params.toString());
        // window.history.replaceState({}, '', window.location.pathname); // URLのパラメータを削除
    }

    // ページロード時に検索条件を復元
    const savedParams = sessionStorage.getItem("searchParams");
    if (savedParams) {
        // URLにパラメーターを追加せずにリクエストを実行
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        fetch("/admin/manual/publish/save-session-conditions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({ params: savedParams }),
        })
            .then((response) => response.json())
            .then((data) => {})
            .catch((error) => {
                console.error("Error:", error);
            });
    }

    // 検索条件を保存
    $(".saveSearchBtn").on("click", function (e) {
        e.preventDefault();
        const csrfToken = $('meta[name="csrf-token"]').attr("content");
        const overlay = document.getElementById("overlay");
        overlay.style.display = "block";

        let baseUrl = "/admin/manual/publish";
        let params = new URLSearchParams();

        // 業態
        params.set("brand", $('select[name="brand"]').val());

        // 形式
        params.set("manual_type", $('select[name="manual_type"]').val());

        // カテゴリ
        $("input.categoryCheck:checked").each(function () {
            params.append("new_category[]", $(this).val());
        });

        // 状態
        $('input[name="status[]"]:checked').each(function () {
            params.append("status[]", $(this).val());
        });

        // 掲載期間
        params.set("publish-date[0]", $('input[name="publish-date[0]"]').val());
        params.set("publish-date[1]", $('input[name="publish-date[1]"]').val());

        // キーワード
        params.set("q", $('input[name="q"]').val());

        let fullUrl = `${baseUrl}?${params.toString()}`;

        fetch("/admin/manual/publish/save-search-conditions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({ url: fullUrl }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    $(".manual-publish.active a").attr("href", fullUrl);
                    alert("検索条件が保存されました。");
                } else {
                    alert("保存に失敗しました。");
                }
            })
            .catch((err) => alert(err.message))
            .finally(() => (overlay.style.display = "none"));
    });

});
