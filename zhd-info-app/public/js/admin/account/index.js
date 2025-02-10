// ユーザー削除ボタン
$(document).on("click", "#deleteBtn", function (e) {
    e.preventDefault();
    console.log("delete");

    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    var checkedCheckboxes = $(".form-check-input:checked");

    if (checkedCheckboxes.length < 1) {
        alert("ユーザーを選択してください");
        return;
    }

    let checkedValues = checkedCheckboxes
        .map(function () {
            var row = $(this).closest("tr");
            return row.find(".user_id").text();
        })
        .get();

    fetch("/admin/account/delete", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
            user_id: checkedValues,
        }),
    })
        .then((response) => {
            if (response.ok) {
                return response.json();
            } else {
                return response.json().then((data) => {
                    throw new Error(data.message); // エラーメッセージをスロー
                });
            }
        })
        .then((data) => {
            const message = data.message;
            // メッセージの表示や処理を行う
            alert(message);
            window.location.reload();
        })
        .catch((error) => {
            const message = error.message;
            alert(message);
        });
});

$(document).ready(function () {
    // Base64デコード関数を追加
    function base64Decode(str) {
        try {
            return decodeURIComponent(atob(str).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
        } catch (e) {
            console.error('Base64 decode error:', e);
            return '';
        }
    }


    // DS、BL、ARの組織を取得
    $(document).on("change", 'select[name="organization1"]', function (e) {
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        const url = "/admin/account/organization";
        let organization1 = base64Decode(e.target.value);

        let selectDS = $('#selectOrgDS');
        let selectBL = $('#selectOrgBL');
        let selectAR = $('#selectOrgAR');

        let buttonDS = $('#dropdownOrgDS');
        let buttonBL = $('#dropdownOrgBL');
        let buttonAR = $('#dropdownOrgAR');

        let selectedOrgsDS = $('#selectedOrgsDS');
        let selectedOrgsBL = $('#selectedOrgsBL');
        let selectedOrgsAR = $('#selectedOrgsAR');

        $.ajax({
            type: "GET",
            url: url,
            data: {
                organization1: organization1,
            },
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
        })
            .done(function (res) {
                // console.log(res);
                selectDS.empty();
                selectBL.empty();
                selectAR.empty();

                let resDS = res.organization3;
                let resAR = res.organization4;
                let resBL = res.organization5;

                // DSの組織を設定
                if (!resDS.length) {
                    buttonDS.prop("disabled", true);
                    selectedOrgsDS.text('　');
                } else {
                    buttonDS.prop("disabled", false);
                    selectedOrgsDS.text('全て');
                    createDropdownMenu('DS', resDS);
                }

                // BLの組織を設定
                if (!resBL.length) {
                    buttonBL.prop("disabled", true);
                    selectedOrgsBL.text('　');
                } else {
                    buttonBL.prop("disabled", false);
                    selectedOrgsBL.text('全て');
                    createDropdownMenu('BL', resBL);
                }

                // ARの組織を設定
                if (!resAR.length) {
                    buttonAR.prop("disabled", true);
                    selectedOrgsAR.text('　');
                } else {
                    buttonAR.prop("disabled", false);
                    selectedOrgsAR.text('全て');
                    createDropdownMenu('AR', resAR);
                }
            })
            .fail((jqXHR, textStatus, errorThrown) => {
                console.error("Ajax error:", textStatus, errorThrown);
                throw errorThrown;
            });
    });

    // ドロップダウンメニューの生成
    function createDropdownMenu(organization, organizationList) {
        let dropdownMenu = `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAllOrgs${organization}" onclick="toggleAllOrgs('${organization}')">
                <label class="form-check-label" for="selectAllOrgs${organization}" class="custom-label" onclick="event.stopPropagation();">すべて選択/選択解除</label>
            </div>
        `;

        organizationList.forEach(org => {
            dropdownMenu += `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="org[${organization}][]" value="${org.id}" id="org${organization}${org.id}" onchange="updateSelectedOrgs('${organization}')">
                    <label class="form-check-label" for="org${organization}${org.id}" class="custom-label" onclick="event.stopPropagation();">
                        ${org.name}
                    </label>
                </div>
            `;
        });

        $(`#selectOrg${organization}`).append(dropdownMenu);
    }
});


// ドロップダウンメニューを閉じる
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.dropdown-menu');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });
});

// 選択された組織を表示
function updateSelectedOrgs(organization) {
    const selected = [];
    const checkboxes = document.querySelectorAll(`input[name="org[${organization}][]"]:checked`);
    checkboxes.forEach((checkbox) => {
        selected.push(checkbox.nextElementSibling.textContent);
    });
    const dropdownButton = document.getElementById(`dropdownOrg${organization}`);
    if (!dropdownButton.disabled) {
        document.getElementById(`selectedOrgs${organization}`).textContent = selected.length > 0 ? selected.join(', ') : '全て';
    }
    // すべて選択チェックボックスの状態を更新
    const allCheckbox = document.getElementById(`selectAllOrgs${organization}`);
    const allCheckboxes = document.querySelectorAll(`input[name="org[${organization}][]"]`);
    if (allCheckbox) {
        allCheckbox.checked = allCheckboxes.length === checkboxes.length;
    }
}

// すべて選択チェックボックスのクリックイベント
document.addEventListener('DOMContentLoaded', function() {
    // すべての組織に対して選択された組織を表示
    const organizations = ['DS', 'BL', 'AR'];
    organizations.forEach(org => updateSelectedOrgs(org));
});

function toggleAllOrgs(organization) {
    const selectAllCheckbox = document.getElementById(`selectAllOrgs${organization}`);
    const checkboxes = document.querySelectorAll(`input[name="org[${organization}][]"]`);
    if (selectAllCheckbox) {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateSelectedOrgs(organization);
    }
}


$(document).ready(function() {
    // ページロード時に検索条件を削除
    sessionStorage.removeItem('searchParams');

    // クエリパラメータを取得して保存
    const params = new URLSearchParams(window.location.search);
    if (params.toString()) {
        sessionStorage.setItem('searchParams', params.toString());
        // window.history.replaceState({}, '', window.location.pathname); // URLのパラメータを削除
    }

    // ページロード時に検索条件を復元
    const savedParams = sessionStorage.getItem('searchParams');
    if (savedParams) {
        // URLにパラメーターを追加せずにリクエストを実行
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        fetch("/admin/account/save-session-conditions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({ params: savedParams })
        })
        .then(response => response.json())
        .then(data => {
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }


    // 編集モード
    $('.accountEditBtn').on('click', function() {
        const overlay = $('#overlay');
        overlay.show(); // オーバーレイを表示

        // 検索欄非表示
        $('#page-wrapper .input-group').hide();
        $('#page-wrapper .form-group').css('height', '44px');

        // 編集ボタングループ
        const accountEditBtnGroup = $('.account-edit-btn-group');
        // 編集ボタン
        const accountEditBtn = accountEditBtnGroup.find('.accountEditBtn');

        // 編集ボタン
        if (accountEditBtn) {
            $(accountEditBtn).hide();
            const saveButtonHtml = `<p class="accountEditSaveBtn btn btn-admin" style="margin-right: 5px;">登録</p>`;
            $(accountEditBtnGroup).append(saveButtonHtml);
            const deleteButtonHtml = `<p class="accountEditDeleteBtn btn btn-admin">取消</p>`;
            $(accountEditBtnGroup).append(deleteButtonHtml);
        }

        // WowTalk1の閲覧状況通知のすべて選択/解除ボタン
        const WT1StatusBreak = $('.WT1StatusBreak');
        const WT1StatusAllSelectBtn = $('.WT1StatusAllSelectBtn');
        WT1StatusBreak.show();
        WT1StatusAllSelectBtn.show();

        // WowTalk1の業連配信通知のすべて選択/解除ボタン
        const WT1SendBreak = $('.WT1SendBreak');
        const WT1SendAllSelectBtn = $('.WT1SendAllSelectBtn');
        WT1SendBreak.show();
        WT1SendAllSelectBtn.show();

        // WowTalk2の閲覧状況通知のすべて選択/解除ボタン
        const WT2StatusBreak = $('.WT2StatusBreak');
        const WT2StatusAllSelectBtn = $('.WT2StatusAllSelectBtn');
        WT2StatusBreak.show();
        WT2StatusAllSelectBtn.show()

        // WowTalk2の業連配信通知のすべて選択/解除ボタン
        const WT2SendBreak = $('.WT2SendBreak')
        const WT2SendAllSelectBtn = $('.WT2SendAllSelectBtn');
        WT2SendBreak.show();
        WT2SendAllSelectBtn.show();

        $('table#list.account tbody tr').each(function() {
            const row = $(this);
            const shopId = row.data('shop_id');

            // WowTalk1の閲覧状況通知
            const wt1Status = row.find('.WT1_status-select');
            // WowTalk1の業連配信通知
            const wt1Send = row.find('.WT1_send-select');

            let wt1IdFlg = true;
            if (!row.find('.label-WT1_id').text()) {
                wt1IdFlg = false;
            }

            // WowTalk2の閲覧状況通知
            const wt2Status = row.find('.WT2_status-select');
            // WowTalk2の業連配信通知
            const wt2Send = row.find('.WT2_send-select');

            let wt2IdFlg = true;
            if (!row.find('.label-WT2_id').text()) {
                wt2IdFlg = false;
            }

            // WowTalk1の閲覧状況通知
            if (wt1Status) {
                $(wt1Status).hide();
                const wt1StatusSelectGroupHtml = `
                    <div class="wowtalk1-status-select-group">
                        <select class="form-control" name="WT1_status" style="padding: 0px; ${!(wt1IdFlg) ? 'cursor: not-allowed;' : 'cursor: pointer;'}" ${!(wt1IdFlg) ? 'disabled' : ''}>
                            <option value="0">未設定</option>
                            <option value="1" ${wt1Status.attr('value') === 'selected' ? 'selected' : ''}>〇</option>
                        </select>
                    </div>
                `;
                $(wt1Status).after(wt1StatusSelectGroupHtml);
            }

            // WowTalk1の業連配信通知
            if (wt1Send) {
                $(wt1Send).hide();
                const wt1SendSelectGroupHtml = `
                <div class="wowtalk1-send-select-group">
                    <select class="form-control" name="WT1_send" style="padding: 0px; ${!(wt1IdFlg) ? 'cursor: not-allowed;' : 'cursor: pointer;'}" ${!(wt1IdFlg) ? 'disabled' : ''}>
                        <option value="0">未設定</option>
                        <option value="1" ${wt1Send.attr('value') === 'selected' ? 'selected' : ''}>〇</option>
                    </select>
                </div>
            `;
                $(wt1Send).after(wt1SendSelectGroupHtml);
            }

            // WowTalk2の閲覧状況通知
            if (wt2Status) {
                $(wt2Status).hide();
                const wt2StatusSelectGroupHtml = `
                <div class="wowtalk2-status-select-group">
                    <select class="form-control" name="WT2_status" style="padding: 0px; ${!(wt2IdFlg) ? 'cursor: not-allowed;' : 'cursor: pointer;'}" ${!(wt2IdFlg) ? 'disabled' : ''}>
                        <option value="0">未設定</option>
                        <option value="1" ${wt2Status.attr('value') === 'selected' ? 'selected' : ''}>〇</option>
                    </select>
                </div>
            `;
                $(wt2Status).after(wt2StatusSelectGroupHtml);
            }

            // WowTalk2の業連配信通知
            if (wt2Send) {
                $(wt2Send).hide();
                const wt2SendSelectGroupHtml = `
                <div class="wowtalk2-send-select-group">
                    <select class="form-control" name="WT2_send" style="padding: 0px; ${!(wt2IdFlg) ? 'cursor: not-allowed;' : 'cursor: pointer;'}" ${!(wt2IdFlg) ? 'disabled' : ''}>
                        <option value="0">未設定</option>
                        <option value="1" ${wt2Send.attr('value') === 'selected' ? 'selected' : ''}>〇</option>
                    </select>
                </div>
            `;
                $(wt2Send).after(wt2SendSelectGroupHtml);
            }
        });


        // セレクトの変更を監視
        $('table#list.account').on('change', 'select', function() {
            const row = $(this).closest('tr');
            row.addClass('edit-modified');
        });


        // すべて選択/解除ボタン処理
        $('.WT1StatusAllSelectBtn, .WT1SendAllSelectBtn, .WT2StatusAllSelectBtn, .WT2SendAllSelectBtn').on('click', function() {
            const buttonClass = $(this).attr('class').split(' ').find(cls => cls.includes('AllSelectBtn'));
            let targetName;
            let targetId;
            const isActive = $(this).hasClass('active');
            const newValue = isActive ? '0' : '1';

            // ボタンのクラスに基づいて対象のselect要素を決定
            switch(buttonClass) {
                case 'WT1StatusAllSelectBtn':
                    targetName = 'WT1_status';
                    targetId = 'label-WT1_id';
                    break;
                case 'WT1SendAllSelectBtn':
                    targetName = 'WT1_send';
                    targetId = 'label-WT1_id';
                    break;
                case 'WT2StatusAllSelectBtn':
                    targetName = 'WT2_status';
                    targetId = 'label-WT2_id';
                    break;
                case 'WT2SendAllSelectBtn':
                    targetName = 'WT2_send';
                    targetId = 'label-WT2_id';
                    break;
            }

            $('table#list.account tbody tr').each(function() {
                const row = $(this);

                // wowtalkIdが存在するか確認
                const hasId = !!row.find(`.${targetId}`).text();

                if (hasId) {
                    row.addClass('edit-modified');
                    row.find(`select[name="${targetName}"]`).val(newValue);
                }
            });
        });


        // 取消ボタン処理
        $(`.accountEditDeleteBtn`).on('click', function() {
            // 検索欄表示
            $('#page-wrapper .input-group').show();
            $('#page-wrapper .form-group').css('height', '');

            // 編集ボタン
            const accountEditSaveBtn = accountEditBtnGroup.find('.accountEditSaveBtn');
            const accountEditDeleteBtn = accountEditBtnGroup.find('.accountEditDeleteBtn');
            if (accountEditSaveBtn.length && accountEditDeleteBtn.length) {
                accountEditSaveBtn.remove();
                accountEditDeleteBtn.remove();
                $(accountEditBtn).show();
                $(accountEditBtn).css('pointer-events', '');
            }

            // すべて選択/解除ボタン
            const WT1StatusAllSelectBtn = $('.WT1StatusAllSelectBtn');
            WT1StatusAllSelectBtn.hide();
            const WT1SendAllSelectBtn = $('.WT1SendAllSelectBtn');
            WT1SendAllSelectBtn.hide();
            const WT2StatusAllSelectBtn = $('.WT2StatusAllSelectBtn');
            WT2StatusAllSelectBtn.hide();
            const WT2SendAllSelectBtn = $('.WT2SendAllSelectBtn');
            WT2SendAllSelectBtn.hide();

            $('table#list.account tbody tr').each(function() {
                const row = $(this);
                // modifiedクラスを削除
                row.removeClass('edit-modified');

                // WowTalk1の閲覧状況通知
                const wt1Status = row.find('.WT1_status-select');
                // WowTalk1の業連配信通知
                const wt1Send = row.find('.WT1_send-select');
                // WowTalk2の閲覧状況通知
                const wt2Status = row.find('.WT2_status-select');
                // WowTalk2の業連配信通知
                const wt2Send = row.find('.WT2_send-select');

                // WowTalk1の閲覧状況通知
                if (wt1Status) {
                    $(wt1Status).show();
                    const wt1StatusGroup = $(wt1Status).next();
                    if (wt1StatusGroup) wt1StatusGroup.remove();
                }

                // WowTalk1の業連配信通知
                if (wt1Send) {
                    $(wt1Send).show();
                    const wt1SendGroup = $(wt1Send).next();
                    if (wt1SendGroup) wt1SendGroup.remove();
                }

                // WowTalk2の閲覧状況通知
                if (wt2Status) {
                    $(wt2Status).show();
                    const wt2StatusGroup = $(wt2Status).next();
                    if (wt2StatusGroup) wt2StatusGroup.remove();
                }

                // WowTalk2の業連配信通知
                if (wt2Send) {
                    $(wt2Send).show();
                    const wt2SendGroup = $(wt2Send).next();
                    if (wt2SendGroup) wt2SendGroup.remove();
                }
            });
        });


        // 保存ボタン処理
        $(`.accountEditSaveBtn`).on('click', function() {
            const overlay = $('#overlay');
            overlay.show(); // オーバーレイを表示

            const csrfToken = $('meta[name="csrf-token"]').attr("content");
            const wowtalkAlertData = [];

            $($('tr.edit-modified').get().reverse()).each(function() {
                const row = $(this);
                const shopId = row.data('shop_id');

                // 各データを収集
                const rowData = {
                    shop_id: shopId,
                    WT1_status: row.find('select[name="WT1_status"]').val() || 0,
                    WT1_send: row.find('select[name="WT1_send"]').val() || 0,
                    WT2_status: row.find('select[name="WT2_status"]').val() || 0,
                    WT2_send: row.find('select[name="WT2_send"]').val() || 0
                };

                wowtalkAlertData.push(rowData);
            });

            // 保存のリクエストを送信
            $.ajax({
                url: `/admin/account/wowtalkAlertUpdate`,
                type: "post",
                data: {
                    wowtalkAlertData: JSON.stringify(wowtalkAlertData)
                },
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    // リダイレクト先のURLを構築
                    if (savedParams) {
                        window.location.href = "/admin/account?" + savedParams;
                    } else {
                        window.location.href = "/admin/account";
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error:", errorThrown);
                    console.log("Response Text:", jqXHR.responseText);
                    alert('更新中にエラーが発生しました。');

                    overlay.hide(); // オーバーレイを非表示にする
                }
            });
        });

        // オーバーレイを非表示
        overlay.hide();
    });
});
