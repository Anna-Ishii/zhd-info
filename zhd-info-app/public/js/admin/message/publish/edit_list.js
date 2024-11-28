$(document).ready(function() {
    // 組織ID
    const org1Id = $('input[name="organization1_id"]').val();
    // 業連ファイルを管理
    const fileDataByMessageId = {};
    // 店舗選択された値を管理
    const selectedValuesByMessageId = {};

    // No
    let messageNumber = 0;
    // カテゴリー
    let categoryList = [];
    // 対象者
    let targetRollList = [];
    // 業態
    let brandList = [];
    // 全店舗一覧
    let allShopList = [];
    // 組織一覧
    let organizationList = [];
    // 店舗データ取得完了フラグ
    let shopDataFetched = false;


    // 業連ファイル編集モーダル
    function initializeFileModal(messageId, message, messageContents, mode) {
        // モーダルが既に存在するか確認し、存在しない場合は生成
        if (!document.getElementById(`editTitleFileModal-${messageId}`)) {
            let fileInputsHtml = '';

            if (mode === 'new'){
                fileInputsHtml = `
                    <div class="file-input-container">
                        <div class="row">
                            <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="" required>
                            <label class="col-sm-2 control-label">業連<span class="text-danger required">*</span></label>
                            <div class="col-sm-8">
                                <label class="inputFile form-control">
                                    <span class="fileName" style="text-align: center;">
                                        ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能
                                    </span>
                                    <input type="file" name="file[]" accept=".pdf" multiple="multiple" data-cache="active">
                                    <input type="hidden" name="file_name[]" value="">
                                    <input type="hidden" name="file_path[]" value="">
                                    <input type="hidden" name="join_flg[]" value="">
                                    <button type="button" class="btn btn-sm delete-btn" style="background-color: #eee; color: #000; position: absolute; top: 0; right: 0;">削除</button>
                                </label>
                                <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: 0%"></div>
                                </div>
                            </div>
                            <label class="col-sm-2" style="padding-top: 10px; display: none;">結合</label>
                        </div>
                    </div>
                `;
            } else {
                if (messageContents && messageContents.length > 0) {
                    messageContents.forEach((messageContent, index) => {
                        fileInputsHtml += `
                            <div class="file-input-container">
                                <div class="row">
                                    <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="${messageContent.id}" required>
                                    <label class="col-sm-2 control-label">${index === 0 ? '業連' : '添付' + index}<span class="text-danger required">*</span></label>
                                    <div class="col-sm-8">
                                        <label class="inputFile form-control">
                                            <span class="fileName" style="text-align: center;">
                                                ${messageContent.content_name || 'ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能'}
                                            </span>
                                            <input type="file" name="file" accept=".pdf" data-cache="active">
                                            <input type="hidden" name="file_name[]" value="${messageContent.content_name}">
                                            <input type="hidden" name="file_path[]" value="${messageContent.content_url}">
                                            <input type="hidden" name="join_flg[]" value="${messageContent.join_flg}">
                                            <button type="button" class="btn btn-sm delete-btn" style="background-color: #eee; color: #000; position: absolute; top: 0; right: 0;">削除</button>
                                        </label>
                                        <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <label class="col-sm-2" style="padding-top: 10px; ${messageContent.join_flg === 'join' ? '' : 'display: none;'}">結合</label>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    if (message) {
                        fileInputsHtml = `
                            <div class="file-input-container">
                                <div class="row">
                                    <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="${message.id}" required>
                                    <label class="col-sm-2 control-label">業連<span class="text-danger required">*</span></label>
                                    <div class="col-sm-8">
                                        <label class="inputFile form-control">
                                            <span class="fileName" style="text-align: center;">
                                                ${message.content_name || 'ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能'}
                                            </span>
                                            <input type="file" name="file" accept=".pdf" data-cache="active">
                                            <input type="hidden" name="file_name[]" value="${message.content_name}">
                                            <input type="hidden" name="file_path[]" value="${message.content_url}">
                                            <input type="hidden" name="join_flg[]" value="">
                                            <button type="button" class="btn btn-sm delete-btn" style="background-color: #eee; color: #000; position: absolute; top: 0; right: 0;">削除</button>
                                        </label>
                                        <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <label class="col-sm-2" style="padding-top: 10px; display: none;">結合</label>
                                </div>
                            </div>
                        `;
                    }
                }
            }

            const modalHtml = `
                <div id="editTitleFileModal-${messageId}" class="modal fade editTitleFileModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                            </div>
                            <div class="modal-body">
                                <form id="editForm-${messageId}" class="form-horizontal">
                                    <input type="hidden" name="id" value="${messageId}">
                                    <div class="form-group" style="max-height: 400px; overflow-y: auto; overflow-x: hidden; margin-left: 0; margin-right: 0;">
                                        <div class="fileInputs" data-message-id="${messageId}">
                                            ${fileInputsHtml}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-3 control-label">
                                            <span class="text-danger required">*</span>：必須項目
                                        </div>
                                        <div class="col-sm-2 col-sm-offset-6 control-label">
                                            <input type="button" id="fileImportBtn-${messageId}" class="btn btn-admin" value="設定">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        // 結合PDFファイルモーダル
        if (!document.getElementById(`editJoinFileModal-${messageId}`)) {
            const modalHtml = `
                <div class="modal fade" id="editJoinFileModal-${messageId}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                <h4 class="modal-title">結合するファイルを選択してください</h4>
                            </div>
                            <div class="modal-body modal-body-scrollable" id="fileCheckboxes-${messageId}" style="max-height: 300px; overflow-y: auto;">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-admin" id="joinFileBtn-${messageId}">結合</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }


        // 業連ファイル編集処理
        const editTitleFileInputsSelector = `.editTitleFileModal .fileInputs[data-message-id="${messageId}"]`;

        // 編集モードの場合はボタンの有効/無効を設定し、メッセージを表示
        if(mode === 'edit'){
            // 初期状態でボタンの有効/無効を設定し、メッセージを表示
            addFileInputAdd(messageId);
            addJoinFileBtn(messageId);
            // 「結合中」メッセージを更新する関数の呼び出し
            updateModalFooterMessage(messageId);
            // 初期状態でメッセージを表示
            updateJoinFileCount(messageId);
            // "join" フラグがあるか
            updateJoinFileLabel(messageId);
            // 業連ファイルを保存
            saveFileData(messageId);
        }

        $(document).on("change", `${editTitleFileInputsSelector} input[type="file"]`, function () {
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
            let filesCount = $(`${editTitleFileInputsSelector} .file-input-container`).length - 1;
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
                handleResponse(response, fileName, filePath, joinFile, dataCache, messageId);
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


        // 削除ボタンのクリックイベント
        $(document).on("click", `${editTitleFileInputsSelector} .delete-btn`, function () {
            let joinFileBtnAdd = $(`${editTitleFileInputsSelector} .join-file-btn`);
            let dataCacheCount = $(`${editTitleFileInputsSelector} [data-cache]`).length;
            let maxFiles = 20; // 上限数を設定（20）

            // 上限を超えていない場合、かつファイル数が上限に達していない場合のみファイル入力欄を追加
            if (dataCacheCount < maxFiles) {
                $(this).closest('.file-input-container').remove();

                // 添付ラベルの番号を振り直す
                renumberSendLabels(messageId);

            } else {
                if (dataCacheCount === maxFiles) {
                    $(this).closest('.file-input-container').remove();
                    // 添付ラベルの番号を振り直す
                    renumberSendLabels(messageId);

                    if (joinFileBtnAdd.length) {
                        joinFileBtnAdd.remove();
                    }
                    addFileInputAdd(messageId);
                    addJoinFileBtn(messageId);
                }
            }
            if (dataCacheCount === 0) {
                if (joinFileBtnAdd.length) {
                    joinFileBtnAdd.remove();
                }
                addFileInputAdd(messageId);
                addJoinFileBtn(messageId);
            }

            // 「結合中」メッセージを更新する関数の呼び出し
            updateModalFooterMessage(messageId);

            // "join" フラグがあるか
            updateJoinFileLabel(messageId);
        });

        // ファイルの結合ボタン処理
        $(document).on("click", `${editTitleFileInputsSelector} #joinFileId-${messageId}`, function () {
            var selectedFiles = [];
            var selectedFilePaths = [];
            var selectedJoinFiles = [];

            // ファイル名とファイルパスをそれぞれの配列に追加
            $(`${editTitleFileInputsSelector} [name='file_name[]']`).each(function(){
                var value = $(this).val();
                if (value) {
                    selectedFiles.push(value);
                }
            });
            $(`${editTitleFileInputsSelector} [name='file_path[]']`).each(function(){
                var value = $(this).val();
                if (value) {
                    selectedFilePaths.push(value);
                }
            });
            $(`${editTitleFileInputsSelector} [name='join_flg[]']`).each(function(){
                var value = $(this).val();
                selectedJoinFiles.push(value);
            });

            var $modalBody = $(`#editJoinFileModal-${messageId} #fileCheckboxes-${messageId}`);
            var $modalFooter = $(`#editJoinFileModal-${messageId} .modal-footer`);
            $modalBody.empty();
            $modalFooter.find('p').remove();

            if (selectedFiles.length > 0) {
                selectedFiles.forEach(function(file, index) {
                    var filePath = selectedFilePaths[index] || 'パスがありません';
                    var isChecked = selectedJoinFiles[index] === "join" ? "checked" : "";
                    var labelText = (index === 0) ? '業連' : `添付${index}`;
                    var checkbox =
                        `<div class="checkbox">
                            <label>
                                <input type="checkbox" value="${filePath}" ${isChecked}>${labelText} ${file}
                            </label>
                        </div>`;
                    $modalBody.append(checkbox);
                });

                // 選択されたファイルのカウントを更新する関数
                updateJoinFileCount(messageId);
            } else {
                $modalFooter.append(`<p style="float: left;">結合するファイルが選択されていません。</p>`);
            }
        });


        // 結合ボタン処理
        $(document).on('click', `#editJoinFileModal-${messageId} #joinFileBtn-${messageId}`, function() {
            // 結合モーダルのチェックされたファイルパスを取得
            var checkedFileValues = [];
            $(`#editJoinFileModal-${messageId} #fileCheckboxes-${messageId} input[type="checkbox"]:checked`).each(function() {
                checkedFileValues.push($(this).val());
            });

            // 選択されたファイルパスを取得
            var selectedFilePaths = [];
            $(`${editTitleFileInputsSelector} [name='file_path[]']`).each(function() {
                var value = $(this).val();
                if (value) {
                    selectedFilePaths.push(value);
                }
            });

            // チェックされたファイルパスと一致するファイルパスのjoin_flg[]の値を"join"に設定し、ラベルを表示
            // チェックが外された場合は"single"に設定し、ラベルを非表示
            $(`${editTitleFileInputsSelector} [name='file_path[]']`).each(function(index) {
                var value = $(this).val();
                if (checkedFileValues.includes(value)) {
                    $(`${editTitleFileInputsSelector} [name='join_flg[]']`).eq(index).val("join");
                    // 結合ラベルを表示
                    $(this).closest('.row').find("label[style*='padding-top: 10px']").show();
                } else {
                    $(`${editTitleFileInputsSelector} [name='join_flg[]']`).eq(index).val("single");
                    // 結合ラベルを非表示
                    $(this).closest('.row').find("label[style*='padding-top: 10px']").hide();
                }
            });

            var modalFooterMessage = $(`${editTitleFileInputsSelector} .join-file-btn .inputFile p`);
            if (modalFooterMessage.length) {
                var checkedCount = checkedFileValues.length;
                if (checkedCount >= 2) {
                    modalFooterMessage.text(`${checkedCount}ファイルを結合します。`).show();
                } else {
                    modalFooterMessage.text("").hide();
                }
            }

            // "join" フラグがあるか
            updateJoinFileLabel(messageId);

            $(`#editJoinFileModal-${messageId}`).modal("hide");
        });

        // 結合モーダルのチェックボックス変更イベント処理
        $(document).on('change', `#editJoinFileModal-${messageId} #fileCheckboxes-${messageId} input[type="checkbox"]`, function() {
            // 選択されたファイルのカウントを更新する関数
            updateJoinFileCount(messageId);
        });


        // 業連ファイル設定ボタンのクリックイベント
        $(document).on('click', `#fileImportBtn-${messageId}`, function() {
            saveFileData(messageId);

            // モーダルを閉じる
            $(`#editTitleFileModal-${messageId}`).modal("hide");
        });


        // アップロード完了後の処理
        function handleResponse(response, fileName, filePath, joinFile, dataCache, messageId) {
            // responseが複数ファイルに対応している場合
            response.content_names.forEach((content_name, i) => {
                let content_url = response.content_urls[i];
                if (i === 0) {
                    fileName.val(content_name);
                    filePath.val(content_url);
                    joinFile.val("single");
                } else {
                    addNewFileInput(content_name, content_url, join_flg = "single", messageId);
                }
            });

            if (!dataCache) {
                let fileInputs = document.querySelector(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"]`);
                let fileInput = fileInputs.querySelector('input[name="file[]"]');

                // 単一ファイル欄に加工
                if (fileInput) {
                    fileInput.removeAttribute("multiple");
                    fileInput.name = "file";
                    // 削除ボタン追加
                    addDeleteButton(fileInput);
                }

                // 上限を超えていない場合、かつファイル数が上限に達していない場合のみファイル入力欄を追加
                let existingFilesCount = $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] .file-input-container`).length;
                let joinFileBtnAdd = $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] .join-file-btn`);

                let maxFiles = 20; // 上限数を設定（20）
                if (existingFilesCount < maxFiles) {
                    if (joinFileBtnAdd) {
                        joinFileBtnAdd.remove();
                    }
                    addFileInputAdd(messageId);
                    addJoinFileBtn(messageId);

                } else {
                    if (joinFileBtnAdd) {
                        joinFileBtnAdd.remove();
                    }
                    addJoinFileBtn(messageId);
                }

            // PDFファイルの上書き
            } else {
                $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] [name='join_flg[]']`).each(function() {
                    if ($(this).val() === "single") {
                        // 結合ラベルを非表示
                        $(this).closest('.row').find("label[style*='padding-top: 10px']").hide();
                    }
                });

                // "join" フラグがあるか
                updateJoinFileLabel(messageId);
            }

            // 「結合中」メッセージを更新する関数の呼び出し
            updateModalFooterMessage(messageId);
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
        function addNewFileInput(content_name, content_url, join_flg, messageId) {
            // 既存の添付ラベルの数を取得
            let currentLabelCount = $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] .file-input-container .control-label:contains('添付')`).length + 1;

            $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"]`).append(`
                <div class="file-input-container">
                    <div class="row">
                        <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="" required>
                        <label class="col-sm-2 control-label">添付${currentLabelCount}</label>
                        <div class="col-sm-8">
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
                        <label class="col-sm-2" style="padding-top: 10px; display: none;">結合</label>
                    </div>
                </div>
            `);
        }

        // 結合ボタンを追加
        function addJoinFileBtn(messageId) {
            $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"]`).append(`
                <div class="col-sm-11 join-file-btn">
                    <label class="inputFile" style="float: right; display: flex; align-items: center; justify-content: space-between;">
                        <p style="margin: 0; padding-right: 10px; display: none;">0ファイルを結合中です。</p>
                        <input type="button" class="btn btn-admin joinFile" id="joinFileId-${messageId}" data-toggle="modal" data-target="#editJoinFileModal-${messageId}" value="ファイルの結合">
                    </label>
                </div>
            `);
        }

        // 追加ファイル欄の追加
        function addFileInputAdd(messageId) {
            // 変数を初期化
            let file_name = "";
            let file_path = "";
            let join_flg = "";

            // 既存の添付ラベルの数を取得
            let currentLabelCount = $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] .file-input-container .control-label:contains('添付')`).length + 1;

            $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"]`).append(`
                <div class="file-input-container">
                    <div class="row">
                        <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="" required>
                        <label class="col-sm-2 control-label">添付${currentLabelCount}</label>
                        <div class="col-sm-8">
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
                        <label class="col-sm-2" style="padding-top: 10px; display: none;">結合</label>
                    </div>
                </div>
            `);
        }

        // 添付ラベルの番号を振り直す処理
        function renumberSendLabels(messageId) {
            $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] .file-input-container .control-label:contains('添付'), .editTitleFileModal .fileInputs[data-message-id="${messageId}"] .file-input-container .control-label:contains('業連')`).each(function(index) {
                if (index === 0) {
                    $(this).html('業連<span class="text-danger required">*</span>');
                } else {
                    $(this).text(`添付${index}`);
                }
            });
        }


        // 選択されたファイルのカウントを更新する関数
        function updateJoinFileCount(messageId) {
            var checkedCount = $(`#editJoinFileModal-${messageId} #fileCheckboxes-${messageId} input[type="checkbox"]:checked`).length;

            // 既存のメッセージを削除
            $(`#editJoinFileModal-${messageId} .modal-footer p`).remove();

            // メッセージを追加
            if (checkedCount >= 2) {
                $(`#editJoinFileModal-${messageId} .modal-footer`).append(`<p style="float: left;">${checkedCount}ファイルを結合します。よろしいでしょうか？</p>`);
            } else if (checkedCount == 0) {
                $(`#editJoinFileModal-${messageId} .modal-footer`).append(`<p style="float: left;">結合するファイルが選択されていません。</p>`);
            }

            // ボタンの有効/無効を設定
            var modalFooterJoinFileBtn = $(`#editJoinFileModal-${messageId} .modal-footer #joinFileBtn-${messageId}`);
            if (modalFooterJoinFileBtn.length) {
                if (checkedCount === 1) {
                    modalFooterJoinFileBtn.prop('disabled', true);
                } else {
                    modalFooterJoinFileBtn.prop('disabled', false);
                }
            }
        }

        // 「結合中」メッセージを更新する関数の呼び出し
        function updateModalFooterMessage(messageId) {
            var selectedJoinFiles = [];

            $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] [name='join_flg[]']`).each(function() {
                var value = $(this).val();
                selectedJoinFiles.push(value);
            });

            var checkedCount = selectedJoinFiles.filter(value => value === "join").length;

            var modalFooterMessage = $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] .join-file-btn .inputFile p`);
            if (modalFooterMessage.length) {
                if (checkedCount >= 2) {
                    modalFooterMessage.text(`${checkedCount}ファイルを結合します。`).show();
                } else {
                    modalFooterMessage.text("").hide();
                }
            }
        }

        // "join" フラグがあるか
        function updateJoinFileLabel(messageId) {
            // "join" フラグが1つ以下の場合に文言を変更
            var joinFlagCount = $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] [name='join_flg[]']`).filter(function() {
                return $(this).val() === "join";
            }).length;

            if (joinFlagCount <= 1) {
                // "join" フラグが1つの場合に他の "join_flg" を "single" に変更
                if (joinFlagCount === 1) {
                    $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] [name='join_flg[]']`).each(function() {
                        if ($(this).val() === "join") {
                            $(this).val("single");
                            // 結合ラベルを非表示
                            $(this).closest('.row').find("label[style*='padding-top: 10px']").hide();
                        }
                    });
                }

                $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] .inputFile #joinFileId-${messageId}`).val("ファイルの結合");
            }

            // "join" フラグが一つでもあるかチェックして文言を変更
            var hasJoinFlag = joinFlagCount > 1;

            if (hasJoinFlag) {
                $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] .inputFile #joinFileId-${messageId}`).val("結合の修正");
            }
        }

        // 業連ファイルを保存
        function saveFileData(messageId) {
            const contentIds = [];
            const fileNames = [];
            const filePaths = [];
            const joinFlags = [];

            // 各ファイルの情報を取得して配列に保存
            $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] [name='content_id[]']`).each(function() {
                contentIds.push($(this).val());
            });

            $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] [name='file_name[]']`).each(function() {
                fileNames.push($(this).val());
            });

            $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] [name='file_path[]']`).each(function() {
                filePaths.push($(this).val());
            });

            $(`.editTitleFileModal .fileInputs[data-message-id="${messageId}"] [name='join_flg[]']`).each(function() {
                joinFlags.push($(this).val());
            });

            // message_idをキーとしてファイル情報を保存
            fileDataByMessageId[messageId] = {
                contentIds: contentIds,
                fileNames: fileNames,
                filePaths: filePaths,
                joinFlags: joinFlags
            };
        }
    }


    // 店舗編集モーダル
    function initializeShopModal(messageId, org1Id, organizationList, allShopList, targetOrg, mode) {
        if (!document.getElementById(`editShopModal-${messageId}`)) {
            let checkSelectedClass = '';
            let selectStoreValue = '';

            // 編集モードの場合はボタンの有効/無効を設定し、メッセージを表示
            if (mode === 'edit') {
                const isSelected = targetOrg.select === 'store' || targetOrg.select === 'oldStore';
                checkSelectedClass = isSelected ? 'check-selected' : '';
                selectStoreValue = isSelected ? 'selected' : '';
            }

            const modalHtml = `
                <div id="editShopModal-${messageId}" class="modal fade editShopModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                            </div>
                            <div class="modal-body">
                                <form id="editForm-${messageId}" class="form-horizontal">
                                    <input type="hidden" name="id" id="messageId-${messageId}">

                                    <div class="form-group">
                                        <div class="editShopInputs" data-message-id="${messageId}">
                                            <label class="col-sm-2 control-label">対象店舗<span class="text-danger required">*</span></label>
                                            <div class="col-sm-10 checkArea">
                                                <div class="check-store-list mb8 text-left">
                                                    <label class="mr16">
                                                        <input type="button" class="btn btn-admin ${checkSelectedClass}" id="checkStore-${messageId}" data-toggle="modal"
                                                            data-target="#editShopSelectModal-${messageId}" value="店舗選択">
                                                        <input type="hidden" id="selectStore-${messageId}" name="select_organization[store]" value="${selectStoreValue}">
                                                    </label>

                                                    <label class="mr16">
                                                        <input type="button" class="btn btn-admin" id="importCsv-${messageId}" data-toggle="modal"
                                                            data-target="#editShopImportModal-${messageId}" value="インポート">
                                                        <input type="hidden" id="selectCsv-${messageId}" name="select_organization[csv]" value="">
                                                    </label>

                                                    <label class="mr16">
                                                        <input type="button" class="btn btn-admin" id="exportCsv-${messageId}" value="エクスポート">
                                                        <input type="hidden" name="message_id" value="${messageId}">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-3 control-label">
                                            <span class="text-danger required">*</span>：必須項目
                                        </div>
                                        <div class="col-sm-2 col-sm-offset-6 control-label">
                                            <input type="button" id="shopImportBtn-${messageId}" class="btn btn-admin" data-toggle="modal"
                                                data-target="#messageStoreModal" value="設定">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        // 店舗選択モーダル
        if (!document.getElementById(`editShopSelectModal-${messageId}`)) {
            let organizationItems = '';

            organizationList.forEach((organization, index) => {
                let orgId, orgName, shopList;

                if (organization['organization5_name']) {
                    org = 'org5';
                    orgId = organization['organization5_id'];
                    orgName = organization['organization5_name'];
                    shopList = organization['organization5_shop_list'] || {};
                } else if (organization['organization4_name']) {
                    org = 'org4';
                    orgId = organization['organization4_id'];
                    orgName = organization['organization4_name'];
                    shopList = organization['organization4_shop_list'] || {};
                } else if (organization['organization3_name']) {
                    org = 'org3';
                    orgId = organization['organization3_id'];
                    orgName = organization['organization3_name'];
                    shopList = organization['organization3_shop_list'] || {};
                } else if (organization['organization2_name']) {
                    org = 'org2';
                    orgId = organization['organization2_id'];
                    orgName = organization['organization2_name'];
                    shopList = organization['organization2_shop_list'] || {};
                }

                if (orgId && orgName) {
                    let shopsHtml = Object.values(shopList).map(shop => `
                        <li class="list-group-item">
                            <div>
                                <label style="font-weight: 500 !important; cursor: pointer;">
                                    <input type="checkbox" name="organization_shops[]"
                                        data-organization-id="${orgId}"
                                        data-store-id="${shop.id}"
                                        value="${shop.id}"
                                        class="checkCommon mr8 shop-checkbox"
                                        ${Array.isArray(targetOrg.shops) && targetOrg.shops.includes(shop.id) ? 'checked' : ''}
                                        >
                                        ${shop.shop_code} ${shop.display_name}
                                </label>
                            </div>
                        </li>
                    `).join('');

                    organizationItems += `
                        <li class="list-group-item">
                            <div>
                                <div>
                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                        <input type="checkbox" name="organization[${org}][]"
                                            data-organization-id="${orgId}"
                                            value="${orgId}"
                                            class="checkCommon mr8 org-checkbox"
                                            ${Array.isArray(targetOrg[orgId]) && targetOrg[orgId].includes(orgId) ? 'checked' : ''}
                                            >
                                        ${orgName}
                                    </label>
                                    <div id="id-collapse" data-toggle="collapse" aria-expanded="false"
                                        data-target="#storeCollapse${index}-${messageId}"
                                        style="float: right; cursor: pointer;"></div>
                                </div>
                                <ul id="storeCollapse${index}-${messageId}" class="list-group mt-2 collapse">
                                    ${shopsHtml}
                                </ul>
                            </div>
                        </li>
                    `;
                }
            });

            let shopItems = Array.isArray(allShopList) ? allShopList.map(shop => `
                <li class="list-group-item">
                    <div>
                        <label style="font-weight: 500 !important; cursor: pointer;">
                            <input type="checkbox" name="shops_code[]"
                                data-store-id="${shop.shop_id}"
                                value="${shop.shop_id}"
                                class="checkCommon mr8 shop-checkbox"
                                ${Array.isArray(targetOrg.shops) && targetOrg.shops.includes(shop.shop_id) ? 'checked' : ''}
                                >
                                ${shop.shop_code} ${shop.display_name}
                        </label>
                    </div>
                </li>
            `).join('') : '';

            const modalHtml = `
                <!-- モーダル：店舗選択 -->
                <div id="editShopSelectModal-${messageId}" class="modal fade editShopSelectModal" tabindex="-1" style="top: -20%;">
                    <div class="modal-dialog" style="max-width: 450px;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">店舗を選択してください。</h4>
                            </div>
                            <div class="modal-body shopSelectInputs" data-message-id="${messageId}">
                                <div class="storeSelected mb-1">0店舗選択中</div>
                                <ul class="nav nav-tabs" id="myTab-${messageId}" role="tablist" style="margin-left: 30px; margin-right: 30px;">
                                    <li class="nav-item active" role="presentation">
                                        <a class="nav-link" id="byOrganization-tab-${messageId}" data-toggle="tab" href="#byOrganization-${messageId}"
                                            role="tab" aria-controls="byOrganization-${messageId}" aria-selected="true">組織単位</a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="byStoreCode-tab-${messageId}" data-toggle="tab" href="#byStoreCode-${messageId}" role="tab"
                                            aria-controls="byStoreCode-${messageId}" aria-selected="false">店舗コード順</a>
                                    </li>
                                </ul>
                                <div class="tab-content modal-body-scroll"
                                    style="max-height: 400px; overflow-y: auto;">
                                    <div class="tab-pane fade in active" id="byOrganization-${messageId}" role="tabpanel"
                                        aria-labelledby="byOrganization-tab-${messageId}">
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <div>
                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                        <input type="checkbox" id="selectOrganization-${messageId}"> 選択中のみ表示
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div>
                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                        <input type="checkbox" id="selectAllOrganization-${messageId}"> 全て選択/選択解除
                                                    </label>
                                                </div>
                                            </li>
                                            ${organizationItems}
                                        </ul>
                                    </div>
                                    <div class="tab-pane fade" id="byStoreCode-${messageId}" role="tabpanel" aria-labelledby="byStoreCode-tab-${messageId}">
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <div>
                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                        <input type="checkbox" id="selectStoreCode-${messageId}"> 選択中のみ表示
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div>
                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                        <input type="checkbox" id="selectAllStoreCode-${messageId}"> 全て選択/選択解除
                                                    </label>
                                                </div>
                                            </li>
                                            ${shopItems}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-admin pull-left" id="editShopCancelBtn-${messageId}" data-dismiss="modal">キャンセル</button>
                                <button type="button" class="btn btn-admin pull-right" id="editShopSelectBtn-${messageId}">選択</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        // 業務連絡csvインポート
        if (!document.getElementById(`editShopImportModal-${messageId}`)) {
            const modalHtml = `
                <div id="editShopImportModal-${messageId}" class="modal fade editShopImportModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                <h4 class="modal-title">店舗選択csvインポート</h4>
                            </div>
                            <div class="modal-body editShopImport" data-message-id="${messageId}">
                                <div>
                                    csvデータを店舗選択モーダルに表示します
                                </div>
                                <form class="form-horizontal">
                                    <input type="hidden" name="organization1" value="${org1Id}">
                                    <input type="hidden" name="message_id" value="${messageId}">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">csv添付<span class="text-danger required">*<span></label>
                                        <div class="col-sm-9">
                                            <label class="inputFile form-control">
                                                <span class="fileName">ファイルを選択またはドロップ</span>
                                                <input type="file" name="csv" accept=".csv">
                                            </label>
                                            <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-3 control-label">
                                            <span class="text-danger required">*</span>：必須項目
                                        </div>
                                        <div class="col-sm-2 col-sm-offset-6 control-label">
                                            <input type="button" id="importButton-${messageId}" class="btn btn-admin" data-toggle="modal"
                                                data-target="#editShopImportSelector-${messageId}" value="インポート" disabled>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }


        //店舗選択処理
        selectedValuesByMessageId[messageId] = {
            org5: [],
            org4: [],
            org3: [],
            org2: [],
            shops: []
        };

        const editShopInputsSelector = `.editShopModal .editShopInputs[data-message-id="${messageId}"]`;
        const shopSelectInputsSelector = `.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"]`;
        const editShopImportSelector = `.editShopImportModal .editShopImport[data-message-id="${messageId}"]`;

        // 初期表示の更新
        updateSelectedStores(messageId);
        updateAllParentCheckboxes(messageId);
        updateSelectAllCheckboxes(messageId);

        // 編集モードの場合は値を更新
        if(mode === 'edit'){
            changeValues(messageId);
        }

        if ($(`${editShopInputsSelector} #selectStore-${messageId}`).val() === "selected") {
            // 店舗選択中の処理
            const selectedCountStore = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).length;
            $(`${editShopInputsSelector} #checkStore-${messageId}`).val(`店舗選択(${selectedCountStore}店舗)`);
        }
        if ($(`${editShopInputsSelector} #selectCsv-${messageId}`).val() === "selected") {
            // インポート選択中の処理
            const selectedCountStore = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).length;
            $(`${editShopInputsSelector} #importCsv-${messageId}`).val(`インポート(${selectedCountStore}店舗)`);
        }

        // チェックボックスの変更イベントリスナーを追加
        $(document).on('change', `${shopSelectInputsSelector} input[name="organization_shops[]"], ${shopSelectInputsSelector} input[name="shops_code[]"]`, function() {
            syncCheckboxes($(this).attr('data-store-id'), this.checked, messageId);
            updateSelectedStores(messageId);
            if ($(this).hasClass('shop-checkbox')) {
                updateParentCheckbox($(this).attr('data-organization-id'));
            }
            updateSelectAllCheckboxes(messageId);
        });

        // 親チェックボックスの変更イベントリスナーを追加
        $(document).on('change', `${shopSelectInputsSelector} input.org-checkbox`, function() {
            const organizationId = $(this).attr('data-organization-id');
            const checked = this.checked;
            $(`${shopSelectInputsSelector} input[data-organization-id="${organizationId}"].shop-checkbox`).each(function() {
                this.checked = checked;
                syncCheckboxes($(this).attr('data-store-id'), checked, messageId);
            });

            // "選択中のみ表示"がチェックされている場合、すべての項目を表示し、チェックを外す
            if ($(`${shopSelectInputsSelector} #selectOrganization-${messageId}`).is(':checked')) {
                $(`${shopSelectInputsSelector} #byOrganization-${messageId} li`).show();
                $(`${shopSelectInputsSelector} #selectOrganization-${messageId}`).prop('checked', false);
            }
            if ($(`${shopSelectInputsSelector} #selectStoreCode-${messageId}`).is(':checked')) {
                $(`${shopSelectInputsSelector} #byStoreCode-${messageId} li`).show();
                $(`${shopSelectInputsSelector} #selectStoreCode-${messageId}`).prop('checked', false);
            }

            updateSelectedStores(messageId);
            updateSelectAllCheckboxes(messageId);
        });

        // 組織単位タブの選択中のみ表示
        $(document).on("change", `${shopSelectInputsSelector} #selectOrganization-${messageId}`, function () {
            if (this.checked) {
                // 子要素（店舗）の表示/非表示
                $(`${shopSelectInputsSelector} input[name="organization_shops[]"]`).each(function () {
                    const listItem = $(this).closest("li");
                    if (this.checked) {
                        listItem.show();
                    } else {
                        listItem.hide();
                    }
                });

                // 親要素（org5, org4, org3, org2）の表示/非表示とプルダウンの開閉
                $(`${shopSelectInputsSelector} input[name="organization[${org}][]"]`).each(function () {
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
                $(`${shopSelectInputsSelector} input[name="organization_shops[]"]`).each(function () {
                    $(this).closest("li").show();
                });

                // すべての親要素を表示し、プルダウンを閉じる
                $(`${shopSelectInputsSelector} input[name="organization[${org}][]"]`).each(function () {
                    const parentListItem = $(this).closest("li");
                    parentListItem.show();
                    const collapseElement = parentListItem.find('.collapse');
                    collapseElement.collapse('hide');
                });
            }
        });

        // 店舗コード順タブの選択中のみ表示
        $(document).on("change", `${shopSelectInputsSelector} #selectStoreCode-${messageId}`, function () {
            if (this.checked) {
                // チェックされている項目のみ表示
                $(`${shopSelectInputsSelector} input[name="shops_code[]"]`).each(function () {
                    const listItem = $(this).closest("li");
                    if (this.checked) {
                        listItem.show();
                    } else {
                        listItem.hide();
                    }
                });
            } else {
                // すべての項目を表示
                $(`${shopSelectInputsSelector} input[name="shops_code[]"]`).each(function () {
                    $(this).closest("li").show();
                });
            }
        });

        // 組織単位タブの全選択/選択解除
        $(document).on("change", `${shopSelectInputsSelector} #selectAllOrganization-${messageId}`, function () {
            var overlay = document.getElementById('overlay');
            overlay.style.display = 'block';  // オーバーレイを表示

            const checked = this.checked;
            const items = $(`${shopSelectInputsSelector} #byOrganization-${messageId} input[type="checkbox"]`).toArray(); // 組織のチェックボックス
            let index = 0;

            // 全選択/選択解除の処理
            function processNextBatch(deadline) {
                while (index < items.length && deadline.timeRemaining() > 0) {
                    const item = items[index];
                    if ($(item).attr("id") !== `selectOrganization-${messageId}`) {
                        item.checked = checked;
                    }
                    if ($(item).hasClass("shop-checkbox")) {
                        syncCheckboxes($(item).attr("data-store-id"), checked, messageId);
                    }
                    index++;
                }

            if (index < items.length) {
                requestIdleCallback(processNextBatch);
            } else {
                finishProcess(messageId); // 全選択/解除処理の後処理
            }
        }

            // 処理の後、状態を更新
            function finishProcess(messageId) {
                if ($(`${shopSelectInputsSelector} #selectOrganization-${messageId}`).is(':checked')) {
                    $(`${shopSelectInputsSelector} #byOrganization-${messageId} li`).show();
                    $(`${shopSelectInputsSelector} #selectOrganization-${messageId}`).prop('checked', false);
                }
                if ($(`${shopSelectInputsSelector} #selectStoreCode-${messageId}`).is(':checked')) {
                    $(`${shopSelectInputsSelector} #byStoreCode-${messageId} li`).show();
                    $(`${shopSelectInputsSelector} #selectStoreCode-${messageId}`).prop('checked', false);
                }

                // 親要素の状態をリセット
                if (!checked) {
                    $(`${shopSelectInputsSelector} input[name="organization[${org}][]"]`).each(function () {
                        const parentListItem = $(this).closest("li");
                        parentListItem.show();
                        const collapseElement = parentListItem.find('.collapse');
                        collapseElement.collapse('hide');
                    });
                }

                updateSelectedStores(messageId);
                updateSelectAllCheckboxes(messageId);

                // オーバーレイを非表示にする
                overlay.style.display = 'none';
            }

            requestIdleCallback(processNextBatch); // 最初のアイドル時間で処理を開始
        });

        // 店舗コード順タブの全選択/選択解除
        $(document).on("change", `${shopSelectInputsSelector} #selectAllStoreCode-${messageId}`, function () {
            var overlay = document.getElementById('overlay');
            overlay.style.display = 'block';  // オーバーレイを表示

            const checked = this.checked;
            const items = $(`${shopSelectInputsSelector} #byStoreCode-${messageId} input[type="checkbox"]`).toArray(); // 店舗コードのチェックボックス
            let index = 0;

            // 全選択/選択解除の処理
            function processNextBatch(deadline) {
                while (index < items.length && deadline.timeRemaining() > 0) {
                    const item = items[index];
                    if ($(item).attr("id") !== `selectStoreCode-${messageId}`) {
                        item.checked = checked;
                    }
                    if ($(item).hasClass("shop-checkbox")) {
                        syncCheckboxes($(item).attr("data-store-id"), checked, messageId);
                    }
                    index++;
                }

                if (index < items.length) {
                    requestIdleCallback(processNextBatch);
                } else {
                    finishProcess(messageId); // 全選択/解除処理の後処理
                }
            }

            // 処理の後、状態を更新
            function finishProcess(messageId) {
                if ($(`${shopSelectInputsSelector} #selectOrganization-${messageId}`).is(':checked')) {
                    $(`${shopSelectInputsSelector} #byOrganization-${messageId} li`).show();
                    $(`${shopSelectInputsSelector} #selectOrganization-${messageId}`).prop('checked', false);
                }
                if ($(`${shopSelectInputsSelector} #selectStoreCode-${messageId}`).is(':checked')) {
                    $(`${shopSelectInputsSelector} #byStoreCode-${messageId} li`).show();
                    $(`${shopSelectInputsSelector} #selectStoreCode-${messageId}`).prop('checked', false);
                }

                updateSelectedStores(messageId);
                updateSelectAllCheckboxes(messageId);

                // オーバーレイを非表示にする
                overlay.style.display = 'none';
            }

            requestIdleCallback(processNextBatch); // 最初のアイドル時間で処理を開始
        });


        // 全店ボタン処理
        $(document).on('click', `input[id="checkAll-${messageId}"][name="organizationAll"]`, function() {
            removeSelectedClass(messageId);
            // 全ての organization_shops[] チェックボックスをチェックする
            $(`${shopSelectInputsSelector} input[name="organization_shops[]"]`).each(function() {
                $(this).prop('checked', true);
                syncCheckboxes($(this).attr("data-store-id"), true, messageId);
            });
            // 全ての親チェックボックスをチェックする
            $(`${shopSelectInputsSelector} input.org-checkbox`).each(function() {
                $(this).prop('checked', true);
            });
            // 全選択ボタン チェックボックスをチェックする
            $(`${shopSelectInputsSelector} #selectAllOrganization-${messageId}`).each(function() {
                $(this).prop('checked', true);
            });
            $(`${shopSelectInputsSelector} #selectAllStoreCode-${messageId}`).each(function() {
                $(this).prop('checked', true);
            });
            // チェックされているチェックボックスの値を変数に格納
            changeValues(messageId);
            // フォームクリア（全店ボタン）
            $(`#selectOrganizationAll-${messageId}`).val("selected");
            // 店舗選択、インポートボタンをもとに戻す
            $(`${editShopInputsSelector} #checkStore-${messageId}`).val('店舗選択');
            $(`${editShopInputsSelector} #importCsv-${messageId}`).val('インポート');
            // 選択中の店舗数を更新する
            updateSelectedStores(messageId);
            // ボタンの見た目を変更する
            $(this).addClass("check-selected");
            // csvインポートボタン変更
            $(`${editShopInputsSelector} #importCsv-${messageId}`).attr('data-target', `#editShopImportModal-${messageId}`);
        });


        // 店舗選択モーダル 選択処理
        $(document).on('click', `${editShopInputsSelector} input[id="checkStore-${messageId}"]`, function() {
            // モーダルタイトル変更
            var storeModalTitle = $(`${shopSelectInputsSelector} h4.modal-title`);
            if (storeModalTitle.length) {
                storeModalTitle.html('店舗を選択してください。');
            }

            // 元のボタンのセレクターを取得して、新しいボタンのセレクターに変更
            var selectCsvButton = $(`${shopSelectInputsSelector} #editCsvSelectBtn-${messageId}`);
            if (selectCsvButton.length) {
                selectCsvButton.attr("id", `${shopSelectInputsSelector} #editShopSelectBtn-${messageId}`);
            }
            // キャンセルボタン表示
            $(`.editShopImportModal [data-message-id="${messageId}"] .editShopCancelBtn`).show();
            // csv再インポートボタン削除
            if ($(`${shopSelectInputsSelector} .editShopCsvImportBtn`).length) {
                $(`${shopSelectInputsSelector} .modal-footer .editShopCsvImportBtn`).remove();
            }

            // キャンセルボタン処理
            // 変数から選択された値を取得
            const org5Values = selectedValuesByMessageId[messageId].org5;
            const org4Values = selectedValuesByMessageId[messageId].org4;
            const org3Values = selectedValuesByMessageId[messageId].org3;
            const org2Values = selectedValuesByMessageId[messageId].org2;
            const shopValues = selectedValuesByMessageId[messageId].shops;

            let allOrg_flg = true;
            let allStore_flg = true;
            // チェックボックスを更新
            if ($(`${shopSelectInputsSelector} input[name="organization[org5][]"]`).length > 0) {
                $(`${shopSelectInputsSelector} input[name="organization[org5][]"]`).each(function() {
                    if (org5Values.includes($(this).val())) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
            }
            if ($(`${shopSelectInputsSelector} input[name="organization[org4][]"]`).length > 0) {
                $(`${shopSelectInputsSelector} input[name="organization[org4][]"]`).each(function() {
                    if (org4Values.includes($(this).val())) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
            }
            if ($(`${shopSelectInputsSelector} input[name="organization[org3][]"]`).length > 0) {
                $(`${shopSelectInputsSelector} input[name="organization[org3][]"]`).each(function() {
                    if (org3Values.includes($(this).val())) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
            }
            if ($(`${shopSelectInputsSelector} input[name="organization[org2][]"]`).length > 0) {
                $(`${shopSelectInputsSelector} input[name="organization[org2][]"]`).each(function() {
                    if (org2Values.includes($(this).val())) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
            }
            $(`${shopSelectInputsSelector} input[name="organization_shops[]"]`).each(function() {
                if (shopValues.includes($(this).val())) {
                    $(this).prop('checked', true);
                } else {
                    allOrg_flg = false;
                    $(this).prop('checked', false);
                }
            });
            $(`${shopSelectInputsSelector} input[name="shops_code[]"]`).each(function() {
                if (shopValues.includes($(this).val())) {
                    $(this).prop('checked', true);
                } else {
                    allStore_flg = false;
                    $(this).prop('checked', false);
                }
            });
            $(`${shopSelectInputsSelector} #selectAllOrganization-${messageId}`).prop('checked', allOrg_flg);
            $(`${shopSelectInputsSelector} #selectAllStoreCode-${messageId}`).prop('checked', allStore_flg);

            // 店舗選択中の処理
            updateSelectedStores(messageId);
        });

        $(document).on('click', `.editShopSelectModal #editShopSelectBtn-${messageId}`, function() {
            removeSelectedClass(messageId);
            // チェックされているチェックボックスの値を変数に格納
            changeValues(messageId);
            // フォームクリア（店舗選択ボタン）
            $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] #selectStore-${messageId}`).val("selected");
            // インポートボタンをもとに戻す
            $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] #importCsv-${messageId}`).val('インポート');
            // モーダルを閉じる
            $(`#editShopSelectModal-${messageId}`).modal("hide");
            // check-selected クラスを追加
            $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] #checkStore-${messageId}`).addClass("check-selected");
            // csvインポートボタン変更
            $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] #importCsv-${messageId}`).attr('data-target', `#editShopSelectModal-${messageId}`);
            // 店舗選択中の処理
            const selectedCountStore = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] input[name="organization_shops[]"]:checked`).length;
            $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] .check-store-list input[id="checkStore-${messageId}"]`).val(`店舗選択(${selectedCountStore}店舗)`);
        });

        // // モーダルが閉じられる際にchangeValuesを実行
        // $(`#editShopSelectModal-${messageId}`).on('hidden.bs.modal', function () {
        //     changeValues(messageId);
        // });

        // 選択された値を変数に格納
        function changeValues(messageId) {
            selectedValuesByMessageId[messageId].org5 = $(`${shopSelectInputsSelector} input[name="organization[org5][]"]:checked`).map(function() { return this.value; }).get();
            selectedValuesByMessageId[messageId].org4 = $(`${shopSelectInputsSelector} input[name="organization[org4][]"]:checked`).map(function() { return this.value; }).get();
            selectedValuesByMessageId[messageId].org3 = $(`${shopSelectInputsSelector} input[name="organization[org3][]"]:checked`).map(function() { return this.value; }).get();
            selectedValuesByMessageId[messageId].org2 = $(`${shopSelectInputsSelector} input[name="organization[org2][]"]:checked`).map(function() { return this.value; }).get();
            selectedValuesByMessageId[messageId].shops = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).map(function() { return this.value; }).get();
        }


        // インポート後のモーダルがまだ！！！
        // CSVインポートモーダル 選択処理
        $(document).on('click', `${editShopImportSelector} input[id="importCsv-${messageId}"]`, function() {
            // 元のボタンのセレクターを取得
            var selectStoreButton = $(`${shopSelectInputsSelector} #editShopSelectBtn-${messageId}`);
            // 新しいボタンのセレクターに変更
            if (selectStoreButton) {
                selectStoreButton.attr("id", `${shopSelectInputsSelector} #editCsvSelectBtn-${messageId}`);
            }
            // キャンセルボタン非表示
            $(`${shopSelectInputsSelector} #editShopCancelBtn-${messageId}`).hide();
            // csv再インポートボタン追加
            if (!$(`${shopSelectInputsSelector} #editShopCsvImportBtn-${messageId}`).length) {
                $(`${shopSelectInputsSelector} .modal-footer`).append(`<input type="button" class="btn btn-admin pull-left" id="editShopCsvImportBtn-${messageId}" data-toggle="modal" data-target="#editShopImportModal-${messageId}" value="再インポート">`);
            }
        });

        // インポートボタンのクリックイベント
        $(document).on('click', `${shopSelectInputsSelector} #editShopCsvImportBtn-${messageId}`, function() {
            // モーダルを閉じる
            $(`#editShopImportModal-${messageId}`).modal("hide");
        });

        $(document).on('click', `${shopSelectInputsSelector} #editCsvSelectBtn-${messageId}`, function() {
            removeSelectedClass(messageId);
            // チェックされているチェックボックスの値を変数に格納
            changeValues(messageId);
            // フォームクリア（CSVインポートボタン）
            $(`${editShopInputsSelector} #selectCsv-${messageId}`).val("selected");
            // モーダルを閉じる
            $(`$editShopSelectModal-${messageId}`).modal("hide");
            // 店舗選択ボタンをもとに戻す
            $(`${editShopInputsSelector} #checkStore-${messageId}`).val('店舗選択');
            // check-selected クラスを追加
            $(`${editShopInputsSelector} #importCsv-${messageId}`).addClass("check-selected");
            // 店舗選択中の処理
            const selectedCountStore = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).length;
            $(`${editShopInputsSelector} .check-store-list input[id="importCsv-${messageId}"]`).val(`インポート(${selectedCountStore}店舗)`);
        });

        $(document).on('click', `${editShopImportSelector} #editShopImportSelector-${messageId}`, function() {
            // モーダルを閉じる
            $(`#editShopSelectModal-${messageId}`).modal("hide");

            // ファイルを削除
            $(`${editShopImportSelector} input[type="file"]`).val('');
        });

        // 業務連絡店舗CSV アップロード
        $(document).on('change' , `${editShopImportSelector} input[type=file]` , function(){
            let changeTarget = $(this);
            changeFileName(changeTarget);
        });

        let newMessageJson;
        $(document).on('change', `${editShopImportSelector} input[type="file"]`, function() {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            let log_file_name = getNumericDateTime();
            let formData = new FormData();
            formData.append("file", $(this)[0].files[0]);
            formData.append("organization1", $(`${editShopImportSelector} input[name="organization1"]`).val())
            formData.append("log_file_name", log_file_name)

            let button = $(`${editShopImportSelector} input[type="button"]`);

            var labelForm = $(this).parent();
            var progress = labelForm.parent().find('.progress');
            var progressBar = progress.children(".progress-bar");

            progressBar.hide();
            progressBar.css('width', 0 + '%');
            progress.show();

            let progress_request = true;

            $(`${editShopImportSelector} .modal-body .alert-danger`).remove();

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
                // console.log(response);
                progress_request = false;
                button.prop("disabled", false);
                labelForm.parent().find('.text-danger').remove();
                newMessageJson = response.json;

            }).fail(function(jqXHR, textStatus, errorThrown){
                $(`${editShopImportSelector} .modal-body`).prepend(`
                    <div class="alert alert-danger">
                        <ul></ul>
                    </div>
                `);
                const errorUl =  $(`${editShopImportSelector} .modal-body .alert ul`);
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
                    console.log(response);
                }).fail(function(qXHR, textStatus, errorThrown){
                    console.log("終了");
                })
                if(percent == 100 || !progress_request) {
                    clearInterval(id);
                    console.log("終了");
                }
            }, 500);
        });

        // 業務連絡店舗CSV インポート
        $(document).on('click', `${editShopImportSelector} input[type="button"]`, function(e){
            e.preventDefault();

            if(!newMessageJson) {
                $(`${editShopImportSelector} .modal-body`).prepend(`
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

            $(`${editShopImportSelector} .modal-body .alert-danger`).remove();
            $.ajax({
                url: '/admin/message/publish/csv/store/import',
                type: 'post',
                data: JSON.stringify({
                    file_json: newMessageJson,
                    organization1_id: $(`${editShopImportSelector} input[name="organization1"]`).val()
                }),
                processData: false,
                contentType: "application/json; charset=utf-8",
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },

            }).done(function(response){
                // console.log(response);
                overlay.style.display = 'none';

                $(`#editShopSelectModal-${messageId}`).html(response);

                var allOrg_flg = true;
                var allStore_flg = true;

                // organization_shops のチェック状態を確認
                $(`${shopSelectInputsSelector} input[name="organization_shops[]"]`).each(function() {
                    if (!$(this).prop('checked')) {
                        allOrg_flg = false;
                    }
                });
                $(`${shopSelectInputsSelector} #selectAllOrganization-${messageId}`).prop('checked', allOrg_flg);

                // shops_code のチェック状態を確認
                $(`${shopSelectInputsSelector} input[name="shops_code[]"]`).each(function() {
                    if (!$(this).prop('checked')) {
                        allStore_flg = false;
                    }
                });
                $(`${shopSelectInputsSelector} #selectAllStoreCode-${messageId}`).prop('checked', allStore_flg);

                // 初期表示の更新
                updateSelectedStores(messageId);
                updateAllParentCheckboxes(messageId);

                // csvインポートボタン変更
                $(`${editShopInputsSelector} #importCsv-${messageId}`).attr('data-target', `#editShopSelectModal-${messageId}`);

            }).fail(function(jqXHR, textStatus, errorThrown){
                overlay.style.display = 'none';

                $(`${editShopImportSelector} .modal-body`).prepend(`
                    <div class="alert alert-danger">
                        <ul></ul>
                    </div>
                `);
                // labelForm.parent().find('.text-danger').remove();

                jqXHR.responseJSON.error_message?.forEach((errorMessage)=>{

                    errorMessage['errors'].forEach((error) => {
                        $(`${editShopImportSelector} .modal-body .alert ul`).append(
                            `<li>${errorMessage['row']}行目：${error}</li>`
                        );
                    })
                })
                if(errorThrown) {
                    $(`${editShopImportSelector} .modal-body .alert ul`).append(
                        `<li>エラーが発生しました</li>`
                    );
                }
            });
        });


        // 業務連絡店舗CSV エクスポート
        $(document).on('click', `${editShopInputsSelector} #exportCsv-${messageId}`, function() {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            let formData = new FormData();
            formData.append("message_id", $(`${editShopInputsSelector} .check-store-list input[name="message_id"]`).val());

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


        // 店舗選択設定ボタンのクリックイベント
        $(document).on('click', `#shopImportBtn-${messageId}`, function() {
            changeValues(messageId);

            // フォームクリア（店舗選択ボタン）
            $(`#selectStore-${messageId}`).val("selected");
            // check-selected クラスを追加
            $(`#checkStore-${messageId}`).addClass("check-selected");

            // モーダルを閉じる
            $(`#editShopModal-${messageId}`).modal("hide");
        });


        // 店舗選択中の処理
        function updateSelectedStores(messageId) {
            const selectedCount = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] input[name="organization_shops[]"]:checked`).length;
            $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] .storeSelected`).text(`${selectedCount}店舗選択中`);
        }

        // チェックボックスの連携を設定
        function syncCheckboxes(storeId, checked, messageId) {
            document.querySelectorAll(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] input[data-store-id="${storeId}"]`).forEach(function(checkbox) {
                checkbox.checked = checked;
            });

            // 各親組織のチェックボックスを更新
            const organizationId = document.querySelector(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] input[data-store-id="${storeId}"]`).getAttribute('data-organization-id');
            if (organizationId) {
                updateParentCheckbox(messageId, organizationId);
            }
        }

        // 親チェックボックスの状態を更新
        function updateParentCheckbox(messageId, organizationId) {
            const parentCheckbox = document.querySelector(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] input[data-organization-id="${organizationId}"]`);
            if (parentCheckbox) {
                const childCheckboxes = document.querySelectorAll(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] input[data-organization-id="${organizationId}"].shop-checkbox`);
                const allChecked = Array.from(childCheckboxes).every(checkbox => checkbox.checked);
                parentCheckbox.checked = allChecked;
            }
        }

        // 全ての親チェックボックスの状態を更新
        function updateAllParentCheckboxes(messageId) {
            const parentCheckboxes = document.querySelectorAll(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] input.org-checkbox`);
            parentCheckboxes.forEach(parentCheckbox => updateParentCheckbox(messageId, parentCheckbox.getAttribute('data-organization-id')));
        }

        // 全選択/選択解除のチェックボックスの状態を更新
        function updateSelectAllCheckboxes(messageId) {
            // 組織タブのチェックボックスの状態を更新
            const organizationCheckboxes = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] #byOrganization-${messageId} input.shop-checkbox`);
            const selectAllOrganizationCheckbox = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] #selectAllOrganization-${messageId}`);
            const allCheckedOrganization = Array.from(organizationCheckboxes).every(checkbox => checkbox.checked);
            selectAllOrganizationCheckbox.checked = allCheckedOrganization;

            // 店舗コード順タブのチェックボックスの状態を更新
            const storeCodeCheckboxes = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] #byStoreCode-${messageId} input.shop-checkbox`);
            const selectAllStoreCodeCheckbox = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] #selectAllStoreCode-${messageId}`);
            const allCheckedStoreCode = Array.from(storeCodeCheckboxes).every(checkbox => checkbox.checked);
            selectAllStoreCodeCheckbox.checked = allCheckedStoreCode;
        }

        // check-selected クラスを削除と選択された値をクリア
        function removeSelectedClass(messageId) {
            // すべてのボタンから check-selected クラスを削除
            $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] .check-store-list .btn`).removeClass("check-selected");

            // 選択された値をクリア
            selectedValuesByMessageId[messageId] = {
                org5: [],
                org4: [],
                org3: [],
                org2: [],
                shops: []
            };
        }

        // ファイル名を変更
        function changeFileName(e){
            let fileNameTarget = e.siblings('.fileName');
            if(e.val() == ''){
                fileNameTarget.empty().text('ファイルを選択またはドロップ');
            }else{
                let chkFileName = e.prop('files')[0].name;
                fileNameTarget.empty().text(chkFileName);
            }
        }

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
    }

    // 掲載期間
    function initDatetimepicker(messageId) {
        var d = new Date();
        d.setDate(d.getDate() + 1);
        /* datetimepicker */
        $.datetimepicker.setLocale('ja');

        $(`#dateFrom-${messageId}`).datetimepicker({
            format:'Y/m/d (D) H:00',
            onShow:function( ct ){
                this.setOptions({
                    maxDate:$(`#dateTo-${messageId}`).val()?$(`#dateTo-${messageId}`).val():false
                })
            },
            defaultDate: d,
            defaultTime: '00:00',
        });

        $(`#dateTo-${messageId}`).datetimepicker({
            format:'Y/m/d (D) H:i',
            onShow:function( ct ){
                this.setOptions({
                    minDate:$(`#dateFrom-${messageId}`).val()?$(`#dateFrom-${messageId}`).val():false
                })
            },
            allowTimes:[
                '00:00','01:00','02:00','03:00','04:00','05:00','06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00',
            ],
            defaultDate: d,
            defaultTime: '00:00',
        });
    }



    // 追加モード
    // 新しい行のHTMLを作成する関数
    function createNewRow(newMessageId, org1Id, messageNumber, categoryList, targetRollList, brandList, organizationList, allShopList) {
        var newRow = `
            <tr data-message_id="${newMessageId}"
                data-organization1_id="${org1Id}"
                class="editing">

                <td nowrap>
                    <p class="messageNewSaveBtn btn btn-admin" data-message-id="${newMessageId}">保存</p>
                    <p class="messageNewDeleteBtn btn btn-admin" data-message-id="${newMessageId}">取消</p>
                </td>
                <td class="shop-id">
                    ${messageNumber}
                    ${targetRollList.map(targetRoll => `
                        <input type="hidden" name="target_roll[]" value="${targetRoll.id}">
                    `).join('')}
                </td>
                <td class="label-brand">
                    <div class="brand-input-group" style="width: max-content;">
                        <select class="form-control" name="brand[]" style="cursor: pointer;">
                            <option value="all" selected>全業態</option>
                                ${brandList.map(brand => `
                                    <option value="${brand.id}">${brand.name}</option>
                                `).join('')}
                        </select>
                    </div>
                </td>
                <td class="label-colum-danger">
                    <div class="emergency-flg-input-group" style="background-color: #ffffff00; color: black;">
                        <input type="checkbox" name="emergency_flg" class="checkCommon mr8" style="cursor: pointer;"><span>重要</span>
                    </div>
                </td>
                <td class="label-category">
                    <div class="category-input-group" style="width: max-content;">
                        <select class="form-control" name="category_id" style="cursor: pointer;">
                            ${categoryList.map(category => `
                                ${(org1Id === 8 || category.id !== 7) ? `
                                    <option value="${category.id}" >
                                        ${category.name}
                                    </option>
                                ` : ''}
                            `).join('')}
                        </select>
                    </div>
                </td>
                <td class="label-title">
                    <div class="title-input-group" style="display: flex;">
                        <input type="text" class="form-control" name="title" style="border-radius: 4px 0 0 4px;">
                        <input type="button" class="btn btn-admin" id="titleFileEditBtn-${newMessageId}"
                            data-toggle="modal" data-target="#editTitleFileModal-${newMessageId}" value="編集"
                            style="border-radius: 0 4px 4px 0;">
                    </div>
                </td>
                <td class="label-file"></td>
                <td class="label-tags">
                    <div class="tags-text-group">
                        <div class="tags-input-group form-group tag-form" style="width: -webkit-fill-available;">
                            <div class="form-control">
                                <span contenteditable="true" class="focus:outline-none tag-form-input"></span>
                            </div>
                        </div>
                    </div>
                    <div class="tags-input-mark">複数入力する場合は「,」で区切る</div>
                </td>
                <td></td>
                <td class="date-time">
                    <div class="start-datetime-group" style="width: max-content;">
                        <input id="dateFrom-${newMessageId}" class="form-control"
                            value="" name="start_datetime" autocomplete="off">
                    </div>
                </td>
                <td class="date-time">
                    <div class="end-datetime-group" style="width: max-content;">
                        <input id="dateTo-${newMessageId}" class="form-control"
                            value="" name="end_datetime" autocomplete="off">
                    </div>
                </td>
                <td></td>
                <td style="text-align: right">
                    <div class="shop-edit-group">
                        <input type="button" class="btn btn-admin" id="checkAll-${newMessageId}" name="organizationAll" value="全店">
                        <input type="hidden" id="selectOrganizationAll-${newMessageId}" name="select_organization[all]" value="">

                        <input type="button" class="btn btn-admin" id="shopEditBtn-${newMessageId}"
                            data-toggle="modal" data-target="#editShopModal-${newMessageId}" value="一部">
                        <input type="hidden" id="selectStore-${newMessageId}" name="select_organization[store]" value="">
                    </div>
                </td>
                <td class="view-rate"></td>
                <td></td>
                <td class="detailBtn"></td>
                <td></td>
                <td class="date-time"></td>
                <td></td>
                <td class="date-time"></td>
            </tr>
        `;

        // テーブルのtbodyの最初に新しい行を追加
        $('#list tbody').prepend(newRow);

        // 掲載期間
        initDatetimepicker(newMessageId);
        // 業連ファイル編集モーダル
        initializeFileModal(newMessageId, [], [], 'new');
        // 店舗編集モーダル
        initializeShopModal(newMessageId, org1Id, organizationList, allShopList, {}, 'new');
    }

    // 日付のフォーマット
    function cleanAndFormatDate(dateString) {
        // dateStringがnullまたはundefinedの場合はnullを返す
        if (!dateString) {
            return null;
        }
        // 曜日部分を削除
        const cleanedDateString = dateString.replace(/\s*\(.*?\)\s*/, ' ');
        // 日付文字列をパース
        const date = new Date(cleanedDateString);
        // 日付が無効な場合はnullを返す
        if (isNaN(date.getTime())) {
            return null;
        }
        // 年、月、日、時、分を取得
        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).slice(-2);
        const day = ('0' + date.getDate()).slice(-2);
        const hours = ('0' + date.getHours()).slice(-2);
        const minutes = ('0' + date.getMinutes()).slice(-2);
        // フォーマットされた日付を返す
        return `${year}/${month}/${day} ${hours}:${minutes}`;
    }

    // 追加ボタン
    $('.messageAddBtn').each(function() {
        $(this).on('click', function() {

            // 現在の最大メッセージIDを取得
            var maxId = 0;
            $('#list tbody tr').each(function() {
                var currentId = parseInt($(this).data('message_id'), 10);
                if (currentId > maxId) {
                    maxId = currentId;
                }
            });
            // 新しいメッセージIDを設定
            var newMessageId = maxId + 1;


            // 削除ボタン処理
            function initializeNewDeleteBtn(newMessageId) {
                $(`.messageNewDeleteBtn[data-message-id="${newMessageId}"]`).on('click', function() {
                    // ボタンが属する行を削除
                    $(this).closest('tr').remove();

                    messageNumber--;
                });
            }


            // 保存ボタン処理
            function initializeNewSaveBtn(newMessageId, brandList) {
                $(`.messageNewSaveBtn[data-message-id="${newMessageId}"]`).on('click', function() {
                    var overlay = document.getElementById('overlay');
                    overlay.style.display = 'block';  // オーバーレイを表示

                    const row = document.querySelector(`tr[data-message_id="${newMessageId}"]`);

                    // カテゴリ
                    const categoryId = row.querySelector('select[name="category_id"]')?.value || null;
                    // ラベル
                    const emergencyFlg = row.querySelector('input[name="emergency_flg"]')?.checked ? 'on' : 'off';
                    // タイトル
                    const title = row.querySelector('input[name="title"]')?.value || null;
                    // 掲載期間
                    let startDatetime = row.querySelector('input[name="start_datetime"]')?.value || null;
                    let endDatetime = row.querySelector('input[name="end_datetime"]')?.value || null;
                    startDatetime = cleanAndFormatDate(startDatetime);
                    endDatetime = cleanAndFormatDate(endDatetime);
                    // 検索タグ
                    const tags = Array.from(row.querySelectorAll('input[name="tag_name[]"]')).map(input => input.value) || null;
                    // 業連ファイル
                    // const contentId = (fileDataByMessageId[newMessageId]?.contentIds || []).map(id => id || null);
                    const fileName = (fileDataByMessageId[newMessageId]?.fileNames || []).map(name => name || null);
                    const filePath = (fileDataByMessageId[newMessageId]?.filePaths || []).map(path => path || null);
                    const joinFlg = (fileDataByMessageId[newMessageId]?.joinFlags || []).map(flg => flg || null);
                    // 対象者
                    const targetRoll = Array.from(row.querySelectorAll('input[name="target_roll[]"]')).map(input => input.value) || null;
                    // 対象業態
                    const brand = row.querySelector('select[name="brand[]"]')?.value === 'all'
                        ? brandList.map(brand => brand.id)
                        : Array.from(row.querySelectorAll('select[name="brand[]"]')).map(input => input.value) || null;
                    const organization = [
                        selectedValuesByMessageId[newMessageId]?.org5 || null,
                        selectedValuesByMessageId[newMessageId]?.org4 || null,
                        selectedValuesByMessageId[newMessageId]?.org3 || null,
                        selectedValuesByMessageId[newMessageId]?.org2 || null
                    ].map(org => org || null);
                    // 対象店舗
                    const organizationShops = (selectedValuesByMessageId[newMessageId]?.shops || []).map(shop => shop || null);
                    const selectOrganizationAll = row.querySelector('input[name="select_organization[all]"]')?.value || null;
                    const selectOrganization = {
                        all: selectOrganizationAll === 'selected' ? 'selected' : null,
                        store: selectOrganizationAll !== 'selected' ? 'selected' : null,
                        csv: null
                    };

                    // バリデーション
                    let errors = [];
                    if (!title) errors.push("タイトルは必須項目です");
                    if (errors.length > 0) {
                        overlay.style.display = 'none';
                        // エラーメッセージを表示するコンテナを取得または作成
                        let errorContainer = $('#error-messages');
                        if (!errorContainer || errorContainer.length === 0) {
                            errorContainer = $('<div id="error-messages" class="alert alert-danger"></div>');
                            $('.pagenation-top').after(errorContainer);
                        }
                        // エラーメッセージをクリアして新しいメッセージを追加
                        errorContainer.empty();
                        errors.forEach(error => {
                            errorContainer.append(`<div class="text-danger">${error}</div>`);
                        });
                        return;
                    }

                    let csrfToken = $('meta[name="csrf-token"]').attr("content");
                    let formData = new FormData();
                    // 業態ID
                    formData.append('org1Id', org1Id);
                    // ラベル
                    formData.append('emergency_flg', emergencyFlg);
                    // カテゴリ
                    formData.append('category_id', categoryId);
                    // タイトル
                    formData.append('title', title);
                    // 掲載期間
                    formData.append('start_datetime', startDatetime);
                    formData.append('end_datetime', endDatetime);
                    // 検索タグ
                    tags.forEach(tag => formData.append('tag_name[]', tag));
                    // 業連ファイル
                    // contentId.forEach(id => formData.append('content_id[]', id));
                    fileName.forEach(name => formData.append('file_name[]', name));
                    filePath.forEach(path => formData.append('file_path[]', path));
                    joinFlg.forEach(flg => formData.append('join_flg[]', flg));
                    // 対象者
                    targetRoll.forEach(roll => formData.append('target_roll[]', roll));
                    // 対象業態
                    brand.forEach(b => formData.append('brand[]', b));
                    organization.forEach(org => formData.append('organization[]', org));
                    formData.append('organization_shops', organizationShops);
                    // 対象店舗
                    Object.keys(selectOrganization).forEach(key => {
                        formData.append(`select_organization[${key}]`, selectOrganization[key]);
                    });

                    $.ajax({
                        url: `/admin/message/publish/messageStoreData`,
                        type: "post",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        success: function(response) {
                            window.location.href = "/admin/message/publish/";
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            // オーバーレイを非表示にする
                            overlay.style.display = 'none';

                            try {
                                const response = JSON.parse(jqXHR.responseText);
                                const errors = response.errors;

                                let errorContainer = $('#error-messages');
                                if (errorContainer.length === 0) {
                                    errorContainer = $('<div id="error-messages" class="alert alert-danger"></div>');
                                    $('.pagenation-top').after(errorContainer);
                                }

                                errorContainer.empty();

                                for (const field in errors) {
                                    if (errors.hasOwnProperty(field)) {
                                        errors[field].forEach(message => {
                                            errorContainer.append(`<div class="text-danger">${message}</div>`);
                                        });
                                    }
                                }
                            } catch (e) {
                                console.error("Failed to parse response:", e);
                            }
                        }
                    });
                });
            }

            let csrfToken = $('meta[name="csrf-token"]').attr("content");
            if (!shopDataFetched) {
                $.ajax({
                    url: `/admin/message/publish/messageNewData/${org1Id}`,
                    type: "post",
                    data: {
                        organization1_id: org1Id
                    },
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    success: function(response) {
                        messageNumber = response.message_number;
                        categoryList = response.category_list;
                        targetRollList = response.target_roll_list;
                        brandList = response.brand_list;
                        allShopList = response.all_shop_list;
                        organizationList = response.organization_list;
                        shopDataFetched = true;

                        // 新しい行を作成する関数を呼び出す
                        messageNumber++;
                        createNewRow(newMessageId, org1Id, messageNumber, categoryList, targetRollList, brandList, organizationList, allShopList);
                        // 保存ボタン処理
                        initializeNewSaveBtn(newMessageId, brandList);
                        // 削除ボタン処理
                        initializeNewDeleteBtn(newMessageId);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error:", errorThrown);
                        console.error("Response Text:", jqXHR.responseText);
                    }
                });
            } else {
                messageNumber++;
                createNewRow(newMessageId, org1Id, messageNumber, categoryList, targetRollList, brandList, organizationList, allShopList);
                // 保存ボタン処理
                initializeNewSaveBtn(newMessageId, brandList);
                // 削除ボタン処理
                initializeNewDeleteBtn(newMessageId);
            }
        });
    });



    // 日付をフォーマット
    function formatDateWithDay(dateString) {
        const date = new Date(dateString);
        const days = ["日", "月", "火", "水", "木", "金", "土"];
        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).slice(-2);
        const day = ('0' + date.getDate()).slice(-2);
        const dayOfWeek = days[date.getDay()];
        const hours = ('0' + date.getHours()).slice(-2);
        const minutes = ('0' + date.getMinutes()).slice(-2);
        return `${year}/${month}/${day} (${dayOfWeek}) ${hours}:${minutes}`;
    }

    // 編集モード
    $('.messageEditBtn').each(function() {
        $(this).on('click', function() {
            const row = this.closest('tr');
            const messageId = row.getAttribute('data-message_id');

            // No
            const shopId = row.querySelector('.shop-id');
            // 対象業態
            const brandText = row.querySelector('.brand-text');
            // ラベル
            const emergencyFlgText = row.querySelector('.emergency-flg-text');
            // カテゴリ
            const categoryText = row.querySelector('.category-text');
            // タイトル
            const titleText = row.querySelector('.title-text');
            // 検索タグ
            const tagsTexts = row.querySelectorAll('.tags-text');
            const tagsInputMark = row.querySelector('.tags-input-mark');
            // 掲載期間
            const startDatetimeGroup = row.querySelector('.start-datetime-group');
            const startDatetimeText = row.querySelector('.start-datetime-text');
            const endDatetimeGroup = row.querySelector('.end-datetime-group');
            const endDatetimeText = row.querySelector('.end-datetime-text');
            // 配信店舗数
            const shopCount = row.querySelector('.shop-count');
            // 編集ボタングループ
            const messageEditBtnGroup = row.querySelector('.message-edit-btn-group');
            // 編集ボタン
            const messageEditBtn = row.querySelector('.messageEditBtn');

            // 編集画面処理
            function initializeEditRow(messageId, org1Id, categoryList, targetRollList, brandList, organizationList, allShopList, targetTag, message, messageContents, targetBrand, targetOrg) {
                // No
                const targetRollHtml = targetRollList.map(targetRoll => `
                    <input type="hidden" name="target_roll[]" value="${targetRoll.id}">
                `).join('');
                if (shopId) shopId.insertAdjacentHTML('afterend', targetRollHtml);

                // 対象業態
                if (brandText) {
                    brandText.style.display = 'none';
                    const allBrandsSelected = targetBrand.length === brandList.length;
                    const brandInputGroupHtml = `
                        <div class="brand-input-group" style="width: max-content;">
                            <select class="form-control" name="brand[]" style="cursor: pointer;">
                                <option value="all" ${allBrandsSelected ? 'selected' : ''}>全業態</option>
                                    ${brandList.map(brand => `
                                        <option value="${brand.id}"
                                            ${targetBrand.includes(brand.id) && !allBrandsSelected ? 'selected' : ''}
                                            >${brand.name}
                                        </option>
                                    `).join('')}
                            </select>
                        </div>
                    `;
                    brandText.insertAdjacentHTML('afterend', brandInputGroupHtml);
                }

                // ラベル
                const emergencyFlgChecked = message.emergency_flg;
                const emergencyFlgInputGroupHtml = `
                    <div class="emergency-flg-input-group" style="background-color: #ffffff00; color: black;">
                        <input type="checkbox" name="emergency_flg" class="checkCommon mr8" style="cursor: pointer;"
                            ${emergencyFlgChecked ? 'checked' : ''}
                            ><span>重要</span>
                    </div>
                `;
                if (emergencyFlgText) {
                    emergencyFlgText.style.display = 'none';
                    emergencyFlgText.insertAdjacentHTML('afterend', emergencyFlgInputGroupHtml);
                } else {
                    const emergencyFlgColumn = row.querySelector('.label-colum-danger');
                    emergencyFlgColumn.insertAdjacentHTML('beforeend', emergencyFlgInputGroupHtml);
                }

                // カテゴリ
                const categoryInputGroupHtml = `
                    <div class="category-input-group" style="width: max-content;">
                        <select class="form-control" name="category_id" style="cursor: pointer;">
                            ${categoryList.map(category => `
                                ${(org1Id === 8 || category.id !== 7) ? `
                                    <option value="${category.id}"
                                        ${message.category_id == category.id ? 'selected' : ''}
                                        >${category.name}
                                    </option>
                                ` : ''}
                            `).join('')}
                        </select>
                    </div>
                `;
                if (categoryText) {
                    categoryText.style.display = 'none';
                    categoryText.insertAdjacentHTML('afterend', categoryInputGroupHtml);
                }

                // タイトル
                const titleInputGroupHtml = `
                    <div class="title-input-group" style="display: flex;">
                        <input type="text" class="form-control" name="title"
                            value="${message.title}" style="border-radius:4px 0 0 4px;">
                        <input type="button" class="btn btn-admin" id="titleFileEditBtn-${messageId}"
                            data-toggle="modal" data-target="#editTitleFileModal-${messageId}" value="編集"
                            style="border-radius:0 4px 4px 0;">
                    </div>
                `;
                if (titleText) {
                    titleText.style.display = 'none';
                    titleText.insertAdjacentHTML('afterend', titleInputGroupHtml);
                }

                // 検索タグ
                const tagsInputGroupHtml = `
                    <div class="tags-input-group form-group tag-form" style="width: -webkit-fill-available;">
                        <div class="form-control">
                            ${targetTag.map(tag => `
                                <span class="focus:outline-none tag-form-label">
                                    ${tag.name}<span class="tag-form-delete">×</span>
                                    <input type="hidden" name="tag_name[]" value='${tag.name}'>
                                </span>
                            `).join('')}
                            <span contenteditable="true" class="focus:outline-none tag-form-input"></span>
                        </div>
                    </div>
                `;
                if (tagsTexts.length > 0) {
                    tagsTexts.forEach(tagsText => {
                        tagsText.style.display = 'none';
                    });
                    tagsInputMark.style.display = 'block';
                    tagsTexts[0].insertAdjacentHTML('afterend', tagsInputGroupHtml);
                } else {
                    tagsInputMark.style.display = 'block';
                    const tagsColumn = row.querySelector('.tags-text-group');
                    if (tagsColumn) {
                        tagsColumn.insertAdjacentHTML('beforeend', tagsInputGroupHtml);
                    }
                }

                // 掲載期間
                const startDatetimeInputGroupHtml = `
                    <input id="dateFrom-${messageId}" class="form-control datepicker"
                        value="${formatDateWithDay(message.start_datetime)}"
                        name="start_datetime" autocomplete="off">
                `;
                if (startDatetimeText){
                    startDatetimeText.style.display = 'none';
                    startDatetimeText.insertAdjacentHTML('afterend', startDatetimeInputGroupHtml);
                    startDatetimeGroup.style.width = 'max-content';
                }

                const endDatetimeInputGroupHtml = `
                    <input id="dateTo-${messageId}" class="form-control datepicker"
                        value="${formatDateWithDay(message.end_datetime)}"
                        name="end_datetime" autocomplete="off">
                `;
                if (endDatetimeText) {
                    endDatetimeText.style.display = 'none';
                    endDatetimeText.insertAdjacentHTML('afterend', endDatetimeInputGroupHtml);
                    endDatetimeGroup.style.width = 'max-content';
                }

                // 配信店舗数
                const shopEditGroupHtml = `
                    <div class="shop-edit-group">
                        ${targetOrg.select === 'all' ? `
                            <input type="button" class="btn btn-admin check-selected" id="checkAll-${messageId}" name="organizationAll" value="全店">
                            <input type="hidden" id="selectOrganizationAll-${messageId}" name="select_organization[all]" value="selected">
                        ` : `
                            <input type="button" class="btn btn-admin" id="checkAll-${messageId}" name="organizationAll" value="全店">
                            <input type="hidden" id="selectOrganizationAll-${messageId}" name="select_organization[all]" value="">
                        `}
                        ${targetOrg.select === 'store' ? `
                            <input type="button" class="btn btn-admin check-selected" id="shopEditBtn-${messageId}"
                                data-toggle="modal" data-target="#editShopModal-${messageId}" value="一部">
                            <input type="hidden" id="selectStore-${messageId}" name="select_organization[store]" value="">
                        ` : targetOrg.select === 'oldStore' ? `
                            <input type="button" class="btn btn-admin check-selected" id="shopEditBtn-${messageId}"
                                data-toggle="modal" data-target="#editShopModal-${messageId}" value="一部">
                            <input type="hidden" id="selectStore-${messageId}" name="select_organization[store]" value="selected">
                        ` : `
                            <input type="button" class="btn btn-admin" id="shopEditBtn-${messageId}"
                                data-toggle="modal" data-target="#editShopModal-${messageId}" value="一部">
                            <input type="hidden" id="selectStore-${messageId}" name="select_organization[store]" value="">
                        `}
                    </div>
                `;
                if (shopCount) {
                    shopCount.style.display = 'none';
                    shopCount.insertAdjacentHTML('afterend', shopEditGroupHtml);
                }

                // 編集ボタン
                if (messageEditBtn) {
                    messageEditBtn.style.display = 'none';
                    const saveButtonHtml = `<p class="messageEditSaveBtn btn btn-admin" data-message-id="${messageId}" style="margin-right: 5px;">保存</p>`;
                    messageEditBtnGroup.insertAdjacentHTML('beforeend', saveButtonHtml);
                    const deleteButtonHtml = `<p class="messageEditDeleteBtn btn btn-admin" data-message-id="${messageId}">取消</p>`;
                    messageEditBtnGroup.insertAdjacentHTML('beforeend', deleteButtonHtml);
                }

                // 掲載期間
                initDatetimepicker(messageId);
                // 業連ファイル編集モーダル
                initializeFileModal(messageId, message, messageContents, 'edit');
                // 店舗編集モーダル
                initializeShopModal(messageId, org1Id, organizationList, allShopList, targetOrg, 'edit');
            }


            // 削除ボタン処理
            function initializeEditDeleteBtn(messageId) {
                $(`.messageEditDeleteBtn[data-message-id="${messageId}"]`).on('click', function() {
                    // 対象業態
                    if (brandText) {
                        brandText.style.display = '';
                        const brandInputGroup = brandText.nextElementSibling;
                        if (brandInputGroup) brandInputGroup.remove();
                    }

                    // ラベル
                    if (emergencyFlgText) {
                        emergencyFlgText.style.display = '';
                        const emergencyFlgInputGroup = emergencyFlgText.nextElementSibling;
                        if (emergencyFlgInputGroup) emergencyFlgInputGroup.remove();
                    } else {
                        const emergencyFlgColumn = row.querySelector('.label-colum-danger');
                        const emergencyFlgInputGroup = emergencyFlgColumn.querySelector('.emergency-flg-input-group');
                        if (emergencyFlgInputGroup) emergencyFlgInputGroup.remove();
                    }

                    // カテゴリ
                    if (categoryText) {
                        categoryText.style.display = '';
                        const categoryInputGroup = categoryText.nextElementSibling;
                        if (categoryInputGroup) categoryInputGroup.remove();
                    }

                    // タイトル
                    if (titleText) {
                        titleText.style.display = '';
                        const titleInputGroup = titleText.nextElementSibling;
                        if (titleInputGroup) titleInputGroup.remove();
                    }

                    // 検索タグ
                    if (tagsTexts.length > 0) {
                        tagsTexts.forEach(tagsText => {
                            tagsText.style.display = '';
                        });
                        const tagsInputGroup = tagsTexts[0].nextElementSibling;
                        if (tagsInputGroup) tagsInputGroup.remove();
                        tagsInputMark.style.display = 'none';
                    } else {
                        const tagsColumn = row.querySelector('.tags-text-group');
                        const tagsInputGroup = tagsColumn.querySelector('.tags-input-group');
                        if (tagsInputGroup) tagsInputGroup.remove();
                        tagsInputMark.style.display = 'none';
                    }

                    // 掲載期間
                    if (startDatetimeText) {
                        startDatetimeText.style.display = '';
                        startDatetimeGroup.style.width = '';
                        const startDatetimeInputGroup = startDatetimeText.nextElementSibling;
                        if (startDatetimeInputGroup) startDatetimeInputGroup.remove();
                    }
                    if (endDatetimeText) {
                        endDatetimeText.style.display = '';
                        endDatetimeGroup.style.width = '';
                        const endDatetimeInputGroup = endDatetimeText.nextElementSibling;
                        if (endDatetimeInputGroup) endDatetimeInputGroup.remove();
                    }

                    // 配信店舗数
                    if (shopCount) {
                        shopCount.style.display = '';
                        const shopEditGroup = shopCount.nextElementSibling;
                        if (shopEditGroup) shopEditGroup.remove();
                    }

                    // 編集ボタン
                    const messageEditSaveBtn = row.querySelector('.messageEditSaveBtn');
                    const messageEditDeleteBtn = row.querySelector('.messageEditDeleteBtn');
                    if (messageEditSaveBtn && messageEditDeleteBtn) {
                        messageEditSaveBtn.remove();
                        messageEditDeleteBtn.remove();
                        messageEditBtn.style.display = 'inline-block';
                    }
                });
            }

            // 保存ボタン処理
            function initializeEditSaveBtn(messageId, brandList) {
                $(`.messageEditSaveBtn[data-message-id="${messageId}"]`).on('click', function() {
                    var overlay = document.getElementById('overlay');
                    overlay.style.display = 'block';  // オーバーレイを表示

                    const row = document.querySelector(`tr[data-message_id="${messageId}"]`);

                    // カテゴリ
                    const categoryId = row.querySelector('select[name="category_id"]')?.value || null;
                    // ラベル
                    const emergencyFlg = row.querySelector('input[name="emergency_flg"]')?.checked ? 'on' : 'off';
                    // タイトル
                    const title = row.querySelector('input[name="title"]')?.value || null;
                    // 掲載期間
                    const startDatetime = row.querySelector('input[name="start_datetime"]')?.value || null;
                    const endDatetime = row.querySelector('input[name="end_datetime"]')?.value || null;
                    // 検索タグ
                    const tags = Array.from(row.querySelectorAll('input[name="tag_name[]"]')).map(input => input.value) || null;
                    // 業連ファイル
                    const contentId = (fileDataByMessageId[messageId]?.contentIds || []).map(id => id || null);
                    const fileName = (fileDataByMessageId[messageId]?.fileNames || []).map(name => name || null);
                    const filePath = (fileDataByMessageId[messageId]?.filePaths || []).map(path => path || null);
                    const joinFlg = (fileDataByMessageId[messageId]?.joinFlags || []).map(flg => flg || null);
                    // 対象者
                    const targetRoll = Array.from(row.querySelectorAll('input[name="target_roll[]"]')).map(input => input.value) || null;
                    // 対象業態
                    const brand = row.querySelector('select[name="brand[]"]')?.value === 'all'
                        ? brandList.map(brand => brand.id)
                        : Array.from(row.querySelectorAll('select[name="brand[]"]')).map(input => input.value) || null;
                    const organization = [
                        selectedValuesByMessageId[messageId]?.org5 || null,
                        selectedValuesByMessageId[messageId]?.org4 || null,
                        selectedValuesByMessageId[messageId]?.org3 || null,
                        selectedValuesByMessageId[messageId]?.org2 || null
                    ].map(org => org || null);
                    // 対象店舗
                    const organizationShops = (selectedValuesByMessageId[messageId]?.shops || []).map(shop => shop || null);
                    const selectOrganizationAll = row.querySelector('input[name="select_organization[all]"]')?.value || null;
                    const selectOrganization = {
                        all: selectOrganizationAll === 'selected' ? 'selected' : null,
                        store: selectOrganizationAll !== 'selected' ? 'selected' : null,
                        csv: null
                    };

                    // バリデーション
                    let errors = [];
                    if (!title) errors.push("タイトルは必須項目です");
                    if (errors.length > 0) {
                        overlay.style.display = 'none';
                        // エラーメッセージを表示するコンテナを取得または作成
                        let errorContainer = $('#error-messages');
                        if (!errorContainer || errorContainer.length === 0) {
                            errorContainer = $('<div id="error-messages" class="alert alert-danger"></div>');
                            $('.pagenation-top').after(errorContainer);
                        }
                        // エラーメッセージをクリアして新しいメッセージを追加
                        errorContainer.empty();
                        errors.forEach(error => {
                            errorContainer.append(`<div class="text-danger">${error}</div>`);
                        });
                        return;
                    }

                    let csrfToken = $('meta[name="csrf-token"]').attr("content");
                    let formData = new FormData();
                    // id
                    formData.append('message_id', messageId);
                    // ラベル
                    formData.append('emergency_flg', emergencyFlg);
                    // カテゴリ
                    formData.append('category_id', categoryId);
                    // タイトル
                    formData.append('title', title);
                    // 掲載期間
                    formData.append('start_datetime', startDatetime);
                    formData.append('end_datetime', endDatetime);
                    // 検索タグ
                    tags.forEach(tag => formData.append('tag_name[]', tag));
                    // 業連ファイル
                    contentId.forEach(id => formData.append('content_id[]', id));
                    fileName.forEach(name => formData.append('file_name[]', name));
                    filePath.forEach(path => formData.append('file_path[]', path));
                    joinFlg.forEach(flg => formData.append('join_flg[]', flg));
                    // 対象者
                    targetRoll.forEach(roll => formData.append('target_roll[]', roll));
                    // 対象業態
                    brand.forEach(b => formData.append('brand[]', b));
                    organization.forEach(org => formData.append('organization[]', org));
                    // 対象店舗
                    formData.append('organization_shops', organizationShops);
                    Object.keys(selectOrganization).forEach(key => {
                        formData.append(`select_organization[${key}]`, selectOrganization[key]);
                    });

                    $.ajax({
                        url: `/admin/message/publish/messageUpdateData`,
                        type: "post",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        success: function(response) {
                            window.location.href = "/admin/message/publish/";
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("Error:", errorThrown);
                            console.log("Response Text:", jqXHR.responseText);

                            // オーバーレイを非表示にする
                            overlay.style.display = 'none';

                            try {
                                const response = JSON.parse(jqXHR.responseText);
                                const errors = response.errors;

                                let errorContainer = $('#error-messages');
                                if (errorContainer.length === 0) {
                                    errorContainer = $('<div id="error-messages" class="alert alert-danger"></div>');
                                    $('.pagenation-top').after(errorContainer);
                                }

                                errorContainer.empty();

                                for (const field in errors) {
                                    if (errors.hasOwnProperty(field)) {
                                        errors[field].forEach(message => {
                                            errorContainer.append(`<div class="text-danger">${message}</div>`);
                                        });
                                    }
                                }
                            } catch (e) {
                                console.error("Failed to parse response:", e);
                            }
                        }
                    });
                });
            }


            // 業連ファイルと店舗データ取得
            let csrfToken = $('meta[name="csrf-token"]').attr("content");
            $.ajax({
                url: `/admin/message/publish/messageEditData/${messageId}/${org1Id}`,
                type: "post",
                data: {
                    message_id: messageId,
                    organization1_id: org1Id
                },
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    if (!shopDataFetched) {
                        // No
                        messageNumber = response.message_number;
                        // カテゴリ
                        categoryList = response.category_list;
                        // 対象者
                        targetRollList = response.target_roll_list;
                        // 対象業態
                        brandList = response.brand_list;
                        organizationList = response.organization_list;
                        // 全店舗
                        allShopList = response.all_shop_list;

                        shopDataFetched = true;
                    }

                    // 検索タグ
                    let targetTag = response.target_tag;
                    // 業連データ
                    let message = response.message;
                    let messageContents = response.message_contents;
                    // 対象業態
                    let targetBrand = response.target_brand;
                    let targetOrg = response.target_org;


                    // 編集画面処理
                    initializeEditRow(messageId, org1Id, categoryList, targetRollList, brandList, organizationList, allShopList, targetTag, message, messageContents, targetBrand, targetOrg);
                    // 保存ボタン処理
                    initializeEditSaveBtn(messageId, brandList);
                    // 取消ボタン処理
                    initializeEditDeleteBtn(messageId);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error:", errorThrown);
                    console.error("Response Text:", jqXHR.responseText);
                }
            });
        });
    });
});
