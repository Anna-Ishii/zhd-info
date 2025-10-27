

$(document).ready(function(){
    $('#form').submit(function(event) {
        event.preventDefault();
        // ファイルは送信しない
        $('input[type="file"]').prop('disabled', true);

        // 掲載期間の入力フィールドの disabled を解除（値が送信されるようにする）
        $('input[name="start_datetime"]').prop('disabled', false);
        $('input[name="end_datetime"]').prop('disabled', false);
        
        // 「未定」チェックボックスがチェックされている場合は、値を'null'文字列にする
        var startDatetimeCheckbox = $('.dateDisabled[data-target="dateFrom"]');
        var endDatetimeCheckbox = $('.dateDisabled[data-target="dateTo"]');
        var startDatetimeInput = $('input[name="start_datetime"]');
        var endDatetimeInput = $('input[name="end_datetime"]');
        
        if (startDatetimeCheckbox.is(':checked')) {
            startDatetimeInput.val('null');
            console.log('開始日時: 未定チェックあり → null設定');
        } else {
            console.log('開始日時:', startDatetimeInput.val());
        }
        
        if (endDatetimeCheckbox.is(':checked')) {
            endDatetimeInput.val('null');
            console.log('終了日時: 未定チェックあり → null設定');
        } else {
            console.log('終了日時:', endDatetimeInput.val());
        }

        if(!emptyTagInputForm()) {
            appendFormTagInput()
        }

        console.log('=== フォーム送信実行 ===');

        // フォーム送信を直接実行（イベントハンドラーを削除してから送信）
        this.submit();
    });
});

function emptyTagLabelForm() {
    return $('.tag-form-label').length == 0;
}

function emptyTagInputForm() {
    const tagInputElement = $('.tag-form-input')[0];
    return !tagInputElement || tagInputElement.innerText == '';
}

function appendFormTagInput() {
    const tagInputElement = $('.tag-form-input')[0];
    if (tagInputElement && tagInputElement.innerText) {
        $('<input>').attr({
            type: 'hidden',
            name: 'tag_name[]',
            value: tagInputElement.innerText
        }).appendTo($('#form'));
    }
}



// アップロード中の表示を追加する関数
function showUploadingDisplay(fileName, fileSize) {
    // アップロード前の表示を非表示
    $('.uploadbefore').hide();
    
    // 既存の.file-uploading要素を表示
    $('.file-uploading').show();
    $('.file-uploading .file__name a').text(fileName);
    $('.file-uploading .file__size').text(formatFileSize(fileSize));
}

// ファイルサイズをフォーマットする関数
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// ファイル表示を更新する関数
function updateFileDisplay(fileName, filePath, fileSize = 0) {
    // アップロード中の表示を非表示
    $('.file-uploading').hide();
    
    // ファイル選択部分を再表示
    $('.uploadbefore').show();
    
    // 新しいfile-uploaded要素を追加
    addFileUploadedElement(fileName, filePath, fileSize);
}

// file-uploaded要素を追加する関数
function addFileUploadedElement(fileName, filePath, fileSize = 0) {
    const fileUploadedHtml = `
        <div class="file-uploaded">
            <p class="file__name"><a href="#">${fileName}</a></p>
            <p class="file__size">${formatFileSize(fileSize)}</p>
            <p class="file__upload_message">アップロード完了</p>
            <p class="file__delete_btn">
                <img src="/img/delete_icon.svg" alt="ファイル削除">
                <input type="hidden" name="file_name[]" value="${fileName}">
                <input type="hidden" name="file_path[]" value="${filePath}">
                <input type="hidden" name="join_flg[]" value="single">
            </p>
        </div>
    `;

    // file-uploaded要素を適切な場所に追加
    if ($('.file-uploaded').length > 0) {
        // 既存のfile-uploaded要素の後に追加
        $('.file-uploaded').last().after(fileUploadedHtml);
    } else {
        // file-join__wrapの前に追加
        if ($('.file-join__wrap').length > 0) {
            $('.file-join__wrap').before(fileUploadedHtml);
        } else {
            // フォールバック: content-fileの最後に追加
            $('.content-file').append(fileUploadedHtml);
        }
    }
}

// PDFファイル処理
$(document).ready(function(){
    addFileInputAdd();
    addJoinFileBtn();
});

$(document).on("change", 'input[type="file"][name="file[]"]', function () {
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

    // ファイルが選択されていない場合は処理を終了
    if (fileList.length === 0) {
        return;
    }

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

    // アップロード中の表示を追加
    showUploadingDisplay(fileList[0].name, fileList[0].size);

    // ファイルサイズをdata属性に保存
    _this.data('file-size', fileList[0].size);

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
                    // プログレスバーの幅を更新
                    $('.upload-progress-fill').css("width", progVal + "%");

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

            // HTMLの表示を更新
            // サーバーからのファイルサイズまたは保存されたファイルサイズを使用
            let fileSize = 0;
            if (response.file_sizes && response.file_sizes[i]) {
                fileSize = response.file_sizes[i];
            } else {
                // 保存されたファイルサイズを取得
                const fileInput = $('input[type="file"][data-cache="active"]');
                if (fileInput.length > 0) {
                    fileSize = fileInput.data('file-size') || 0;
                } else {
                    // 別の方法でファイルサイズを取得
                    const allFileInputs = $('input[type="file"]');
                    for (let j = 0; j < allFileInputs.length; j++) {
                        const savedSize = $(allFileInputs[j]).data('file-size');
                        if (savedSize) {
                            fileSize = savedSize;
                            break;
                        }
                    }
                }
            }
            updateFileDisplay(content_name, content_url, fileSize);
        } else {
            addNewFileInput(content_name, content_url, join_flg = "single");
        }
    });

    if (!dataCache) {
        let fileInputs = document.querySelector(".fileInputs");

        // fileInputsが存在しない場合は処理をスキップ
        if (!fileInputs) {
            return;
        }

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
        let joinFileBtnAdd = document.querySelector(".file-join__wrap");

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

    // PDFファイルの上書き
    } else {
        $(".fileInputs [name='join_flg[]']").each(function() {
            if ($(this).val() === "single") {
                // 結合ラベルを非表示
                $(this).closest('.row').find("label[style*='padding-top: 10px']").hide();
            }
        });

        // "join" フラグがあるか
        updateJoinFileLabel();
    }

    // 「結合中」メッセージを更新する関数の呼び出し
    updateModalFooterMessage();
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
    let fileInputs = $(".fileInputs");
    if (fileInputs.length === 0) {
        return;
    }

    fileInputs.append(`
        <div class="file-join__wrap">
            <label class="inputFile" style="display: flex; align-items: center; gap: 10px;">
                <p style="margin: 0; display: none;">0ファイルを結合中です。</p>
                <input type="button" class="joinFile" id="joinFileId" value="ファイルの結合">
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

    // fileInputs要素の存在チェック
    let fileInputs = $(".fileInputs");
    if (fileInputs.length === 0) {
        return;
    }

    // 既存の添付ラベルの数を取得
    let currentLabelCount = $(".file-input-container .control-label:contains('添付')").length + 1;

    fileInputs.append(`
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

// file-uploadedの削除ボタンのクリックイベント
$(document).on("click", ".file__delete_btn", function () {
    // 親のfile-uploaded要素を削除
    $(this).closest('.file-uploaded').remove();

    // ファイル入力フィールドの値をクリア
    $('input[type="file"][name="file[]"]').val('');

    // data-cache属性を削除して再アップロード可能にする
    $('input[type="file"][name="file[]"]').removeAttr('data-cache');

    // アップロード表示をリセット
    $('.file-uploading').hide();
    $('.upload-progress-fill').css('width', '0%');

    // モーダルのメッセージをクリア
    $("#joinFileModal .footer-message").text("");
});

// 初期状態でボタンを無効化
$(document).ready(function() {
    $("#joinFileModal .join-file-modal-footer #joinFileBtn").prop('disabled', true);
});

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

    // ファイル入力フィールドの値をクリア
    $('input[type="file"][name="file[]"]').val('');

    // data-cache属性を削除して再アップロード可能にする
    $('input[type="file"][name="file[]"]').removeAttr('data-cache');

    // アップロード表示をリセット
    $('.file-uploading').hide();
    $('.upload-progress-fill').css('width', '0%');
    // 「結合中」メッセージを更新する関数の呼び出し
    updateModalFooterMessage();

    // "join" フラグがあるか
    updateJoinFileLabel();
});

// 初期状態でボタンの有効/無効を設定し、メッセージを表示
$(document).ready(function() {
    // 「結合中」メッセージを更新する関数の呼び出し
    updateModalFooterMessage();
    // 初期状態でメッセージを表示
    updateJoinFileCount();
    // "join" フラグがあるか
    updateJoinFileLabel();
});

// ファイルの結合ボタン処理
$(document).on("click", "#joinFileId", function () {
    var selectedFiles = [];
    var selectedFilePaths = [];
    var selectedJoinFiles = [];
    var pdfFiles = [];
    var imageFiles = [];

    // ファイル名とファイルパスをそれぞれの配列に追加
    $(".file-uploaded [name='file_name[]']").each(function(){
        var value = $(this).val();
        if (value) {
            selectedFiles.push(value);
            // ファイル拡張子をチェック
            var extension = value.toLowerCase().split('.').pop();
            if (extension === 'pdf') {
                pdfFiles.push(value);
            } else if (['jpg', 'jpeg', 'png'].includes(extension)) {
                imageFiles.push(value);
            }
        }
    });
    $(".file-uploaded [name='file_path[]']").each(function(){
        var value = $(this).val();
        if (value) {
            selectedFilePaths.push(value);
        }
    });
    $(".file-uploaded [name='join_flg[]']").each(function(){
        var value = $(this).val();
        selectedJoinFiles.push(value);
    });

    var $modalBody = $("#joinFileModal #fileCheckboxes");
    var $modalFooter = $("#joinFileModal .join-file-modal-footer");
    $modalBody.empty();
    $modalFooter.find('p').remove();

    // 古いメッセージをクリア
    $("#joinFileModal .footer-message").text("");

    if (selectedFiles.length > 0) {
        // PDFファイルのみを表示
        if (pdfFiles.length > 0) {
            // ファイルタイプ別のメッセージを表示
            var messageText = "";
            if (imageFiles.length > 0) {
                messageText = `PDFファイルのみが結合対象です（画像ファイル${imageFiles.length}件は除外されています）`;
            } else {
                messageText = "PDFファイルのみが結合対象です";
            }
            $modalBody.append(`<div class="file-type-message">${messageText}</div>`);

            // PDFファイルのみをチェックボックスとして表示
            pdfFiles.forEach(function(file, index) {
                var filePath = selectedFilePaths[selectedFiles.indexOf(file)] || 'パスがありません';
                var isChecked = selectedJoinFiles[selectedFiles.indexOf(file)] === "join" ? "checked" : "";
                var labelText = (index === 0) ? '業連' : `添付${index}`;
                var checkbox =
                    `<div class="checkbox">
                        <label>
                            <input type="checkbox" value="${filePath}" ${isChecked}>${labelText} ${file}
                        </label>
                    </div>`;
                $modalBody.append(checkbox);
            });
        } else {
            // PDFファイルがない場合
            $modalBody.append(`<div class="no-pdf-message">結合可能なPDFファイルがありません</div>`);
        }
        // 選択されたファイルのカウントを更新する関数
        updateJoinFileCount();
    } else {
        $("#joinFileModal .footer-message").text("結合するファイルが選択されていません。");
    }

    // カスタムモーダルを開く
    openJoinFileModal();
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
    $(".file-uploaded [name='file_path[]']").each(function() {
        var value = $(this).val();
        if (value) {
            selectedFilePaths.push(value);
        }
    });

    // チェックされたファイルパスと一致するファイルパスのjoin_flg[]の値を"join"に設定し、ラベルを表示
    // チェックが外された場合は"single"に設定し、ラベルを非表示
    $(".file-uploaded [name='file_path[]']").each(function(index) {
        var value = $(this).val();
        if (checkedFileValues.includes(value)) {
            // "join" フラグを設定
            $(".file-uploaded [name='join_flg[]']").eq(index).val("join");
        } else {
            // "single" フラグを設定
            $(".file-uploaded [name='join_flg[]']").eq(index).val("single");
        }
    });

    var modalFooterMessage = $(".file-join__wrap p");
    if (modalFooterMessage.length) {
        var checkedCount = checkedFileValues.length;
        if (checkedCount >= 2) {
            modalFooterMessage.text(`${checkedCount}ファイルを結合します。`).show();
        } else {
            modalFooterMessage.text("").hide();
        }
    }

    // "join" フラグがあるか
    updateJoinFileLabel();

    closeJoinFileModal();
});

// 結合モーダルのチェックボックス変更イベント処理
$(document).on('change', '#fileCheckboxes input[type="checkbox"]', function() {
    // 選択されたファイルのカウントを更新する関数
    updateJoinFileCount();
});

// 選択されたファイルのカウントを更新する関数
function updateJoinFileCount() {
    var checkedCount = $('#joinFileModal #fileCheckboxes input[type="checkbox"]:checked').length;

    // 既存のメッセージを削除
    $("#joinFileModal .footer-message").empty();

    // メッセージを追加
    if (checkedCount >= 2) {
        $("#joinFileModal .footer-message").text(`${checkedCount}ファイルを結合します。よろしいでしょうか？`);
    } else if (checkedCount == 0) {
        $("#joinFileModal .footer-message").text("結合するファイルが選択されていません。");
    } else {
        $("#joinFileModal .footer-message").text("");
    }

    // ボタンの有効/無効を設定
    var modalFooterJoinFileBtn = $("#joinFileModal .join-file-modal-footer #joinFileBtn");
    if (modalFooterJoinFileBtn.length) {
        if (checkedCount === 1) {
        modalFooterJoinFileBtn.prop('disabled', true);
        } else {
            modalFooterJoinFileBtn.prop('disabled', false);
        }
    }
}

// 「結合中」メッセージを更新する関数の呼び出し
function updateModalFooterMessage() {
    var selectedJoinFiles = [];

    $(".file-uploaded [name='join_flg[]']").each(function() {
        var value = $(this).val();
        selectedJoinFiles.push(value);
    });

    var checkedCount = selectedJoinFiles.filter(value => value === "join").length;

    var modalFooterMessage = $(".file-join__wrap p");
    if (modalFooterMessage.length) {
        if (checkedCount >= 2) {
            modalFooterMessage.text(`${checkedCount}ファイルを結合します。`).show();
        } else {
            modalFooterMessage.text("").hide();
        }
    }
}

// "join" フラグがあるか
function updateJoinFileLabel() {
    // "join" フラグが1つ以下の場合に文言を変更
    var joinFlagCount = $(".file-uploaded [name='join_flg[]']").filter(function() {
        return $(this).val() === "join";
    }).length;

    if (joinFlagCount <= 1) {
        // "join" フラグが1つの場合に他の "join_flg" を "single" に変更
        if (joinFlagCount === 1) {
            $(".file-uploaded [name='join_flg[]']").each(function() {
                if ($(this).val() === "join") {
                    $(this).val("single");
                }
            });
        }

        $(".inputFile #joinFileId").val("ファイルの結合");
    }

    // "join" フラグが一つでもあるかチェックして文言を変更
    var hasJoinFlag = joinFlagCount > 1;

    if (hasJoinFlag) {
        $(".inputFile #joinFileId").val("結合の修正");
    }
}

// 削除確認モーダル制御
window.confirmDelete = function() {
    const modal = document.getElementById('deleteConfirmModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
    } else {
        console.error('Modal not found');
    }
}

// 複製確認モーダル制御
window.confirmDuplicate = function() {
    const modal = document.getElementById('duplicateConfirmModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
    } else {
        console.error('Modal not found');
    }
}

// 登録確認モーダル制御
window.confirmRegister = function() {
    const modal = document.getElementById('registerConfirmModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
    } else {
        console.error('Modal not found');
    }
}

// 複製処理
function executeDuplicate(deleteOriginal) {
    const executeBtn = deleteOriginal ? document.getElementById('duplicateWithDeleteBtn') : document.getElementById('duplicateWithoutDeleteBtn');

    // ボタンを非活性化（連打防止）
    if (executeBtn) {
        executeBtn.disabled = true;
        executeBtn.style.opacity = '0.6';
        executeBtn.style.cursor = 'not-allowed';
    }

    // 現在のURLからmessage_idを取得
    const currentPath = window.location.pathname;
    const messageId = currentPath.split('/').pop();

    // 処理を実行
    $.ajax({
        url: '/admin/message/publish/duplicate/' + messageId,
        type: 'POST',
        data: JSON.stringify({
            delete_original: deleteOriginal
        }),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            // 確認モーダルを閉じる
            const confirmModal = document.getElementById('duplicateConfirmModal');
            if (confirmModal) {
                confirmModal.style.display = 'none';
                confirmModal.classList.remove('show');
            }

            // 複製されたメッセージの編集ページに遷移
            window.location.href = '/admin/message/publish/edit/' + response.new_message_id;
        },
        error: function(xhr, status, error) {
            console.error('Duplicate failed:', error);
            console.error('Response:', xhr.responseText);
            console.error('Status:', xhr.status);

            let errorMessage = '複製に失敗しました。';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.error) {
                    errorMessage += '\n詳細: ' + xhr.responseJSON.error;
                } else if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
            }
            alert(errorMessage);

            // エラー時はボタンを再度有効化
            if (executeBtn) {
                executeBtn.disabled = false;
                executeBtn.style.opacity = '1';
                executeBtn.style.cursor = 'pointer';
            }

            // 確認モーダルを閉じる
            const confirmModal = document.getElementById('duplicateConfirmModal');
            if (confirmModal) {
                confirmModal.style.display = 'none';
                confirmModal.classList.remove('show');
            }
        }
    });
}

// 配信停止・配信再開処理
window.confirmStop = function(isRestart) {
    // 適切なモーダルを表示
    if (isRestart) {
        const modal = document.getElementById('restartConfirmModal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('show');
        }
    } else {
        const modal = document.getElementById('stopConfirmModal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('show');
        }
    }
}

// 配信停止・配信再開の実行処理
function executeDeliveryControl(isRestart) {
    const action = isRestart ? '配信再開' : '配信停止';
    const executeBtn = document.getElementById(isRestart ? 'restartExecuteBtn' : 'stopExecuteBtn');

    // ボタンを非活性化（連打防止）
    if (executeBtn) {
        executeBtn.disabled = true;
        executeBtn.style.opacity = '0.6';
        executeBtn.style.cursor = 'not-allowed';
    }

    // 現在のURLからmessage_idを取得
    const currentPath = window.location.pathname;
    const messageId = currentPath.split('/').pop();

    const endpoint = isRestart ? '/admin/message/publish/restart' : '/admin/message/publish/stop';

    // 処理を実行
    $.ajax({
        url: endpoint,
        type: 'POST',
        data: JSON.stringify({
            message_id: [messageId]
        }),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            // 確認モーダルを閉じる
            const confirmModal = document.getElementById(isRestart ? 'restartConfirmModal' : 'stopConfirmModal');
            if (confirmModal) {
                confirmModal.style.display = 'none';
                confirmModal.classList.remove('show');
            }

            // 成功時はページをリロード
            window.location.reload();
        },
        error: function(xhr, status, error) {
            console.error(action + ' failed:', error);
            console.error('Response:', xhr.responseText);
            const errorMessage = xhr.responseJSON?.message || action + 'に失敗しました。';
            alert(errorMessage);

            // エラー時はボタンを再度有効化
            if (executeBtn) {
                executeBtn.disabled = false;
                executeBtn.style.opacity = '1';
                executeBtn.style.cursor = 'pointer';
            }

            // 確認モーダルを閉じる
            const confirmModal = document.getElementById(isRestart ? 'restartConfirmModal' : 'stopConfirmModal');
            if (confirmModal) {
                confirmModal.style.display = 'none';
                confirmModal.classList.remove('show');
            }
        }
    });
}

$(document).ready(function() {

    // 削除実行ボタンのクリックイベント
    $('#deleteExecuteBtn').on('click', function() {

        // 現在のURLからmessage_idを取得
        const currentPath = window.location.pathname;
        const messageId = currentPath.split('/').pop();

        // 削除処理を実行
        $.ajax({
            url: '/admin/message/publish/delete/' + messageId,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // モーダルを閉じる
                const modal = document.getElementById('deleteConfirmModal');
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                }
                // 成功時のリダイレクト（少し遅延を入れる）
                setTimeout(function() {
                    window.location.href = '/admin/message/publish';
                }, 100);
            },
            error: function(xhr, status, error) {
                console.error('Delete failed:', error);
                console.error('Response:', xhr.responseText);
                alert('削除に失敗しました。');
            }
        });
    });

    // 戻るボタンでモーダルを閉じる
    $('#deleteBackBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const modal = document.getElementById('deleteConfirmModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    });

    // モーダル背景をクリックしたら閉じる
    $('#deleteConfirmModal').on('click', function(e) {
        if (e.target === this) {
            $(this).css('display', 'none');
            $(this).removeClass('show');
        }
    });

    // 配信停止実行ボタンのクリックイベント
    $('#stopExecuteBtn').on('click', function() {
        executeDeliveryControl(false);
    });

    // 配信停止の戻るボタンでモーダルを閉じる
    $('#stopBackBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const modal = document.getElementById('stopConfirmModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    });

    // 配信停止モーダル背景をクリックしたら閉じる
    $('#stopConfirmModal').on('click', function(e) {
        if (e.target === this) {
            $(this).css('display', 'none');
            $(this).removeClass('show');
        }
    });

    // 配信再開実行ボタンのクリックイベント
    $('#restartExecuteBtn').on('click', function() {
        executeDeliveryControl(true);
    });

    // 配信再開の戻るボタンでモーダルを閉じる
    $('#restartBackBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const modal = document.getElementById('restartConfirmModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    });

    // 配信再開モーダル背景をクリックしたら閉じる
    $('#restartConfirmModal').on('click', function(e) {
        if (e.target === this) {
            $(this).css('display', 'none');
            $(this).removeClass('show');
        }
    });

    // 削除して複製ボタンのクリックイベント
    $('#duplicateWithDeleteBtn').on('click', function() {
        executeDuplicate(true);
    });

    // 削除しないで複製ボタンのクリックイベント
    $('#duplicateWithoutDeleteBtn').on('click', function() {
        executeDuplicate(false);
    });

    // 複製モーダル背景をクリックしたら閉じる
    $('#duplicateConfirmModal').on('click', function(e) {
        if (e.target === this) {
            $(this).css('display', 'none');
            $(this).removeClass('show');
        }
    });

    // 登録実行ボタンのクリックイベント
    $('#registerExecuteBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        // 確認モーダルを閉じる
        const confirmModal = document.getElementById('registerConfirmModal');
        if (confirmModal) {
            confirmModal.style.display = 'none';
            confirmModal.classList.remove('show');
        }

        // フォームを送信
        const form = document.getElementById('form');
        if (form) {
            // 既存のregister inputがあれば削除
            const existingRegisterInput = form.querySelector('input[name="register"]');
            if (existingRegisterInput) {
                existingRegisterInput.remove();
            }

            // register inputを追加
            const registerInput = document.createElement('input');
            registerInput.type = 'hidden';
            registerInput.name = 'register';
            registerInput.value = '1';
            form.appendChild(registerInput);

            // instruction の値を instruction_flg に変換
            const instructionRadio = form.querySelector('input[name="instruction"]:checked');
            if (instructionRadio) {
                // 既存のinstruction_flg inputがあれば削除
                const existingInstructionFlgInput = form.querySelector('input[name="instruction_flg"]');
                if (existingInstructionFlgInput) {
                    existingInstructionFlgInput.remove();
                }

                // instruction_flg inputを追加
                const instructionFlgInput = document.createElement('input');
                instructionFlgInput.type = 'hidden';
                instructionFlgInput.name = 'instruction_flg';
                instructionFlgInput.value = instructionRadio.value === 'あり' ? '1' : '0';
                form.appendChild(instructionFlgInput);

                // URLパラメータから instruction_id と instruction_title を取得
                const urlParams = new URLSearchParams(window.location.search);
                const instructionId = urlParams.get('instruction_id');
                const instructionTitle = urlParams.get('instruction_title');

                // instruction_id が存在する場合は追加（空でも追加）
                if (instructionId !== null) {
                    const existingInstructionIdInput = form.querySelector('input[name="instruction_id"]');
                    if (existingInstructionIdInput) {
                        existingInstructionIdInput.value = instructionId;
                    } else {
                        const instructionIdInput = document.createElement('input');
                        instructionIdInput.type = 'hidden';
                        instructionIdInput.name = 'instruction_id';
                        instructionIdInput.value = instructionId;
                        form.appendChild(instructionIdInput);
                    }
                }

                // instruction_title が存在する場合は追加（空でも追加）
                if (instructionTitle !== null) {
                    const existingInstructionTitleInput = form.querySelector('input[name="instruction_title"]');
                    if (existingInstructionTitleInput) {
                        existingInstructionTitleInput.value = instructionTitle;
                    } else {
                        const instructionTitleInput = document.createElement('input');
                        instructionTitleInput.type = 'hidden';
                        instructionTitleInput.name = 'instruction_title';
                        instructionTitleInput.value = instructionTitle;
                        form.appendChild(instructionTitleInput);
                    }
                }
            }

            // onbeforeunloadイベントを無効化
            window.onbeforeunload = null;

            // フォームを送信
            form.submit();
        }
    });

    // 登録の戻るボタンでモーダルを閉じる
    $('#registerBackBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const modal = document.getElementById('registerConfirmModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    });

    // 登録モーダル背景をクリックしたら閉じる
    $('#registerConfirmModal').on('click', function(e) {
        if (e.target === this) {
            $(this).css('display', 'none');
            $(this).removeClass('show');
        }
    });
});

// 保存ボタンの処理（グローバルスコープ）
window.submitSave = function() {
    const form = document.getElementById('form');
    if (!form) {
        console.error('フォームが見つかりません');
        return;
    }

    // POSTメソッドを確実に設定
    form.method = 'POST';

    // 保存フラグを追加（既存のものがあれば削除してから追加）
    const existingSaveInput = form.querySelector('input[name="save"]');
    if (existingSaveInput) {
        existingSaveInput.remove();
    }

    const saveInput = document.createElement('input');
    saveInput.type = 'hidden';
    saveInput.name = 'save';
    saveInput.value = '1';
    form.appendChild(saveInput);

    // instruction の値を instruction_flg に変換
    const instructionRadio = form.querySelector('input[name="instruction"]:checked');
    if (instructionRadio) {
        // 既存のinstruction_flg inputがあれば削除
        const existingInstructionFlgInput = form.querySelector('input[name="instruction_flg"]');
        if (existingInstructionFlgInput) {
            existingInstructionFlgInput.remove();
        }

        // instruction_flg inputを追加
        const instructionFlgInput = document.createElement('input');
        instructionFlgInput.type = 'hidden';
        instructionFlgInput.name = 'instruction_flg';
        instructionFlgInput.value = instructionRadio.value === 'あり' ? '1' : '0';
        form.appendChild(instructionFlgInput);

        // URLパラメータから instruction_id と instruction_title を取得
        const urlParams = new URLSearchParams(window.location.search);
        const instructionId = urlParams.get('instruction_id');
        const instructionTitle = urlParams.get('instruction_title');

        // instruction_id が存在する場合は追加（空でも追加）
        if (instructionId !== null) {
            const existingInstructionIdInput = form.querySelector('input[name="instruction_id"]');
            if (existingInstructionIdInput) {
                existingInstructionIdInput.value = instructionId;
            } else {
                const instructionIdInput = document.createElement('input');
                instructionIdInput.type = 'hidden';
                instructionIdInput.name = 'instruction_id';
                instructionIdInput.value = instructionId;
                form.appendChild(instructionIdInput);
            }
        }

        // instruction_title が存在する場合は追加（空でも追加）
        if (instructionTitle !== null) {
            const existingInstructionTitleInput = form.querySelector('input[name="instruction_title"]');
            if (existingInstructionTitleInput) {
                existingInstructionTitleInput.value = instructionTitle;
            } else {
                const instructionTitleInput = document.createElement('input');
                instructionTitleInput.type = 'hidden';
                instructionTitleInput.name = 'instruction_title';
                instructionTitleInput.value = instructionTitle;
                form.appendChild(instructionTitleInput);
            }
        }
    }

    // window.onbeforeunloadをクリア
    window.onbeforeunload = null;

    // フォーム送信
    form.submit();
}


// ファイル結合モーダルの開閉機能
function openJoinFileModal() {
    $('#joinFileModal').show();
    $('body').addClass('modal-open');
}

function closeJoinFileModal() {
    $('#joinFileModal').hide();
    $('body').removeClass('modal-open');
}

// モーダル閉じるボタンのイベント
$(document).on('click', '#joinFileModalClose', function() {
    closeJoinFileModal();
});

// オーバーレイクリックでモーダルを閉じる
$(document).on('click', '.join-file-modal-overlay', function(e) {
    e.preventDefault();
    e.stopPropagation();
    closeJoinFileModal();
});

// ESCキーでモーダルを閉じる
$(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#joinFileModal').is(':visible')) {
        closeJoinFileModal();
    }
});