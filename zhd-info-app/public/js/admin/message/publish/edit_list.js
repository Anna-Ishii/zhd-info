$(document).ready(function() {
    // 組織ID
    const org1Id = $('input[name="organization1_id"]').val();
    // 業連ファイルを管理
    const fileDataByMessageId = {};
    // 店舗選択された値を管理
    const selectedValuesByMessageId = {};

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
        if (!$(`#editTitleFileModal-${messageId}`).length) {
            let fileInputsHtml = '';

            if (mode === 'new'){
                fileInputsHtml = `
                    <div class="file-input-container">
                        <div class="row">
                            <label class="col-sm-2 control-label">業連<span class="text-danger required">*</span></label>
                            <div class="col-sm-8">
                                <label class="inputFile form-control">
                                    <span class="fileName" style="text-align: center;">
                                        ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能
                                    </span>
                                    <input type="file" name="file[]" accept=".pdf" multiple="multiple">
                                    <input type="hidden" name="file_name[]" value="">
                                    <input type="hidden" name="file_path[]" value="">
                                    <input type="hidden" name="join_flg[]" value="">
                                </label>
                                <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: 0%"></div>
                                </div>
                            </div>
                            <label class="col-sm-2" style="padding-top: 10px; display: none;">結合</label>
                        </div>
                    </div>
                    <div class="col-sm-11 join-file-btn">
                        <label class="inputFile" style="float: right; display: flex; align-items: center; justify-content: space-between;">
                            <p style="margin: 0; padding-right: 10px; display: none;">0ファイルを結合中です。</p>
                            <input type="button" class="btn btn-admin joinFile" id="joinFileId-${messageId}" data-toggle="modal" data-target="#editJoinFileModal-${messageId}" value="ファイルの結合">
                        </label>
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
                <!-- モーダル：業連ファイル編集 -->
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
                                        <div class="fileInputs">
                                            ${fileInputsHtml}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-3 control-label">
                                            <span class="text-danger required">*</span>：必須項目
                                        </div>
                                        <div class="col-sm-2 col-sm-offset-6 control-label">
                                            <input type="button" id="fileImportBtn-${messageId}" class="btn btn-admin" value="設定" disabled>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
        }


        // 結合PDFファイルモーダル
        if (!$(`#editJoinFileModal-${messageId}`).length) {
            const modalHtml = `
                <!-- モーダル：結合PDFファイル -->
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
            $('body').append(modalHtml);
        }


        // 業連ファイル編集処理
        const editTitleFileInputsSelector = `#editTitleFileModal-${messageId} .fileInputs`;
        const editJoinFileModalSelector = `#editJoinFileModal-${messageId}`;


        // 追加ファイル欄の追加
        function addFileInputAdd() {
            // 変数を初期化
            let file_name = "";
            let file_path = "";
            let join_flg = "";

            // 既存の添付ラベルの数を取得
            let currentLabelCount = $(`${editTitleFileInputsSelector} .file-input-container .control-label:contains('添付')`).length + 1;

            $(`${editTitleFileInputsSelector}`).append(`
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

        // 結合ボタンを追加
        function addJoinFileBtn(messageId) {
            $(`${editTitleFileInputsSelector}`).append(`
                <div class="col-sm-11 join-file-btn">
                    <label class="inputFile" style="float: right; display: flex; align-items: center; justify-content: space-between;">
                        <p style="margin: 0; padding-right: 10px; display: none;">0ファイルを結合中です。</p>
                        <input type="button" class="btn btn-admin joinFile" id="joinFileId-${messageId}" data-toggle="modal" data-target="#editJoinFileModal-${messageId}" value="ファイルの結合">
                    </label>
                </div>
            `);
        }

        // 「結合中」メッセージを更新する関数の呼び出し
        function updateModalFooterMessage() {
            var selectedJoinFiles = [];

            $(`${editTitleFileInputsSelector} [name='join_flg[]']`).each(function() {
                var value = $(this).val();
                selectedJoinFiles.push(value);
            });

            var checkedCount = selectedJoinFiles.filter(value => value === "join").length;

            var modalFooterMessage = $(`${editTitleFileInputsSelector} .join-file-btn .inputFile p`);
            if (modalFooterMessage.length) {
                if (checkedCount >= 2) {
                    modalFooterMessage.text(`${checkedCount}ファイルを結合します。`).show();
                } else {
                    modalFooterMessage.text("").hide();
                }
            }
        }


        // 選択されたファイルのカウントを更新する関数
        function updateJoinFileCount(messageId) {
            var checkedCount = $(`${editJoinFileModalSelector} #fileCheckboxes-${messageId} input[type="checkbox"]:checked`).length;

            // 既存のメッセージを削除
            $(`${editJoinFileModalSelector} .modal-footer p`).remove();

            // メッセージを追加
            if (checkedCount >= 2) {
                $(`${editJoinFileModalSelector} .modal-footer`).append(`<p style="float: left;">${checkedCount}ファイルを結合します。よろしいでしょうか？</p>`);
            } else if (checkedCount == 0) {
                $(`${editJoinFileModalSelector} .modal-footer`).append(`<p style="float: left;">結合するファイルが選択されていません。</p>`);
            }

            // ボタンの有効/無効を設定
            var modalFooterJoinFileBtn = $(`${editJoinFileModalSelector} .modal-footer #joinFileBtn-${messageId}`);
            if (modalFooterJoinFileBtn.length) {
                if (checkedCount === 1) {
                    modalFooterJoinFileBtn.prop('disabled', true);
                } else {
                    modalFooterJoinFileBtn.prop('disabled', false);
                }
            }
        }


        // "join" フラグがあるか
        function updateJoinFileLabel(messageId) {
            // "join" フラグが1つ以下の場合に文言を変更
            var joinFlagCount = $(`${editTitleFileInputsSelector} [name='join_flg[]']`).filter(function() {
                return $(this).val() === "join";
            }).length;

            if (joinFlagCount <= 1) {
                // "join" フラグが1つの場合に他の "join_flg" を "single" に変更
                if (joinFlagCount === 1) {
                    $(`${editTitleFileInputsSelector} [name='join_flg[]']`).each(function() {
                        if ($(this).val() === "join") {
                            $(this).val("single");
                            // 結合ラベルを非表示
                            $(this).closest('.row').find("label[style*='padding-top: 10px']").hide();
                        }
                    });
                }

                $(`${editTitleFileInputsSelector} .inputFile #joinFileId-${messageId}`).val("ファイルの結合");
            }

            // "join" フラグが一つでもあるかチェックして文言を変更
            var hasJoinFlag = joinFlagCount > 1;

            if (hasJoinFlag) {
                $(`${editTitleFileInputsSelector} .inputFile #joinFileId-${messageId}`).val("結合の修正");
            }
        }


        // 業連ファイルを保存
        function saveFileData(messageId) {
            // フォームクリア（ファイル設定ボタン）
            $(`#titleFileEditBtn-${messageId}`).addClass("check-selected");

            const contentIds = [];
            const fileNames = [];
            const filePaths = [];
            const joinFlags = [];

            if (!fileDataByMessageId[mode]) {
                fileDataByMessageId[mode] = {};
            }

            // 各ファイルの情報を取得して配列に保存
            $(`${editTitleFileInputsSelector} [name='content_id[]']`).each(function() {
                contentIds.push($(this).val());
            });

            $(`${editTitleFileInputsSelector} [name='file_name[]']`).each(function() {
                fileNames.push($(this).val());
            });

            $(`${editTitleFileInputsSelector} [name='file_path[]']`).each(function() {
                filePaths.push($(this).val());
            });

            $(`${editTitleFileInputsSelector} [name='join_flg[]']`).each(function() {
                joinFlags.push($(this).val());
            });

            // message_idをキーとしてファイル情報を保存
            fileDataByMessageId[mode][messageId] = {
                contentIds: contentIds,
                fileNames: fileNames,
                filePaths: filePaths,
                joinFlags: joinFlags
            };
        }


        // 新規モードの場合は業連ファイルの初期化
        if(mode === 'new'){
            const contentIds = [];
            const fileNames = [];
            const filePaths = [];
            const joinFlags = [];

            if (!fileDataByMessageId[mode]) {
                fileDataByMessageId[mode] = {};
            }

            fileDataByMessageId[mode][messageId] = {
                contentIds: contentIds,
                fileNames: fileNames,
                filePaths: filePaths,
                joinFlags: joinFlags
            };

            // 結合モーダルの初期状態で結合ボタンを無効化
            $(`${editJoinFileModalSelector} .modal-footer #joinFileBtn-${messageId}`).prop('disabled', true);

        // 編集モードの場合はボタンの有効/無効を設定し、メッセージを表示
        } else if(mode === 'edit'){
            addFileInputAdd();
            addJoinFileBtn(messageId);
            // 「結合中」メッセージを更新する関数の呼び出し
            updateModalFooterMessage();
            // 初期状態でメッセージを表示
            updateJoinFileCount(messageId);
            // "join" フラグがあるか
            updateJoinFileLabel(messageId);
            // 業連ファイルを保存
            saveFileData(messageId);

            // ファイル設定ボタンを有効化
            $(`#editTitleFileModal-${messageId} #fileImportBtn-${messageId}`).prop('disabled', false);
        }


        // 新しいファイル入力欄を追加
        function addNewFileInput(content_name, content_url, join_flg) {
            // 既存の添付ラベルの数を取得
            let currentLabelCount = $(`${editTitleFileInputsSelector} .file-input-container .control-label:contains('添付')`).length + 1;

            $(`${editTitleFileInputsSelector}`).append(`
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
                    addNewFileInput(content_name, content_url, join_flg = "single");
                }
            });

            if (!dataCache) {
                let fileInputs = document.querySelector(`${editTitleFileInputsSelector}`);
                let fileInput = fileInputs.querySelector('input[name="file[]"]');

                // 単一ファイル欄に加工
                if (fileInput) {
                    fileInput.removeAttribute("multiple");
                    fileInput.name = "file";
                    // 削除ボタン追加
                    addDeleteButton(fileInput);
                }

                // 上限を超えていない場合、かつファイル数が上限に達していない場合のみファイル入力欄を追加
                let existingFilesCount = $(`${editTitleFileInputsSelector} .file-input-container`).length;
                let joinFileBtnAdd = $(`${editTitleFileInputsSelector} .join-file-btn`);

                let maxFiles = 20; // 上限数を設定（20）
                if (existingFilesCount < maxFiles) {
                    if (joinFileBtnAdd) {
                        joinFileBtnAdd.remove();
                    }
                    addFileInputAdd();
                    addJoinFileBtn(messageId);

                } else {
                    if (joinFileBtnAdd) {
                        joinFileBtnAdd.remove();
                    }
                    addJoinFileBtn(messageId);
                }

            // PDFファイルの上書き
            } else {
                $(`${editTitleFileInputsSelector} [name='join_flg[]']`).each(function() {
                    if ($(this).val() === "single") {
                        // 結合ラベルを非表示
                        $(this).closest('.row').find("label[style*='padding-top: 10px']").hide();
                    }
                });

                // "join" フラグがあるか
                updateJoinFileLabel(messageId);
            }

            // 「結合中」メッセージを更新する関数の呼び出し
            updateModalFooterMessage();
        }

        // PDFファイル処理
        $(document).on(`change.editTitleFileModal-${messageId}`, `${editTitleFileInputsSelector} input[type="file"]`, function () {
            let _this = $(this);
            const csrfToken = $('meta[name="csrf-token"]').attr("content");
            let fileList = _this[0].files;
            const formData = new FormData();
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
                // console.log(response);
                labelForm.parent().find(".text-danger").remove();
                handleResponse(response, fileName, filePath, joinFile, dataCache, messageId);
                _this.attr('data-cache', 'active');
                // ファイル設定ボタンを有効化
                $(`#editTitleFileModal-${messageId} #fileImportBtn-${messageId}`).prop('disabled', false);
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


        // 添付ラベルの番号を振り直す処理
        function renumberSendLabels() {
            $(`${editTitleFileInputsSelector} .file-input-container .control-label:contains('添付'), ${editTitleFileInputsSelector} .file-input-container .control-label:contains('業連')`).each(function(index) {
                if (index === 0) {
                    $(this).html('業連<span class="text-danger required">*</span>');
                } else {
                    $(this).text(`添付${index}`);
                }
            });
        }

        // 削除ボタンのクリックイベント
        $(document).on(`click.editTitleFileModal-${messageId}`, `${editTitleFileInputsSelector} .delete-btn`, function () {
            let joinFileBtnAdd = $(`${editTitleFileInputsSelector} .join-file-btn`);
            let dataCacheCount = $(`${editTitleFileInputsSelector} [data-cache]`).length;
            let maxFiles = 20; // 上限数を設定（20）

            // 上限を超えていない場合、かつファイル数が上限に達していない場合のみファイル入力欄を追加
            if (dataCacheCount < maxFiles) {
                $(this).closest('.file-input-container').remove();

                // 添付ラベルの番号を振り直す
                renumberSendLabels();

            } else {
                if (dataCacheCount === maxFiles) {
                    $(this).closest('.file-input-container').remove();
                    // 添付ラベルの番号を振り直す
                    renumberSendLabels();

                    if (joinFileBtnAdd.length) {
                        joinFileBtnAdd.remove();
                    }
                    addFileInputAdd(messageId);
                    addJoinFileBtn(messageId);
                }
            }
            if(dataCacheCount === 1) {
                // ファイル設定ボタンを無効化
                $(`#editTitleFileModal-${messageId} #fileImportBtn-${messageId}`).prop('disabled', true);
            }
            if (dataCacheCount === 0) {
                if (joinFileBtnAdd.length) {
                    joinFileBtnAdd.remove();
                }
                addFileInputAdd();
                addJoinFileBtn(messageId);
            }


            // 「結合中」メッセージを更新する関数の呼び出し
            updateModalFooterMessage();

            // "join" フラグがあるか
            updateJoinFileLabel(messageId);
        });


        // ファイルの結合ボタン処理
        $(document).on(`click.editTitleFileModal-${messageId}`, `${editTitleFileInputsSelector} #joinFileId-${messageId}`, function () {
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

            var $modalBody = $(`${editJoinFileModalSelector} #fileCheckboxes-${messageId}`);
            var $modalFooter = $(`${editJoinFileModalSelector} .modal-footer`);
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
        $(document).on(`click.editJoinFileModal-${messageId}`, `${editJoinFileModalSelector} #joinFileBtn-${messageId}`, function() {
            // 結合モーダルのチェックされたファイルパスを取得
            var checkedFileValues = [];
            $(`${editJoinFileModalSelector} #fileCheckboxes-${messageId} input[type="checkbox"]:checked`).each(function() {
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

            $(`${editJoinFileModalSelector}`).modal("hide");
        });

        // 結合モーダルのチェックボックス変更イベント処理
        $(document).on(`change.editJoinFileModal-${messageId}`, `${editJoinFileModalSelector} #fileCheckboxes-${messageId} input[type="checkbox"]`, function() {
            // 選択されたファイルのカウントを更新する関数
            updateJoinFileCount(messageId);
        });


        // 業連ファイル設定ボタンのクリックイベント
        $(document).on(`click.editTitleFileModal-${messageId}`, `.editTitleFileModal #fileImportBtn-${messageId}`, function() {
            saveFileData(messageId);

            // モーダルを閉じる
            $(`#editTitleFileModal-${messageId}`).modal("hide");
        });
    }



    // 店舗編集モーダル
    function initializeShopModal(messageId, org1Id, organizationList, allShopList, targetOrg, mode) {
        if (!$(`#editShopModal-${messageId}`).length) {
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
                                        <div class="editShopInputs">
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
                                                        <input type="hidden" name="organization1_id" value="${org1Id}">
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
            $('body').append(modalHtml);
        }

        // 店舗選択モーダル
        if (!$(`#editShopSelectModal-${messageId}`).length) {
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
                            <div class="modal-body shopSelectInputs">
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
                                <input type="button" class="btn btn-admin pull-left" id="editShopCsvImportBtn-${messageId}" data-toggle="modal" data-target="#editShopImportModal-${messageId}" style="display: none;" value="再インポート">
                                <button type="button" class="btn btn-admin pull-left" id="editShopCancelBtn-${messageId}" data-dismiss="modal">キャンセル</button>
                                <button type="button" class="btn btn-admin pull-right" id="editShopSelectBtn-${messageId}">選択</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
        }

        // 業務連絡csvインポート
        if (!$(`#editShopImportModal-${messageId}`).length) {
            const modalHtml = `
                <div id="editShopImportModal-${messageId}" class="modal fade editShopImportModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                <h4 class="modal-title">店舗選択csvインポート</h4>
                            </div>
                            <div class="modal-body editShopImport">
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
                                                data-target="#editShopImport-${messageId}" value="インポート" disabled>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
        }


        //店舗選択処理
        if (!selectedValuesByMessageId[mode]) {
            selectedValuesByMessageId[mode] = {};
        }
        selectedValuesByMessageId[mode][messageId] = {
            org5: [],
            org4: [],
            org3: [],
            org2: [],
            shops: []
        };

        const shopInputsSelector = `#editShopModal-${messageId} .editShopInputs`;
        const shopSelectInputsSelector = `#editShopSelectModal-${messageId} .shopSelectInputs`;
        const shopImportSelector = `#editShopImportModal-${messageId} .editShopImport`;


        // 店舗選択中の処理
        function updateSelectedStores() {
            const selectedCount = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).length;
            $(`${shopSelectInputsSelector} .storeSelected`).text(`${selectedCount}店舗選択中`);
        }

        // 親チェックボックスの状態を更新
        function updateParentCheckbox(organizationId) {
            const parentCheckbox = document.querySelector(`${shopSelectInputsSelector} input[data-organization-id="${organizationId}"]`);
            if (parentCheckbox) {
                const childCheckboxes = document.querySelectorAll(`${shopSelectInputsSelector} input[data-organization-id="${organizationId}"].shop-checkbox`);
                const allChecked = Array.from(childCheckboxes).every(checkbox => checkbox.checked);
                parentCheckbox.checked = allChecked;
            }
        }

        // 全ての親チェックボックスの状態を更新
        function updateAllParentCheckboxes() {
            const parentCheckboxes = document.querySelectorAll(`${shopSelectInputsSelector} input.org-checkbox`);
            parentCheckboxes.forEach(parentCheckbox => updateParentCheckbox(parentCheckbox.getAttribute('data-organization-id')));
        }

        // 全選択/選択解除のチェックボックスの状態を更新
        function updateSelectAllCheckboxes(messageId) {
            // 組織タブのチェックボックスの状態を更新
            const organizationCheckboxes = $(`${shopSelectInputsSelector} #byOrganization-${messageId} input.shop-checkbox`);
            const selectAllOrganizationCheckbox = $(`${shopSelectInputsSelector} #selectAllOrganization-${messageId}`);
            const allCheckedOrganization = Array.from(organizationCheckboxes).every(checkbox => checkbox.checked);
            selectAllOrganizationCheckbox[0].checked = allCheckedOrganization;

            // 店舗コード順タブのチェックボックスの状態を更新
            const storeCodeCheckboxes = $(`${shopSelectInputsSelector} #byStoreCode-${messageId} input.shop-checkbox`);
            const selectAllStoreCodeCheckbox = $(`${shopSelectInputsSelector} #selectAllStoreCode-${messageId}`);
            const allCheckedStoreCode = Array.from(storeCodeCheckboxes).every(checkbox => checkbox.checked);
            selectAllStoreCodeCheckbox[0].checked = allCheckedStoreCode;
        }

        // 選択された値を変数に格納
        function changeValues(messageId) {
            selectedValuesByMessageId[mode][messageId].org5 = $(`${shopSelectInputsSelector} input[name="organization[org5][]"]:checked`).map(function() { return this.value; }).get();
            selectedValuesByMessageId[mode][messageId].org4 = $(`${shopSelectInputsSelector} input[name="organization[org4][]"]:checked`).map(function() { return this.value; }).get();
            selectedValuesByMessageId[mode][messageId].org3 = $(`${shopSelectInputsSelector} input[name="organization[org3][]"]:checked`).map(function() { return this.value; }).get();
            selectedValuesByMessageId[mode][messageId].org2 = $(`${shopSelectInputsSelector} input[name="organization[org2][]"]:checked`).map(function() { return this.value; }).get();
            selectedValuesByMessageId[mode][messageId].shops = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).map(function() { return this.value; }).get();
        }


        // 初期表示の更新
        updateSelectedStores();
        updateAllParentCheckboxes();
        updateSelectAllCheckboxes(messageId);

        // 編集モードの場合は値を更新
        if(mode === 'edit'){
            changeValues(messageId);
        }


        // 店舗選択中の処理
        if ($(`${shopInputsSelector} #selectStore-${messageId}`).val() === "selected") {
            const selectedCountStore = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).length;
            $(`${shopInputsSelector} #checkStore-${messageId}`).val(`店舗選択(${selectedCountStore}店舗)`);
        }
        // インポート選択中の処理
        if ($(`${shopInputsSelector} #selectCsv-${messageId}`).val() === "selected") {
            const selectedCountStore = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).length;
            $(`${shopInputsSelector} #importCsv-${messageId}`).val(`インポート(${selectedCountStore}店舗)`);
        }


        // チェックボックスの連携を設定
        function syncCheckboxes(storeId, checked) {
            document.querySelectorAll(`${shopSelectInputsSelector} input[data-store-id="${storeId}"]`).forEach(function(checkbox) {
                checkbox.checked = checked;
            });

            // 各親組織のチェックボックスを更新
            const organizationId = document.querySelector(`${shopSelectInputsSelector} input[data-store-id="${storeId}"]`).getAttribute('data-organization-id');
            if (organizationId) {
                updateParentCheckbox(organizationId);
            }
        }

        // チェックボックスの変更イベントリスナーを追加
        $(document).on(`change.editShopSelectModal-${messageId}`, `${shopSelectInputsSelector} input[name="organization_shops[]"], ${shopSelectInputsSelector} input[name="shops_code[]"]`, function() {
            syncCheckboxes($(this).attr('data-store-id'), this.checked);
            updateSelectedStores();
            if ($(this).hasClass('shop-checkbox')) {
                updateParentCheckbox($(this).attr('data-organization-id'));
            }
            updateSelectAllCheckboxes(messageId);
        });

        // 親チェックボックスの変更イベントリスナーを追加
        $(document).on(`change.editShopSelectModal-${messageId}`, `${shopSelectInputsSelector} input.org-checkbox`, function() {
            const organizationId = $(this).attr('data-organization-id');
            const checked = this.checked;
            $(`${shopSelectInputsSelector} input[data-organization-id="${organizationId}"].shop-checkbox`).each(function() {
                this.checked = checked;
                syncCheckboxes($(this).attr('data-store-id'), checked);
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

            updateSelectedStores();
            updateSelectAllCheckboxes(messageId);
        });


        // 組織単位タブの選択中のみ表示
        $(document).on(`change.editShopSelectModal-${messageId}`, `${shopSelectInputsSelector} #selectOrganization-${messageId}`, function () {
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
        $(document).on(`change.editShopSelectModal-${messageId}`, `${shopSelectInputsSelector} #selectStoreCode-${messageId}`, function () {
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
        $(document).on(`change.editShopSelectModal-${messageId}`, `${shopSelectInputsSelector} #selectAllOrganization-${messageId}`, function () {
            const overlay = $('#overlay');
            overlay.show(); // オーバーレイを表示

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
                        syncCheckboxes($(item).attr('data-store-id'), checked);
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

                updateSelectedStores();
                updateSelectAllCheckboxes(messageId);

                // オーバーレイを非表示にする
                overlay.hide();
            }

            requestIdleCallback(processNextBatch); // 最初のアイドル時間で処理を開始
        });

        // 店舗コード順タブの全選択/選択解除
        $(document).on(`change.editShopSelectModal-${messageId}`, `${shopSelectInputsSelector} #selectAllStoreCode-${messageId}`, function () {
            const overlay = $('#overlay');
            overlay.show(); // オーバーレイを表示

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
                        syncCheckboxes($(item).attr('data-store-id'), checked);
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

                updateSelectedStores();
                updateSelectAllCheckboxes(messageId);

                // オーバーレイを非表示にする
                overlay.hide();
            }

            requestIdleCallback(processNextBatch); // 最初のアイドル時間で処理を開始
        });

        // check-selected クラスを削除と選択された値をクリア
        function removeSelectedClass(messageId) {
            // すべてのボタンから check-selected クラスを削除
            $(`${shopInputsSelector} .check-store-list .btn`).removeClass("check-selected");

            // 選択された値をクリア
            if (!selectedValuesByMessageId[mode]) {
                selectedValuesByMessageId[mode] = {};
            }
            selectedValuesByMessageId[mode][messageId] = {
                org5: [],
                org4: [],
                org3: [],
                org2: [],
                shops: []
            };

            // フォームクリア（全店ボタン）
            $(`#selectOrganizationAll-${messageId}`).val("");
            $(`${shopInputsSelector} #selectStore-${messageId}`).val("");
            $(`${shopInputsSelector} #selectCsv-${messageId}`).val("");
        }


        // 全店ボタン処理
        $(document).on(`click.allBtn-${messageId}`, `input[id="checkAll-${messageId}"][name="organizationAll"]`, function() {
            const overlay = $('#overlay');
            overlay.show(); // オーバーレイを表示

            removeSelectedClass(messageId);
            $(`#shopEditBtn-${messageId}`).removeClass("check-selected");
            $(`#selectStore-${messageId}`).val("");
            // 全ての organization_shops[] チェックボックスをチェックする
            $(`${shopSelectInputsSelector} input[name="organization_shops[]"]`).each(function() {
                $(this).prop('checked', true);
                syncCheckboxes($(this).attr('data-store-id'), true);
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
            $(`${shopInputsSelector} #checkStore-${messageId}`).val('店舗選択');
            $(`${shopInputsSelector} #importCsv-${messageId}`).val('インポート');
            // 選択中の店舗数を更新する
            updateSelectedStores();
            // ボタンの見た目を変更する
            $(this).addClass("check-selected");
            // csvインポートボタン変更
            $(`${shopInputsSelector} #importCsv-${messageId}`).attr('data-target', `#editShopImportModal-${messageId}`);

            // オーバーレイを非表示にする
            overlay.hide();
        });


        // 店舗選択モーダル 選択処理
        $(document).on(`click.editShopModal-${messageId}`, `${shopInputsSelector} input[id="checkStore-${messageId}"]`, function() {
            // モーダルタイトル変更
            var storeModalTitle = $(`#editShopSelectModal-${messageId} h4.modal-title`);
            if (storeModalTitle.length) {
                storeModalTitle.html('店舗を選択してください。');
            }

            // キャンセルボタン変更
            $(`#editShopSelectModal-${messageId} #editShopCancelBtn-${messageId}`).show();
            // 再インポートボタン変更
            var csvImportButton = $(`#editShopSelectModal-${messageId} #editShopCsvImportBtn-${messageId}`);
            if (csvImportButton.length) {
                csvImportButton.hide();
            }

            // 元のボタンのセレクターを取得
            var selectCsvButton = $(`#editShopSelectModal-${messageId} #editCsvSelectBtn-${messageId}`);
            // 選択ボタンのセレクターに変更
            if (selectCsvButton) {
                selectCsvButton.attr("id", `editShopSelectBtn-${messageId}`);
            }

            // キャンセルボタン処理
            // 変数から選択された値を取得
            const org5Values = selectedValuesByMessageId[mode][messageId].org5;
            const org4Values = selectedValuesByMessageId[mode][messageId].org4;
            const org3Values = selectedValuesByMessageId[mode][messageId].org3;
            const org2Values = selectedValuesByMessageId[mode][messageId].org2;
            const shopValues = selectedValuesByMessageId[mode][messageId].shops;

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
            updateSelectedStores();
        });

        $(document).on(`click.editShopSelectModal-${messageId}`, `.editShopSelectModal #editShopSelectBtn-${messageId}`, function() {
            removeSelectedClass(messageId);
            // チェックされているチェックボックスの値を変数に格納
            changeValues(messageId);
            // フォームクリア（店舗選択ボタン）
            $(`${shopInputsSelector} #selectStore-${messageId}`).val("selected");
            // インポートボタンをもとに戻す
            $(`${shopInputsSelector} #importCsv-${messageId}`).val('インポート');
            // モーダルを閉じる
            $(`#editShopSelectModal-${messageId}`).modal("hide");
            // check-selected クラスを追加
            $(`${shopInputsSelector} #checkStore-${messageId}`).addClass("check-selected");
            // 店舗選択中の処理
            const selectedCountStore = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).length;
            $(`${shopInputsSelector} .check-store-list input[id="checkStore-${messageId}"]`).val(`店舗選択(${selectedCountStore}店舗)`);
        });


        // CSVインポートモーダル 選択処理
        $(document).on(`click.editShopModal-${messageId}`, `${shopInputsSelector} input[id="importCsv-${messageId}"]`, function() {
            // モーダルタイトル変更
            var storeModalTitle = $(`#editShopSelectModal-${messageId} h4.modal-title`);
            if (storeModalTitle.length) {
                storeModalTitle.html('以下店舗で取り込みました。<br /><small class="text-muted">変更がある場合は、「再取込」もしくは下記で選択しなおしてください</small>');
            }

            // キャンセルボタン変更
            $(`#editShopSelectModal-${messageId} #editShopCancelBtn-${messageId}`).hide();
            // 再インポートボタン変更
            $(`#editShopSelectModal-${messageId} #editShopCsvImportBtn-${messageId}`).show();

            // 元のボタンのセレクターを取得
            var selectStoreButton = $(`#editShopSelectModal-${messageId} #editShopSelectBtn-${messageId}`);
            // 選択ボタンのセレクターに変更
            if (selectStoreButton) {
                selectStoreButton.attr("id", `editCsvSelectBtn-${messageId}`);
            }
        });


        // インポートボタンのクリックイベント
        $(document).on(`click.editShopSelectModal-${messageId}`, `#editShopSelectModal-${messageId} #editShopCsvImportBtn-${messageId}`, function() {
            // モーダルを閉じる
            $(`#editShopSelectModal-${messageId}`).modal("hide");
        });

        $(document).on(`click.editShopSelectModal-${messageId}`, `#editShopSelectModal-${messageId} #editCsvSelectBtn-${messageId}`, function() {
            removeSelectedClass(messageId);
            // チェックされているチェックボックスの値を変数に格納
            changeValues(messageId);
            // フォームクリア（CSVインポートボタン）
            $(`${shopInputsSelector} #selectCsv-${messageId}`).val("selected");
            // モーダルを閉じる
            $(`#editShopSelectModal-${messageId}`).modal("hide");
            // 店舗選択ボタンをもとに戻す
            $(`${shopInputsSelector} #checkStore-${messageId}`).val('店舗選択');
            // check-selected クラスを追加
            $(`${shopInputsSelector} #importCsv-${messageId}`).addClass("check-selected");
            // 店舗選択中の処理
            const selectedCountStore = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).length;
            $(`${shopInputsSelector} .check-store-list input[id="importCsv-${messageId}"]`).val(`インポート(${selectedCountStore}店舗)`);
        });

        $(document).on(`click.editShopModal-${messageId}`, `${shopInputsSelector} #editShopImportSelector-${messageId}`, function() {
            // モーダルを閉じる
            $(`#editShopSelectModal-${messageId}`).modal("hide");

            // ファイルを削除
            $(`${shopImportSelector} input[type="file"]`).val('');
        });

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


        // 業務連絡店舗CSV アップロード
        $(document).on(`change.editShopImportModal-${messageId}`, `${shopImportSelector} input[type=file]` , function(){
            let changeTarget = $(this);
            changeFileName(changeTarget);
        });

        let newMessageJson;
        $(document).on(`change.editShopImportModal-${messageId}`, `${shopImportSelector} input[type="file"]`, function() {
            const overlay = $('#overlay');
            overlay.show(); // オーバーレイを表示

            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            let log_file_name = getNumericDateTime();
            const formData = new FormData();
            formData.append("file", $(this)[0].files[0]);
            formData.append("organization1", $(`${shopImportSelector} input[name="organization1"]`).val())
            formData.append("log_file_name", log_file_name)

            let button = $(`${shopImportSelector} input[type="button"]`);

            var labelForm = $(this).parent();
            var progress = labelForm.parent().find('.progress');
            var progressBar = progress.children(".progress-bar");

            progressBar.hide();
            progressBar.css('width', 0 + '%');
            progress.show();

            let progress_request = true;

            $(`#editShopImportModal-${messageId} .modal-body .alert-danger`).remove();

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

                // オーバーレイを非表示にする
                overlay.hide();

            }).fail(function(jqXHR, textStatus, errorThrown){
                $(`#editShopImportModal-${messageId} .modal-body`).prepend(`
                    <div class="alert alert-danger" style="max-height: 200px; overflow-y: auto;">
                        <ul></ul>
                    </div>
                `);
                const errorUl =  $(`#editShopImportModal-${messageId} .modal-body .alert ul`);
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
                // オーバーレイを非表示にする
                overlay.hide();
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
        $(document).on(`click.editShopImportModal-${messageId}`, `${shopImportSelector} input[type="button"]`, function(e){
            e.preventDefault();

            if(!newMessageJson) {
                $(`#editShopImportModal-${messageId} .modal-body`).prepend(`
                    <div class="alert alert-danger">
                        <ul>
                            <li>ファイルを添付してください</l>
                        </ul>
                    </div>
                `);
                return;
            }
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            const overlay = $('#overlay');
            overlay.show();

            $(`#editShopImportModal-${messageId} .modal-body .alert-danger`).remove();
            $.ajax({
                url: '/admin/message/publish/csv/store/import',
                type: 'post',
                data: JSON.stringify({
                    file_json: newMessageJson,
                    organization1_id: $(`${shopImportSelector} input[name="organization1"]`).val()
                }),
                processData: false,
                contentType: "application/json; charset=utf-8",
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },

            }).done(function(response){
                // console.log(response);
                overlay.hide();

                $(`#editShopImportModal-${messageId}`).modal("hide");
                $(`#editShopSelectModal-${messageId}`).modal("show");

                var allOrg_flg = true;
                var allStore_flg = true;

                // CSVから返ってきたIDを取得
                const csvStoreIds = response.csvStoreIds;

                // organizationItemsの生成
                let organizationItems = '';
                response.organization_list.forEach((organization, index) => {
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
                        shopsHtml = Object.values(shopList).map(shop => `
                            <li class="list-group-item">
                                <div>
                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                        <input type="checkbox" name="organization_shops[]"
                                            data-organization-id="${orgId}"
                                            data-store-id="${shop.id}"
                                            value="${shop.id}"
                                            class="checkCommon mr8 shop-checkbox"
                                            ${csvStoreIds.includes(shop.id) ? 'checked' : ''}
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
                                                ${csvStoreIds.includes(orgId) ? 'checked' : ''}
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

                // list-group-itemの3つ目以降の要素をすべて書き換え
                $(`#editShopSelectModal-${messageId} #byOrganization-${messageId} .list-group-item`).slice(2).remove();
                $(`#editShopSelectModal-${messageId} #byOrganization-${messageId} .list-group`).append(organizationItems);


                // shopItemsの生成
                shopItems = Array.isArray(response.all_shop_list) ? response.all_shop_list.map(shop => `
                    <li class="list-group-item">
                        <div>
                            <label style="font-weight: 500 !important; cursor: pointer;">
                                <input type="checkbox" name="shops_code[]"
                                    data-store-id="${shop.shop_id}"
                                    value="${shop.shop_id}"
                                    class="checkCommon mr8 shop-checkbox"
                                    ${csvStoreIds.includes(shop.shop_id) ? 'checked' : ''}
                                    >
                                    ${shop.shop_code} ${shop.display_name}
                            </label>
                        </div>
                    </li>
                `).join('') : '';

                // list-group-itemの3つ目以降の要素をすべて書き換え
                $(`#editShopSelectModal-${messageId} #byStoreCode-${messageId} .list-group-item`).slice(2).remove();
                $(`#editShopSelectModal-${messageId} #byStoreCode-${messageId} .list-group`).append(shopItems);

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
                updateSelectedStores();
                updateAllParentCheckboxes();

                // モーダルタイトル変更
                var storeModalTitle = $(`#editShopSelectModal-${messageId} h4.modal-title`);
                if (storeModalTitle.length) {
                    storeModalTitle.html('以下店舗で取り込みました。<br /><small class="text-muted">変更がある場合は、「再取込」もしくは下記で選択しなおしてください</small>');
                }

                // キャンセルボタン変更
                $(`#editShopSelectModal-${messageId} #editShopCancelBtn-${messageId}`).hide();
                // 再インポートボタン変更
                $(`#editShopSelectModal-${messageId} #editShopCsvImportBtn-${messageId}`).show();
                // 選択ボタン変更
                $(`#editShopSelectModal-${messageId} #editShopSelectBtn-${messageId}`).attr("id", `editCsvSelectBtn-${messageId}`);
                // csvインポートボタン変更
                $(`${shopInputsSelector} #importCsv-${messageId}`).attr('data-target', `#editShopSelectModal-${messageId}`);

            }).fail(function(jqXHR, textStatus, errorThrown){
                overlay.hide();

                $(`#editShopImportModal-${messageId} .modal-body`).prepend(`
                    <div class="alert alert-danger" style="max-height: 200px; overflow-y: auto;">
                        <ul></ul>
                    </div>
                `);
                // labelForm.parent().find('.text-danger').remove();

                jqXHR.responseJSON.error_message?.forEach((errorMessage)=>{

                    errorMessage['errors'].forEach((error) => {
                        $(`#editShopImportModal-${messageId} .modal-body .alert ul`).append(
                            `<li>${errorMessage['row']}行目：${error}</li>`
                        );
                    })
                })
                if(errorThrown) {
                    $(`#editShopImportModal-${messageId} .modal-body .alert ul`).append(
                        `<li>エラーが発生しました</li>`
                    );
                }
            });
        });


        // 業務連絡店舗CSV エクスポート
        $(document).on(`click.editShopModal-${messageId}`, `${shopInputsSelector} #exportCsv-${messageId}`, function() {
            const overlay = $('#overlay');
            overlay.show(); // オーバーレイを表示

            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const formData = new FormData();

            if (mode === 'new') {
                formData.append("organization1_id", $(`${shopInputsSelector} .check-store-list input[name="organization1_id"]`).val());
            } else {
                formData.append("message_id", $(`${shopInputsSelector} .check-store-list input[name="message_id"]`).val());
            }

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

                // オーバーレイを非表示にする
                overlay.hide();

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

                // オーバーレイを非表示にする
                overlay.hide();
            });
        });


        // 店舗選択設定ボタンのクリックイベント
        $(document).on(`click.editShopModal-${messageId}`, `.editShopModal #shopImportBtn-${messageId}`, function() {
            // 選択された値をクリア
            $(`#checkAll-${messageId}`).removeClass("check-selected");
            $(`#selectOrganizationAll-${messageId}`).val("");

            // フォームクリア（一部ボタン）
            $(`#shopEditBtn-${messageId}`).addClass("check-selected");
            $(`#selectStore-${messageId}`).val("selected");

            // モーダルを閉じる
            $(`#editShopModal-${messageId}`).modal("hide");
        });


        function isEmptyImportFile(modal) {
            return !$(modal).find('input[type="file"]')[0].value
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

    // 日付をフォーマット
    function formatDateWithDay(dateString) {
        // dateStringがnullまたはundefinedの場合はnullを返す
        if (!dateString) {
            return '';
        }
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



    // 追加モード
    // 新しい行のHTMLを作成する関数
    function createNewRow(newMessageId, org1Id, newMessageNumber, categoryList, targetRollList, brandList, organizationList, allShopList) {
        var newRow = `
            <tr data-message_id="${newMessageId}"
                data-organization1_id="${org1Id}"
                class="publish new-modified">

                <td nowrap>
                    <p class="messageNewSaveBtn btn btn-admin" data-message-id="${newMessageId}">保存</p>
                    <p class="messageNewDeleteBtn btn btn-admin" data-message-id="${newMessageId}">取消</p>
                </td>
                <td class="shop-id" data-message-number="${newMessageNumber}">
                    ${newMessageNumber}
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
                            data-toggle="modal" data-target="#editTitleFileModal-${newMessageId}" value="ファイル設定"
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
                <td nowrap>
                    <div class="button-group"></div>
                </td>
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



    // 追加ボタン処理
    $('#messageAddBtn').on('click', function() {
        // 一括登録ボタンを活性化
        $('#messageAllSaveBtn').removeClass('disabled');

        const csrfToken = $('meta[name="csrf-token"]').attr("content");

        // 新しいmessage_id
        let maxMessageId = 0;
        $('#list tbody tr').each(function() {
            const currentId = parseInt($(this).data('message_id'), 10);
            if (currentId > maxMessageId) {
                maxMessageId = currentId;
            }
        });
        let newMessageId = maxMessageId + 1;

        // 新しいメッセージNo
        let maxMessageNumber = 0;
        $('#list tbody tr').each(function() {
            const currentMessageNumber = parseInt($(this).find('[data-message-number]').text(), 10);
            if (currentMessageNumber > maxMessageNumber) {
                maxMessageNumber = currentMessageNumber;
            }
        });
        let newMessageNumber = maxMessageNumber + 1;


        // 削除ボタン処理
        function initializeNewDeleteBtn(newMessageId, newMessageNumber) {
            $(`.messageNewDeleteBtn[data-message-id="${newMessageId}"]`).on('click', function() {
                // ボタンが属する行を削除
                $(this).closest('tr').remove();

                // メッセージ番号を振り直す
                newMessageNumber--;

                // 業連ファイル、店舗を初期化
                delete fileDataByMessageId['new'][newMessageId];
                delete selectedValuesByMessageId['new'][newMessageId];
                // 既存のイベントリスナーを解除
                $(document).off(`.editTitleFileModal-${newMessageId}`);
                $(document).off(`.editJoinFileModal-${newMessageId}`);
                $(document).off(`.editShopModal-${newMessageId}`);
                $(document).off(`.editShopSelectModal-${newMessageId}`);
                $(document).off(`.editShopImportModal-${newMessageId}`);
                $(document).off(`.allBtn-${newMessageId}`);
                // モーダルを削除
                $(`#editTitleFileModal-${newMessageId}`).remove();
                $(`#editJoinFileModal-${newMessageId}`).remove();
                $(`#editShopModal-${newMessageId}`).remove();
                $(`#editShopSelectModal-${newMessageId}`).remove();
                $(`#editShopImportModal-${newMessageId}`).remove();

                // new-modifiedまたはedit-modifiedが1つもない場合は、一括登録ボタンを非活性
                if ($('tr.new-modified').length === 0 && $('tr.edit-modified').length === 0) {
                    $('#messageAllSaveBtn').addClass('disabled');
                }
            });
        }


        // 保存ボタン処理
        function initializeNewSaveBtn(newMessageId, brandList, newMessageNumber) {
            $(`.messageNewSaveBtn[data-message-id="${newMessageId}"]`).on('click', function() {
                const overlay = $('#overlay');
                overlay.show(); // オーバーレイを表示

                const csrfToken = $('meta[name="csrf-token"]').attr("content");
                const formData = new FormData();
                const row = $(`tr[data-message_id="${newMessageId}"]`);

                // 各データを収集
                let categoryId = row.find('select[name="category_id"]').val() || null;
                let emergencyFlg = row.find('input[name="emergency_flg"]').is(':checked') ? 'on' : 'off';
                let title = row.find('input[name="title"]').val() || null;
                let startDatetime = row.find('input[name="start_datetime"]').val() || null;
                let endDatetime = row.find('input[name="end_datetime"]').val() || null;
                startDatetime = cleanAndFormatDate(startDatetime);
                endDatetime = cleanAndFormatDate(endDatetime);
                let tags = row.find('input[name="tag_name[]"]').map(function() { return $(this).val(); }).get() || null;
                let fileName = (fileDataByMessageId['new'][newMessageId]?.fileNames || []).map(name => name || null);
                let filePath = (fileDataByMessageId['new'][newMessageId]?.filePaths || []).map(path => path || null);
                let joinFlg = (fileDataByMessageId['new'][newMessageId]?.joinFlags || []).map(flg => flg || null);
                let targetRoll = row.find('input[name="target_roll[]"]').map(function() { return $(this).val(); }).get() || null;
                let brand = row.find('select[name="brand[]"]').val() === 'all'
                    ? brandList.map(brand => brand.id)
                    : row.find('select[name="brand[]"]').map(function() { return $(this).val(); }).get() || null;
                let organization = [
                    selectedValuesByMessageId['new'][newMessageId]?.org5 || null,
                    selectedValuesByMessageId['new'][newMessageId]?.org4 || null,
                    selectedValuesByMessageId['new'][newMessageId]?.org3 || null,
                    selectedValuesByMessageId['new'][newMessageId]?.org2 || null
                ].map(org => org || null);
                let organizationShops = (selectedValuesByMessageId['new'][newMessageId]?.shops || []).map(shop => shop || null);
                let selectOrganizationAll = row.find('input[name="select_organization[all]"]').val() || null;
                let selectOrganization = {
                    all: selectOrganizationAll === 'selected' ? 'selected' : null,
                    store: selectOrganizationAll !== 'selected' ? 'selected' : null,
                    csv: null
                };

                // バリデーション
                let errors = [];
                if (!title) {
                    errors.push("タイトルは必須項目です");
                } else if (title.length > 20) {
                    errors.push("タイトルは20文字以内で入力してください");
                }
                if (!fileName.length) errors.push("ファイルを添付してください");
                if (!organizationShops.length) errors.push("対象店舗を選択してください");
                if (errors.length > 0) {
                    overlay.hide();
                    let errorContainer = $('#error-messages');
                    if (!errorContainer.length) {
                        errorContainer = $('<div id="error-messages" class="alert alert-danger"><ul></ul></div>');
                        $('.pagenation-top').after(errorContainer);
                    }
                    errorContainer.empty();
                    // エラーメッセージをまとめて追加
                    const errorList = errors.reverse().map(error => `<li class="text-danger">No.${newMessageNumber} : ${error}</li>`).join('');
                    errorContainer.append(errorList);

                    return;
                }

                // 各データをformDataに追加
                formData.append('org1Id', org1Id);
                formData.append('emergency_flg', emergencyFlg);
                formData.append('category_id', categoryId);
                formData.append('title', title);
                formData.append('start_datetime', startDatetime);
                formData.append('end_datetime', endDatetime);
                tags.forEach(tag => formData.append('tag_name[]', tag));
                fileName.forEach(name => formData.append('file_name[]', name));
                filePath.forEach(path => formData.append('file_path[]', path));
                joinFlg.forEach(flg => formData.append('join_flg[]', flg));
                targetRoll.forEach(roll => formData.append('target_roll[]', roll));
                brand.forEach(b => formData.append('brand[]', b));
                organization.forEach(org => formData.append('organization[]', org));
                formData.append('organization_shops', organizationShops);
                Object.keys(selectOrganization).forEach(key => {
                    formData.append(`select_organization[${key}]`, selectOrganization[key]);
                });

                // 保存のリクエストを送信
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
                        overlay.hide(); // オーバーレイを非表示にする

                        try {
                            const response = JSON.parse(jqXHR.responseText);
                            const errors = response.errors;

                            let errorContainer = $('#error-messages');
                            if (!errorContainer.length) {
                                errorContainer = $('<div id="error-messages" class="alert alert-danger"><ul></ul></div>');
                                $('.pagenation-top').after(errorContainer);
                            }

                            errorContainer.empty();

                            for (const field in errors) {
                                if (errors.hasOwnProperty(field)) {
                                    errors[field].forEach(message => {
                                        errorContainer.append(`<li class="text-danger">No.${newMessageNumber} : ${message}</li>`);
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

        // 追加のリクエストを送信
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
                    categoryList = response.category_list;
                    targetRollList = response.target_roll_list;
                    brandList = response.brand_list;
                    allShopList = response.all_shop_list;
                    organizationList = response.organization_list;
                    shopDataFetched = true;

                    // 新しい行を作成する関数を呼び出す
                    createNewRow(newMessageId, org1Id, newMessageNumber, categoryList, targetRollList, brandList, organizationList, allShopList);
                    // 保存ボタン処理
                    initializeNewSaveBtn(newMessageId, brandList, newMessageNumber);
                    // 削除ボタン処理
                    initializeNewDeleteBtn(newMessageId);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error:", errorThrown);
                    console.error("Response Text:", jqXHR.responseText);
                }
            });
        } else {
            createNewRow(newMessageId, org1Id, newMessageNumber, categoryList, targetRollList, brandList, organizationList, allShopList);
            // 保存ボタン処理
            initializeNewSaveBtn(newMessageId, brandList, newMessageNumber);
            // 削除ボタン処理
            initializeNewDeleteBtn(newMessageId);
        }
    });



    // 一括登録ボタン処理
    $('#messageAllSaveBtn').on('click', function() {
        const overlay = $('#overlay');
        overlay.show(); // オーバーレイを表示

        const csrfToken = $('meta[name="csrf-token"]').attr("content");
        const messagesData = [];
        let errors = [];

        // 編集または追加された行をループ
        $($('tr.new-modified, tr.edit-modified').get().reverse()).each(function() {
            const row = $(this);
            let messageId = row.attr('data-message_id');
            let allMessageNumber = parseInt(row.find('[data-message-number]').text(), 10);

            // 各データを収集
            let categoryId = row.find('select[name="category_id"]').val() || null;
            let emergencyFlg = row.find('input[name="emergency_flg"]').is(':checked') ? 'on' : 'off';
            let title = row.find('input[name="title"]').val() || null;
            let startDatetime = row.find('input[name="start_datetime"]').val() || null;
            let endDatetime = row.find('input[name="end_datetime"]').val() || null;
            if (row.hasClass('new-modified')) {
                startDatetime = cleanAndFormatDate(startDatetime);
                endDatetime = cleanAndFormatDate(endDatetime);
            }
            let tags = row.find('input[name="tag_name[]"]').map(function() { return $(this).val(); }).get() || null;
            let contentId = [];
            if (row.hasClass('edit-modified')) {
                contentId = (fileDataByMessageId['edit'][messageId]?.contentIds || []).map(id => id || null);
            }
            let fileName = (fileDataByMessageId[row.hasClass('new-modified') ? 'new' : 'edit'][messageId]?.fileNames || []).map(name => name || null);
            let filePath = (fileDataByMessageId[row.hasClass('new-modified') ? 'new' : 'edit'][messageId]?.filePaths || []).map(path => path || null);
            let joinFlg = (fileDataByMessageId[row.hasClass('new-modified') ? 'new' : 'edit'][messageId]?.joinFlags || []).map(flg => flg || null);
            let targetRoll = row.find('input[name="target_roll[]"]').map(function() { return $(this).val(); }).get() || null;
            let brand = row.find('select[name="brand[]"]').val() === 'all'
                ? brandList.map(brand => brand.id)
                : row.find('select[name="brand[]"]').map(function() { return $(this).val(); }).get() || null;
            let organization = [
                selectedValuesByMessageId[row.hasClass('new-modified') ? 'new' : 'edit'][messageId]?.org5 || null,
                selectedValuesByMessageId[row.hasClass('new-modified') ? 'new' : 'edit'][messageId]?.org4 || null,
                selectedValuesByMessageId[row.hasClass('new-modified') ? 'new' : 'edit'][messageId]?.org3 || null,
                selectedValuesByMessageId[row.hasClass('new-modified') ? 'new' : 'edit'][messageId]?.org2 || null
            ].map(org => org || null);
            let organizationShops = (selectedValuesByMessageId[row.hasClass('new-modified') ? 'new' : 'edit'][messageId]?.shops || [])
                .map(shop => shop || null)
                .filter(Boolean)
                .join(',');
            let selectOrganizationAll = row.find('input[name="select_organization[all]"]').val() || null;
            let selectOrganization = {
                all: selectOrganizationAll === 'selected' ? 'selected' : null,
                store: selectOrganizationAll !== 'selected' ? 'selected' : null,
                csv: null
            };

            // バリデーション
            if (!title) {
                errors.push(`No.${allMessageNumber} : タイトルは必須項目です`);
            } else if (title.length > 20) {
                errors.push(`No.${allMessageNumber} : タイトルは20文字以内で入力してください`);
            }
            if (!fileName.length) errors.push(`No.${allMessageNumber} : ファイルを添付してください`);
            if (!organizationShops.length) errors.push(`No.${allMessageNumber} : 対象店舗を選択してください`);

            // メッセージデータを配列に追加
            if (row.hasClass('new-modified')) {
                messagesData.push({
                    operation: 'new',
                    org1Id: org1Id,
                    emergency_flg: emergencyFlg,
                    category_id: categoryId,
                    title: title,
                    start_datetime: startDatetime,
                    end_datetime: endDatetime,
                    tag_name: tags,
                    file_name: fileName,
                    file_path: filePath,
                    join_flg: joinFlg,
                    target_roll: targetRoll,
                    brand: brand,
                    organization: organization,
                    organization_shops: organizationShops,
                    select_organization: selectOrganization
                });
            } else {
                messagesData.push({
                    operation: 'edit',
                    message_id: messageId,
                    emergency_flg: emergencyFlg,
                    category_id: categoryId,
                    title: title,
                    start_datetime: startDatetime,
                    end_datetime: endDatetime,
                    tag_name: tags,
                    content_id: contentId,
                    file_name: fileName,
                    file_path: filePath,
                    join_flg: joinFlg,
                    target_roll: targetRoll,
                    brand: brand,
                    organization: organization,
                    organization_shops: organizationShops,
                    select_organization: selectOrganization
                });
            }
        });

        // 一括登録が10件以上の場合、送信を防ぐ
        if (messagesData.length > 10) {
            errors.push("一度に登録できる件数は10件までです");
        }

        // エラーがある場合、表示して処理を中止
        if (errors.length > 0) {
            overlay.hide();
            let errorContainer = $('#error-messages');
            if (!errorContainer.length) {
                errorContainer = $('<div id="error-messages" class="alert alert-danger" style="max-height: 300px; overflow-y: auto;"><ul></ul></div>');
                $('.pagenation-top').after(errorContainer);
            }
            errorContainer.empty();
            // エラーメッセージをまとめて追加
            const errorList = errors.reverse().map(error => `<li class="text-danger">${error}</li>`).join('');
            errorContainer.append(errorList);

            return;
        }

        // 一括登録のリクエストを送信
        $.ajax({
            url: `/admin/message/publish/messageAllSaveData`,
            type: "post",
            data: JSON.stringify({ messagesData: messagesData }),
            processData: false,
            contentType: "application/json",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            success: function(response) {
                window.location.href = "/admin/message/publish/";
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error:", errorThrown);
                console.log("Response Text:", jqXHR.responseText);

                overlay.hide(); // オーバーレイを非表示

                try {
                    const response = JSON.parse(jqXHR.responseText);
                    const errors = response.errors;

                    let errorContainer = $('#error-messages');
                    if (!errorContainer.length) {
                        errorContainer = $('<div id="error-messages" class="alert alert-danger" style="max-height: 300px; overflow-y: auto;"><ul></ul></div>');
                        $('.pagenation-top').after(errorContainer);
                    }

                    errorContainer.empty();

                    if (Array.isArray(errors)) {
                        errors.forEach(error => {
                            if (Array.isArray(error.messages)) {
                                error.messages.forEach(message => {
                                    errorContainer.append(`<li class="text-danger">${message}</li>`);
                                });
                            } else {
                                console.error('Error messages is not an array:', error.messages);
                            }
                        });
                    } else {
                        console.error('Errors is not an array:', errors);
                    }
                } catch (e) {
                    console.error("Failed to parse response:", e);
                }
            }
        });
    });



    // 編集モード
    $('.messageEditBtn').each(function() {
        $(this).on('click', function() {
            const overlay = $('#overlay');
            if (overlay.length) {
                console.log('Overlay element found');
                overlay.show(); // オーバーレイを表示
                console.log('Overlay should be visible now');
            } else {
                console.error('Overlay element not found');
            }

            const row = $(this).closest('tr');
            row.addClass('edit-modified');

            // 一括登録ボタンを活性化
            $('#messageAllSaveBtn').removeClass('disabled');

            const csrfToken = $('meta[name="csrf-token"]').attr("content");

            const messageId = row.attr('data-message_id');
            // No
            const shopId = row.find('.shop-id').get(0);
            // 対象業態
            const brandText = row.find('.brand-text').get(0);
            // ラベル
            const emergencyFlgText = row.find('.emergency-flg-text').get(0);
            // カテゴリ
            const categoryText = row.find('.category-text').get(0);
            // タイトル
            const titleText = row.find('.title-text').get(0);
            // 検索タグ
            const tagsTexts = row.find('.tags-text').get();
            const tagsInputMark = row.find('.tags-input-mark').get(0);
            // 掲載期間
            const startDatetimeGroup = row.find('.start-datetime-group').get(0);
            const startDatetimeText = row.find('.start-datetime-text').get(0);
            const endDatetimeGroup = row.find('.end-datetime-group').get(0);
            const endDatetimeText = row.find('.end-datetime-text').get(0);
            // 配信店舗数
            const shopCount = row.find('.shop-count').get(0);
            // 編集、配信停止ボタン
            const buttonGroup = row.find('.button-group').get(0);
            // 編集ボタングループ
            const messageEditBtnGroup = row.find('.message-edit-btn-group').get(0);
            // 編集ボタン
            const messageEditBtn = row.find('.messageEditBtn').get(0);

            // 編集画面処理
            function initializeEditRow(messageId, org1Id, categoryList, targetRollList, brandList, organizationList, allShopList, targetTag, message, messageContents, targetBrand, targetOrg) {
                // No
                const targetRollHtml = targetRollList.map(targetRoll => `
                    <input type="hidden" name="target_roll[]" value="${targetRoll.id}">
                `).join('');
                if (shopId) $(shopId).after(targetRollHtml);

                // 対象業態
                if (brandText) {
                    $(brandText).hide();
                    const allBrandsSelected = targetBrand.length == brandList.length;
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
                    $(brandText).after(brandInputGroupHtml);
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
                    $(emergencyFlgText).hide();
                    $(emergencyFlgText).after(emergencyFlgInputGroupHtml);
                } else {
                    const emergencyFlgColumn = row.find('.label-colum-danger').get(0);
                    $(emergencyFlgColumn).append(emergencyFlgInputGroupHtml);
                }

                // カテゴリ
                const categoryInputGroupHtml = `
                    <div class="category-input-group" style="width: max-content;">
                        <select class="form-control" name="category_id" style="cursor: pointer;">
                            ${categoryList.map(category => `
                                ${(org1Id == 8 || category.id !== 7) ? `
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
                    $(categoryText).hide();
                    $(categoryText).after(categoryInputGroupHtml);
                }

                // タイトル
                const titleInputGroupHtml = `
                    <div class="title-input-group" style="display: flex;">
                        <input type="text" class="form-control" name="title"
                            value="${message.title}" style="border-radius:4px 0 0 4px;">
                        <input type="button" class="btn btn-admin" id="titleFileEditBtn-${messageId}"
                            data-toggle="modal" data-target="#editTitleFileModal-${messageId}" value="ファイル設定"
                            style="border-radius:0 4px 4px 0;">
                    </div>
                `;
                if (titleText) {
                    $(titleText).hide();
                    $(titleText).after(titleInputGroupHtml);
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
                    $(tagsTexts).each(function() {
                        $(this).hide();
                    });
                    $(tagsInputMark).show();
                    $(tagsTexts[0]).after(tagsInputGroupHtml);
                } else {
                    $(tagsInputMark).show();
                    const tagsColumn = row.find('.tags-text-group').get(0);
                    if (tagsColumn) {
                        $(tagsColumn).append(tagsInputGroupHtml);
                    }
                }

                // 掲載期間
                const startDatetimeInputGroupHtml = `
                    <input id="dateFrom-${messageId}" class="form-control datepicker"
                        value="${formatDateWithDay(message.start_datetime)}"
                        name="start_datetime" autocomplete="off">
                `;
                if (startDatetimeText){
                    $(startDatetimeText).hide();
                    $(startDatetimeText).after(startDatetimeInputGroupHtml);
                    $(startDatetimeGroup).css('width', 'max-content');
                }

                const endDatetimeInputGroupHtml = `
                    <input id="dateTo-${messageId}" class="form-control datepicker"
                        value="${formatDateWithDay(message.end_datetime)}"
                        name="end_datetime" autocomplete="off">
                `;
                if (endDatetimeText) {
                    $(endDatetimeText).hide();
                    $(endDatetimeText).after(endDatetimeInputGroupHtml);
                    $(endDatetimeGroup).css('width', 'max-content');
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
                    $(shopCount).hide();
                    $(shopCount).after(shopEditGroupHtml);
                }

                // 編集、配信停止ボタン
                if (buttonGroup) {
                    $(buttonGroup).hide();
                }

                // 編集ボタン
                if (messageEditBtn) {
                    $(messageEditBtn).hide();
                    const saveButtonHtml = `<p class="messageEditSaveBtn btn btn-admin" data-message-id="${messageId}" style="margin-right: 5px;">保存</p>`;
                    $(messageEditBtnGroup).append(saveButtonHtml);
                    const deleteButtonHtml = `<p class="messageEditDeleteBtn btn btn-admin" data-message-id="${messageId}">取消</p>`;
                    $(messageEditBtnGroup).append(deleteButtonHtml);
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
                    const row = $(this).closest('tr');
                    // modifiedクラスを削除
                    row.removeClass('edit-modified');

                    // new-modifiedまたはedit-modifiedが1つもない場合は、一括登録ボタンを非活性
                    if ($('tr.new-modified').length === 0 && $('tr.edit-modified').length === 0) {
                        $('#messageAllSaveBtn').addClass('disabled');
                    }

                    // 業連ファイル、店舗を初期化
                    delete fileDataByMessageId['edit'][messageId];
                    delete selectedValuesByMessageId['edit'][messageId];
                    // 既存のイベントリスナーを解除
                    $(document).off(`.editTitleFileModal-${messageId}`);
                    $(document).off(`.editJoinFileModal-${messageId}`);
                    $(document).off(`.editShopModal-${messageId}`);
                    $(document).off(`.editShopSelectModal-${messageId}`);
                    $(document).off(`.editShopImportModal-${messageId}`);
                    $(document).off(`.allBtn-${messageId}`);
                    // モーダルを削除
                    $(`#editTitleFileModal-${messageId}`).remove();
                    $(`#editJoinFileModal-${messageId}`).remove();
                    $(`#editShopModal-${messageId}`).remove();
                    $(`#editShopSelectModal-${messageId}`).remove();
                    $(`#editShopImportModal-${messageId}`).remove();

                    // 対象業態
                    if (brandText) {
                        $(brandText).show();
                        const brandInputGroup = $(brandText).next();
                        if (brandInputGroup) brandInputGroup.remove();
                    }

                    // ラベル
                    if (emergencyFlgText) {
                        $(emergencyFlgText).show();
                        const emergencyFlgInputGroup = $(emergencyFlgText).next();
                        if (emergencyFlgInputGroup) emergencyFlgInputGroup.remove();
                    } else {
                        const emergencyFlgColumn = row.find('.label-colum-danger');
                        const emergencyFlgInputGroup = emergencyFlgColumn.find('.emergency-flg-input-group');
                        if (emergencyFlgInputGroup) emergencyFlgInputGroup.remove();
                    }

                    // カテゴリ
                    if (categoryText) {
                        $(categoryText).show();
                        const categoryInputGroup = $(categoryText).next();
                        if (categoryInputGroup) categoryInputGroup.remove();
                    }

                    // タイトル
                    if (titleText) {
                        $(titleText).show();
                        const titleInputGroup = $(titleText).next();
                        if (titleInputGroup) titleInputGroup.remove();
                    }

                    // 検索タグ
                    if (tagsTexts.length > 0) {
                        $(tagsTexts).each(function() {
                            $(this).show();
                        });
                        const tagsInputGroup = $(tagsTexts[0]).next();
                        if (tagsInputGroup) tagsInputGroup.remove();
                        $(tagsInputMark).hide();
                    } else {
                        const tagsColumn = row.find('.tags-text-group');
                        const tagsInputGroup = tagsColumn.find('.tags-input-group');
                        if (tagsInputGroup) tagsInputGroup.remove();
                        $(tagsInputMark).hide();
                    }

                    // 掲載期間
                    if (startDatetimeText) {
                        $(startDatetimeText).show();
                        $(startDatetimeGroup).css('width', '');
                        const startDatetimeInputGroup = $(startDatetimeText).next();
                        if (startDatetimeInputGroup) startDatetimeInputGroup.remove();
                    }
                    if (endDatetimeText) {
                        $(endDatetimeText).show();
                        $(endDatetimeGroup).css('width', '');
                        const endDatetimeInputGroup = $(endDatetimeText).next();
                        if (endDatetimeInputGroup) endDatetimeInputGroup.remove();
                    }

                    // 配信店舗数
                    if (shopCount) {
                        $(shopCount).show();
                        const shopEditGroup = $(shopCount).next();
                        if (shopEditGroup) shopEditGroup.remove();
                    }

                    // 編集、配信停止ボタン
                    if (buttonGroup) {
                        $(buttonGroup).show();
                    }

                    // 編集ボタン
                    const messageEditSaveBtn = row.find('.messageEditSaveBtn');
                    const messageEditDeleteBtn = row.find('.messageEditDeleteBtn');
                    if (messageEditSaveBtn.length && messageEditDeleteBtn.length) {
                        messageEditSaveBtn.remove();
                        messageEditDeleteBtn.remove();
                        $(messageEditBtn).show();
                        $(messageEditBtn).css('pointer-events', '');
                    }
                });
            }


            // 保存ボタン処理
            function initializeEditSaveBtn(messageId, brandList) {
                $(`.messageEditSaveBtn[data-message-id="${messageId}"]`).on('click', function() {
                    const overlay = $('#overlay');
                    overlay.show(); // オーバーレイを表示

                    const csrfToken = $('meta[name="csrf-token"]').attr("content");
                    const formData = new FormData();
                    const row = $(`tr[data-message_id="${messageId}"]`);

                    // 各データを収集
                    let editMessageNumber = parseInt(row.find('[data-message-number]').text(), 10) || null;
                    let categoryId = row.find('select[name="category_id"]').val() || null;
                    let emergencyFlg = row.find('input[name="emergency_flg"]').is(':checked') ? 'on' : 'off';
                    let title = row.find('input[name="title"]').val() || null;
                    let startDatetime = row.find('input[name="start_datetime"]').val() || null;
                    let endDatetime = row.find('input[name="end_datetime"]').val() || null;
                    let tags = row.find('input[name="tag_name[]"]').map(function() { return $(this).val(); }).get() || null;
                    let contentId = (fileDataByMessageId['edit'][messageId]?.contentIds || []).map(id => id || null);
                    let fileName = (fileDataByMessageId['edit'][messageId]?.fileNames || []).map(name => name || null);
                    let filePath = (fileDataByMessageId['edit'][messageId]?.filePaths || []).map(path => path || null);
                    let joinFlg = (fileDataByMessageId['edit'][messageId]?.joinFlags || []).map(flg => flg || null);
                    let targetRoll = row.find('input[name="target_roll[]"]').map(function() { return $(this).val(); }).get() || null;
                    let brand = row.find('select[name="brand[]"]').val() === 'all'
                        ? brandList.map(brand => brand.id)
                        : row.find('select[name="brand[]"]').map(function() { return $(this).val(); }).get() || null;
                    let organization = [
                        selectedValuesByMessageId['edit'][messageId]?.org5 || null,
                        selectedValuesByMessageId['edit'][messageId]?.org4 || null,
                        selectedValuesByMessageId['edit'][messageId]?.org3 || null,
                        selectedValuesByMessageId['edit'][messageId]?.org2 || null
                    ].map(org => org || null);
                    let organizationShops = (selectedValuesByMessageId['edit'][messageId]?.shops || []).map(shop => shop || null);
                    let selectOrganizationAll = row.find('input[name="select_organization[all]"]').val() || null;
                    let selectOrganization = {
                        all: selectOrganizationAll === 'selected' ? 'selected' : null,
                        store: selectOrganizationAll !== 'selected' ? 'selected' : null,
                        csv: null
                    };

                    // バリデーション
                    let errors = [];
                    if (!title) {
                        errors.push("タイトルは必須項目です");
                    } else if (title.length > 20) {
                        errors.push("タイトルは20文字以内で入力してください");
                    }
                    if (!fileName.length) errors.push("ファイルを添付してください");
                    if (!organizationShops.length) errors.push("対象店舗を選択してください");
                    if (errors.length > 0) {
                        overlay.hide();
                        let errorContainer = $('#error-messages');
                        if (!errorContainer.length) {
                            errorContainer = $('<div id="error-messages" class="alert alert-danger"><ul></ul></div>');
                            $('.pagenation-top').after(errorContainer);
                        }
                        errorContainer.empty();
                        // エラーメッセージをまとめて追加
                        const errorList = errors.reverse().map(error => `<li class="text-danger">No.${editMessageNumber} : ${error}</li>`).join('');
                        errorContainer.append(errorList);

                        return;
                    }

                    // 各データをformDataに追加
                    formData.append('message_id', messageId);
                    formData.append('emergency_flg', emergencyFlg);
                    formData.append('category_id', categoryId);
                    formData.append('title', title);
                    formData.append('start_datetime', startDatetime);
                    formData.append('end_datetime', endDatetime);
                    tags.forEach(tag => formData.append('tag_name[]', tag));
                    contentId.forEach(id => formData.append('content_id[]', id));
                    fileName.forEach(name => formData.append('file_name[]', name));
                    filePath.forEach(path => formData.append('file_path[]', path));
                    joinFlg.forEach(flg => formData.append('join_flg[]', flg));
                    targetRoll.forEach(roll => formData.append('target_roll[]', roll));
                    brand.forEach(b => formData.append('brand[]', b));
                    organization.forEach(org => formData.append('organization[]', org));
                    formData.append('organization_shops', organizationShops);
                    Object.keys(selectOrganization).forEach(key => {
                        formData.append(`select_organization[${key}]`, selectOrganization[key]);
                    });

                    // 保存のリクエストを送信
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

                            overlay.hide(); // オーバーレイを非表示にする

                            try {
                                const response = JSON.parse(jqXHR.responseText);
                                const errors = response.errors;

                                let errorContainer = $('#error-messages');
                                if (!errorContainer.length) {
                                    errorContainer = $('<div id="error-messages" class="alert alert-danger"><ul></ul></div>');
                                    $('.pagenation-top').after(errorContainer);
                                }

                                errorContainer.empty();

                                for (const field in errors) {
                                    if (errors.hasOwnProperty(field)) {
                                        errors[field].forEach(message => {
                                            errorContainer.append(`<li class="text-danger">No.${editMessageNumber} : ${message}</li>`);
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


            // 編集のリクエストを送信
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

            // オーバーレイを非表示
            overlay.hide();
        });
    });
});
