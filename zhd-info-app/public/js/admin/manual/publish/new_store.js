$(document).ready(function () {
    // 初期表示の更新
    updateSelectedStores();
    updateAllParentCheckboxes();
    updateSelectAllCheckboxes();

    if ($("#selectStore").val() === "selected") {
        // 店舗選択中の処理
        const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
        $("#checkStore").val(`店舗選択(${selectedCountStore}店舗)`);
    }
    if ($("#selectCsv").val() === "selected") {
        // インポート選択中の処理
        const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
        $("#importCsv").val(`インポート(${selectedCountStore}店舗)`);
    }
});

// 店舗選択中の処理
function updateSelectedStores() {
    const selectedCount = document.querySelectorAll('#storeModal input[name="organization_shops[]"]:checked').length;
    document.querySelector('#storeModal div[id="storeSelected"]').textContent = `${selectedCount}店舗選択中`;
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

// 全選択/選択解除のチェックボックスの状態を更新
function updateSelectAllCheckboxes() {
    // 組織タブのチェックボックスの状態を更新
    const organizationCheckboxes = document.querySelectorAll('#storeModal #byOrganization input.shop-checkbox');
    const selectAllOrganizationCheckbox = document.querySelector('#selectAllOrganization');
    const allCheckedOrganization = Array.from(organizationCheckboxes).every(checkbox => checkbox.checked);
    selectAllOrganizationCheckbox.checked = allCheckedOrganization;

    // 店舗コード順タブのチェックボックスの状態を更新
    const storeCodeCheckboxes = document.querySelectorAll('#storeModal #byStoreCode input.shop-checkbox');
    const selectAllStoreCodeCheckbox = document.querySelector('#selectAllStoreCode');
    const allCheckedStoreCode = Array.from(storeCodeCheckboxes).every(checkbox => checkbox.checked);
    selectAllStoreCodeCheckbox.checked = allCheckedStoreCode;
}

// チェックボックスの変更イベントリスナーを追加
$(document).on('change', '#storeModal input[name="organization_shops[]"], #storeModal input[name="shops_code[]"]', function() {
    syncCheckboxes($(this).attr('data-store-id'), this.checked);
    updateSelectedStores();
    if ($(this).hasClass('shop-checkbox')) {
        updateParentCheckbox($(this).attr('data-organization-id'));
    }
    updateSelectAllCheckboxes();
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
    updateSelectAllCheckboxes();
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
    var overlay = document.getElementById('overlay');
    overlay.style.display = 'block';  // オーバーレイを表示

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
    updateSelectAllCheckboxes();

        // オーバーレイを非表示にする
        overlay.style.display = 'none';
    }

    requestIdleCallback(processNextBatch); // 最初のアイドル時間で処理を開始
});

// 店舗コード順タブの全選択/選択解除
$(document).on("change", "#selectAllStoreCode", function () {
    var overlay = document.getElementById('overlay');
    overlay.style.display = 'block';  // オーバーレイを表示

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
        updateSelectAllCheckboxes();

        // オーバーレイを非表示にする
        overlay.style.display = 'none';
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



// 全店ボタン処理
$(document).on('click', 'input[id="checkAll"][name="organizationAll"]', function() {
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
    $('#checkStore').val('店舗選択');
    $('#importCsv').val('インポート');
    // 選択中の店舗数を更新する
    updateSelectedStores();
    // ボタンの見た目を変更する
    $(this).addClass("check-selected");
    // csvインポートボタン変更
    $('#importCsv').attr('data-target', '#manualStoreImportModal');
});



// 店舗選択モーダル 選択処理
$(document).on('click', 'input[id="checkStore"]', function() {
    // モーダルタイトル変更
    var storeModalTitle = $("#manualStoreModal h4.modal-title");
    if (storeModalTitle.length) {
        // storeModalTitle.html('店舗を選択してください。<br /><small class="text-muted">※変更履歴は保存され、引き継がれます</small>');
        storeModalTitle.html('店舗を選択してください。');
    }

    // 元のボタンのセレクターを取得して、新しいボタンのセレクターに変更
    var selectCsvButton = $("#selectCsvBtn");
    if (selectCsvButton.length) {
        selectCsvButton.attr("id", "selectStoreBtn");
    }
    // キャンセルボタン表示
    $('#cancelBtn').show();
    // csv再インポートボタン削除
    if ($('#csvImportBtn').length) {
        $('#manualStoreModal .modal-footer #csvImportBtn').remove();
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

$(document).on('click', '#selectStoreBtn', function() {
    removeSelectedClass();
    // チェックされているチェックボックスの値を隠し入力フィールドに値を割り当てる
    changeValues();
    // フォームクリア（店舗選択ボタン）
    $("#selectStore").val("selected");
    // インポートボタンをもとに戻す
    $("#importCsv").val('インポート');
    // モーダルを閉じる
    $("#manualStoreModal").modal("hide");
    // check-selected クラスを追加
    $("#checkStore").addClass("check-selected");
    // csvインポートボタン変更
    $('#importCsv').attr('data-target', '#manualStoreImportModal');
    // 店舗選択中の処理
    const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
    $('.check-store-list input[id="checkStore"]').val(`店舗選択(${selectedCountStore}店舗)`);
});

// モーダルが閉じられる際にchangeValuesを実行
$('#storeModal').on('hidden.bs.modal', function () {
    changeValues();
});



// CSVインポートモーダル 選択処理
$(document).on('click', 'input[id="importCsv"]', function() {
    // 元のボタンのセレクターを取得
    var selectStoreButton = document.getElementById("selectStoreBtn");
    // 新しいボタンのセレクターに変更
    if (selectStoreButton) {
        selectStoreButton.id = "selectCsvBtn";
    }
    // キャンセルボタン非表示
    $('#cancelBtn').hide();
    // csv再インポートボタン追加
    if (!$('#csvImportBtn').length) {
        $('#manualStoreModal .modal-footer').append(`<input type="button" class="btn btn-admin pull-left" id="csvImportBtn" data-toggle="modal" data-target="#manualStoreImportModal" value="再インポート">`);
    }
});

// インポートボタンのクリックイベント
$(document).on('click', '#importButton', function() {
    // モーダルを閉じる
    $("#manualStoreImportModal").modal("hide");
});

$(document).on('click', '#selectCsvBtn', function() {
    removeSelectedClass();
    // チェックされているチェックボックスの値を隠し入力フィールドに値を割り当てる
    changeValues();
    // フォームクリア（CSVインポートボタン）
    $("#selectCsv").val("selected");
    // モーダルを閉じる
    $("#manualStoreModal").modal("hide");
    // 店舗選択ボタンをもとに戻す
    $("#checkStore").val('店舗選択');
    // check-selected クラスを追加
    $("#importCsv").addClass("check-selected");
    // 店舗選択中の処理
    const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
    $('.check-store-list input[id="importCsv"]').val(`インポート(${selectedCountStore}店舗)`);
});

$(document).on('click', '#csvImportBtn', function() {
    // モーダルを閉じる
    $("#manualStoreModal").modal("hide");

    // ファイルを削除
    $('#manualStoreImportModal input[type="file"]').val('');
});



/* ファイル検知 */
function changeFileName(e){
	let fileNameTarget = e.siblings('.fileName');
	if(e.val() == ''){
		fileNameTarget.empty().text('ファイルを選択またはドロップ');
	}else{
		let chkFileName = e.prop('files')[0].name;
		fileNameTarget.empty().text(chkFileName);
	}
}

// 動画マニュアルCSV アップロード
$(document).on('change' , '#manualStoreImportModal input[type=file]' , function(){
	let changeTarget = $(this);
	changeFileName(changeTarget);
});

let newManualJson;
$(document).on('change', '#manualStoreImportModal input[type="file"]', function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
	let log_file_name = getNumericDateTime();
    let formData = new FormData();
    formData.append("file", $(this)[0].files[0]);
	formData.append("organization1", $('#manualStoreImportModal input[name="organization1"]').val())
	formData.append("log_file_name", log_file_name)

	let button = $('#manualStoreImportModal input[type="button"]');

    var labelForm = $(this).parent();
    var progress = labelForm.parent().find('.progress');
    var progressBar = progress.children(".progress-bar");

    progressBar.hide();
    progressBar.css('width', 0 + '%');
    progress.show();

	let progress_request = true;

	$('#manualStoreImportModal .modal-body .alert-danger').remove();

    $.ajax({
        url: '/admin/manual/publish/csv/store/upload',
        type: 'post',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
    }).done(function(response){
        // console.log(response);
		progress_request = false;
		button.prop("disabled", false);
        labelForm.parent().find('.text-danger').remove();
		newManualJson = response.json;

    }).fail(function(jqXHR, textStatus, errorThrown){
		$('#manualStoreImportModal .modal-body').prepend(`
			<div class="alert alert-danger">
				<ul></ul>
			</div>
		`);
		const errorUl =  $('#manualStoreImportModal .modal-body .alert ul');
		progress_request = false;
		if (jqXHR.status === 422) {
			jqXHR.responseJSON.manual?.forEach((errorManual)=>{
				errorManual['errors'].forEach((error) => {
					errorUl.append(
						`<li>${errorManual['row']}行目：${error}</li>`
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
				`<li>${jqXHR.responseJSON.manual}</li>`
			);
		}
    });

	let percent;
	let id = setInterval(() => {
		$.ajax({
			url: '/admin/manual/publish/csv/store/progress',
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

// 動画マニュアルCSV インポート
$('#manualStoreImportModal input[type="button"]').click(function(e){
	e.preventDefault();

	if(!newManualJson) {
		$('#manualStoreImportModal .modal-body').prepend(`
			<div class="alert alert-danger">
				<ul>
					<li>ファイルを添付してください</l>
				</ul>
			</div>
		`);
		return;
	}
	var csrfToken = $('meta[name="csrf-token"]').attr('content');

	var overlay = document.getElementById('overlay');
	overlay.style.display = 'block';

	$('#manualStoreImportModal .modal-body .alert-danger').remove();
	$.ajax({
		url: '/admin/manual/publish/csv/store/import',
		type: 'post',
        data: JSON.stringify({
            file_json: newManualJson,
            organization1_id: $('#manualStoreImportModal input[name="organization1"]').val()
        }),
		processData: false,
		contentType: "application/json; charset=utf-8",
		headers: {
			'X-CSRF-TOKEN': csrfToken,
		},

	}).done(function(response){
		// console.log(response);
		overlay.style.display = 'none';

        $('#manualStoreModal').html(response);

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

        // 初期表示の更新
        updateSelectedStores();
        updateAllParentCheckboxes();

        // csvインポートボタン変更
        $('#importCsv').attr('data-target', '#manualStoreModal');

	}).fail(function(jqXHR, textStatus, errorThrown){
		overlay.style.display = 'none';

		$('#manualStoreImportModal .modal-body').prepend(`
			<div class="alert alert-danger">
				<ul></ul>
			</div>
		`);
		// labelForm.parent().find('.text-danger').remove();

		jqXHR.responseJSON.error_message?.forEach((errorManual)=>{

			errorManual['errors'].forEach((error) => {
				$('#manualStoreImportModal .modal-body .alert ul').append(
					`<li>${errorManual['row']}行目：${error}</li>`
				);
			})
		})
		if(errorThrown) {
			$('#manualStoreImportModal .modal-body .alert ul').append(
				`<li>エラーが発生しました</li>`
			);
		}
	});
})

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



// 動画マニュアルCSV エクスポート
$(document).on('click', '#exportCsv', function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    let formData = new FormData();
    formData.append("organization1_id", $('.check-store-list input[name="organization1_id"]').val());

    $.ajax({
        url: '/admin/manual/publish/csv/store/export',
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
        var errorManual = 'An error occurred. Please try again later.';

        if (jqXHR.status === 422) {
            errorManual = 'Validation error. Please check your input and try again.';
        } else if (jqXHR.status === 504) {
            errorManual = 'Server timeout. Please try again later.';
        } else if (jqXHR.status === 500) {
            errorManual = 'Internal server error. Please try again later.';
        }

        console.log('Error: ' + jqXHR.status + ' - ' + textStatus);
        alert(errorManual);
    });
});
