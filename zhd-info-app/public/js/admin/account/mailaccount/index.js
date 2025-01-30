$(document).ready(function () {
    // DS, BL, AR, 店舗ID, 店舗名の幅を取得
    let th0 = $('table.mail-account thead th[data-column="0"]');
    let th1 = $('table.mail-account thead th[data-column="1"]');
    let th2 = $('table.mail-account thead th[data-column="2"]');
    let th3 = $('table.mail-account thead th[data-column="3"]');
    let th4 = $('table.mail-account thead th[data-column="4"]');

    let th0Width = th0.length ? Math.round(th0.outerWidth()) : 0;
    let th1Width = th1.length ? Math.floor(th1.outerWidth()) : 0;
    let th2Width = th2.length ? Math.floor(th2.outerWidth()) : 0;
    let th3Width = th3.length ? Math.floor(th3.outerWidth()) : 0;
    let th4Width = th4.length ? Math.floor(th4.outerWidth()) : 0;

    let DSWidth = th0Width;
    let BLWidth = DSWidth + th1Width;
    let ARWidth = BLWidth + th2Width;
    let shopIDWidth = ARWidth + th3Width + 1;
    let shopNameWidth = shopIDWidth + th4Width;

    // テーブルの横スクロールの位置取得
    let org1Array = {
        1: "JP",
        2: "BB",
        3: "TAG",
        4: "HY",
        8: "SK",
    };
    let org1 = $('select[name="organization1"]').val();
    // 業態がJP以外の場合、ARWidthを調整
    if (org1Array[org1] !== "JP") {
        ARWidth = BLWidth + th2Width + 1;
    }
    // 業態がJPの場合、shopIDWidthを調整
    if (org1Array[org1] === "JP") {
        shopIDWidth = ARWidth + th3Width;
    }

    // 幅をCSSに適用
    document.documentElement.style.setProperty("--left-2", `${DSWidth}px`);
    document.documentElement.style.setProperty("--left-3", `${BLWidth}px`);
    document.documentElement.style.setProperty("--left-4", `${ARWidth}px`);
    document.documentElement.style.setProperty("--left-5", `${shopIDWidth}px`);
});


// DS、BL、ARの組織を取得
$(document).on("change", 'select[name="organization1"]', function (e) {
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    const url = "/admin/account/organization";
    let organization1 = e.target.value;

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
            <label class="form-check-label" for="selectAllOrgs${organization}" class="custom-label" onclick="event.stopPropagation();">全て選択/選択解除</label>
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
        window.history.replaceState({}, '', window.location.pathname); // URLのパラメータを削除
    }

    // ページロード時に検索条件を復元
    const savedParams = sessionStorage.getItem('searchParams');
    if (savedParams) {
        // URLにパラメーターを追加せずにリクエストを実行
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        fetch("/admin/account/mail/save-session-conditions", {
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

        // DMの業連閲覧状況メール配信のすべて選択/解除ボタン
        const DMStatusBreak = $('.DMStatusBreak');
        const DMStatusAllSelectBtn = $('.DMStatusAllSelectBtn');
        DMStatusBreak.show();
        DMStatusAllSelectBtn.show();

        // BMの業連閲覧状況メール配信のすべて選択/解除ボタン
        const BMStatusBreak = $('.BMStatusBreak');
        const BMStatusAllSelectBtn = $('.BMStatusAllSelectBtn');
        BMStatusBreak.show();
        BMStatusAllSelectBtn.show();

        // AMの業連閲覧状況メール配信のすべて選択/解除ボタン
        const AMStatusBreak = $('.AMStatusBreak');
        const AMStatusAllSelectBtn = $('.AMStatusAllSelectBtn');
        AMStatusBreak.show();
        AMStatusAllSelectBtn.show()

        $('table#list.mail-account tbody tr').each(function() {
            const row = $(this);

            // DMの業連閲覧状況メール配信
            const dmStatus = row.find('.DM_status-select');
            const dmId = row.data('dm_id');
            // BMの業連閲覧状況メール配信
            const bmStatus = row.find('.BM_status-select');
            const bmId = row.data('bm_id');
            // AMの業連閲覧状況メール配信
            const amStatus = row.find('.AM_status-select');
            const amId = row.data('am_id');

            // DMの業連閲覧状況メール配信
            if (dmStatus) {
                $(dmStatus).hide();
                const dmStatusSelectGroupHtml = `
                    <div class="dm-status-select-group">
                        <select class="form-control" name="DM_status" style="padding: 0px; cursor: pointer;" data-dm_id="${dmId}">
                            <option value="0">未設定</option>
                            <option value="1" ${dmStatus.attr('value') === 'selected' ? 'selected' : ''}>〇</option>
                        </select>
                    </div>
                `;
                $(dmStatus).after(dmStatusSelectGroupHtml);
            }

            // BMの業連閲覧状況メール配信
            if (bmStatus) {
                $(bmStatus).hide();
                const bmStatusSelectGroupHtml = `
                <div class="bm-status-select-group">
                    <select class="form-control" name="BM_status" style="padding: 0px; cursor: pointer;" data-bm_id="${bmId}">
                        <option value="0">未設定</option>
                        <option value="1" ${bmStatus.attr('value') === 'selected' ? 'selected' : ''}>〇</option>
                    </select>
                </div>
            `;
                $(bmStatus).after(bmStatusSelectGroupHtml);
            }

            // AMの業連閲覧状況メール配信
            if (amStatus) {
                $(amStatus).hide();
                const amStatusSelectGroupHtml = `
                <div class="am-status-select-group">
                    <select class="form-control" name="AM_status" style="padding: 0px; cursor: pointer;" data-am_id="${amId}">
                        <option value="0">未設定</option>
                        <option value="1" ${amStatus.attr('value') === 'selected' ? 'selected' : ''}>〇</option>
                    </select>
                </div>
            `;
                $(amStatus).after(amStatusSelectGroupHtml);
            }
        });


        // セレクトの変更を監視
        $('table#list.mail-account').on('change', 'select', function() {
            const row = $(this).closest('tr');
            row.addClass('edit-modified');
            const selectedValue = $(this).val();

            // セレクトのIDを取得
            let areaId;
            if ($(this).data('dm_id')) {
                areaId = $(this).data('dm_id');
            }
            if ($(this).data('bm_id')) {
                areaId = $(this).data('bm_id');
            }
            if ($(this).data('am_id')) {
                areaId = $(this).data('am_id');
            }

            // 同じIDの場合、セレクトの値を変更
            $('table#list.mail-account tbody tr').each(function() {
                const currentRow = $(this);
                const currentDmId = currentRow.data('dm_id');
                const currentBmId = currentRow.data('bm_id');
                const currentAmId = currentRow.data('am_id');

                if (currentDmId === areaId) {
                    currentRow.addClass('edit-modified');
                    currentRow.find('select[name="DM_status"]').val(selectedValue);
                }
                if (currentBmId === areaId) {
                    currentRow.addClass('edit-modified');
                    currentRow.find('select[name="BM_status"]').val(selectedValue);
                }
                if (currentAmId === areaId) {
                    currentRow.addClass('edit-modified');
                    currentRow.find('select[name="AM_status"]').val(selectedValue);
                }
            });
        });


        // すべて選択/解除ボタン処理
        $('.DMStatusAllSelectBtn, .BMStatusAllSelectBtn, .AMStatusAllSelectBtn').on('click', function() {
            const buttonClass = $(this).attr('class').split(' ').find(cls => cls.includes('AllSelectBtn'));
            let targetName;
            const isActive = $(this).hasClass('active');
            const newValue = isActive ? '0' : '1';

            // ボタンのクラスに基づいて対象のselect要素を決定
            switch(buttonClass) {
                case 'DMStatusAllSelectBtn':
                    targetName = 'DM_status';
                    break;
                case 'BMStatusAllSelectBtn':
                    targetName = 'BM_status';
                    break;
                case 'AMStatusAllSelectBtn':
                    targetName = 'AM_status';
                    break;
            }

            $('table#list.mail-account tbody tr').each(function() {
                const row = $(this);
                row.addClass('edit-modified');
                row.find(`select[name="${targetName}"]`).val(newValue);
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
            const DMStatusAllSelectBtn = $('.DMStatusAllSelectBtn');
            DMStatusAllSelectBtn.hide();
            const BMStatusAllSelectBtn = $('.BMStatusAllSelectBtn');
            BMStatusAllSelectBtn.hide();
            const AMStatusAllSelectBtn = $('.AMStatusAllSelectBtn');
            AMStatusAllSelectBtn.hide();

            $('table#list.mail-account tbody tr').each(function() {
                const row = $(this);
                // modifiedクラスを削除
                row.removeClass('edit-modified');

                // DMの業連閲覧状況メール配信
                const dmStatus = row.find('.DM_status-select');
                // BMの業連閲覧状況メール配信
                const bmStatus = row.find('.BM_status-select');
                // AMの業連閲覧状況メール配信
                const amStatus = row.find('.AM_status-select');

                // DMの業連閲覧状況メール配信
                if (dmStatus) {
                    $(dmStatus).show();
                    const dmStatusGroup = $(dmStatus).next();
                    if (dmStatusGroup) dmStatusGroup.remove();
                }

                // BMの業連閲覧状況メール配信
                if (bmStatus) {
                    $(bmStatus).show();
                    const bmStatusGroup = $(bmStatus).next();
                    if (bmStatusGroup) bmStatusGroup.remove();
                }

                // AMの業連閲覧状況メール配信
                if (amStatus) {
                    $(amStatus).show();
                    const amStatusGroup = $(amStatus).next();
                    if (amStatusGroup) amStatusGroup.remove();
                }
            });
        });


        // 保存ボタン処理
        $(`.accountEditSaveBtn`).on('click', function() {
            const overlay = $('#overlay');
            overlay.show(); // オーバーレイを表示

            const csrfToken = $('meta[name="csrf-token"]').attr("content");
            const userRoleData = [];

            $($('tr.edit-modified').get().reverse()).each(function() {
                const row = $(this);
                const userId = row.data('user_id');
                const shopId = row.data('shop_id');

                // 各データを収集
                const rowData = {
                    user_id: userId,
                    shop_id: shopId,
                    DM_status: row.find('select[name="DM_status"]').val() || 0,
                    BM_status: row.find('select[name="BM_status"]').val() || 0,
                    AM_status: row.find('select[name="AM_status"]').val() || 0
                };

                userRoleData.push(rowData);
            });

            // 保存のリクエストを送信
            $.ajax({
                url: `/admin/account/mail/userRoleUpdate`,
                type: "post",
                data: {
                    userRoleData: JSON.stringify(userRoleData)
                },
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    // リダイレクト先のURLを構築
                    if (savedParams) {
                        window.location.href = "/admin/account/mail?" + savedParams;
                    } else {
                        window.location.href = "/admin/account/mail";
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
