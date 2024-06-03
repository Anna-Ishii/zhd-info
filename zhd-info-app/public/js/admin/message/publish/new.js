$(document).ready(function () {
    $("#form").submit(function (event) {
        event.preventDefault();
        // ファイルは送信しない
        $('input[type="file"]').prop("disabled", true);

        if (!emptyTagInputForm()) {
            appendFormTagInput();
        }

        $("#form").off("submit").submit();
    });
});

function emptyTagLabelForm() {
    return $(".tag-form-label").length == 0;
}

function emptyTagInputForm() {
    return $(".tag-form-input")[0].innerText == "";
}

function appendFormTagInput() {
    $("<input>")
        .attr({
            type: "hidden",
            name: "tag_name[]",
            value: $(".tag-form-input")[0].innerText,
        })
        .appendTo($("#form"));
}


// 追加ボタンのクリックイベント
let addFlg = false;
$(document).on("change", '#fileUpload', function () {
    addFlg = true;
});


// 初回アップロードを示すフラグ
let initialUpload = true;

$(document).on("change", 'input[type="file"]', function () {
    let csrfToken = $('meta[name="csrf-token"]').attr("content");
    let fileList = $(this)[0].files;
    let formData = new FormData();
    let labelForm = $(this).parent();
    let progress = labelForm.parent().find(".progress");
    let progressBar = progress.children(".progress-bar");

    // 初回アップロード時は1減らす
    let existingFilesCount =  initialUpload ? $(".fileInputs .file-input-container").length - 1 : $(".fileInputs .file-input-container").length;

    labelForm.parent().find(".text-danger").remove();

    if (initialUpload || addFlg) {
        // ファイル数が上限を超えているかチェック
        let maxFiles = 10; // パラメータから上限数を取得（10）
        if (existingFilesCount + fileList.length > maxFiles) {
            labelForm.parent().append(`<div class="text-danger">登録可能なファイルの上限は${maxFiles}件です</div>`);
            // ファイル入力をクリア
            $(this).val('');
            // 上限を超えていたら処理を中断
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

        // 初回アップロードが完了したらフラグをfalseにする
        initialUpload = false;
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
    // 追加ボタンではない場合の処理
    } else {
        fileInput.removeAttribute("multiple");
        fileInput.name = "file";
        if (!fileInputs.querySelector('.delete-btn')) {
            addDeleteButton(fileInput);
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
            } else {
                addNewFileInput(content_name, content_url);
            }
        }
    });

    // 上限を超えていない場合、かつファイル数が上限に達していない場合のみ追加ボタンを表示
    let maxFiles = 10; // パラメータから上限数を取得
    if ($(".fileInputs .file-input-container").length < maxFiles) {
        let fileInputAdd = document.querySelector(".file-input-add");
        if (fileInputAdd === null) {
            addFileInputAddButton();
        }
    }

    addFlg = false;
}

// 削除ボタン
function addDeleteButton(fileInput) {
    let deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.className = "btn btn-danger btn-sm delete-btn";
    deleteButton.style.position = "absolute";
    deleteButton.style.top = "0";
    deleteButton.style.right = "0";
    deleteButton.textContent = "削除";
    fileInput.parentNode.appendChild(deleteButton);
}

// 追加アップロードファイル欄
function addNewFileInput(content_name, content_url) {
    $(".fileInputs").append(`
        <div class="file-input-container">
            <label class="inputFile form-control">
                <span class="fileName">${content_name}</span>
                <input type="file" name="file" accept=".pdf">
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
                <input type="file" id="fileUpload" name="file[]" accept=".pdf" multiple="multiple">
            </label>
        </div>
    `);
}


window.onbeforeunload = function (e) {
    if (inputCheck()) return;
    e.preventDefault();
    e.returnValue = "";
};


// 入力チェック
// 何か入力状態であれば、falseを返す
function inputCheck() {
    if ($('input[name="title"]').val() != "") return false;
    if ($('input[name="file"]').val() != "") return false;
    if ($('input[name="category_id"]:checked').val() != null) return false;
    if ($('input[name="emergency_flg"]:checked').val() != null) return false;
    if ($("input[class='dateDisabled']:checked").length > 0) return false;
    if ($('input[name="start_datetime"]').val() != "") return false;
    if ($('input[name="end_datetime"]').val() != "") return false;
    if ($('input[name="target_roll[]"]:checked').val() != null) return false;
    if ($('input[name="brand[]"]:checked').val() != null) return false;
    if ($('input[name="organization[]"]:checked').val() != null) return false;
    return true;
}


// 削除ボタンのクリックイベント
$(document).on("click", ".delete-btn", function () {
    var labelInputFile = $(this).parent();
    var div = labelInputFile.parent();
    div.remove();

    let fileInputAdd = document.querySelector(".file-input-add");
    if (fileInputAdd === null) {
        // 上限を超えていない場合、かつファイル数が上限に達していない場合のみ追加ボタンを表示
        let maxFiles = 10; // パラメータから上限数を取得
        if ($(".fileInputs .file-input-container").length < maxFiles) {
            addFileInputAddButton();
        }
    }
});


//
document.getElementById('storeSelectBtn').addEventListener('click', function () {
    Swal.fire({
        title: '店舗を選択してください。',
        html: `
            <ul class="list-group">
                <li class="list-group-item">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="tokyoStore">
                        <label class="form-check-label" for="tokyoStore">
                            東京・川崎BL
                        </label>
                        <ul class="list-group mt-2">
                            <li class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="tachikawaStore">
                                    <label class="form-check-label" for="tachikawaStore">
                                        257保谷
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="tachikawaStore">
                                    <label class="form-check-label" for="tachikawaStore">
                                        272立川
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="tachikawaStore">
                                    <label class="form-check-label" for="tachikawaStore">
                                        372川崎中原
                                    </label>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
                <!-- 他の店舗の繰り返し -->
            </ul>
        `,
        confirmButtonText: '選択',
        preConfirm: () => {
            // チェックされた店舗を収集し、必要な処理を行う
        }
    });
});


// モーダル：CSV取込
document.getElementById('importCsvBtn').addEventListener('click', function () {
    Swal.fire({
        title: 'CSV取込',
        html: `
            <form class="mb-3">
                <label for="storeSearch" class="form-label">2店舗選択中</label>
            </form>
            <ul class="list-group">
                <li class="list-group-item">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="tokyoCsv">
                        <label class="form-check-label" for="tokyoCsv">
                            東京・川崎BL
                        </label>
                        <ul class="list-group mt-2">
                            <li class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="tokyoCsv">
                                    <label class="form-check-label" for="tokyoCsv">
                                        257保谷
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="tokyoCsv">
                                    <label class="form-check-label" for="tokyoCsv">
                                        272立川
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="tokyoCsv">
                                    <label class="form-check-label" for="tokyoCsv">
                                        372川崎中原
                                    </label>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
                <!-- 他の店舗の繰り返し -->
            </ul>
        `,
        confirmButtonText: '選択',
        preConfirm: () => {
            // チェックされた店舗を収集し、必要な処理を行う
        }
    });
});
