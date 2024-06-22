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
}

// 親チェックボックスの状態を更新
function updateParentCheckbox(organizationId) {
    const parentCheckbox = document.querySelector(`#storeModal input[data-organization-id="${organizationId}"]`);
    const childCheckboxes = document.querySelectorAll(`#storeModal input[data-organization-id="${organizationId}"].shop-checkbox`);
    const allChecked = Array.from(childCheckboxes).every(checkbox => checkbox.checked);
    parentCheckbox.checked = allChecked;
}

// 全ての親チェックボックスの状態を更新
function updateAllParentCheckboxes() {
    const parentCheckboxes = document.querySelectorAll('#storeModal input.org-checkbox');
    parentCheckboxes.forEach(function(parentCheckbox) {
        updateParentCheckbox(parentCheckbox.getAttribute('data-organization-id'));
    });
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

// 初期表示の更新
updateSelectedStores();
updateAllParentCheckboxes();
updateSelectAllCheckboxes();

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
    updateSelectedStores();
    updateSelectAllCheckboxes();
});

// 組織単位タブの全選択/選択解除
$(document).on('change', '#selectAllOrganization', function() {
    const checked = this.checked;
    $('#storeModal #byOrganization input[type="checkbox"]').each(function() {
        this.checked = checked;
        if ($(this).hasClass('shop-checkbox')) {
            syncCheckboxes($(this).attr('data-store-id'), checked);
        }
    });
    updateSelectedStores();
    updateSelectAllCheckboxes();
});

// 店舗コード順タブの全選択/選択解除
$(document).on('change', '#selectAllStoreCode', function() {
    const checked = this.checked;
    $('#storeModal #byStoreCode input[type="checkbox"]').each(function() {
        this.checked = checked;
        if ($(this).hasClass('shop-checkbox')) {
            syncCheckboxes($(this).attr('data-store-id'), checked);
        }
    });
    updateSelectedStores();
    updateSelectAllCheckboxes();
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

    // 隠し入力フィールドに値を割り当てる
    $("#checkOrganization5").val(selectedOrg5Values.join(","));
    $("#checkOrganization4").val(selectedOrg4Values.join(","));
    $("#checkOrganization3").val(selectedOrg3Values.join(","));
    $("#checkOrganization2").val(selectedOrg2Values.join(","));
    $("#checkOrganizationShops").val(selectedShopValues.join(","));
}

$(document).ready(function () {
    if ($("#selectStore").val() === "selected") {
        // 店舗選択中の処理
        const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
        $("#checkStore").val(`店舗選択(${selectedCountStore}店舗)`);
    }
    if ($("#selectCsv").val() === "selected") {
        // インポート選択中の処理
        const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
        $("#checkCsv").val(`インポート(${selectedCountStore}店舗)`);
    }
});



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
    // チェックされているチェックボックスの値を隠し入力フィールドに値を割り当てる
    changeValues();
    // フォームクリア（全店ボタン）
    $('#selectOrganizationAll').val("selected");
    // 店舗選択、インポートボタンをもとに戻す
    $('#checkStore').val('店舗選択');
    $('#checkCsv').val('インポート');
    // 選択中の店舗数を更新する
    updateSelectedStores();
    // ボタンの見た目を変更する
    $(this).addClass("check-selected");
    // csvインポートボタン変更
    $('#checkCsv').attr('data-target', '#manualStoreImportModal');
});



// 店舗選択モーダル 選択処理
$(document).on('click', 'input[id="checkStore"]', function() {
    // モーダルタイトル変更
    var storeModalTitle = $("#manualStoreModal h4.modal-title");
    if (storeModalTitle.length) {
        storeModalTitle.html('店舗を選択してください。<br /><small class="text-muted">※変更履歴は保存され、引き継がれます</small>');
    }

    // 元のボタンのセレクターを取得して、新しいボタンのセレクターに変更
    var selectCsvButton = $("#selectCsvBtn");
    if (selectCsvButton.length) {
        selectCsvButton.attr("id", "selectStoreBtn");
    }

    // csv再インポートボタン削除
    if ($('#csvImportBtn').length) {
        $('#manualStoreModal .modal-footer #csvImportBtn').remove();
    }
});

$(document).on('click', '#selectStoreBtn', function() {
    removeSelectedClass();
    // チェックされているチェックボックスの値を隠し入力フィールドに値を割り当てる
    changeValues();
    // フォームクリア（店舗選択ボタン）
    $("#selectStore").val("selected");
    // インポートボタンをもとに戻す
    $("#checkCsv").val('インポート');
    // モーダルを閉じる
    $("#manualStoreModal").modal("hide");
    // check-selected クラスを追加
    $("#checkStore").addClass("check-selected");
    // csvインポートボタン変更
    $('#checkCsv').attr('data-target', '#manualStoreImportModal');
    // 店舗選択中の処理
    const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
    $('.check-store-list input[id="checkStore"]').val(`店舗選択(${selectedCountStore}店舗)`);
});



// CSVインポートモーダル 選択処理
$(document).on('click', 'input[id="checkCsv"]', function() {
    // 元のボタンのセレクターを取得
    var selectStoreButton = document.getElementById("selectStoreBtn");
    // 新しいボタンのセレクターに変更
    if (selectStoreButton) {
        selectStoreButton.id = "selectCsvBtn";
    }
    // csv再インポートボタン追加
    if (!$('#csvImportBtn').length) {
        $('#manualStoreModal .modal-footer').append(`<input type="button" class="btn btn-admin" id="csvImportBtn" data-toggle="modal" data-target="#manualStoreImportModal" value="再インポート">`);
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
    $("#checkCsv").addClass("check-selected");
    // 店舗選択中の処理
    const selectedCountStore = $('#storeModal input[name="organization_shops[]"]:checked').length;
    $('.check-store-list input[id="checkCsv"]').val(`インポート(${selectedCountStore}店舗)`);
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
			jqXHR.responseJSON.manual?.forEach((errormanual)=>{
				errormanual['errors'].forEach((error) => {
					errorUl.append(
						`<li>${errormanual['row']}行目：${error}</li>`
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

	let persent;
	let id = setInterval(() => {
		$.ajax({
			url: '/admin/manual/publish/csv/store/progress',
			type: 'get',
			data: {
				file_name: log_file_name
			},
			contentType: 'text/plain'
		}).done(function(response){
			persent = response;
			progressBar.show();
			progressBar.css('width', persent + '%');
            setTimeout(() => {
                progress.hide();
            }, 1000);
			console.log(response);
		}).fail(function(qXHR, textStatus, errorThrown){
			console.log("終了");
		})
		if(persent == 100 || !progress_request) {
			clearInterval(id);
			console.log("終了");
		}
	}, 500);

});


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
		url: '/admin/manual/publish/store/import',
		type: 'post',
		data: JSON.stringify(newManualJson),
		processData: false,
		contentType: "application/json; charset=utf-8",
		headers: {
			'X-CSRF-TOKEN': csrfToken,
		},

	}).done(function(response){
		// console.log(response);
		overlay.style.display = 'none';

        $('#manualStoreModal').html(response);

        // 初期表示の更新
        updateSelectedStores();
        updateAllParentCheckboxes();

        // csvインポートボタン変更
        $('#checkCsv').attr('data-target', '#manualStoreModal');

	}).fail(function(jqXHR, textStatus, errorThrown){
		overlay.style.display = 'none';

		$('#manualStoreImportModal .modal-body').prepend(`
			<div class="alert alert-danger">
				<ul></ul>
			</div>
		`);
		// labelForm.parent().find('.text-danger').remove();

		jqXHR.responseJSON.error_manual?.forEach((errormanual)=>{

			errormanual['errors'].forEach((error) => {
				$('#manualStoreImportModal .modal-body .alert ul').append(
					`<li>${errormanual['row']}行目：${error}</li>`
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
