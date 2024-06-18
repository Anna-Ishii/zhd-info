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


$(document).on("change", '.fileInputs input[type="file"]', function () {
    let csrfToken = $('meta[name="csrf-token"]').attr("content");
    let fileList = $(this)[0].files;
    let formData = new FormData();
    let labelForm = $(this).parent();
    let progress = labelForm.parent().find(".progress");
    let progressBar = progress.children(".progress-bar");

    // 以前のエラーメッセージを削除
    labelForm.parent().find(".text-danger").remove();

    // 既存のファイル数を取得 (ファイル入力欄の-1)
    let existingFilesCount1 = $(".fileInputs .file-input-container").length - 1;
    if (existingFilesCount1) {
        let maxFiles = 10; // 上限数を設定（10）
        if (existingFilesCount1 + fileList.length > maxFiles) {
            labelForm.parent().append(`<div class="text-danger">登録可能なファイルの上限は${maxFiles}件です</div>`);
            // ファイル入力をクリア
            $(this).val('');
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

    let fileName = $(this).siblings('input[name="file_name[]"]');
    let filePath = $(this).siblings('input[name="file_path[]"]');

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
        handleResponse(response, fileName, filePath);

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
    });
});

// アップロード完了後の処理
function handleResponse(response, fileName, filePath) {
    let fileInputs = document.querySelector(".fileInputs");
    let fileInput = fileInputs.querySelector('input[name="file[]"]');

    // 単一ファイル欄に加工
    if (fileInput) {
        fileInput.removeAttribute("multiple");
        fileInput.name = "file";
        // 削除ボタン追加
        addDeleteButton(fileInput);
    }

    // responseが複数ファイルに対応している場合
    response.content_names.forEach((content_name, i) => {
        let content_url = response.content_urls[i];
        if (i === 0) {
            fileName.val(content_name);
            filePath.val(content_url);
        } else {
            addNewFileInput(content_name, content_url);
        }
    });

    // 上限を超えていない場合、かつファイル数が上限に達していない場合のみ追加ボタンを表示
    let existingFilesCount2 = $(".fileInputs .file-input-container").length; // 既存のファイル数を取得
    let maxFiles = 10; // 上限数を設定（10）
    if (existingFilesCount2 < maxFiles) {
        let fileInputAdd = document.querySelector(".file-input-add");
        if (fileInputAdd === null) {
            addFileInputAddButton();
        }
    }
}

// 削除ボタン
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
function addNewFileInput(content_name, content_url) {
    $(".fileInputs").append(`
        <div class="file-input-container">
            <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="" required>
            <label class="inputFile form-control">
                <span class="fileName">${content_name}</span>
                <input type="file" name="file" accept=".pdf">
                <input type="hidden" name="file_name[]" value="${content_name}">
                <input type="hidden" name="file_path[]" value="${content_url}">
                <button type="button" class="btn btn-sm delete-btn" style="background-color: #eee; color: #000; position: absolute; top: 0; right: 0;">削除</button>
            </label>
            <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: 0%"></div>
            </div>
        </div>
    `);
}

// 追加ボタンを表示
function addFileInputAddButton() {
    $(".fileInputs").append(`
        <div class="file-input-add">
            <label class="inputFile" style="float: right;">
                <span class="custom-upload" style="background-color: #eee; padding: 10px 20px; border-radius: 5px; cursor: pointer; display: inline-block;">追　加</span>
                <input type="button" class="fileUploadButton" style="display: none;">
            </label>
        </div>
    `);
}

// 追加ボタンのクリックイベント
$(document).on("click", '.custom-upload', function () {
    // 追加ボタン削除
    let fileInputAdd = document.querySelector(".file-input-add");
    if (fileInputAdd) {
        fileInputAdd.remove();
    }

    // 変数を初期化
    let file_name = "";
    let file_path = "";

    $(".fileInputs").append(`
        <div class="file-input-container">
            <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="" required>
            <label class="inputFile form-control">
                <span class="fileName" style="text-align: center;">${file_name ? file_name : "ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能"}</span>
                <input type="file" name="file[]" accept=".pdf" multiple="multiple">
                <input type="hidden" name="file_name[]" value="${file_name}">
                <input type="hidden" name="file_path[]" value="${file_path}">
            </label>
            <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: 0%"></div>
            </div>
        </div>
    `);
});

// 削除ボタンのクリックイベント
$(document).on("click", ".delete-btn", function () {
    var labelInputFile = $(this).parent();
    var div = labelInputFile.parent();
    div.remove();

    let fileInputAdd = document.querySelector(".file-input-add");
    let existingFilesCount3 = $(".fileInputs .file-input-container").length;

    // 上限を超えていない場合、かつファイル数が上限に達していない場合のみ追加ボタンを表示
    let maxFiles = 10; // 上限数を設定（10）
    if (fileInputAdd === null && existingFilesCount3 < maxFiles) {
        addFileInputAddButton();
    }

    // ファイル入力欄がない際の追加処理
    if (existingFilesCount3 === 0) {
        // 追加ボタン削除
        if (fileInputAdd) {
            fileInputAdd.remove();
        }

        // 変数を初期化
        let file_name = "";
        let file_path = "";

        $(".fileInputs").append(`
            <div class="file-input-container">
                <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="" required>
                <label class="inputFile form-control">
                    <span class="fileName" style="text-align: center;">${file_name ? file_name : "ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能"}</span>
                    <input type="file" name="file[]" accept=".pdf" multiple="multiple">
                    <input type="hidden" name="file_name[]" value="${file_name}">
                    <input type="hidden" name="file_path[]" value="${file_path}">
                </label>
                <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar" style="width: 0%"></div>
                </div>
            </div>
        `);
    }
});
