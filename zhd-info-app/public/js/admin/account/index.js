$(document).ready(function () {
    // DS, BL, AR, 店舗ID, 店舗名の幅を取得
    let th0 = $('table.account thead th[data-column="0"]');
    let th1 = $('table.account thead th[data-column="1"]');
    let th2 = $('table.account thead th[data-column="2"]');
    let th3 = $('table.account thead th[data-column="3"]');
    let th4 = $('table.account thead th[data-column="4"]');

    let th0Width = th0.length ? Math.round(th0.outerWidth()) : 0;
    let th1Width = th1.length ? Math.floor(th1.outerWidth()) : 0;
    let th2Width = th2.length ? Math.floor(th2.outerWidth()) : 0;
    let th3Width = th3.length ? Math.floor(th3.outerWidth()) : 0;
    let th4Width = th4.length ? Math.floor(th4.outerWidth()) : 0;

    let DSWidth = th0Width;
    let BLWidth = DSWidth + th1Width;
    let ARWidth = BLWidth + th2Width;
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
