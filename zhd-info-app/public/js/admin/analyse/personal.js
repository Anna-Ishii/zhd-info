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
    let th1Width = th1.length ? Math.round(th1.outerWidth()) : 0;
    let th2Width = th2.length ? Math.round(th2.outerWidth()) : 0;

    let DSWidth = th0Width;
    let BLWidth = DSWidth + th1Width;
    let ARWidth = BLWidth + th2Width;

    // 店舗名の幅を取得
    let tableOffset = Math.round($('.personal.table.table-bordered').offset().left);
    let shopName = $('table.personal tbody tr td.shop_name');
    let shopNameLeft = shopName.length ? Math.round(shopName.offset().left - tableOffset) : 25;

    // 幅をCSSに適用
    document.documentElement.style.setProperty('--left-2', `${DSWidth}px`);
    document.documentElement.style.setProperty('--left-3', `${BLWidth}px`);
    document.documentElement.style.setProperty('--left-4', `${ARWidth}px`);
    document.documentElement.style.setProperty('--left-5', `${shopNameLeft}px`);
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

$(document).on("change", 'select[name="organization1"]', function (e) {
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    const url = "/admin/analyse/personal/organization";
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
            console.log(res);
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
    console.log(e.target.value);
});
