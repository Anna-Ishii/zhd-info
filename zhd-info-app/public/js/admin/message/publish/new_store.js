$(document).ready(function () {
    // 初期表示の更新
    updateSelectedStores();
    updateAllParentCheckboxes();

    if ($("#selectStore").val() === "selected") {
        // 店舗選択中の処理
        const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
        $("#checkStore").text(`店舗選択(${selectedCountStore}店舗)`);
    }
    if ($("#selectCsv").val() === "selected") {
        // インポート選択中の処理
        const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
        $("#importCsv").text(`インポート(${selectedCountStore}店舗)`);
    }
});

// CSVインポートモーダルの初期化関数
function initializeCsvImportModal() {
    // 1. ファイル入力をクリア
    $('#csvFileUp').val('');

    // 2. 表示を初期状態に戻す（強制的に）
    $('#messageStoreImportModal .upload-before').show();
    $('#messageStoreImportModal .store-file-uploaded').hide();

    // 3. インポートボタンを無効化
    $('#csvImportBtn').prop('disabled', true);
    $('#csvImportBtn').addClass('disabled-btn');

    // 4. ファイル名とサイズをリセット
    $('#messageStoreImportModal .file__name').text('ファイルを選択してください');
    $('#messageStoreImportModal .file__size').text('0KB');

    // 5. エラーメッセージをクリア
    $('#messageStoreImportModal .alert-danger').remove();

    // 6. 進捗バーをリセット
    $('#messageStoreImportModal .progress').hide();
    $('#messageStoreImportModal .progress-bar').css('width', '0%');

    // 7. newMessageJsonをクリア
    newMessageJson = null;
}

// 店舗選択中の処理
function updateSelectedStores() {
    const selectedCount = $('#storeModal input[name="organization_shops[]"]:checked').length;
    const storeSelectedElement = $('#storeModal #storeSelected');
    if (storeSelectedElement.length) {
        storeSelectedElement.text(`${selectedCount}店舗選択中`);
    }
}

// チェックボックスの連携を設定
function syncCheckboxes(storeId, checked) {
    document.querySelectorAll(`#storeModal input[data-store-id="${storeId}"]`).forEach(function(checkbox) {
        checkbox.checked = checked;
    });

    // 各親組織のチェックボックスを更新
    const organizationId = document.querySelector(`#storeModal input[data-store-id="${storeId}"]`).getAttribute('data-organization-id');
    if (organizationId) {
        updateParentCheckbox(organizationId);
    }
}

// 親チェックボックスの状態を更新
function updateParentCheckbox(organizationId) {
    const parentCheckbox = document.querySelector(`#storeModal input[data-organization-id="${organizationId}"]`);
    if (parentCheckbox) {
        const childCheckboxes = document.querySelectorAll(`#storeModal input[data-organization-id="${organizationId}"].shop-checkbox`);
        const allChecked = Array.from(childCheckboxes).every(checkbox => checkbox.checked);
        parentCheckbox.checked = allChecked;
    }
}

// 全ての親チェックボックスの状態を更新
function updateAllParentCheckboxes() {
    const parentCheckboxes = document.querySelectorAll('#storeModal input.org-checkbox');
    parentCheckboxes.forEach(parentCheckbox => updateParentCheckbox(parentCheckbox.getAttribute('data-organization-id')));
}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 店舗選択モーダルのチェックボックスのイベント ////////////////////////////////////////////////////////////////////////

// チェックボックスの変更イベントリスナーを追加
$(document).on('change', '#storeModal input[name="organization_shops[]"], #storeModal input[name="shops_code[]"]', function() {
    syncCheckboxes($(this).attr('data-store-id'), this.checked);
    updateSelectedStores();
    if ($(this).hasClass('shop-checkbox')) {
        updateParentCheckbox($(this).attr('data-organization-id'));
    }
});

// 親チェックボックスの変更イベントリスナーを追加
$(document).on('change', '#storeModal input.org-checkbox', function() {
    const organizationId = $(this).attr('data-organization-id');
    const checked = this.checked;
        $(`#storeModal input[data-organization-id="${organizationId}"].shop-checkbox`).each(function() {
            this.checked = checked;
            syncCheckboxes($(this).attr('data-store-id'), checked);
        });

        // "選択中のみ表示"がチェックされている場合、すべての項目を表示し、チェックを外す
        if ($('#selectOrganization').is(':checked')) {
            $('#storeModal #byOrganization li').show();
            $('#selectOrganization').prop('checked', false);
        }
        if ($('#selectStoreCode').is(':checked')) {
            $('#storeModal #byStoreCode li').show();
            $('#selectStoreCode').prop('checked', false);
        }

        updateSelectedStores();
});

// 組織単位タブの選択中のみ表示
$(document).on("change", "#selectOrganization", function () {
    if (this.checked) {
        // 子要素（店舗）の表示/非表示
        $('#storeModal input[name="organization_shops[]"]').each(function () {
            const listItem = $(this).closest("li");
            if (this.checked) {
                listItem.show();
            } else {
                listItem.hide();
            }
        });

        // 親要素（org5, org4, org3, org2）の表示/非表示とプルダウンの開閉
        $('#storeModal input[name^="organization[org"]').each(function () {
            const parentListItem = $(this).closest('li');
            const hasCheckedChild = parentListItem.find('input[name="organization_shops[]"]:checked').length > 0;

            // 子要素がチェックされていれば親要素のプルダウンを開く
            if (hasCheckedChild) {
                parentListItem.show();
                // 親要素のプルダウンを開く
                const collapseElement = parentListItem.find('.collapse');
                collapseElement.collapse('show');
            } else {
                parentListItem.hide();
            }
        });
    } else {
        // すべての子要素と親要素を表示し、親要素のプルダウンを閉じる
        $('#storeModal input[name="organization_shops[]"]').each(function () {
            $(this).closest("li").show();
        });

        // すべての親要素を表示し、プルダウンを閉じる
        $('#storeModal input[name^="organization[org"]').each(function () {
            const parentListItem = $(this).closest("li");
            parentListItem.show();
            const collapseElement = parentListItem.find('.collapse');
            collapseElement.collapse('hide');
        });
    }
});

// 店舗コード順タブの選択中のみ表示
$(document).on("change", "#selectStoreCode", function () {
    if (this.checked) {
        // チェックされている項目のみ表示
        $('#storeModal input[name="shops_code[]"]').each(function () {
            const listItem = $(this).closest("li");
            if (this.checked) {
                listItem.show();
            } else {
                listItem.hide();
            }
        });
    } else {
        // すべての項目を表示
        $('#storeModal input[name="shops_code[]"]').each(function () {
            $(this).closest("li").show();
        });
    }
});

// 組織単位タブの全選択/選択解除
$(document).on("change", "#selectAllOrganization", function () {
    var overlay = $('#overlay');
    overlay.css('display', 'block');  // オーバーレイを表示

    const checked = this.checked;
    const items = $('#storeModal #byOrganization input[type="checkbox"]').toArray(); // 組織のチェックボックス
    let index = 0;

    // 全選択/選択解除の処理
    function processNextBatch(deadline) {
        while (index < items.length && deadline.timeRemaining() > 0) {
            const item = items[index];
            if ($(item).attr("id") !== "selectOrganization") {
                item.checked = checked;
            }
            if ($(item).hasClass("shop-checkbox")) {
                syncCheckboxes($(item).attr("data-store-id"), checked);
            }
            index++;
        }

        if (index < items.length) {
            requestIdleCallback(processNextBatch);
        } else {
            finishProcess(); // 全選択/解除処理の後処理
        }
    }

    // 全選択/解除処理の後処理：状態を更新
    function finishProcess() {
        if ($('#selectOrganization').is(':checked')) {
            $('#storeModal #byOrganization li').show();
            $('#selectOrganization').prop('checked', false);
        }
        if ($('#selectStoreCode').is(':checked')) {
            $('#storeModal #byStoreCode li').show();
            $('#selectStoreCode').prop('checked', false);
        }

        // 親要素の状態をリセット
        if (!checked) {
            $('#storeModal input[name^="organization[org"]').each(function () {
                const parentListItem = $(this).closest("li");
                parentListItem.show();
                const collapseElement = parentListItem.find('.collapse');
                collapseElement.collapse('hide');
            });
        }

        updateSelectedStores();

        // オーバーレイを非表示にする
        overlay.css('display', 'none');
    }

    requestIdleCallback(processNextBatch); // 最初のアイドル時間で処理を開始
});

// 店舗コード順タブの全選択/選択解除
$(document).on("change", "#selectAllStoreCode", function () {
    var overlay = $('#overlay');
    overlay.css('display', 'block');  // オーバーレイを表示

    const checked = this.checked;
    const items = $('#storeModal #byStoreCode input[type="checkbox"]').toArray(); // 店舗コードのチェックボックス
    let index = 0;

    // 全選択/選択解除の処理
    function processNextBatch(deadline) {
        while (index < items.length && deadline.timeRemaining() > 0) {
            const item = items[index];
            if ($(item).attr("id") !== "selectStoreCode") {
                item.checked = checked;
            }
            if ($(item).hasClass("shop-checkbox")) {
                syncCheckboxes($(item).attr("data-store-id"), checked);
            }
            index++;
        }

        if (index < items.length) {
            requestIdleCallback(processNextBatch);
        } else {
            finishProcess(); // 全選択/解除処理の後処理
        }
    }

// 店舗選択モーダルのチェックボックスのイベント ////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    // 処理の後、状態を更新
    function finishProcess() {
        if ($('#selectOrganization').is(':checked')) {
            $('#storeModal #byOrganization li').show();
            $('#selectOrganization').prop('checked', false);
        }
        if ($('#selectStoreCode').is(':checked')) {
            $('#storeModal #byStoreCode li').show();
            $('#selectStoreCode').prop('checked', false);
        }

        updateSelectedStores();

        // オーバーレイを非表示にする
        overlay.css('display', 'none');
    }

    requestIdleCallback(processNextBatch); // 最初のアイドル時間で処理を開始
});

// check-selected クラスを削除と隠し入力フィールドの値を空にする
function removeSelectedClass() {
    // すべてのボタンから check-selected クラスを削除
    $(".check-store-list .btn").removeClass("check-selected");

    // 隠し入力フィールドの値を空にする
    $("#checkOrganization5").val("");
    $("#checkOrganization4").val("");
    $("#checkOrganization3").val("");
    $("#checkOrganization2").val("");
    $("#checkOrganizationShops").val("");

    // フォームクリア（全店ボタン）
    $("#selectOrganizationAll").val("");
    $("#selectStore").val("");
    $("#selectCsv").val("");
}

// チェックされているチェックボックスの値を隠し入力フィールドに値を割り当てる
function changeValues() {
    // チェックされているチェックボックスの値を取得
    const selectedOrg5Values = $('#storeModal input[name="organization[org5][]"]:checked').map(function() { return this.value; }).get();
    const selectedOrg4Values = $('#storeModal input[name="organization[org4][]"]:checked').map(function() { return this.value; }).get();
    const selectedOrg3Values = $('#storeModal input[name="organization[org3][]"]:checked').map(function() { return this.value; }).get();
    const selectedOrg2Values = $('#storeModal input[name="organization[org2][]"]:checked').map(function() { return this.value; }).get();
    const selectedShopValues = $('#storeModal input[name="organization_shops[]"]:checked').map(function() { return this.value; }).get();

    const chunkSize = 100; // チャンクサイズを設定

    // チャンクに値がある場合のみ隠し入力フィールドに追加
    if (selectedOrg5Values.length > 0) {
        processInChunks(selectedOrg5Values, chunkSize, chunk => appendChunkValue("#checkOrganization5", chunk));
    }
    if (selectedOrg4Values.length > 0) {
        processInChunks(selectedOrg4Values, chunkSize, chunk => appendChunkValue("#checkOrganization4", chunk));
    }
    if (selectedOrg3Values.length > 0) {
        processInChunks(selectedOrg3Values, chunkSize, chunk => appendChunkValue("#checkOrganization3", chunk));
    }
    if (selectedOrg2Values.length > 0) {
        processInChunks(selectedOrg2Values, chunkSize, chunk => appendChunkValue("#checkOrganization2", chunk));
    }
    if (selectedShopValues.length > 0) {
        processInChunks(selectedShopValues, chunkSize, chunk => appendChunkValue("#checkOrganizationShops", chunk));
    }
}

// チャンクの値を追加する関数
function appendChunkValue(selector, chunk) {
    // 既存の値を取得
    const currentValue = $(selector).val();
    // 既存の値に新しいチャンクを追加
    const newValue = currentValue ? currentValue + "," + chunk.join(",") : chunk.join(",");
    $(selector).val(newValue);
}

// チャンク処理
function processInChunks(array, chunkSize, callback, doneCallback) {
    let index = 0;

    function processNextChunk() {
        const chunk = array.slice(index, index + chunkSize);
        index += chunkSize;

        // 現在のチャンクを処理
        callback(chunk);

        // まだ残りがあれば次のチャンクを処理
        if (index < array.length) {
            setTimeout(processNextChunk, 0);
        } else if (doneCallback) {
            doneCallback();
        }
    }

    processNextChunk();
}


///////////////////////////////////////////////////////////////////////////////////////////
// 親画面のイベント ////////////////////////////////////////////////////////////////////////

// 全店ボタン処理
$(document).on('click', '#checkAll[data-action="all"]', function() {
    removeSelectedClass();
    // 全ての organization_shops[] チェックボックスをチェックする
    $('#storeModal input[name="organization_shops[]"]').each(function() {
        $(this).prop('checked', true);
        syncCheckboxes($(this).attr("data-store-id"), true);
    });
    // 全ての親チェックボックスをチェックする
    $('#storeModal input.org-checkbox').each(function() {
        $(this).prop('checked', true);
    });
    // 全選択ボタン チェックボックスをチェックする
    $('#storeModal #selectAllOrganization').each(function() {
        $(this).prop('checked', true);
    });
    $('#storeModal #selectAllStoreCode').each(function() {
        $(this).prop('checked', true);
    });
    // チェックされているチェックボックスの値を隠し入力フィールドに値を割り当てる
    changeValues();
    // フォームクリア（全店ボタン）
    $('#selectOrganizationAll').val("selected");
    // 店舗選択、インポートボタンをもとに戻す
    $('#checkStore').text('店舗選択');
    $('#importCsv').text('インポート');
    // 選択中の店舗数を更新する
    updateSelectedStores();
    // ボタンの見た目を変更する
    $(this).addClass("check-selected");
});

// 店舗選択ボタン処理
$(document).on('click', '#checkStore[data-action="store"]', function() {
    // モーダルタイトル変更
    var storeModalTitle = $("#messageStoreModal h4.modal-title");
    if (storeModalTitle.length) {
        storeModalTitle.html('店舗を選択してください。');
    }

    // 通常モードに切り替え
    // 元のボタンのセレクターを取得して、新しいボタンのセレクターに変更
    var selectCsvButton = $("#selectCsvBtn");
    if (selectCsvButton.length) {
        selectCsvButton.attr("id", "selectStoreBtn");
    }
    // キャンセルボタン表示
    $('#cancelBtn').show();
    // CSVモードの再インポートボタン削除
    if ($('#csvReImportBtn').length) {
        $('#messageStoreModal .modal-footer #csvReImportBtn').remove();
    }

    // キャンセルボタン処理
    // 隠し入力フィールドの値を取得
    const org5Values = $("#checkOrganization5").val().split(",");
    const org4Values = $("#checkOrganization4").val().split(",");
    const org3Values = $("#checkOrganization3").val().split(",");
    const org2Values = $("#checkOrganization2").val().split(",");
    const shopValues = $("#checkOrganizationShops").val().split(",");

    let allOrg_flg = true;
    let allStore_flg = true;
    // チェックボックスを更新
    if ($('input[name="organization[org5][]"]').length > 0) {
        $('input[name="organization[org5][]"]').each(function() {
            if (org5Values.includes($(this).val())) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    }
    if ($('input[name="organization[org4][]"]').length > 0) {
        $('input[name="organization[org4][]"]').each(function() {
            if (org4Values.includes($(this).val())) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    }
    if ($('input[name="organization[org3][]"]').length > 0) {
        $('input[name="organization[org3][]"]').each(function() {
            if (org3Values.includes($(this).val())) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    }
    if ($('input[name="organization[org2][]"]').length > 0) {
        $('input[name="organization[org2][]"]').each(function() {
            if (org2Values.includes($(this).val())) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    }
    $('input[name="organization_shops[]"]').each(function() {
        if (shopValues.includes($(this).val())) {
            $(this).prop('checked', true);
        } else {
            allOrg_flg = false;
            $(this).prop('checked', false);
        }
    });
    $('input[name="shops_code[]"]').each(function() {
        if (shopValues.includes($(this).val())) {
            $(this).prop('checked', true);
        } else {
            allStore_flg = false;
            $(this).prop('checked', false);
        }
    });
    $('#selectAllOrganization').prop('checked', allOrg_flg);
    $('#selectAllStoreCode').prop('checked', allStore_flg);

    // 店舗選択中の処理
    updateSelectedStores();
});

// インポートボタン処理
$(document).on('click', '#importCsv[data-action="import"]', function() {
    // CSVモードかどうかを判定
    const isCsvMode = $("#importCsv").hasClass("check-selected") &&
                        $("#selectCsv").val() === "selected";

    if (isCsvMode) {
        // CSVモード時：CSVモードの店舗選択画面を表示（カスタムモーダル）
        $('#messageStoreModal').modal('show');

    } else {
        // 通常モード時：CSVインポートモーダルを開く（カスタムモーダル）
        // ボタンIDの変更（CSVモード用）
        var selectStoreButton = document.getElementById("selectStoreBtn");
        if (selectStoreButton) {
            selectStoreButton.id = "selectCsvBtn";
        }

        // UI要素の設定
        $('#cancelBtn').hide();

        // 再インポートボタンの追加
        if (!$('#csvReImportBtn').length) {
            $('#messageStoreModal .modal-footer').append(`<button class="c-btn__blue disabled-btn" id="csvReImportBtn" data-file="bb_sk_inport_csv">再インポート</button>`);
        }

        // CSVインポートモーダルを開く
        $('#messageStoreImportModal').addClass('disp');
    }
});

// エクスポートボタン処理
$(document).on('click', '#exportCsv', function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    let formData = new FormData();
    var organization1Id = $('.check-store-list input[name="organization1_id"]').val();
    formData.append("organization1_id", organization1Id);

    $.ajax({
        url: '/admin/message/publish/csv/store/export',
        type: 'post',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
        xhrFields: {
            responseType: 'blob' // レスポンスのタイプをBlobに設定
        },
    }).done(function(response, textStatus, jqXHR){
        var blob = new Blob([response], { type: 'text/csv' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');

        // サーバーからファイル名を取得する
        var disposition = jqXHR.getResponseHeader('Content-Disposition');
        var fileName = disposition ? disposition.split('filename=')[1].split(';')[0].replace(/"/g, '') : 'export.csv';

        a.href = url;
        a.download = "店舗選択_" + fileName;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url); // オブジェクトURLを解放
        document.body.removeChild(a); // 一時的に生成したリンクを削除

    }).fail(function(jqXHR, textStatus, errorThrown){
        var errorMessage = 'An error occurred. Please try again later.';

        if (jqXHR.status === 422) {
            errorMessage = 'Validation error. Please check your input and try again.';
        } else if (jqXHR.status === 504) {
            errorMessage = 'Server timeout. Please try again later.';
        } else if (jqXHR.status === 500) {
            errorMessage = 'Internal server error. Please try again later.';
        }

        console.log('Error: ' + jqXHR.status + ' - ' + textStatus);
        alert(errorMessage);
    });
});

// 親画面のイベント ////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////
// 店舗選択モーダルのイベント ////////////////////////////////////////////////////////////////////////

// 通常モード店舗選択モーダルの選択処理
// 通常モード店舗選択モーダルで選択した店舗データを保存し、UIを通常モードに切り替える
$(document).on('click', '#selectStoreBtn', function() {
    removeSelectedClass();
    // チェックされているチェックボックスの値を隠し入力フィールドに値を割り当てる
    changeValues();
    // フォームクリア（店舗選択ボタン）
    $("#selectStore").val("selected");
    // インポートボタンをもとに戻す
    $("#importCsv").text('インポート');
    // モーダルを閉じる
    $("#messageStoreModal").modal("hide");
    // check-selected クラスを追加
    $("#checkStore").addClass("check-selected");
    // 店舗選択中の処理
    const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
    $('#checkStore').text(`店舗選択(${selectedCountStore}店舗)`);
});

// モーダルが閉じられる際にchangeValuesを実行
$('#storeModal').on('hidden.bs.modal', function () {
    changeValues();
});

// CSVモード店舗選択モーダルの選択処理
// CSVモード店舗選択モーダルで選択した店舗データを保存し、UIをCSVモードに切り替える
$(document).on('click', '#selectCsvBtn', function() {
    removeSelectedClass();
    // チェックされているチェックボックスの値を隠し入力フィールドに値を割り当てる
    changeValues();
    // フォームクリア（CSVインポートボタン）
    $("#selectCsv").val("selected");
    // モーダルを閉じる
    $("#messageStoreModal").modal("hide");
    // 店舗選択ボタンをもとに戻す
    $("#checkStore").text('店舗選択');
    // check-selected クラスを追加
    $("#importCsv").addClass("check-selected");
    // 店舗選択中の処理
    const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
    $('#importCsv').text(`インポート(${selectedCountStore}店舗)`);
});

// CSVモード店舗選択モーダルの再インポートボタン処理
$(document).on('click', '#csvReImportBtn[data-action="reImport"]', function() {
    // モーダルを閉じる
    $("#messageStoreModal").modal("hide");

    // ファイル入力をクリア
    $('#csvFileUp').val('');

    // モーダル内の要素を直接ターゲット
    $('#messageStoreImportModal .upload-before').show();
    $('#messageStoreImportModal .store-file-uploaded').hide();

    // インポートボタンを無効化
    $('#csvImportBtn').prop('disabled', true);
    $('#csvImportBtn').addClass('disabled-btn');

    newMessageJson = null;

    // インポートモーダルを開く
    $('#messageStoreImportModal').addClass('disp');
});

// 店舗選択モーダルのイベント ////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////
// CSVインポートモーダルのイベント ////////////////////////////////////////////////////////////////////////

// CSVインポートモーダルの閉じるボタン処理
$(document).on('click', '#inportCloselBtn', function() {
    // インポートモーダルを閉じる
    $("#messageStoreImportModal").removeClass("disp");

    // ファイル入力をクリア
    $('#csvFileUp').val('');
});

// CSVインポートモーダルのファイル削除ボタン処理
$(document).on('click', '.file__delete_btn_store', function() {
    // エラーメッセージをクリア
    $('#messageStoreImportModal .middle-erea .alert-danger').remove();

    // ファイル入力をクリア
    $('#csvFileUp').val('');

    // モーダル内の要素を直接ターゲット
    $('#messageStoreImportModal .upload-before').show();
    $('#messageStoreImportModal .store-file-uploaded').hide();

    // インポートボタンを無効化
    $('#csvImportBtn').prop('disabled', true);
    $('#csvImportBtn').addClass('disabled-btn');

    newMessageJson = null;
});

/* ファイル検知 */
function changeFileName(e){
	if(e.val() == ''){
		$('#messageStoreImportModal .upload-before').show();
		$('#messageStoreImportModal .store-file-uploaded').hide();
	}else{
		let chkFileName = e.prop('files')[0].name;
		let fileSize = e.prop('files')[0].size;
		let fileSizeKB = (fileSize / 1024).toFixed(1) + 'KB';

		// ファイル名を設定（モーダル内で直接検索）
		$('#messageStoreImportModal .file__name').text(chkFileName);
		// ファイルサイズを設定（モーダル内で直接検索）
		$('#messageStoreImportModal .file__size').text(fileSizeKB);

		// 表示切り替え（モーダル内で直接検索）
		$('#messageStoreImportModal .upload-before').hide();
		$('#messageStoreImportModal .store-file-uploaded').show();
	}
}

// CSVインポートモーダルのファイル変更イベント処理
let newMessageJson; // 店舗CSV保持用
$(document).on('change', '#csvFileUp', function() {
    // ファイル名変更処理
    let changeTarget = $(this);
    changeFileName(changeTarget);

    // インポートボタンを有効化
    $('#csvImportBtn').prop('disabled', false);
    $('#csvImportBtn').removeClass('disabled-btn');

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

	let log_file_name = getNumericDateTime();
    let formData = new FormData();
    formData.append("file", $(this)[0].files[0]);
	formData.append("organization1", $('#messageStoreImportModal input[name="organization1"]').val())
	formData.append("log_file_name", log_file_name)

	let button = $('#csvImportBtn'); // IDを直接指定

    var labelForm = $(this).parent();
    var progress = labelForm.parent().find('.progress');
    var progressBar = progress.children(".progress-bar");

    progressBar.hide();
    progressBar.css('width', 0 + '%');
    progress.show();

	let progress_request = true;

	$('#messageStoreImportModal .middle-erea .alert-danger').remove();

    $.ajax({
        url: '/admin/message/publish/csv/store/upload',
        type: 'post',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
    }).done(function(response){
		progress_request = false;
		button.prop("disabled", false);
		button.removeClass('disabled-btn');
        labelForm.parent().find('.text-danger').remove();
		newMessageJson = response.json;

    }).fail(function(jqXHR, textStatus, errorThrown){
        button.prop("disabled", true);
        button.addClass('disabled-btn');
		$('#messageStoreImportModal .middle-erea').prepend(`
			<div class="alert alert-danger">
				<ul></ul>
			</div>
		`);
		const errorUl =  $('#messageStoreImportModal .middle-erea .alert ul');
		progress_request = false;
		if (jqXHR.status === 422) {
			jqXHR.responseJSON.message?.forEach((errorMessage)=>{
				errorMessage['errors'].forEach((error) => {
					errorUl.append(
						`<li>${errorMessage['row']}行目：${error}</li>`
					);
				})
			})
		}
		if (jqXHR.status === 504) {
			errorUl.append(
				`<li>タイムアウトエラーです</li>`
			);
		}
		if(jqXHR.status === 500) {
			errorUl.append(
				`<li>${jqXHR.responseJSON.message}</li>`
			);
		}
    });

	let percent;
	let id = setInterval(() => {
		$.ajax({
			url: '/admin/message/publish/csv/store/progress',
			type: 'get',
			data: {
				file_name: log_file_name
			},
			contentType: 'text/plain'
		}).done(function(response){
			percent = response;
			progressBar.show();
			progressBar.css('width', percent + '%');
            // setTimeout(() => {
            //     progress.hide();
            // }, 1000);
			// console.log(response);
		}).fail(function(qXHR, textStatus, errorThrown){
			console.log("終了");
		})
		if(percent == 100 || !progress_request) {
			clearInterval(id);
			console.log("終了");
		}
	}, 500);
});

// インポートボタン処理（モーダル内）
$(document).on('click', '#csvImportBtn', function(e) {
	e.preventDefault();

	if(!newMessageJson) {
		$('#messageStoreImportModal .modal-body').prepend(`
			<div class="alert alert-danger">
				<ul>
					<li>ファイルを添付してください</l>
				</ul>
			</div>
		`);
		return;
	}
    // モーダルを閉じる（カスタムモーダル用）
    $("#messageStoreImportModal").removeClass("disp");

	var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var overlay = $('#overlay');
    overlay.css('display', 'block'); // オーバーレイを表示

	$('#messageStoreImportModal .modal-body .alert-danger').remove();
	$.ajax({
		url: '/admin/message/publish/csv/store/import',
		type: 'post',
        data: JSON.stringify({
            file_json: newMessageJson,
            organization1_id: $('#messageStoreImportModal input[name="organization1"]').val()
        }),
		processData: false,
		contentType: "application/json; charset=utf-8",
		headers: {
			'X-CSRF-TOKEN': csrfToken,
		},

	}).done(function(response){
		overlay.css('display', 'none');
        $('#messageStoreModal').html(response);

        var allOrg_flg = true;
        var allStore_flg = true;

        // organization_shops のチェック状態を確認
        $('input[name="organization_shops[]"]').each(function() {
            if (!$(this).prop('checked')) {
                allOrg_flg = false;
            }
        });
        $('#selectAllOrganization').prop('checked', allOrg_flg);

        // shops_code のチェック状態を確認
        $('input[name="shops_code[]"]').each(function() {
            if (!$(this).prop('checked')) {
                allStore_flg = false;
            }
        });
        $('#selectAllStoreCode').prop('checked', allStore_flg);

        // 店舗選択モーダルを表示
        $('#messageStoreModal').modal('show');

        // 初期表示の更新
        updateSelectedStores();
        updateAllParentCheckboxes();

        // CSVインポートモーダル初期化
        initializeCsvImportModal();

	}).fail(function(jqXHR, textStatus, errorThrown){
		overlay.css('display', 'none');

		$('#messageStoreImportModal .modal-body').prepend(`
			<div class="alert alert-danger">
				<ul></ul>
			</div>
		`);
		// labelForm.parent().find('.text-danger').remove();

		jqXHR.responseJSON.error_message?.forEach((errorMessage)=>{

			errorMessage['errors'].forEach((error) => {
				$('#messageStoreImportModal .modal-body .alert ul').append(
					`<li>${errorMessage['row']}行目：${error}</li>`
				);
			})
		})
		if(errorThrown) {
			$('#messageStoreImportModal .modal-body .alert ul').append(
				`<li>エラーが発生しました</li>`
			);
		}
	});
})

// CSVインポートモーダルのイベント ////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////


function isEmptyImportFile(modal) {
	return !$(modal).find('input[type="file"]')[0].value
}

function getNumericDateTime() {
    // 今日の日時を取得
    var today = new Date();

    // 年、月、日、時、分、秒を取得
    var year = today.getFullYear();
    var month = ('0' + (today.getMonth() + 1)).slice(-2); // 月は0から始まるので+1する
    var day = ('0' + today.getDate()).slice(-2);
    var hours = ('0' + today.getHours()).slice(-2);
    var minutes = ('0' + today.getMinutes()).slice(-2);
    var seconds = ('0' + today.getSeconds()).slice(-2);

    // 数字のみの形式で表示して返す
    return `${year}${month}${day}${hours}${minutes}${seconds}`;
}

