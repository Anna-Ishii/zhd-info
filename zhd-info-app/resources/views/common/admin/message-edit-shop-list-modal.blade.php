<!-- モーダル：CSV取込 -->
@foreach($message_list as $message)
    <div id="editShopImportModal-{{ $message->id }}" class="modal fade editShopImportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                    <h4 class="modal-title">店舗選択csvインポート</h4>
                </div>
                <div class="modal-body editShopImport" data-message-id="{{ $message->id }}">
                    <div>
                        csvデータを店舗選択モーダルに表示します
                    </div>
                    <form class="form-horizontal">
                        <input type="hidden" name="organization1" value="{{ $organization1_id }}">
                        <input type="hidden" name="message_id" value="{{ $message->id }}">
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
                                <input type="button" id="importButton" class="btn btn-admin" data-toggle="modal"
                                    data-target="#messageStoreModal" value="インポート" disabled>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- モーダル：店舗選択 -->
@foreach($message_list as $message)
    <div id="editShopSelectModal-{{ $message->id }}" class="modal fade editShopSelectModal" tabindex="-1" style="top: -20%;">
        <div class="modal-dialog" style="max-width: 450px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">店舗を選択してください。</h4>
                </div>
                <div class="modal-body shopSelectInputs" data-message-id="{{ $message->id }}">
                    <div class="storeSelected mb-1">0店舗選択中</div>
                    <ul class="nav nav-tabs" role="tablist" style="margin-left: 30px; margin-right: 30px;">
                        <li class="nav-item active" role="presentation">
                            <a class="nav-link" data-toggle="tab" href=".byOrganization"
                                role="tab" aria-controls="byOrganization" aria-selected="true">組織単位</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-toggle="tab" href=".byStoreCode" role="tab"
                                aria-controls="byStoreCode" aria-selected="false">店舗コード順</a>
                        </li>
                    </ul>
                    <div class="tab-content modal-body-scroll"
                        style="max-height: 400px; overflow-y: auto;">
                        <div class="tab-pane fade in active byOrganization" role="tabpanel"
                            aria-labelledby="byOrganization-tab">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <div>
                                        <label style="font-weight: 500 !important; cursor: pointer;">
                                            <input type="checkbox" class="selectOrganization"> 選択中のみ表示
                                        </label>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div>
                                        <label style="font-weight: 500 !important; cursor: pointer;">
                                            <input type="checkbox" class="selectAllOrganization"> 全て選択/選択解除
                                        </label>
                                    </div>
                                </li>
                                @foreach ($organization_list as $index => $organization)
                                    @if (isset($organization['organization5_name']))
                                        <li class="list-group-item">
                                            <div>
                                                <div>
                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                        <input type="checkbox" name="organization[org5][]"
                                                            data-organization-id="{{ $organization['organization5_id'] }}"
                                                            value="{{ $organization['organization5_id'] }}"
                                                            class="checkCommon mr8 org-checkbox"
                                                            {{ in_array($organization['organization5_id'], $message->target_org['org5'], true) ? 'checked' : '' }}
                                                            >
                                                        {{ $organization['organization5_name'] }}
                                                    </label>
                                                    <div id="id-collapse" data-toggle="collapse" aria-expanded="false"
                                                        data-target="#storeCollapse{{ $index }}-{{ $message->id }}"
                                                        style="float: right; cursor: pointer;"></div>
                                                </div>
                                                <ul id="storeCollapse{{ $index }}-{{ $message->id }}" class="list-group mt-2 collapse">
                                                    @foreach ($organization['organization5_shop_list'] as $index => $shop)
                                                        @if (isset($shop['display_name']))
                                                            <li class="list-group-item">
                                                                <div>
                                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                                        <input type="checkbox" name="organization_shops[]"
                                                                            data-organization-id="{{ $organization['organization5_id'] }}"
                                                                            data-store-id="{{ $shop['id'] }}"
                                                                            value="{{ $shop['id'] }}"
                                                                            class="checkCommon mr8 shop-checkbox"
                                                                            {{ in_array($shop['id'], $message->target_org['shops'], true) ? 'checked' : '' }}
                                                                            >
                                                                            {{ $shop['shop_code'] }} {{ $shop['display_name'] }}
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </li>
                                    @elseif (isset($organization['organization4_name']))
                                        <li class="list-group-item">
                                            <div>
                                                <div>
                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                        <input type="checkbox" name="organization[org4][]"
                                                            data-organization-id="{{ $organization['organization4_id'] }}"
                                                            value="{{ $organization['organization4_id'] }}"
                                                            class="checkCommon mr8 org-checkbox"
                                                            {{ in_array($organization['organization4_id'], $message->target_org['org4'], true) ? 'checked' : '' }}
                                                            >
                                                            {{ $organization['organization4_name'] }}
                                                    </label>
                                                    <div id="id-collapse" data-toggle="collapse" aria-expanded="false"
                                                        data-target="#storeCollapse{{ $index }}-{{ $message->id }}"
                                                        style="float: right; cursor: pointer;"></div>
                                                </div>
                                                <ul id="storeCollapse{{ $index }}-{{ $message->id }}" class="list-group mt-2 collapse">
                                                    @foreach ($organization['organization4_shop_list'] as $index => $shop)
                                                        @if (isset($shop['display_name']))
                                                            <li class="list-group-item">
                                                                <div>
                                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                                        <input type="checkbox" name="organization_shops[]"
                                                                            data-organization-id="{{ $organization['organization4_id'] }}"
                                                                            data-store-id="{{ $shop['id'] }}"
                                                                            value="{{ $shop['id'] }}"
                                                                            class="checkCommon mr8 shop-checkbox"
                                                                            {{ in_array($shop['id'], $message->target_org['shops'], true) ? 'checked' : '' }}
                                                                            >
                                                                            {{ $shop['shop_code'] }} {{ $shop['display_name'] }}
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </li>
                                    @elseif (isset($organization['organization3_name']))
                                        <li class="list-group-item">
                                            <div>
                                                <div>
                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                        <input type="checkbox" name="organization[org3][]"
                                                            data-organization-id="{{ $organization['organization3_id'] }}"
                                                            value="{{ $organization['organization3_id'] }}"
                                                            class="checkCommon mr8 org-checkbox"
                                                            {{ in_array($organization['organization3_id'], $message->target_org['org3'], true) ? 'checked' : '' }}
                                                            >
                                                            {{ $organization['organization3_name'] }}直轄
                                                    </label>
                                                    <div id="id-collapse" data-toggle="collapse" aria-expanded="false"
                                                        data-target="#storeCollapse{{ $index }}-{{ $message->id }}"
                                                        style="float: right; cursor: pointer;"></div>
                                                </div>
                                                <ul id="storeCollapse{{ $index }}-{{ $message->id }}" class="list-group mt-2 collapse">
                                                    @foreach ($organization['organization3_shop_list'] as $index => $shop)
                                                        @if (isset($shop['display_name']))
                                                            <li class="list-group-item">
                                                                <div>
                                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                                        <input type="checkbox" name="organization_shops[]"
                                                                            data-organization-id="{{ $organization['organization3_id'] }}"
                                                                            data-store-id="{{ $shop['id'] }}"
                                                                            value="{{ $shop['id'] }}"
                                                                            class="checkCommon mr8 shop-checkbox"
                                                                            {{ in_array($shop['id'], $message->target_org['shops'], true) ? 'checked' : '' }}
                                                                            >
                                                                            {{ $shop['shop_code'] }} {{ $shop['display_name'] }}
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </li>
                                    @elseif (isset($organization['organization2_name']))
                                        <li class="list-group-item">
                                            <div>
                                                <div>
                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                        <input type="checkbox" name="organization[org2][]"
                                                            data-organization-id="{{ $organization['organization2_id'] }}"
                                                            value="{{ $organization['organization2_id'] }}"
                                                            class="checkCommon mr8 org-checkbox"
                                                            {{ in_array($organization['organization2_id'], $message->target_org['org2'], true) ? 'checked' : '' }}
                                                            >
                                                            {{ $organization['organization2_name'] }}直轄
                                                    </label>
                                                    <div id="id-collapse" data-toggle="collapse" aria-expanded="false"
                                                        data-target="#storeCollapse{{ $index }}-{{ $message->id }}"
                                                        style="float: right; cursor: pointer;"></div>
                                                </div>
                                                <ul id="storeCollapse{{ $index }}-{{ $message->id }}" class="list-group mt-2 collapse">
                                                    @foreach ($organization['organization2_shop_list'] as $index => $shop)
                                                        @if (isset($shop['display_name']))
                                                            <li class="list-group-item">
                                                                <div>
                                                                    <label style="font-weight: 500 !important; cursor: pointer;">
                                                                        <input type="checkbox" name="organization_shops[]"
                                                                            data-organization-id="{{ $organization['organization2_id'] }}"
                                                                            data-store-id="{{ $shop['id'] }}"
                                                                            value="{{ $shop['id'] }}"
                                                                            class="checkCommon mr8 shop-checkbox"
                                                                            {{ in_array($shop['id'], $message->target_org['shops'], true) ? 'checked' : '' }}
                                                                            >
                                                                            {{ $shop['shop_code'] }} {{ $shop['display_name'] }}
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        <div class="tab-pane fade byStoreCode" role="tabpanel" aria-labelledby="byStoreCode-tab">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <div>
                                        <label style="font-weight: 500 !important; cursor: pointer;">
                                            <input type="checkbox" class="selectStoreCode"> 選択中のみ表示
                                        </label>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div>
                                        <label style="font-weight: 500 !important; cursor: pointer;">
                                            <input type="checkbox" class="selectAllStoreCode"> 全て選択/選択解除
                                        </label>
                                    </div>
                                </li>
                                @foreach ($all_shop_list as $index => $shop_list)
                                    @if (isset($shop_list['shop_code']))
                                        <li class="list-group-item">
                                            <div>
                                                <label style="font-weight: 500 !important; cursor: pointer;">
                                                    <input type="checkbox" name="shops_code[]"
                                                        data-store-id="{{ $shop_list['shop_id'] }}"
                                                        value="{{ $shop_list['shop_id'] }}"
                                                        class="checkCommon mr8 shop-checkbox"
                                                        {{ in_array($shop_list['shop_id'], $message->target_org['shops'], true) ? 'checked' : '' }}
                                                        >
                                                        {{ $shop_list['shop_code'] }} {{ $shop_list['display_name'] }}
                                                </label>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin pull-left" id="editShopCancelBtn-{{ $message->id }}" data-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-admin pull-right" id="editShopSelectBtn-{{ $message->id }}">選択</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
    $(document).ready(function(){
        // メッセージIDごとに選択された値を保持するオブジェクト
        const selectedValuesByMessageId = {};

        $('.editShopSelectModal .shopSelectInputs').each(function() {
            let messageId = $(this).data('message-id');

            // 初期化: メッセージIDごとに選択された値を格納するオブジェクトを作成
            selectedValuesByMessageId[messageId] = {
                org5: [],
                org4: [],
                org3: [],
                org2: [],
                shops: []
            };

            const editShopInputsSelector = `.editShopModal .editShopInputs[data-message-id="${messageId}"]`;
            const shopSelectInputsSelector = `.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"]`;

            // 初期表示の更新
            updateSelectedStores(messageId);
            updateAllParentCheckboxes(messageId);
            updateSelectAllCheckboxes(messageId);
            changeValues(messageId);

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
                if ($(`${shopSelectInputsSelector} .selectOrganization`).is(':checked')) {
                    $(`${shopSelectInputsSelector} .byOrganization li`).show();
                    $(`${shopSelectInputsSelector} .selectOrganization`).prop('checked', false);
                }
                if ($(`${shopSelectInputsSelector} .selectStoreCode`).is(':checked')) {
                    $(`${shopSelectInputsSelector} .byStoreCode li`).show();
                    $(`${shopSelectInputsSelector} .selectStoreCode`).prop('checked', false);
                }

                updateSelectedStores(messageId);
                updateSelectAllCheckboxes(messageId);
            });

            // 組織単位タブの選択中のみ表示
            $(document).on("change", `${shopSelectInputsSelector} .selectOrganization`, function () {
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
                    $(`${shopSelectInputsSelector} input[name^="organization[org]"]`).each(function () {
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
                    $(`${shopSelectInputsSelector} input[name^="organization[org]"]`).each(function () {
                        const parentListItem = $(this).closest("li");
                        parentListItem.show();
                        const collapseElement = parentListItem.find('.collapse');
                        collapseElement.collapse('hide');
                    });
                }
            });

            // 店舗コード順タブの選択中のみ表示
            $(document).on("change", `${shopSelectInputsSelector} .selectStoreCode`, function () {
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
            $(document).on("change", `${shopSelectInputsSelector} .selectAllOrganization`, function () {
                var overlay = document.getElementById('overlay');
                overlay.style.display = 'block';  // オーバーレイを表示

                const checked = this.checked;
                const items = $(`${shopSelectInputsSelector} .byOrganization input[type="checkbox"]`).toArray(); // 組織のチェックボックス
                let index = 0;

                // 全選択/選択解除の処理
                function processNextBatch(deadline) {
                    while (index < items.length && deadline.timeRemaining() > 0) {
                        const item = items[index];
                        if ($(item).attr("id") !== "selectOrganization") {
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
                    if ($(`${shopSelectInputsSelector} .selectOrganization`).is(':checked')) {
                        $(`${shopSelectInputsSelector} .byOrganization li`).show();
                        $(`${shopSelectInputsSelector} .selectOrganization`).prop('checked', false);
                    }
                    if ($(`${shopSelectInputsSelector} .selectStoreCode`).is(':checked')) {
                        $(`${shopSelectInputsSelector} .byStoreCode li`).show();
                        $(`${shopSelectInputsSelector} .selectStoreCode`).prop('checked', false);
                    }

                    // 親要素の状態をリセット
                    if (!checked) {
                        $(`${shopSelectInputsSelector} input[name^="organization[org]"]`).each(function () {
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
            $(document).on("change", `${shopSelectInputsSelector} .selectAllStoreCode`, function () {
                var overlay = document.getElementById('overlay');
                overlay.style.display = 'block';  // オーバーレイを表示

                const checked = this.checked;
                const items = $(`${shopSelectInputsSelector} .byStoreCode input[type="checkbox"]`).toArray(); // 店舗コードのチェックボックス
                let index = 0;

                // 全選択/選択解除の処理
                function processNextBatch(deadline) {
                    while (index < items.length && deadline.timeRemaining() > 0) {
                        const item = items[index];
                        if ($(item).attr("id") !== "selectStoreCode") {
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
                    if ($(`${shopSelectInputsSelector} .selectOrganization`).is(':checked')) {
                        $(`${shopSelectInputsSelector} .byOrganization li`).show();
                        $(`${shopSelectInputsSelector} .selectOrganization`).prop('checked', false);
                    }
                    if ($(`${shopSelectInputsSelector} .selectStoreCode`).is(':checked')) {
                        $(`${shopSelectInputsSelector} .byStoreCode li`).show();
                        $(`${shopSelectInputsSelector} .selectStoreCode`).prop('checked', false);
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
                $(`${shopSelectInputsSelector} .selectAllOrganization`).each(function() {
                    $(this).prop('checked', true);
                });
                $(`${shopSelectInputsSelector} .selectAllStoreCode`).each(function() {
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
                $(`${editShopInputsSelector} #importCsv-${messageId}`).attr('data-target', '#messageStoreImportModal');
            });

            // 店舗選択モーダル 選択処理
            $(document).on('click', `${editShopInputsSelector} input[id="checkStore-${messageId}"]`, function() {
                // モーダルタイトル変更
                var storeModalTitle = $(`${shopSelectInputsSelector} h4.modal-title`);
                if (storeModalTitle.length) {
                    storeModalTitle.html('店舗を選択してください。');
                }

                // 元のボタンのセレクターを取得して、新しいボタンのセレクターに変更
                var selectCsvButton = $(`#selectCsvBtn-${messageId}`);
                if (selectCsvButton.length) {
                    selectCsvButton.attr("id", `editShopSelectBtn-${messageId}`);
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
                $(`${shopSelectInputsSelector} .selectAllOrganization`).prop('checked', allOrg_flg);
                $(`${shopSelectInputsSelector} .selectAllStoreCode`).prop('checked', allStore_flg);

                // 店舗選択中の処理
                updateSelectedStores(messageId);
            });

            $(document).on('click', `.editShopSelectModal #editShopSelectBtn-${messageId}`, function() {
                console.log(messageId);
                removeSelectedClass(messageId);
                // チェックされているチェックボックスの値を変数に格納
                changeValues(messageId);
                // フォームクリア（店舗選択ボタン）
                $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] #selectStore-${messageId}`).val("selected");
                // インポートボタンをもとに戻す
                $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] #importCsv-${messageId}`).val('インポート');
                // モーダルを閉じる
                $(`#editShopSelectModal${messageId}`).modal("hide");
                // check-selected クラスを追加
                $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] #checkStore-${messageId}`).addClass("check-selected");
                // csvインポートボタン変更
                $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] #importCsv-${messageId}`).attr('data-target', `#editShopSelectModal-${messageId}`);
                // 店舗選択中の処理
                const selectedCountStore = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] input[name="organization_shops[]"]:checked`).length;
                $(`.editShopModal .editShopInputs[data-message-id="${messageId}"] .check-store-list input[id="checkStore-${messageId}"]`).val(`店舗選択(${selectedCountStore}店舗)`);
            });

            // モーダルが閉じられる際にchangeValuesを実行
            $(`#editShopSelectModal-${messageId}`).on('hidden.bs.modal', function () {
                changeValues(messageId);
            });

            // 選択された値を変数に格納
            function changeValues(messageId) {
                selectedValuesByMessageId[messageId].org5 = $(`${shopSelectInputsSelector} input[name="organization[org5][]"]:checked`).map(function() { return this.value; }).get();
                selectedValuesByMessageId[messageId].org4 = $(`${shopSelectInputsSelector} input[name="organization[org4][]"]:checked`).map(function() { return this.value; }).get();
                selectedValuesByMessageId[messageId].org3 = $(`${shopSelectInputsSelector} input[name="organization[org3][]"]:checked`).map(function() { return this.value; }).get();
                selectedValuesByMessageId[messageId].org2 = $(`${shopSelectInputsSelector} input[name="organization[org2][]"]:checked`).map(function() { return this.value; }).get();
                selectedValuesByMessageId[messageId].shops = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).map(function() { return this.value; }).get();
            }

            // CSVインポートモーダル 選択処理
            $(document).on('click', `.editShopImportModal #importCsv-${messageId}`, function() {
                // 元のボタンのセレクターを取得
                var selectStoreButton = $(`.editShopImportModal [data-message-id="${messageId}"] #editShopSelectBtn-${messageId}`);
                // 新しいボタンのセレクターに変更
                if (selectStoreButton) {
                    selectStoreButton.attr("id", `editShopSelectBtn-${messageId}`);
                }
                // キャンセルボタン非表示
                $(`.editShopImportModal #editShopCancelBtn-${messageId}`).hide();
                // csv再インポートボタン追加
                if (!$(`${shopSelectInputsSelector} #editShopCsvImportBtn-${messageId}`).length) {
                    $(`${shopSelectInputsSelector} .modal-footer`).append(`<input type="button" class="btn btn-admin pull-left" id="editShopCsvImportBtn-${messageId}" data-toggle="modal" data-target="#editShopImportModal${messageId}" value="再インポート">`);
                }
            });

            // インポートボタンのクリックイベント
            $(document).on('click', `${shopSelectInputsSelector} .editShopCsvImportBtn-${messageId}`, function() {
                // モーダルを閉じる
                $(`#editShopImportModal-${messageId}`).modal("hide");
            });

            $(document).on('click', `${shopSelectInputsSelector} #selectCsvBtn${messageId}`, function() {
                removeSelectedClass(messageId);
                // チェックされているチェックボックスの値を変数に格納
                changeValues(messageId);
                // フォームクリア（CSVインポートボタン）
                $(`${editShopInputsSelector} #selectCsv-${messageId}`).val("selected");
                // モーダルを閉じる
                $(`#editShopImportModal-${messageId}`).modal("hide");
                // 店舗選択ボタンをもとに戻す
                $(`${editShopInputsSelector} #checkStore-${messageId}`).val('店舗選択');
                // check-selected クラスを追加
                $(`${editShopInputsSelector} #importCsv-${messageId}`).addClass("check-selected");
                // 店舗選択中の処理
                const selectedCountStore = $(`${shopSelectInputsSelector} input[name="organization_shops[]"]:checked`).length;
                $(`${editShopInputsSelector} .check-store-list input[id="importCsv-${messageId}"]`).val(`インポート(${selectedCountStore}店舗)`);
            });

            $(document).on('click', `#csvImportBtn-${messageId}`, function() {
                // モーダルを閉じる
                $(`#editShopImportModal-${messageId}`).modal("hide");

                // ファイルを削除
                $(`#editShopImportModal-${messageId} input[type="file"]`).val('');
            });

            // 業務連絡店舗CSV アップロード
            $(document).on('change' , `#editShopImportModal-${messageId} input[type=file]` , function(){
                let changeTarget = $(this);
                changeFileName(changeTarget);
            });

            let newMessageJson;
            $(document).on('change', `#editShopImportModal-${messageId} input[type="file"]`, function() {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                let log_file_name = getNumericDateTime();
                let formData = new FormData();
                formData.append("file", $(this)[0].files[0]);
                formData.append("organization1", $(`#editShopImportModal-${messageId} input[name="organization1"]`).val())
                formData.append("log_file_name", log_file_name)

                let button = $(`#editShopImportModal-${messageId} input[type="button"]`);

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

                }).fail(function(jqXHR, textStatus, errorThrown){
                    $(`#editShopImportModal-${messageId} .modal-body`).prepend(`
                        <div class="alert alert-danger">
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
            $(document).on('click', `#editShopImportModal-${messageId} input[type="button"]`, function(e){
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
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                var overlay = document.getElementById('overlay');
                overlay.style.display = 'block';

                $(`#editShopImportModal-${messageId} .modal-body .alert-danger`).remove();
                $.ajax({
                    url: '/admin/message/publish/csv/store/import',
                    type: 'post',
                    data: JSON.stringify({
                        file_json: newMessageJson,
                        organization1_id: $(`#editShopImportModal-${messageId} input[name="organization1"]`).val()
                    }),
                    processData: false,
                    contentType: "application/json; charset=utf-8",
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },

                }).done(function(response){
                    // console.log(response);
                    overlay.style.display = 'none';

                    $(`#messageStoreModal-${messageId}`).html(response);

                    var allOrg_flg = true;
                    var allStore_flg = true;

                    // organization_shops のチェック状態を確認
                    $('input[name="organization_shops[]"]').each(function() {
                        if (!$(this).prop('checked')) {
                            allOrg_flg = false;
                        }
                    });
                    $('#selectAllOrganization').prop('checked', allOrg_flg);

                    // shops_code のチェック状態を確認
                    $('input[name="shops_code[]"]').each(function() {
                        if (!$(this).prop('checked')) {
                            allStore_flg = false;
                        }
                    });
                    $('#selectAllStoreCode').prop('checked', allStore_flg);

                    // 初期表示の更新
                    updateSelectedStores(messageId);
                    updateAllParentCheckboxes(messageId);

                    // csvインポートボタン変更
                    $(`${editShopInputsSelector} #importCsv-${messageId}`).attr('data-target', `#messageStoreModal-${messageId}`);

                }).fail(function(jqXHR, textStatus, errorThrown){
                    overlay.style.display = 'none';

                    $(`#messageStoreImportModal-${messageId} .modal-body`).prepend(`
                        <div class="alert alert-danger">
                            <ul></ul>
                        </div>
                    `);
                    // labelForm.parent().find('.text-danger').remove();

                    jqXHR.responseJSON.error_message?.forEach((errorMessage)=>{

                        errorMessage['errors'].forEach((error) => {
                            $(`#messageStoreImportModal-${messageId} .modal-body .alert ul`).append(
                                `<li>${errorMessage['row']}行目：${error}</li>`
                            );
                        })
                    })
                    if(errorThrown) {
                        $(`#messageStoreImportModal-${messageId} .modal-body .alert ul`).append(
                            `<li>エラーが発生しました</li>`
                        );
                    }
                });


                // 業務連絡店舗CSV エクスポート
                $(document).on('click', `#exportCsv-${messageId}`, function() {
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');
                    let formData = new FormData();
                    formData.append("message_id", $(`.check-store-list input[name="message_id"]`).val());

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
            });
        })

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
            const organizationCheckboxes = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] .byOrganization input.shop-checkbox`);
            const selectAllOrganizationCheckbox = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] .selectAllOrganization`);
            const allCheckedOrganization = Array.from(organizationCheckboxes).every(checkbox => checkbox.checked);
            selectAllOrganizationCheckbox.checked = allCheckedOrganization;

            // 店舗コード順タブのチェックボックスの状態を更新
            const storeCodeCheckboxes = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] .byStoreCode input.shop-checkbox`);
            const selectAllStoreCodeCheckbox = $(`.editShopSelectModal .shopSelectInputs[data-message-id="${messageId}"] .selectAllStoreCode`);
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
    });
</script>
