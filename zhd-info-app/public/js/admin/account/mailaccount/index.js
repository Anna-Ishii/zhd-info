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
    // let shopIDWidth = ARWidth + th3Width + 1;
    let shopIDWidth = ARWidth + th3Width;
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

    // 幅をCSSに適用
    document.documentElement.style.setProperty("--left-2", `${DSWidth}px`);
    document.documentElement.style.setProperty("--left-3", `${BLWidth}px`);
    document.documentElement.style.setProperty("--left-4", `${ARWidth}px`);
    document.documentElement.style.setProperty("--left-5", `${shopIDWidth}px`);
});


// 業態を選択したら、その業態に所属する組織を取得する
$(document).on("change", 'select[name="organization1"]', function (e) {
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    const url = "/admin/account/organization";
    let organization1 = e.target.value;

    let selectDS = $('select[name="org[DS]"]');
    let selectBL = $('select[name="org[BL]"]');
    let selectAR = $('select[name="org[AR]"]');

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

            if (!resDS.length) {
                selectDS.prop("disabled", true);
            } else {
                selectDS.prop("disabled", false);
                let option1 = document.createElement("option");
                option1.value = "";
                option1.textContent = "全て";
                option1.selected = true;
                selectDS.append(option1);

                let option;
                resDS.forEach((value, index) => {
                    option += `
						<option value="${value.id}">${value.name}</option>
					`;
                });
                selectDS.append(option);
            }

            if (!resBL.length) {
                selectBL.prop("disabled", true);
            } else {
                selectBL.prop("disabled", false);
                let option1 = document.createElement("option");
                option1.value = "";
                option1.textContent = "全て";
                option1.selected = true;
                selectBL.append(option1);

                let option;
                resBL.forEach((value, index) => {
                    option += `
						<option value="${value.id}">${value.name}</option>
					`;
                });
                selectBL.append(option);
            }

            if (!resAR.length) {
                selectAR.prop("disabled", true);
            } else {
                selectAR.prop("disabled", false);
                let option1 = document.createElement("option");
                option1.value = "";
                option1.textContent = "全て";
                option1.selected = true;
                selectAR.append(option1);

                let option;
                resAR.forEach((value, index) => {
                    option += `
						<option value="${value.id}">${value.name}</option>
					`;
                });
                selectAR.append(option);
            }
        })
        .fail((jqXHR, textStatus, errorThrown) => {
            console.error("Ajax error:", textStatus, errorThrown);
            throw errorThrown; // エラーを再スローして呼び出し元で処理できるようにする
        });
    // console.log(e.target.value);
});


// 編集モード
$(document).ready(function() {
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
            // BMの業連閲覧状況メール配信
            const bmStatus = row.find('.BM_status-select');
            // AMの業連閲覧状況メール配信
            const amStatus = row.find('.AM_status-select');

            // DMの業連閲覧状況メール配信
            if (dmStatus) {
                $(dmStatus).hide();
                const dmStatusSelectGroupHtml = `
                    <div class="dm-status-select-group">
                        <select class="form-control" name="DM_status" style="padding: 0px; cursor: pointer;">
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
                    <select class="form-control" name="BM_status" style="padding: 0px; cursor: pointer;">
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
                    <select class="form-control" name="AM_status" style="padding: 0px; cursor: pointer;">
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
                    // console.log(response);
                    // 現在のURLから検索パラメータを取得
                    const currentUrl = new URL(window.location.href);
                    const searchParams = currentUrl.searchParams;

                    // リダイレクト先のURLを構築
                    let redirectUrl = "/admin/account/mail";
                    if (searchParams.toString()) {
                        redirectUrl += "?" + searchParams.toString();
                    }

                    window.location.href = redirectUrl;
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
