'use strict';

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
$(document).on('change' , '.inputFile input[type=file]' , function(){
	let changeTarget = $(this);
	changeFileName(changeTarget);
});

$(window).on('load' , function(){
	var d = new Date();
	d.setDate(d.getDate() + 1);
	/* datetimepicker */
	$.datetimepicker.setLocale('ja');
	$('#dateFrom').datetimepicker({
		format:'Y/m/d H:00',
		onShow:function( ct ){
			this.setOptions({
				maxDate:jQuery('#dateTo').val()?jQuery('#dateTo').val():false
			})
        },
        defaultDate: d,
        defaultTime: '00:00',
	});
	$('#dateTo').datetimepicker({
		format:'Y/m/d H:i',
		onShow:function( ct ){
			this.setOptions({
				minDate:jQuery('#dateFrom').val()?jQuery('#dateFrom').val():false
			})
		},
		allowTimes:[
			'00:00','01:00','02:00','03:00','04:00','05:00','06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00',
		],
		defaultDate: d,
		defaultTime: '00:00',
	});
});

/* パスワード入力検知 */
$(document).on('focusout' , '.inputPassword , .inputPassword2' , function(){
	let linkTarget;
	if($(this).hasClass('inputPassword')){
		linkTarget = $('.inputPassword2');
	}else if($(this).hasClass('inputPassword2')){
		linkTarget = $('.inputPassword');
	}

	let chkLinkVal = linkTarget.val();
	console.log(chkLinkVal);
	if(chkLinkVal != '' && $(this).val() != chkLinkVal){
		$('input[name=check_password]').val('1');
	}else{
		$('input[name=check_password]').val('0');
	}
});

$(document).on('click' , '#submitbutton' , function(){
	if($('input[name=check_password]').length){
		if($('input[name=check_password]').val() != 0){
			alert('パスワードが一致しません。\nパスワード欄、確認欄を入力し直してください。');
			return false;
		}
	}
});

/* 日程の未定選択時 */
function toggleInputDate(e){
	let chkTargetData = e.data('target');
	let toggleTarget = $('#'+chkTargetData);
	if(!e.prop('checked')){
		toggleTarget.prop('disabled' , false);
	}else{
		toggleTarget.val('').prop('disabled' , true);
	}
}
$(document).on('change' , '.dateDisabled' , function(){
	let changeTarget = $(this);
	toggleInputDate(changeTarget);
});

/* 全業態、対象ブロック全て選択時 */
function chkAll(e){
	let targets = e.parents('.checkArea').find('input[type=checkbox]').not('#checkAll');
	if(!e.prop('checked')){
		targets.each(function(){
			if($(this).prop('checked')){
				$(this).prop('checked' , false);
			}
		});
	}else{
		targets.each(function(){
			if(!$(this).prop('checked')){
				$(this).prop('checked' , true);
			}
		});
	}
}
$(document).on('click' , '#checkAll' , function(){
	let clickTarget = $(this);
	chkAll(clickTarget);
});

/* チェックを入れた時の全業態、対象ブロック全て部分の切り替え */
function toggleBulkCheckbox(e){
	let chkTarget = e.parents('.checkArea').find('.checkCommon');
	let toggleTarget = e.parents('.checkArea').find('#checkAll');
	chkTarget.each(function(){
		if(!$(this).prop('checked')){
			toggleTarget.prop('checked' , false);
			return false;
		}else{
			toggleTarget.prop('checked' , true);
		}
	});
}
$(document).on('click' , '.checkCommon' , function(){
	let clickTarget = $(this);
	toggleBulkCheckbox(clickTarget);
});

/* name振り直し */
function countVariableBox(){
	let fileTarget = $('.manualVariableArea').find('.manualVariableBox').not('#cloneTarget');
	let fileTargetNum = 0;
	fileTarget.each(function(){
		$(this).find('input[data-variable-name=manual_flow_content_id]').attr({'name':'manual_flow['+fileTargetNum+'][content_id]'});
		$(this).find('input[data-variable-name=manual_flow_title]').attr({'name':'manual_flow['+fileTargetNum+'][title]'});
		$(this).find('input[data-variable-name=manual_file_name]').attr({'name':'manual_flow['+fileTargetNum+'][file_name]'});
		$(this).find('input[data-variable-name=manual_file]').attr({'name':'manual_flow['+fileTargetNum+'][file]'});
		$(this).find('input[data-variable-name=manual_file_path]').attr({'name':'manual_flow['+fileTargetNum+'][file_path]'});
		$(this).find('textarea[data-variable-name=manual_flow_detail]').attr('name' , 'manual_flow['+fileTargetNum+'][detail]');
		fileTargetNum = fileTargetNum + 1;
	});
}
/* 手順タイトル、添付ファイル、手順内容の追加 */
function addVariableBox(callback){
	let cloneTarget = $('#cloneTarget');
	cloneTarget.clone().appendTo('.manualVariableArea');
	if($('.manualVariableBox').length != 1){
		let removeIdTarget = $('.manualVariableArea').find('.manualVariableBox:last-child');
		removeIdTarget.removeAttr('id');
	}

	callback();
}
$(document).on('click' , '.btnAddBox' , function(){
	addVariableBox(countVariableBox);
});

/* 手順タイトル、添付ファイル、手順内容の削除 */
function removeVariableBox(e , callback){
	let removeTargetTitle = e.parents('.manualVariableBox').find('input[data-variable-name=manual_flow_title]').val();
	if(removeTargetTitle != ''){
		if(confirm('手順「'+removeTargetTitle+'」を削除します。\nよろしいですか？')){
			e.parents('.manualVariableBox').remove();
			callback();
		}
	}else{
		e.parents('.manualVariableBox').remove();
		callback();
	}
}
$(document).on('click' , '.btnRemoveBox' , function(){
	let removeTarget = $(this);
	removeVariableBox(removeTarget , countVariableBox);
});

$(document).on('submit' , '#form' , function(event){
	// イベントを停止する
	event.preventDefault();
	// ローディングアニメーション
	var overlay = document.getElementById('overlay');
	overlay.style.display = 'block';

	// form.submit()ではサブミットのボタンをpostしないので、パラメータを追加する
	var submitterButtonName = event.originalEvent.submitter.attributes['name'].value;
	if(submitterButtonName == 'save'){
		let fm = $('#form');
		fm.append($('<input />', {
            type: 'hidden',
            name: 'save',
            value: 1,
        }));
	}
	// 改めてsubmitする
	form.submit();
});

// $(document).on('input', 'input[name="title"], input[data-variable-name="manual_flow_title"]', function(e){
// 	var inputText = $(this).val();
//     var textLength = inputText.length;
// 	var maxLength = 20; // 最大文字数

//     // 文字数を表示
//     var counterText = '入力数 ' + textLength + '/' + maxLength + '文字';
//     $(this).parent().siblings('div.counter').text(counterText);
// })

// $(document).on('input', 'textarea[name="description"], textarea[data-variable-name="manual_flow_detail"]', function(e){
// 	var inputText = $(this).val();
//     var textLength = inputText.length;
// 	var maxLength = 30; // 最大文字数

//     // 文字数を表示
//     var counterText = '入力数 ' + textLength + '/' + maxLength + '文字';
//     $(this).parent().siblings('div.counter').text(counterText);
// })


// 成功テンプレート
const successTamplate = `
	<div class="modal-body">
		<div class="text-center">
			<div class="form-group">
				csv取り込み完了しました
			</div>
			<div class="text-right">
				<a href="" class="btn btn-admin" onClick="location.reload()">閉じる</a>
			</div>
		</div>
	</div>
`;


// 業務連絡 CSV アップロード
let messageJson;
$(document).on('change', '#messageImportModal .fileImport input[type="file"]', function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
	let log_file_name = getNumericDateTime();
    let formData = new FormData();
    formData.append("file", $(this)[0].files[0]);
	formData.append("organization1", $('#messageImportModal input[name="organization1"]').val())
	formData.append("log_file_name", log_file_name)

	let button = $('#messageImportModal input[type="button"]');

    var labelForm = $(this).parent();
    var progress = labelForm.parent().find('.progress');
    var progressBar = progress.children(".progress-bar");

    progressBar.hide();
    progressBar.css('width', 0 + '%');
    progress.show();

	let progress_request = true;

	$('#messageImportModal .modal-body .alert-danger').remove();

    $.ajax({
        url: '/admin/message/publish/csv/upload',
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
        labelForm.parent().find('.text-danger').remove();
		messageJson = response.json;

        if (messageJson && messageJson.length > 0) {
            // すべてのcheck_fileがfalseの場合の処理
            const hasTrueCheckFile = messageJson.some(item => item.check_file);

            if (!hasTrueCheckFile) {
                $('#messageImportModal input[type="button"]').addClass("importBtn").val("インポート");
            } else {
                $('#messageImportModal input[type="button"]').addClass("importFileBtn").val("開く");
                $('#messageImportModal .fileImport').removeClass("fileImport").addClass("fileInputs");
            }
        }
    }).fail(function(jqXHR, textStatus, errorThrown){
		$('#messageImportModal .modal-body').prepend(`
			<div class="alert alert-danger">
				<ul></ul>
			</div>
		`);
		const errorUl =  $('#messageImportModal .modal-body .alert ul');
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

	let persent;
	let id = setInterval(() => {
		$.ajax({
			url: '/admin/message/publish/csv/progress',
			type: 'get',
			data: {
				file_name: log_file_name
			},
			contentType: 'text/plain'
		}).done(function(response){
			persent = response;
			progressBar.show();
			progressBar.css('width', persent + '%');
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


// 業務連絡 CSV PDFファイルアップロード
$(document).on('click', '#messageImportModal input[type="button"].importFileBtn', function(){
    if (messageJson && messageJson.length > 0) {
        // 既存の内容をクリア
        $('#messageImportModal .fileInputs').empty();
        $('#messageImportModal .modal-text').empty();

        // check_fileがtrueのnumberとtitleを表示
        messageJson.forEach(item => {
            if (item.check_file) {
                $("#messageImportModal .fileInputs").append(`
                    <label class="col-sm-4 control-label">No.${item.number}<span style="padding-left: 10px;">${item.title} : <span class="text-danger required">*<span></span></label>
                    <div class="col-sm-7">
                        <label class="inputFile form-control">
                            <span class="fileName">ファイルを選択またはドロップ</span>
                            <input type="file" name="file" accept=".pdf">
                            <input type="hidden" name="number" value="${item.number}">
                            <input type="hidden" name="file_name" value="">
                            <input type="hidden" name="file_path" value="">
                            <input type="hidden" name="join_flg" value="">
                        </label>
                        <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>
                `);
            }
        });

        $('#messageImportModal input[type="button"].importFileBtn')
            .removeClass("importFileBtn")
            .addClass("importBtn")
            .val("インポート")
            .prop("disabled", true);
    }
});


// 業務連絡 PDFファイル処理
$(document).on("change", '#messageImportModal .fileInputs input[type="file"]', function () {
    let _this = $(this);
    let csrfToken = $('meta[name="csrf-token"]').attr("content");
    let fileList = _this[0].files;
    let formData = new FormData();
    let labelForm = _this.parent();
    let progress = labelForm.parent().find(".progress");
    let progressBar = progress.children(".progress-bar");

    labelForm.parent().find(".text-danger").remove();
	$('#messageImportModal .modal-body .alert-danger').remove();

    // ファイルをformDataに追加
    for (let i = 0; i < fileList.length; i++) {
        formData.append("file" + i, fileList[i]);
    }

    progressBar.hide();
    progressBar.css("width", "0%");
    progress.show();

	let button = $('#messageImportModal input[type="button"]');

    let number = _this.siblings('input[name="number"]').val();
    let fileName = _this.siblings('input[name="file_name"]');
    let filePath = _this.siblings('input[name="file_path"]');
    let joinFile = _this.siblings('input[name="join_flg"]');

    $.ajax({
        url: "/admin/message/publish/upload",
        type: "post",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
        xhr: function () {
            let XHR = $.ajaxSettings.xhr();
            if (XHR.upload) {
                XHR.upload.addEventListener("progress", function (e) {
                    let progVal = parseInt((e.loaded / e.total) * 10000) / 100;
                    progressBar.show();
                    progressBar.css("width", progVal + "%");
                    // console.log(progVal);
                    if (progVal === 100) {
                        setTimeout(() => {
                            progress.hide();
                        }, 1000);
                    }
                }, false);
            }
            return XHR;
        },
    })
    .done(function (response) {
        button.prop("disabled", false);
        labelForm.parent().find(".text-danger").remove();
        modalHandleResponse(response,number, fileName, filePath, joinFile);
        _this.attr('data-cache', 'active');

        // すべてのfile_nameが入力されたかを確認
        let allFilesNamed = true;
        $('#messageImportModal .fileInputs input[name="file_name"]').each(function() {
            if (!$(this).val()) {
                allFilesNamed = false;
            }
        });

        if (allFilesNamed) {
            button.prop("disabled", false);
        } else {
            button.prop("disabled", true);
        }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        labelForm.parent().find(".text-danger").remove();
        jqXHR.responseJSON?.errorMessages?.forEach((errorMessage) => {
            labelForm.parent().append(`<div class="text-danger">${errorMessage}</div>`);
        });
        if (errorThrown) {
            labelForm.parent().append(`<div class="text-danger">アップロードできませんでした</div>`);
        }
        fileName.val("");
        filePath.val("");
        joinFile.val("single");
    });
});


// アップロード完了後の処理
function modalHandleResponse(response, number, fileName, filePath, joinFile) {
    // responseが複数ファイルに対応している場合
    response.content_names.forEach((content_name, i) => {
        let content_url = response.content_urls[i];
        if (i === 0) {
            fileName.val(content_name);
            filePath.val(content_url);
            joinFile.val("single");
        }
        // check_fileがtrueのものにfile_nameとfile_pathを追加
        messageJson.forEach(item => {
            if (item.check_file && parseInt(item.number) === parseInt(number)) {
                item.file_name = content_name;
                item.file_path = content_url;
                item.join_flg = "single";
            }
        });
    });
}


// 業務連絡 CSV インポート
$(document).on('click', '#messageImportModal input[type="button"].importBtn', function(e){
    e.preventDefault();

	if (!messageJson) {
		$('#messageImportModal .modal-body').prepend(`
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

	$('#messageImportModal .modal-body .alert-danger').remove();
	$.ajax({
		url: '/admin/message/publish/import',
		type: 'post',
		data: JSON.stringify(messageJson),
		processData: false,
		contentType: "application/json; charset=utf-8",
		headers: {
			'X-CSRF-TOKEN': csrfToken,
		},

	}).done(function(response){
		console.log(response);
		overlay.style.display = 'none';
		$('#messageImportModal .modal-body').replaceWith(successTamplate);

	}).fail(function(jqXHR, textStatus, errorThrown){
		overlay.style.display = 'none';

		$('#messageImportModal .modal-body').prepend(`
			<div class="alert alert-danger">
				<ul></ul>
			</div>
		`);
		// labelForm.parent().find('.text-danger').remove();

		jqXHR.responseJSON.error_message?.forEach((errorMessage)=>{

			errorMessage['errors'].forEach((error) => {
				$('#messageImportModal .modal-body .alert ul').append(
					`<li>${errorMessage['row']}行目：${error}</li>`
				);
			})
		})
		if(errorThrown) {
			$('#messageImportModal .modal-body .alert ul').append(
				`<li>エラーが発生しました</li>`
			);
		}
	});
})


// マニュアル CSV アップロード
let manualJson;
$(document).on('change', '#manualImportModal input[type="file"]', function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
	let log_file_name = getNumericDateTime();
    let formData = new FormData();
    formData.append("file", $(this)[0].files[0]);
	formData.append("organization1", $('#manualImportModal input[name="organization1"]').val())
	formData.append("log_file_name", log_file_name)

	let button = $('#manualImportModal input[type="button"]');

    var labelForm = $(this).parent();
    var progress = labelForm.parent().find('.progress');
    var progressBar = progress.children(".progress-bar");

    progressBar.hide();
    progressBar.css('width', 0 + '%');
    progress.show();

	let progress_request = true;

	$('#manualImportModal .modal-body .alert-danger').remove();

    $.ajax({
        url: '/admin/manual/publish/csv/upload',
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
        labelForm.parent().find('.text-danger').remove();
		manualJson = response.json;
		console.log(manualJson);
    }).fail(function(jqXHR, textStatus, errorThrown){
		$('#manualImportModal .modal-body').prepend(`
			<div class="alert alert-danger">
				<ul></ul>
			</div>
		`);
		const errorUl =  $('#manualImportModal .modal-body .alert ul');
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

	let persent;
	let id = setInterval(() => {
		$.ajax({
			url: '/admin/manual/publish/csv/progress',
			type: 'get',
			data: {
				file_name: log_file_name
			},
			contentType: 'text/plain'
		}).done(function(response){
			persent = response;
			progressBar.show();
			progressBar.css('width', persent + '%');
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


// マニュアル CSV インポート
$('#manualImportModal input[type="button"]').click(function(e){
	e.preventDefault();

	if(!manualJson) {
		$('#manualImportModal .modal-body').prepend(`
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

	$('#manualImportModal .modal-body .alert-danger').remove();
	$.ajax({
		url: '/admin/manual/publish/import',
		type: 'post',
		data: JSON.stringify(manualJson),
		processData: false,
		contentType: "application/json; charset=utf-8",
		headers: {
			'X-CSRF-TOKEN': csrfToken,
		},
	}).done(function(response){
		console.log(response);
		overlay.style.display = 'none';
		$('#manualImportModal .modal-body').replaceWith(successTamplate);

	}).fail(function(jqXHR, textStatus, errorThrown){
		overlay.style.display = 'none';

		$('#manualImportModal .modal-body').prepend(`
			<div class="alert alert-danger">
				<ul></ul>
			</div>
		`);
		// labelForm.parent().find('.text-danger').remove();

		jqXHR.responseJSON.error_message?.forEach((errorMessage)=>{

			errorMessage['errors'].forEach((error) => {
				$('#manualImportModal .modal-body .alert ul').append(
					`<li>${errorMessage['row']}行目：${error}</li>`
				);
			})
		})
		if(errorThrown) {
			$('#manualImportModal .modal-body .alert ul').append(
				`<li>エラーが発生しました</li>`
			);
		}
	});
})





//
// 登録編集画面のタグ機能
//
// タグを削除
$(document).on('click', '.tag-form-delete', function() {
    $(this).parent().remove();
})

// カーソルをフォーカスする
$('.tag-form .form-control').click(function() {
    $('.tag-form-input').focus();
})

// 入力を監視する
$(document).on('keydown', '.tag-form-input', function(e) {

    let tagLabelText = $(this)[0].innerText;
    // エンターの入力
    if(e.keyCode == 13) {
        if(tagLabelText == "") return false;

        $(this).before(createTag(tagLabelText));
        $(this)[0].innerText = "";
        return false;
    }

    // 「,」の入力
    if(e.keyCode == 188) {
        $(this).before(createTag(tagLabelText));
        $(this)[0].innerText = "";
        return false;
    }

    // Backspace
    if(e.keyCode == 8) {
        if($(this)[0].innerText == "") {
            $(this).prev().remove();
        }
    }
})

// カーソルを離したときにタグを追加
$(document).on('focusout', '.tag-form-input', function() {
    let tagLabelText = $(this)[0].innerText;
    if(tagLabelText != "") {
        $(this).before(createTag(tagLabelText));
        $(this)[0].innerText = "";
    }
});

function createTag(tagLabelText) {
    return (
        `
        <span class="focus:outline-none tag-form-label">
            ${tagLabelText}<span class="tag-form-delete">×</span>
            <input type="hidden" name="tag_name[]" value="${tagLabelText}">
        </span>
        `
    )
}

// モーダルを閉じた時にリロード
$('#messageImportModal').on('hidden.bs.modal', function (e) {
	window.location.reload();
})
$('#manualImportModal').on('hidden.bs.modal', function (e) {
	window.location.reload();
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
