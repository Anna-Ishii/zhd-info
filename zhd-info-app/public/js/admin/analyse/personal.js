$(window).on("load", function () {
    let d = new Date();
    /* datetimepicker */
    $.datetimepicker.setLocale("ja");

    $("#publishDateFrom").datetimepicker({
        format: "Y/m/d(D)",
        timepicker: false,
        onShow: function (ct) {
            this.setOptions({
                maxDate: jQuery("#publishDateTo").val()
                    ? jQuery("#publishDateTo").val()
                    : false,
            });
        },
        defaultDate: d,
    });
    $("#publishDateTo").datetimepicker({
        format: "Y/m/d(D)",
        timepicker: false,
        onShow: function (ct) {
            this.setOptions({
                minDate: jQuery("#publishDateFrom").val()
                    ? jQuery("#publishDateFrom").val()
                    : false,
            });
        },
        defaultDate: d,
    });
});

$(document).ready(function () {
    // テーブルソートの初期化
    $("#table").tablesorter({
        headers: {
            0: {
                sorter: false,
            },
            1: {
                sorter: false,
            },
            2: {
                sorter: false,
            },
            3: {
                sorter: false,
            },
            4: {
                sorter: false,
            },
            // '.head2': {
            // 	sorter: "float",
            // },
        },
    });

    // 横スクロールの位置取得
    // DS,BL,ARの幅を取得
    let th0 = $('table.personal thead th[data-column="0"]');
    let th1 = $('table.personal thead th[data-column="1"]');
    let th2 = $('table.personal thead th[data-column="2"]');

    let th0Width = th0.length ? Math.round(th0.outerWidth()) : 0;
    let th1Width = th1.length ? Math.floor(th1.outerWidth()) : 0;
    let th2Width = th2.length ? Math.floor(th2.outerWidth()) : 0;

    let DSWidth = th0Width;
    let BLWidth = DSWidth + th1Width;
    let ARWidth = BLWidth + th2Width;

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

    // 店舗名の幅を取得
    let tableOffset = Math.round(
        $(".personal.table.table-bordered").offset().left
    );
    let shopName = $("table.personal tbody tr td.shop_name");
    let shopNameLeft = shopName.length
        ? Math.round(shopName.offset().left - tableOffset)
        : 0;

    // 幅をCSSに適用
    document.documentElement.style.setProperty("--left-2", `${DSWidth}px`);
    document.documentElement.style.setProperty("--left-3", `${BLWidth}px`);
    document.documentElement.style.setProperty("--left-4", `${ARWidth}px`);
    document.documentElement.style.setProperty("--left-5", `${shopNameLeft}px`);
});

//
// 店舗のポプアップのjs
//

/* 汎用モーダル処理 */
function modalAnim(e) {
    let modalTarget = $(".modal2[data-modal-target=" + e + "]");
    if (modalTarget.length) {
        $(".modalBg").show();
        modalTarget.show();
    }
}

/* モーダル内の未読・既読表示変更 */
$(document).on("click", ".readUser__switch__item", function () {
    let chkTab = $(this).data("readuser-target");
    $(".readUser__switch__item").removeClass("isSelected");
    $(".readUser__list").hide();
    $(this).addClass("isSelected");
    $(".readUser__list[data-readuser-target=" + chkTab + "]").show();

    /* 所属未所属をチェック */
    let chkTabBelongs = $(".readUser__sort")
        .find(".isSelected")
        .data("readuser-belong");
    let users = $(".readUser__list:visible").find(".readUser__list__item");
    users.each(function () {
        if ($(this).data("readuser-belong") == chkTabBelongs) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

    if (chkTab == 1) {
        $('button[data-readuser-belong="1"]').text(
            `所属(${modalNotReadCrewBelong})`
        );
        $('button[data-readuser-belong="2"]').text(
            `未所属(${modalNotReadCrewNotBelong})`
        );
    } else {
        $('button[data-readuser-belong="1"]').text(
            `所属(${modalReadCrewBelong})`
        );
        $('button[data-readuser-belong="2"]').text(
            `未所属(${modalReadCrewNotBelong})`
        );
    }
});

$(document).on("click", ".modal__close, .modalBg", function (e) {
    if ($(this).hasClass("modalBg") && $(e).closest(".modal2")) {
        $(".modalBg").hide();
        $(".modal2").hide();
    } else {
        $(".modalBg").hide();
        $(".modal2").hide();
    }
});

/* モーダル内の未読・既読表示変更 */
$(document).on("click", ".readUser__switch__item", function () {
    let chkTab = $(this).data("readuser-target");
    $(".readUser__switch__item").removeClass("isSelected");
    $(".readUser__list").hide();
    $(this).addClass("isSelected");
    $(".readUser__list[data-readuser-target=" + chkTab + "]").show();

    /* 所属未所属をチェック */
    let chkTabBelongs = $(".readUser__sort")
        .find(".isSelected")
        .data("readuser-belong");
    let users = $(".readUser__list:visible").find(".readUser__list__item");
    users.each(function () {
        if ($(this).data("readuser-belong") == chkTabBelongs) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

    if (chkTab == 1) {
        $('button[data-readuser-belong="1"]').text(
            `所属(${modalNotReadCrewBelong})`
        );
        $('button[data-readuser-belong="2"]').text(
            `未所属(${modalNotReadCrewNotBelong})`
        );
    } else {
        $('button[data-readuser-belong="1"]').text(
            `所属(${modalReadCrewBelong})`
        );
        $('button[data-readuser-belong="2"]').text(
            `未所属(${modalReadCrewNotBelong})`
        );
    }
});

/* 所属・未所属表示変更 */
function userSort(e) {
    let chkTabBelongs = $(e).data("readuser-belong");
    $(".readUser__sort").find("button").removeClass("isSelected");
    $(e).addClass("isSelected");

    let users = $(".readUser__list:visible").find(".readUser__list__item");
    users.each(function () {
        if ($(this).data("readuser-belong") == chkTabBelongs) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}
$(document).on("click", ".readUser__sort button", function () {
    let targegt = $(this);
    userSort(targegt);
});

$(document).on("click", ".readEdit__list__head", function () {
    $(this).toggleClass("isOpen");
});

var modalReadCrew = 0; // 既読数
var modalNotReadCrew = 0; // 未読数
var modalReadCrewBelong = 0; // 所属・既読数
var modalReadCrewNotBelong = 0; // 未所属・既読数
var modalNotReadCrewBelong = 0; // 所属・未読数
var modalNotReadCrewNotBelong = 0; // 未所属・未読数

$(document).on("click", '.view_rate[data-view-type="shops"]', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    var message = $(this).parent("td").attr("data-message");
    var shop = $(this).parent("td").attr("data-shop");

    $.ajax({
        type: "GET",
        url: "/admin/analyse/personal/shop-message",
        data: {
            shop: shop,
            message: message,
        },
        dataType: "json",
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
    })
        .done(function (res) {
            let crews = res.crews;
            getCrewsData = res.crews;

            let readUserListTarget1Element = $(
                '.readUser__list[data-readuser-target="1"]'
            );
            let readUserListTarget2Element = $(
                '.readUser__list[data-readuser-target="2"]'
            );

            readUserListTarget1Element.empty();
            readUserListTarget2Element.empty();
            modalReadCrew = 0;
            modalNotReadCrew = 0;
            modalReadCrewBelong = 0;
            modalReadCrewNotBelong = 0;
            modalNotReadCrewBelong = 0;
            modalNotReadCrewNotBelong = 0;

            crews.forEach((value, index, array) => {
                let belong = value.new_face == 0 ? 1 : 2;
                if (value.readed == 0) {
                    modalNotReadCrew++;
                    if (value.new_face == 0) {
                        modalNotReadCrewBelong++;
                        readUserListTarget1Element.append(`
						<li class="readUser__list__item" data-readuser-belong="1">${value.part_code} ${value.name}</li>
					`);
                    } else {
                        modalNotReadCrewNotBelong++;
                        readUserListTarget1Element.append(`
						<li class="readUser__list__item" data-readuser-belong="2">${value.part_code} ${value.name}</li>
					`);
                    }
                } else {
                    modalReadCrew++;
                    if (value.new_face == 0) {
                        modalReadCrewBelong++;
                        readUserListTarget2Element.append(`
						<li class="readUser__list__item" data-readuser-belong="1">
							<div>
								<div>${value.part_code} ${value.name}</div>
								<div>${value.readed_at}</div>
							</div>
						</li>
					`);
                    } else {
                        modalReadCrewNotBelong++;
                        readUserListTarget2Element.append(`
						<li class="readUser__list__item" data-readuser-belong="2">
							<div>
								<div>${value.part_code} ${value.name}</div>
								<div>${value.readed_at}</div>
							</div>
						</li>
					`);
                    }
                }
            });

            $(".readUser__switch__item[data-readuser-target='1']").text(
                `未読(${modalNotReadCrew})`
            );
            $(".readUser__switch__item[data-readuser-target='2']").text(
                `既読(${modalReadCrew})`
            );

            let chkTab = $(".readUser__switch__item.isSelected").data(
                "readuser-target"
            );
            if (chkTab == 1) {
                $('button[data-readuser-belong="1"]').text(
                    `所属(${modalNotReadCrewBelong})`
                );
                $('button[data-readuser-belong="2"]').text(
                    `未所属(${modalNotReadCrewNotBelong})`
                );
            } else {
                $('button[data-readuser-belong="1"]').text(
                    `所属(${modalReadCrewBelong})`
                );
                $('button[data-readuser-belong="2"]').text(
                    `未所属(${modalReadCrewNotBelong})`
                );
            }

            modalAnim("read");

            if (target == "read") {
                let target = $(".readUser__sort").find(".isSelected");
                userSort(target);
            }
        })
        .fail(function (error) {
            console.log(error);
        });
});

$(document).on("click", '.view_rate[data-view-type="orgs"]', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    var message = $(this).parent("td").attr("data-message");
    var org_type = $(this).parent("td").attr("data-org-type");
    var org_id = $(this).parent("td").attr("data-org-id");

    $.ajax({
        type: "GET",
        url: "/admin/analyse/personal/org-message",
        data: {
            message: message,
            org_type: org_type,
            org_id: org_id,
        },
        dataType: "json",
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
    })
        .done(function (res) {
            let crews = res.crews;
            getCrewsData = res.crews;

            let readUserListTarget1Element = $(
                '.readUser__list[data-readuser-target="1"]'
            );
            let readUserListTarget2Element = $(
                '.readUser__list[data-readuser-target="2"]'
            );

            readUserListTarget1Element.empty();
            readUserListTarget2Element.empty();
            modalReadCrew = 0;
            modalNotReadCrew = 0;
            modalReadCrewBelong = 0;
            modalReadCrewNotBelong = 0;
            modalNotReadCrewBelong = 0;
            modalNotReadCrewNotBelong = 0;

            crews.forEach((value, index, array) => {
                let belong = value.new_face == 0 ? 1 : 2;
                if (value.readed == 0) {
                    modalNotReadCrew++;
                    if (value.new_face == 0) {
                        modalNotReadCrewBelong++;
                        readUserListTarget1Element.append(`
						<li class="readUser__list__item" data-readuser-belong="1">${value.part_code} ${value.name}</li>
					`);
                    } else {
                        modalNotReadCrewNotBelong++;
                        readUserListTarget1Element.append(`
						<li class="readUser__list__item" data-readuser-belong="2">${value.part_code} ${value.name}</li>
					`);
                    }
                } else {
                    modalReadCrew++;
                    if (value.new_face == 0) {
                        modalReadCrewBelong++;
                        readUserListTarget2Element.append(`
						<li class="readUser__list__item" data-readuser-belong="1">
							<div>
								<div>${value.part_code} ${value.name}</div>
								<div>${value.readed_at}</div>
							</div>
						</li>
					`);
                    } else {
                        modalReadCrewNotBelong++;
                        readUserListTarget2Element.append(`
						<li class="readUser__list__item" data-readuser-belong="2">
							<div>
								<div>${value.part_code} ${value.name}</div>
								<div>${value.readed_at}</div>
							</div>
						</li>
					`);
                    }
                }
            });

            $(".readUser__switch__item[data-readuser-target='1']").text(
                `未読(${modalNotReadCrew})`
            );
            $(".readUser__switch__item[data-readuser-target='2']").text(
                `既読(${modalReadCrew})`
            );

            let chkTab = $(".readUser__switch__item.isSelected").data(
                "readuser-target"
            );
            if (chkTab == 1) {
                $('button[data-readuser-belong="1"]').text(
                    `所属(${modalNotReadCrewBelong})`
                );
                $('button[data-readuser-belong="2"]').text(
                    `未所属(${modalNotReadCrewNotBelong})`
                );
            } else {
                $('button[data-readuser-belong="1"]').text(
                    `所属(${modalReadCrewBelong})`
                );
                $('button[data-readuser-belong="2"]').text(
                    `未所属(${modalReadCrewNotBelong})`
                );
            }

            modalAnim("read");

            if (target == "read") {
                let target = $(".readUser__sort").find(".isSelected");
                userSort(target);
            }
        })
        .fail(function (error) {
            console.log(error);
        });
});

// DS、BL、ARの組織を取得
$(document).on("change", 'select[name="organization1"]', function (e) {
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    const url = "/admin/analyse/personal/organization";
    let organization1 = e.target.value;

    let selectDS = $("#selectOrgDS");
    let selectBL = $("#selectOrgBL");
    let selectAR = $("#selectOrgAR");

    let buttonDS = $("#dropdownOrgDS");
    let buttonBL = $("#dropdownOrgBL");
    let buttonAR = $("#dropdownOrgAR");

    let selectedOrgsDS = $("#selectedOrgsDS");
    let selectedOrgsBL = $("#selectedOrgsBL");
    let selectedOrgsAR = $("#selectedOrgsAR");

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
                selectedOrgsDS.text("　");
            } else {
                buttonDS.prop("disabled", false);
                selectedOrgsDS.text("全て");
                createDropdownMenu("DS", resDS);
            }

            // BLの組織を設定
            if (!resBL.length) {
                buttonBL.prop("disabled", true);
                selectedOrgsBL.text("　");
            } else {
                buttonBL.prop("disabled", false);
                selectedOrgsBL.text("全て");
                createDropdownMenu("BL", resBL);
            }

            // ARの組織を設定
            if (!resAR.length) {
                buttonAR.prop("disabled", true);
                selectedOrgsAR.text("　");
            } else {
                buttonAR.prop("disabled", false);
                selectedOrgsAR.text("全て");
                createDropdownMenu("AR", resAR);
            }
        })
        .fail((jqXHR, textStatus, errorThrown) => {
            console.error("Ajax error:", textStatus, errorThrown);
            throw errorThrown;
        });
});

// CSVエクスポートボタンのクリックイベントにオーバーレイ表示
$(document).ready(function () {
    $(".exportBtn").on("click", function (e) {
        e.preventDefault(); // デフォルトのリンク動作を防ぐ
        var overlay = document.getElementById("overlay");
        overlay.style.display = "block";

        // エクスポート処理を実行
        var exportUrl = $(this).attr("href");
        var fileName = $(this).data("filename");
        fetch(exportUrl)
            .then((response) => {
                if (response.ok) {
                    return response.blob();
                } else {
                    throw new Error("エクスポートに失敗しました");
                }
            })
            .then((blob) => {
                overlay.style.display = "none";

                // ダウンロードを開始
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement("a");
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
            })
            .catch((error) => {
                alert(error.message);
                overlay.style.display = "none";
            });

        // ページがリロードされる前にオーバーレイを非表示にする
        window.onbeforeunload = function () {
            overlay.style.display = "none";
        };
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

    organizationList.forEach((org) => {
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
document.addEventListener("click", function (event) {
    const dropdowns = document.querySelectorAll(".dropdown-menu");
    dropdowns.forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove("show");
        }
    });
});

// 選択された組織を表示
function updateSelectedOrgs(organization) {
    const selected = [];
    const checkboxes = document.querySelectorAll(
        `input[name="org[${organization}][]"]:checked`
    );
    checkboxes.forEach((checkbox) => {
        selected.push(checkbox.nextElementSibling.textContent);
    });
    const dropdownButton = document.getElementById(
        `dropdownOrg${organization}`
    );
    if (!dropdownButton.disabled) {
        document.getElementById(`selectedOrgs${organization}`).textContent =
            selected.length > 0 ? selected.join(", ") : "全て";
    }
    // すべて選択チェックボックスの状態を更新
    const allCheckbox = document.getElementById(`selectAllOrgs${organization}`);
    const allCheckboxes = document.querySelectorAll(
        `input[name="org[${organization}][]"]`
    );
    if (allCheckbox) {
        allCheckbox.checked = allCheckboxes.length === checkboxes.length;
    }
}

// すべて選択チェックボックスのクリックイベント
document.addEventListener("DOMContentLoaded", function () {
    // すべての組織に対して選択された組織を表示
    const organizations = ["DS", "BL", "AR"];
    organizations.forEach((org) => updateSelectedOrgs(org));
});

function toggleAllOrgs(organization) {
    const selectAllCheckbox = document.getElementById(
        `selectAllOrgs${organization}`
    );
    const checkboxes = document.querySelectorAll(
        `input[name="org[${organization}][]"]`
    );
    if (selectAllCheckbox) {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateSelectedOrgs(organization);
    }
}

// 検索条件を保存
$(document).ready(function () {
    $(".saveSearchBtn").on("click", function (e) {
        e.preventDefault();
        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        var overlay = document.getElementById("overlay");
        overlay.style.display = "block";

        // URLを構築
        let baseUrl = "/admin/analyse/personal";
        let params = new URLSearchParams({
            organization1: document.querySelector('select[name="organization1"]').value,
        });
        const orgDS = Array.from(
            document.querySelectorAll('input[name="org[DS][]"]:checked')
        ).map((input) => input.value);
        orgDS.forEach((org) => {
            params.append("org[DS][]", org);
        });
        const orgBL = Array.from(
            document.querySelectorAll('input[name="org[BL][]"]:checked')
        ).map((input) => input.value);
        orgBL.forEach((org) => {
            params.append("org[BL][]", org);
        });
        const orgAR = Array.from(
            document.querySelectorAll('input[name="org[AR][]"]:checked')
        ).map((input) => input.value);
        orgAR.forEach((org) => {
            params.append("org[AR][]", org);
        });
        params.append("shop_freeword", document.querySelector('input[name="shop_freeword"]').value);
        params.append("publish-from-date", document.querySelector('input[name="publish-from-date"]').value);
        params.append("publish-to-date", document.querySelector('input[name="publish-to-date"]').value);
        if (document.querySelector('input[name="publish-from-check"]').checked) {
            params.append("publish-from-check", "on");
        }
        if (document.querySelector('input[name="publish-to-check"]').checked) {
            params.append("publish-to-check", "on");
        }
        params.append("message_freeword", document.querySelector('input[name="message_freeword"]').value);

        let fullUrl = `${baseUrl}?${params.toString()}`;

        // 生成されたURLをコンソールに表示（デバッグ用）
        console.log(fullUrl);

        // AJAXリクエストを送信
        fetch("/admin/analyse/personal/save-search-conditions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                url: fullUrl,
            }),
        })
            .then((response) => {
                if (response.ok) {
                    return response.json();
                } else {
                    return response.text().then((text) => {
                        throw new Error(text);
                    });
                }
            })
            .then((data) => {
                if (data.success) {
                    $(".analyse-personal.active a").attr("href", fullUrl);
                    alert("検索条件が保存されました。");
                    overlay.style.display = "none";
                } else {
                    alert("保存に失敗しました。");
                    overlay.style.display = "none";
                }
            })
            .catch((error) => {
                alert(error.message);
                overlay.style.display = "none";
            });
    });
});
