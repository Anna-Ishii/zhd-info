$(document).ready(function(){
    $('#form').submit(function(event) {
        event.preventDefault();
        // ファイルは送信しない
        $('input[type="file"]').prop('disabled', true);

        if(!emptyTagInputForm()) {
            appendFormTagInput()
        }

        $('#form').off('submit').submit();
    });
});

function emptyTagLabelForm() {
    return $('.tag-form-label').length == 0;
}

function emptyTagInputForm() {
    return $('.tag-form-input')[0].innerText == '';
}

function appendFormTagInput() {
    $('<input>').attr({
        type: 'hidden',
        name: 'tag_name[]',
        value: $('.tag-form-input')[0].innerText
    }).appendTo($('#form'));
}



// PDFファイル処理
$(document).ready(function(){
    addFileInputAdd();
    addJoinFileBtn();
});

$(document).on("change", '.fileInputs input[type="file"]', function () {
    let _this = $(this);
    let csrfToken = $('meta[name="csrf-token"]').attr("content");
    let fileList = _this[0].files;
    let formData = new FormData();
    let labelForm = _this.parent();
    let progress = labelForm.parent().find(".progress");
    let progressBar = progress.children(".progress-bar");

    labelForm.parent().find(".text-danger").remove();

    // ファイルが上書きかどうか（上書き=true）
    let dataCache = _this.is("[data-cache]");

    // 既存のファイル数を取得 (ファイル入力欄の-1)
    let filesCount = $(".fileInputs .file-input-container").length - 1;
    if (filesCount) {
        let maxFiles = 20; // 上限数を設定（20）
        if (filesCount + fileList.length > maxFiles) {
            labelForm.parent().append(`<div class="text-danger">登録可能なファイルの上限は${maxFiles}件です</div>`);
            _this.val('');
            return;
        }
    }

    // ファイルをformDataに追加
    for (let i = 0; i < fileList.length; i++) {
        formData.append("file" + i, fileList[i]);
    }

    progressBar.hide();
    progressBar.css("width", "0%");
    progress.show();

    let fileName = _this.siblings('input[name="file_name[]"]');
    let filePath = _this.siblings('input[name="file_path[]"]');
    let joinFile = _this.siblings('input[name="join_flg[]"]');

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
        labelForm.parent().find(".text-danger").remove();
        handleResponse(response, fileName, filePath, joinFile, dataCache);
        _this.attr('data-cache', 'active');
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
function handleResponse(response, fileName, filePath, joinFile, dataCache) {
    // responseが複数ファイルに対応している場合
    response.content_names.forEach((content_name, i) => {
        let content_url = response.content_urls[i];
        if (i === 0) {
            fileName.val(content_name);
            filePath.val(content_url);
            joinFile.val("single");
        } else {
            addNewFileInput(content_name, content_url, join_flg = "single");
        }
    });

    if (!dataCache) {
        let fileInputs = document.querySelector(".fileInputs");
        let fileInput = fileInputs.querySelector('input[name="file[]"]');

        // 単一ファイル欄に加工
        if (fileInput) {
            fileInput.removeAttribute("multiple");
            fileInput.name = "file";
            // 削除ボタン追加
            addDeleteButton(fileInput);
        }

        // 上限を超えていない場合、かつファイル数が上限に達していない場合のみファイル入力欄を追加
        let existingFilesCount = $(".fileInputs .file-input-container").length;
        let joinFileBtnAdd = document.querySelector(".join-file-btn");

        let maxFiles = 20; // 上限数を設定（20）
        if (existingFilesCount < maxFiles) {
            if (joinFileBtnAdd) {
                joinFileBtnAdd.remove();
            }
            addFileInputAdd();
            addJoinFileBtn();

        } else {
            if (joinFileBtnAdd) {
                joinFileBtnAdd.remove();
            }
            addJoinFileBtn();
        }
    }
}

// 削除ボタン追加
function addDeleteButton(fileInput) {
    let deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.className = "btn btn-sm delete-btn";
    deleteButton.style.backgroundColor = "#eee";
    deleteButton.style.color = "#000";
    deleteButton.style.position = "absolute";
    deleteButton.style.top = "0";
    deleteButton.style.right = "0";
    deleteButton.textContent = "削除";
    fileInput.parentNode.appendChild(deleteButton);
}

// 新しいファイル入力欄を追加
function addNewFileInput(content_name, content_url, join_flg) {
    // 既存の添付ラベルの数を取得
    let currentLabelCount = $(".file-input-container .control-label:contains('添付')").length + 1;

    $(".fileInputs").append(`
        <div class="file-input-container">
            <div class="row">
                <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="" required>
                <label class="col-lg-2 control-label">添付${currentLabelCount}</label>
                <div class="col-lg-4">
                    <label class="inputFile form-control">
                        <span class="fileName">${content_name}</span>
                        <input type="file" name="file" accept=".pdf" data-cache="active">
                        <input type="hidden" name="file_name[]" value="${content_name}">
                        <input type="hidden" name="file_path[]" value="${content_url}">
                        <input type="hidden" name="join_flg[]" value="${join_flg}">
                        <button type="button" class="btn btn-sm delete-btn" style="background-color: #eee; color: #000; position: absolute; top: 0; right: 0;">削除</button>
                    </label>
                    <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                </div>
                <label class="col-lg-2" style="padding-top: 10px; display: none;">結合</label>
            </div>
        </div>
    `);
}

// 結合ボタンを追加
function addJoinFileBtn() {
    $(".fileInputs").append(`
        <div class="col-lg-6 join-file-btn">
            <label class="inputFile" style="float: right; display: flex; align-items: center; justify-content: space-between;">
                <p style="margin: 0; padding-right: 10px; display: none;">0ファイルを結合中です。</p>
                <input type="button" class="btn btn-admin joinFile" id="joinFileId" data-toggle="modal" data-target="#joinFileModal" value="結合の修正">
            </label>
        </div>
    `);
}

// 追加ファイル欄の追加
function addFileInputAdd() {
    // 変数を初期化
    let file_name = "";
    let file_path = "";
    let join_flg = "";

    // 既存の添付ラベルの数を取得
    let currentLabelCount = $(".file-input-container .control-label:contains('添付')").length + 1;

    $(".fileInputs").append(`
        <div class="file-input-container">
            <div class="row">
                <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="" required>
                <label class="col-lg-2 control-label">添付${currentLabelCount}</label>
                <div class="col-lg-4">
                    <label class="inputFile form-control">
                        <span class="fileName" style="text-align: center;">${file_name ? file_name : "ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能"}</span>
                        <input type="file" name="file[]" accept=".pdf" multiple="multiple">
                        <input type="hidden" name="file_name[]" value="${file_name}">
                        <input type="hidden" name="file_path[]" value="${file_path}">
                        <input type="hidden" name="join_flg[]" value="${join_flg}">
                    </label>
                    <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                </div>
                <label class="col-lg-2" style="padding-top: 10px; display: none;">結合</label>
            </div>
        </div>
    `);
};

// 添付ラベルの番号を振り直す処理
function renumberSendLabels() {
    $(".file-input-container .control-label:contains('添付'), .file-input-container .control-label:contains('業連')").each(function(index) {
        if (index === 0) {
            $(this).html('業連<span class="text-danger required">*</span>');
        } else {
            $(this).text(`添付${index}`);
        }
    });
}

// 削除ボタンのクリックイベント
$(document).on("click", ".delete-btn", function () {
    let joinFileBtnAdd = document.querySelector(".join-file-btn");
    let dataCacheCount = $("[data-cache]").length;
    let maxFiles = 20; // 上限数を設定（20）

    // 上限を超えていない場合、かつファイル数が上限に達していない場合のみファイル入力欄を追加
    if (dataCacheCount < maxFiles) {
        $(this).parents().eq(3).remove();

        // 添付ラベルの番号を振り直す
        renumberSendLabels();

    } else {
        if (dataCacheCount === maxFiles) {
            $(this).parents().eq(3).remove();
            // 添付ラベルの番号を振り直す
            renumberSendLabels();

            if (joinFileBtnAdd) {
                joinFileBtnAdd.remove();
            }
            addFileInputAdd();
            addJoinFileBtn();
        }
    }
    if (dataCacheCount === 0) {
        if (joinFileBtnAdd) {
            joinFileBtnAdd.remove();
        }
        addFileInputAdd();
        addJoinFileBtn();
    }
});


// 結合の修正ボタン処理
$(document).on("click", "#joinFileId", function () {
    var selectedFiles = [];
    var selectedFilePaths = [];
    var selectedJoinFiles = [];

    // ファイル名とファイルパスをそれぞれの配列に追加
    $(".fileInputs [name='file_name[]']").each(function(){
        var value = $(this).val();
        if (value) {
            selectedFiles.push(value);
        }
    });
    $(".fileInputs [name='file_path[]']").each(function(){
        var value = $(this).val();
        if (value) {
            selectedFilePaths.push(value);
        }
    });
    $(".fileInputs [name='join_flg[]']").each(function(){
        var value = $(this).val();
        selectedJoinFiles.push(value);
    });

    var $modalBody = $("#joinFileModal #fileCheckboxes");
    var $modalFooter = $("#joinFileModal .modal-footer");
    $modalBody.empty();
    $modalFooter.find('p').remove();

    if (selectedFiles.length > 0) {
        selectedFiles.forEach(function(file, index) {
            var filePath = selectedFilePaths[index] || 'パスがありません';
            var isChecked = selectedJoinFiles[index] === "join" ? "checked" : "";
            var labelText = (index === 0) ? '業連<span class="text-danger required">*</span>' : `添付${index}`;
            var checkbox =
                `<div class="checkbox">
                    <label>
                        <input type="checkbox" value="${filePath}" ${isChecked}>${labelText} ${file}
                    </label>
                </div>`;
            $modalBody.append(checkbox);
        });
        updateJoinFileCount();
    } else {
        $modalFooter.append(`<p style="float: left;">結合するファイルが選択されていません。</p>`);
    }
});

// 結合ボタン処理
$(document).on('click', '#joinFileBtn', function() {
    // 結合モーダルのチェックされたファイルパスを取得
    var checkedFileValues = [];
    $('#joinFileModal #fileCheckboxes input[type="checkbox"]:checked').each(function() {
        checkedFileValues.push($(this).val());
    });

    // 選択されたファイルパスを取得
    var selectedFilePaths = [];
    $(".fileInputs [name='file_path[]']").each(function(){
        var value = $(this).val();
        if (value) {
            selectedFilePaths.push(value);
        }
    });

    // チェックされたファイルパスと一致するファイルパスのjoin_flg[]の値を"join"に設定し、ラベルを表示
    // チェックが外された場合は"single"に設定し、ラベルを非表示
    $(".fileInputs [name='file_path[]']").each(function(index){
        var value = $(this).val();
        if (checkedFileValues.includes(value)) {
            $(".fileInputs [name='join_flg[]']").eq(index).val("join");
            // 結合ラベルを表示
            $(this).closest('.row').find("label[style*='padding-top: 10px']").show();
        } else {
            $(".fileInputs [name='join_flg[]']").eq(index).val("single");
            // 結合ラベルを非表示
            $(this).closest('.row').find("label[style*='padding-top: 10px']").hide();
        }
    });
    $("#joinFileModal").modal("hide");
});

// 結合モーダルのチェックボックス変更イベント処理
$(document).on('change', '#fileCheckboxes input[type="checkbox"]', function() {
    $("#joinFileModal .modal-footer").find('p').remove();
    updateJoinFileCount();
});

// 選択されたファイルのカウントを更新する関数
function updateJoinFileCount() {
    var checkedCount = $('#joinFileModal #fileCheckboxes input[type="checkbox"]:checked').length;
    $("#joinFileModal .modal-footer").append(`<p style="float: left;">${checkedCount}ファイルを結合します。よろしいでしょうか？</p>`);

    var modalFooterMessage = $(".fileInputs .join-file-btn .inputFile p");
    if (modalFooterMessage.length) {
        modalFooterMessage.text(`${checkedCount}ファイルを結合します。`).show();;
    }
}
