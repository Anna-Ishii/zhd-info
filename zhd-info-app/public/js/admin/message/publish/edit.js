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


// 追加ボタンのクリックイベント
let addFlg = false;
$(document).on("change", '#fileUpload', function () {
    addFlg = true;
});


$(document).on("change", 'input[type="file"]', function () {
    let csrfToken = $('meta[name="csrf-token"]').attr("content");
    let fileList = $(this)[0].files;
    let formData = new FormData();
    let labelForm = $(this).parent();
    let progress = labelForm.parent().find(".progress");
    let progressBar = progress.children(".progress-bar");

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
                    console.log(progVal);
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

function handleResponse(response, fileName, filePath) {
    let fileInputs = document.getElementsByClassName("fileInputs")[0];
    let fileInput = fileInputs.querySelector('input[type="file"]');

    // 追加ボタンを押した場合の処理
    if (addFlg) {
        let fileInputAdd = document.querySelector(".file-input-add");
        if (fileInputAdd) {
            fileInputAdd.remove();
        }
    }

    // responseが複数ファイルに対応している場合
    response.content_names.forEach((content_name, i) => {
        let content_url = response.content_urls[i];

        // 追加ボタンを押した場合の処理
        if (addFlg) {
            addNewFileInput(content_name, content_url);

        // 追加ボタンではない場合の処理
        } else {
            if (i === 0) {
                fileName.val(content_name);
                filePath.val(content_url);
            }
        }
    });

    if ($(".file-input-add").length === 0) {
        addFileInputAddButton();
    }

    addFlg = false;
}

// 追加アップロードファイル欄
function addNewFileInput(content_name, content_url) {
    $(".fileInputs").append(`
        <div class="file-input-container">
            <label class="inputFile form-control">
                <span class="fileName">${content_name}</span>
                <input type="file" name="file" accept=".pdf" style="display:none">
                <input type="hidden" name="file_name[]" value="${content_name}">
                <input type="hidden" name="file_path[]" value="${content_url}">
                <button type="button" class="btn btn-danger btn-sm delete-btn" style="position: absolute; top: 0; right: 0;">削除</button>
            </label>
            <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: 0%"></div>
            </div>
        </div>
    `);
}

// 追加ボタン
function addFileInputAddButton() {
    $(".fileInputs").append(`
        <div class="file-input-add">
            <label class="inputFile" style="float: right;">
                <label for="fileUpload" class="custom-upload" style="background-color: #eee; padding: 10px 20px; border-radius: 5px; cursor: pointer; display: inline-block;">追　加</label>
                <input type="file" id="fileUpload" name="file[]" accept=".pdf" multiple="multiple" style="display: none">
            </label>
        </div>
    `);
}


// 削除ボタンのクリックイベント
$(document).on("click", ".delete-btn", function () {
    var labelInputFile = $(this).parent();
    var div = labelInputFile.parent();
    div.remove();
});
