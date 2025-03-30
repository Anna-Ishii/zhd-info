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


    // // DS, BL, AR, 店舗ID, 店舗名の幅を取得
    // let th0 = $('table.mail-account thead th[data-column="0"]');
    // let th1 = $('table.mail-account thead th[data-column="1"]');
    // let th2 = $('table.mail-account thead th[data-column="2"]');
    // let th3 = $('table.mail-account thead th[data-column="3"]');
    // let th4 = $('table.mail-account thead th[data-column="4"]');

    // let th0Width = th0.length ? Math.round(th0.outerWidth()) : 0;
    // let th1Width = th1.length ? Math.floor(th1.outerWidth()) : 0;
    // let th2Width = th2.length ? Math.floor(th2.outerWidth()) : 0;
    // let th3Width = th3.length ? Math.floor(th3.outerWidth()) : 0;
    // let th4Width = th4.length ? Math.floor(th4.outerWidth()) : 0;

    // let DSWidth = th0Width;
    // let BLWidth = DSWidth + th1Width;
    // let ARWidth = BLWidth + th2Width;
    // let shopIDWidth = ARWidth + th3Width + 1;
    // let shopNameWidth = shopIDWidth + th4Width;

    // // テーブルの横スクロールの位置取得
    // let org1Array = {
    //     1: "JP",
    //     2: "BB",
    //     3: "TAG",
    //     4: "HY",
    //     8: "SK",
    // };
    // let org1 = base64Decode($('select[name="organization1"]').val());
    // // 業態がJP以外の場合、ARWidthを調整
    // if (org1Array[org1] !== "JP") {
    //     ARWidth = BLWidth + th2Width + 1;
    // }
    // // 業態がJPの場合、shopIDWidthを調整
    // if (org1Array[org1] === "JP") {
    //     shopIDWidth = ARWidth + th3Width;
    // }

    // // 幅をCSSに適用
    // document.documentElement.style.setProperty("--left-2", `${DSWidth}px`);
    // document.documentElement.style.setProperty("--left-3", `${BLWidth}px`);
    // document.documentElement.style.setProperty("--left-4", `${ARWidth}px`);
    // document.documentElement.style.setProperty("--left-5", `${shopIDWidth}px`);
});

$(document).ready(function() {
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

        // すべて選択/解除ボタン
        const statusBreak = $('.statusBreak');
        const statusAllSelectBtn = $('.statusAllSelectBtn');
        statusBreak.show();
        statusAllSelectBtn.show();

        $('table#list.mail-admin-account tbody tr').each(function() {
            const row = $(this);

            // メール配信
            const id = row.data('id');
            const status = row.find('.status-select');
            let statusFlg = true;
            if (!row.find('.label-status').text()) {
                statusFlg = false;
            }

            // メール配信
            if (status) {
                $(status).hide();
                const statusSelectGroupHtml = `
                    <div class="status-select-group">
                        <select class="form-control" name="status" style="padding: 0px; ${!(statusFlg) ? 'cursor: not-allowed;' : 'cursor: pointer;'}" data-id="${id}" ${!(statusFlg) ? 'disabled' : ''}>
                            <option value="0">未設定</option>
                            <option value="1" ${status.attr('value') === 'selected' ? 'selected' : ''}>〇</option>
                        </select>
                    </div>
                `;
                $(status).after(statusSelectGroupHtml);
            }
        });

        // セレクトの変更を監視
        $('table#list.mail-admin-account').on('change', 'select', function() {
            const row = $(this).closest('tr');
            row.addClass('edit-modified');
            const selectedValue = $(this).val();

            // セレクトのIDを取得
            let areaId;
            if ($(this).data('id')) {
                areaId = $(this).data('id');
            }

            // 同じIDの場合、セレクトの値を変更
            $('table#list.mail-admin-account tbody tr').each(function() {
                const currentRow = $(this);

                const currentId = currentRow.data('id');
                const hasNumber = !!currentRow.find('.label-employee_number').text();
                const hasMail = !!currentRow.find('.label-email').text();

                if (currentId === areaId && hasNumber && hasMail) {
                    currentRow.addClass('edit-modified');
                    currentRow.find('select[name="status"]').val(selectedValue);
                }
            });
        });


        // すべて選択/解除ボタン処理
        $('.statusAllSelectBtn').on('click', function() {
            const buttonClass = $(this).attr('class').split(' ').find(cls => cls.includes('AllSelectBtn'));
            let targetName;
            let targetId;
            const isActive = $(this).hasClass('active');
            const newValue = isActive ? '0' : '1';

            // ボタンのクラスに基づいて対象のselect要素を決定
            switch(buttonClass) {
                case 'statusAllSelectBtn':
                    targetName = 'status';
                    targetId = 'label-employee_number';
                    break;
            }

            $('table#list.mail-admin-account tbody tr').each(function() {
                const row = $(this);

                // numberのみ存在するか確認
                const hasNumber = !!row.find(`.${targetId}`).text();

                if (hasNumber) {
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
            const statusAllSelectBtn = $('.statusAllSelectBtn');
            statusAllSelectBtn.hide();

            $('table#list.mail-admin-account tbody tr').each(function() {
                const row = $(this);
                // modifiedクラスを削除
                row.removeClass('edit-modified');

                // 業連閲覧状況メール配信
                const status = row.find('.status-select');

                // 業連閲覧状況メール配信
                if (status) {
                    $(status).show();
                    const statusGroup = $(status).next();
                    if (statusGroup) statusGroup.remove();
                }
            });
        });


        // 保存ボタン処理
        $(`.accountEditSaveBtn`).on('click', function() {
            const overlay = $('#overlay');
            overlay.show(); // オーバーレイを表示

            const csrfToken = $('meta[name="csrf-token"]').attr("content");
            const adminAccountData = [];

            $($('tr.edit-modified').get().reverse()).each(function() {
                const row = $(this);
                const userId = row.data('id');

                // 各データを収集
                const rowData = {
                    id: userId,
                    status: row.find('select[name="status"]').val() || 0
                };

                adminAccountData.push(rowData);
            });

            // 保存のリクエストを送信
            $.ajax({
                url: `/admin/account/adminmail/adminAccountUpdate`,
                type: "post",
                data: {
                    adminAccountData: JSON.stringify(adminAccountData)
                },
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    // リダイレクト先のURLを構築
                    if (savedParams) {
                        window.location.href = "/admin/account/adminmail?" + savedParams;
                    } else {
                        window.location.href = "/admin/account/adminmail";
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
