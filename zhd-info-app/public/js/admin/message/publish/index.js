$(".editBtn").on("click", function (e) {
    e.preventDefault();
    var targetElement = $(this).parents("tr");
    var message_id = targetElement.attr("data-message_id");

    let uri = new URL(window.location.href);
    let targetUrl = uri.origin + "/admin/message/publish/edit/" + message_id;

    window.location.href = targetUrl;
});

$(".StopBtn").on("click", function (e) {
    e.preventDefault();
    var csrfToken = $('meta[name="csrf-token"]').attr("content");

    var targetElement = $(this).parents("tr");
    var message_id = targetElement.attr("data-message_id");

    let messages = [];
    messages.push(message_id);

    fetch("/admin/message/publish/stop", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
            message_id: messages,
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
            // メッセージの表示や処理を行う
            alert(message);
            window.location.reload();
        })
        .catch((error) => {
            const message = error.message;
            alert(message);
        });
});

$(window).on("load", function () {
    let d = new Date();
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
    $('#updateViewRatesBtn').on('click', function() {
        var overlay = document.getElementById('overlay');
        overlay.style.display = 'block';

        // ページが読み込まれたらオーバーレイを非表示にする
        $(window).on('load', function() {
            overlay.style.display = 'none';
        });
    });
});

// CSVエクスポートボタンのクリックイベントにオーバーレイ表示
$(document).ready(function () {
    $('.exportBtn').on('click', function(e) {
        e.preventDefault(); // デフォルトのリンク動作を防ぐ
        var overlay = document.getElementById('overlay');
        overlay.style.display = 'block';

        // エクスポート処理を実行
        var exportUrl = $(this).attr('href');
        var fileName = $(this).data('filename');
        fetch(exportUrl)
            .then(response => {
                if (response.ok) {
                    return response.blob();
                } else {
                    throw new Error('エクスポートに失敗しました');
                }
            })
            .then(blob => {
                // エクスポートが成功した場合、モーダルを閉じる
                $('#messageExportModal').modal('hide');
                overlay.style.display = 'none';

                // ダウンロードを開始
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
            })
            .catch(error => {
                alert(error.message);
                overlay.style.display = 'none';
            });

        // ページがリロードされる前にオーバーレイを非表示にする
        window.onbeforeunload = function() {
            overlay.style.display = 'none';
        };
    });
});


// ドロップダウンメニューを閉じる
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.dropdown-menu');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });
});

// 選択されたカテゴリを表示
function updateSelectedCategories() {
    const selected = [];
    const checkboxes = document.querySelectorAll('input[name="category[]"]:checked');
    checkboxes.forEach((checkbox) => {
        selected.push(checkbox.nextElementSibling.textContent);
    });
    document.getElementById('selectedCategories').textContent = selected.length > 0 ? selected.join(', ') : '指定なし';

    // すべて選択チェックボックスの状態を更新
    const allCheckbox = document.getElementById('selectAllCategories');
    const allCheckboxes = document.querySelectorAll('input[name="category[]"]');
    allCheckbox.checked = allCheckboxes.length === checkboxes.length;
}

// すべて選択チェックボックスのクリックイベント
document.addEventListener('DOMContentLoaded', updateSelectedCategories);

function toggleAllCategories() {
    const selectAllCheckbox = document.getElementById('selectAllCategories');
    const checkboxes = document.querySelectorAll('input[name="category[]"]');
    checkboxes.forEach((checkbox) => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateSelectedCategories();
}

// 選択された状態を表示
function updateSelectedStatuses() {
    const selected = [];
    const checkboxes = document.querySelectorAll('input[name="status[]"]:checked');
    checkboxes.forEach((checkbox) => {
        selected.push(checkbox.nextElementSibling.textContent);
    });
    document.getElementById('selectedStatus').textContent = selected.length > 0 ? selected.join(', ') : '指定なし';

    // すべて選択チェックボックスの状態を更新
    const allCheckbox = document.getElementById('selectAllStatuses');
    const allCheckboxes = document.querySelectorAll('input[name="status[]"]');
    allCheckbox.checked = allCheckboxes.length === checkboxes.length;
}

// すべて選択チェックボックスのクリックイベント
document.addEventListener('DOMContentLoaded', updateSelectedStatuses);

function toggleAllStatuses() {
    const selectAllCheckbox = document.getElementById('selectAllStatuses');
    const checkboxes = document.querySelectorAll('input[name="status[]"]');
    checkboxes.forEach((checkbox) => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateSelectedStatuses();
}
