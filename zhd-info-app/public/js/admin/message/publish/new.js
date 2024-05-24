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

$(document).on("change", 'input[type="file"]', function () {
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    var fileList = $(this)[0].files;
    let formData = new FormData();


    // 複数アップロードファイルの場合の処理
    if (fileList.length > 1) {
        // ファイルをformDataに追加
        for (var i = 0; i < fileList.length; i++) {
            formData.append("file" + i, fileList[i]);
        }

        var labelForm = $(this).parent();
        var progress = labelForm.parent().find(".progress");
        var progressBar = progress.children(".progress-bar");

        progressBar.hide();
        progressBar.css("width", 0 + "%");
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
                XHR = $.ajaxSettings.xhr();
                if (XHR.upload) {
                    XHR.upload.addEventListener(
                        "progress",
                        function (e) {
                            var progVal =
                                parseInt((e.loaded / e.total) * 10000) / 100;
                            progressBar.show();
                            progressBar.css("width", progVal + "%");
                            console.log(progVal);

                            if (progVal == 100) {
                                // アップロードが完了したら、サーバー側で保存処理が始まる
                                setTimeout(() => {
                                    progress.hide();
                                }, 1000);
                            }
                        },
                        false
                    );
                }
                return XHR;
            },
        })
            .done(function (response) {
                labelForm.parent().find(".text-danger").remove();


                // 追加ボタンがある場合の処理
                var fileInputAdd = document.querySelector(".file-input-add");
                if (fileInputAdd) {
                    fileInputAdd.remove();

                // 追加ボタンがない場合の処理
                } else {
                    // multiple属性を削除処理
                    var fileInputs = document.getElementsByClassName("fileInputs")[0];
                    var fileInput  = fileInputs.querySelector('input[type="file"]');
                    fileInput.removeAttribute("multiple");

                    // nameを変更
                    fileInput.name = "file";

                    // 削除ボタンの存在チェック
                    if (!fileInputs.querySelector('.delete-btn')) {
                        // 削除ボタンを追加
                        var deleteButton            = document.createElement("button");
                        deleteButton.type           = "button";
                        deleteButton.className      = "btn btn-danger btn-sm delete-btn";
                        deleteButton.style.position = "absolute";
                        deleteButton.style.top      = "0";
                        deleteButton.style.right    = "0";
                        deleteButton.textContent    = "削除";
                        fileInput.parentNode.appendChild(deleteButton);
                    }
                }


                // responseが複数ファイルに対応している場合
                for (var i = 0; i < response.content_names.length; i++) {
                    // 各ファイルの情報を取得
                    var content_name = response.content_names[i];
                    var content_url = response.content_urls[i];

                    if (addFlg == true) {
                        // 新しいファイル入力欄を追加
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
                    } else {
                        // fileNameとfilePathを設定
                        if (i === 0) {
                            fileName.val(content_name);
                            filePath.val(content_url);
                        } else {
                            // 新しいファイル入力欄を追加
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
                    }
                }

                if ($(".file-input-add").length === 0) {
                    $(".fileInputs").append(`
                    <div class="file-input-add">
                        <label class="inputFile" style="float: right;">
                            <label for="fileUpload" class="custom-upload" style="background-color: #eee; padding: 10px 20px; border-radius: 5px; cursor: pointer; display: inline-block;">追　加</label>
                            <input type="file" id="fileUpload" name="file[]" accept=".pdf" multiple="multiple" style="display: none">
                        </label>
                    </div>
                    `);
                }
                addFlg = false;
            })


            .fail(function (jqXHR, textStatus, errorThrown) {
                labelForm.parent().find(".text-danger").remove();
                jqXHR.responseJSON.errorMessages?.forEach((errorMessage) => {
                    labelForm.parent().append(`
                    <div class="text-danger">${errorMessage}</div>
                `);
                });
                if (errorThrown) {
                    labelForm.parent().append(`
                    <div class="text-danger">アップロードできませんでした</div>
                `);
                }
                fileName.val("");
                filePath.val("");
            });


    // 単一ファイルの場合
    } else {
        let formData = new FormData();
        formData.append("file0", fileList[0]);

        var labelForm = $(this).parent();
        var progress = labelForm.parent().find(".progress");
        var progressBar = progress.children(".progress-bar");

        progressBar.hide();
        progressBar.css("width", 0 + "%");
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
                XHR = $.ajaxSettings.xhr();
                if (XHR.upload) {
                    XHR.upload.addEventListener(
                        "progress",
                        function (e) {
                            var progVal =
                                parseInt((e.loaded / e.total) * 10000) / 100;
                            progressBar.show();
                            progressBar.css("width", progVal + "%");
                            console.log(progVal);

                            if (progVal == 100) {
                                // アップロードが完了したら、サーバー側で保存処理が始まる
                                setTimeout(() => {
                                    progress.hide();
                                }, 1000);
                            }
                        },
                        false
                    );
                }
                return XHR;
            },
        })
            .done(function (response) {
                labelForm.parent().find(".text-danger").remove();


                // 追加ボタンがある場合の処理
                if (addFlg == true) {
                    var fileInputAdd = document.querySelector(".file-input-add");
                    if (fileInputAdd) {
                        fileInputAdd.remove();
                    }


                // 追加ボタンがない場合の処理
                } else {
                    // multiple属性を削除処理
                    var fileInputs = document.getElementsByClassName("fileInputs")[0];
                    var fileInput  = fileInputs.querySelector('input[type="file"]');
                    fileInput.removeAttribute("multiple");

                    // nameを変更
                    fileInput.name = "file";

                    // 削除ボタンの存在チェック
                    if (!fileInputs.querySelector('.delete-btn')) {
                        // 削除ボタンを追加
                        var deleteButton            = document.createElement("button");
                        deleteButton.type           = "button";
                        deleteButton.className      = "btn btn-danger btn-sm delete-btn";
                        deleteButton.style.position = "absolute";
                        deleteButton.style.top      = "0";
                        deleteButton.style.right    = "0";
                        deleteButton.textContent    = "削除";
                        fileInput.parentNode.appendChild(deleteButton);
                    }
                }


                // responseが複数ファイルに対応している場合
                for (var i = 0; i < response.content_names.length; i++) {
                    // 各ファイルの情報を取得
                    var content_name = response.content_names[i];
                    var content_url = response.content_urls[i];

                    if (addFlg == true) {
                        // 新しいファイル入力欄を追加
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
                    } else {
                        // fileNameとfilePathを設定
                        if (i === 0) {
                            fileName.val(content_name);
                            filePath.val(content_url);
                        }
                    }
                }

                if ($(".file-input-add").length === 0) {
                    $(".fileInputs").append(`
                        <div class="file-input-add">
                            <label class="inputFile" style="float: right;">
                                <label for="fileUpload" class="custom-upload" style="background-color: #eee; padding: 10px 20px; border-radius: 5px; cursor: pointer; display: inline-block;">追　加</label>
                                <input type="file" id="fileUpload" name="file[]" accept=".pdf" multiple="multiple" style="display: none">
                            </label>
                        </div>
                        `);
                }
                addFlg = false;
            })


            .fail(function (jqXHR, textStatus, errorThrown) {
                labelForm.parent().find(".text-danger").remove();
                jqXHR.responseJSON.errorMessages?.forEach((errorMessage) => {
                    labelForm.parent().append(`
                        <div class="text-danger">${errorMessage}</div>
                    `);
                });
                if (errorThrown) {
                    labelForm.parent().append(`
                        <div class="text-danger">アップロードできませんでした</div>
                    `);
                }
                fileName.val("");
                filePath.val("");
            });
    }
});


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
});
