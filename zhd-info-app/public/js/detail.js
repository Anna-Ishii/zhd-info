"use strict";

/* マニュアルモーダル関係 */
let modalVideoW, modalVideoH;
function changeMovieUIsize() {
    /* uiの幅をvideoに合わせる */
    modalVideoW = $(".manualAttachment.isActive").find("video").innerWidth();
    $(".manualAttachment.isActive")
        .find(".manualAttachment__ui , .manualAttachment__videoCover")
        .css("maxWidth", modalVideoW);

    /* 外枠を高さをvideoに合わせる（modalVideoHはフルスクリーン解除時にも使用する） */
    modalVideoH = $(".manualAttachment.isActive").find("video").innerHeight();
    $(".manualAttachment.isActive").css("height", modalVideoH);
}

$(document).on(
    "click",
    ".manualAttachmentBg , .manualAttachment__close",
    function (e) {
        let thumbParents = $(this).parents(".main__box , .main__box--single");
        if (
            $(this).hasClass("manualAttachmentBg") &&
            !e.target.closest(".manualAttachment")
        ) {
            /* 動画を止める */
            let targetMovie = $(".manualAttachment.isActive").find("video");
            if (targetMovie.length) {
                targetMovie.get(0).pause();
            }
            $(".manualAttachment.isActive")
                .find(".listPlaySpeed")
                .hide()
                .removeAttr("style");

            thumbParents
                .find(".manualAttachmentBg , .manualAttachment")
                .removeClass("isActive");
        } else if ($(this).hasClass("manualAttachment__close")) {
            /* 動画を止める */
            let targetMovie = $(".manualAttachment.isActive").find("video");
            if (targetMovie.length) {
                targetMovie.get(0).pause();
            }
            $(".manualAttachment.isActive")
                .find(".listPlaySpeed")
                .hide()
                .removeAttr("style");

            thumbParents
                .find(".manualAttachmentBg , .manualAttachment")
                .removeClass("isActive");
        }
    }
);

$(document).on("click", ".btnPrint", function () {
    window.print();
});

/* 移動先選択モーダル */
$(document).on("click", ".btnMoveFolder", function () {
    let chkTargetName = $(this).data("target-name");
    let modalTarget = $(".modal[data-target-name=" + chkTargetName + "]");
    console.log(modalTarget.length);
    if (!$(".modalBg").is(":visible")) {
        $(".modalBg").show();
        modalTarget.show();
    }
});
$(document).on("click", ".modalBg", function (e) {
    if (!e.target.closest(".modal")) {
        $(".modalBg , .modal").hide();
    }
});
/* 移動先フォルダ選択時の背景色切り替え */
$(document).on("change", ".moveFolder", function () {
    $(".modal__list__item").find("label").removeClass("isSelected");
    if ($(this).prop("checked", true)) {
        console.log("test");
        $(this).parents("label").addClass("isSelected");
    }
});

/* 動画関係 */

/* 再生時間計測 */
function setCurrentTime(e) {
    let movieLength = $(e).get(0).duration;
    let movieCurrentTime = $(e).get(0).currentTime;
    let currentTime = Math.floor((movieCurrentTime / movieLength) * 100);

    let targetSeekBar = $(".manualAttachment.isActive").find(
        ".manualAttachment__ui__progress"
    );
    targetSeekBar.css("width", currentTime + "%");
}
/* 秒数の手動移動 */
function moveCurrentTime(e) {
    /* 動画の総再生時間を取っておく */
    let targetMovie = $(".manualAttachment.isActive").find("video");
    let movieLength = targetMovie.get(0).duration;

    /* 総再生時間から移動先の秒数を計算 */
    let targetTime = movieLength * (e / 100);
    targetMovie.get(0).currentTime = targetTime;

    /* 点を正しい位置に戻す */
    let targetSeekBarDot = $(".manualAttachment.isActive").find(
        ".manualAttachment__ui__progressDot"
    );
    let resetCss = {
        right: "0",
        left: "auto",
    };
    targetSeekBarDot.css(resetCss);
}
function changeClassExitFullScreen() {
    if (
        !document.fullscreenElement &&
        !document.webkitFullscreenElement &&
        !document.mozFullScreenElement
    ) {
        $(".manualAttachment__inner").removeClass("isFullScreen");
    }
}

$(window).on("load", function () {
    let targetMovie = $(".manualAttachment").find("video");
    targetMovie.each(function () {
        /* 再生されたときに再生ボタンを消す */
        $(this)
            .get(0)
            .addEventListener(
                "play",
                function () {
                    $(this).removeClass("isPaused");
                    $(".manualAttachment.isActive").find(".txtPlay").hide();
                    $(".manualAttachment.isActive").find(".txtPause").show();
                },
                true
            );
        /* 停止されたときに再生ボタンを表示する */
        $(this)
            .get(0)
            .addEventListener(
                "pause",
                function () {
                    $(this).addClass("isPaused");
                    $(".manualAttachment.isActive").find(".txtPlay").show();
                    $(".manualAttachment.isActive").find(".txtPause").hide();
                },
                true
            );
        /* 経過時間を計測する */
        $(this)
            .get(0)
            .addEventListener("timeupdate", function () {
                let target = $(this);
                setCurrentTime(target);
            });
    });
    /* フルスクリーン解除時の挙動追加 */
    document.addEventListener("webkitfullscreenchange", function () {
        changeClassExitFullScreen();
    });
    document.addEventListener("mozfullscreenchange", function () {
        changeClassExitFullScreen();
    });
    document.addEventListener("fullscreenchange", function () {
        changeClassExitFullScreen();
    });
    /* jquery UI */
    let targetSeekBar = $(".manualAttachment").find(
        ".manualAttachment__ui__progressDot"
    );
    targetSeekBar.each(function () {
        $(this).draggable({
            axis: "x",
            containment: ".manualAttachment__ui__seekbar",
            scroll: false,
            stop: function (e) {
                let targetSeekBar = $(".manualAttachment.isActive").find(
                    ".manualAttachment__ui__seekbar"
                );
                /* ドラッグ位置とシークバーの表示位置取得 */
                let dragPos = e.pageX;
                let clientRect = targetSeekBar.get(0).getBoundingClientRect();
                /* 相対位置 */
                let posX = clientRect.left + window.pageXOffset;
                posX = dragPos - posX;

                /* 割合取得 */
                let seekBarW = targetSeekBar.innerWidth();
                let clickPosPer = Math.floor((posX / seekBarW) * 100);

                moveCurrentTime(clickPosPer);
            },
        });
    });
    /* 画面読みこみ後のプリロード画面の削除 */
    $(".manualAttachment").find(".manualAttachment__preload").fadeOut();
    /* jquery UI Touch Punch */
    $(".manualAttachment__ui__progressDot").sortable();

    $(document).on("click", ".main__thumb", function () {
        let thumbParents = $(this).parents(".main__box , .main__box--single");
        thumbParents
            .find(".manualAttachmentBg , .manualAttachment")
            .toggleClass("isActive");

        /* 動画を自動再生する */
        let targetMovie = $(".manualAttachment.isActive").find("video");
        if (targetMovie.length) {
            targetMovie
                .get(0)
                .play()
                .then(function () {
                    targetMovie.removeClass("isPaused");
                    changeMovieUIsize();
                });
        }
    });
});
/* ウィンドウ幅変更時、videoの表示調整を行うようにする */
$(window).on("resize", function () {
    if ($(".manualAttachment.isActive").find("video").length) {
        changeMovieUIsize();
    }
});

/* 動画UIの操作関係 */
$(document).on("click", ".manualAttachment__ui__btnPlay", function () {
    let targetMovie = $(".manualAttachment.isActive").find("video");
    if (!targetMovie.hasClass("isPaused")) {
        targetMovie.get(0).pause();
    } else {
        targetMovie.get(0).play();
    }
});
$(document).on("click", ".manualAttachment__ui__btnPlaySpeed", function () {
    let target = $(this).find(".listPlaySpeed");
    if (!target.is(":visible")) {
        target.show();
    } else {
        target.hide();
    }
});
$(document).on("click", ".listPlaySpeed li", function () {
    let playSpeed = $(this).data("play-speed");
    let targetMovie = $(".manualAttachment.isActive").find("video");
    targetMovie.get(0).playbackRate = playSpeed;
    $(this).parents(".listPlaySpeed").hide();
});
/* フルスクリーンモードの設定・解除 */
$(document).on("click", ".manualAttachment__ui__btnFull", function () {
    let target = $(".manualAttachment.isActive .manualAttachment__inner");
    // if(!target.requestFullscreen && !target.webkitRequestFullScreen && !target.mozRequestFullScreen){
    if (
        !document.fullscreenElement &&
        !document.webkitFullscreenElement &&
        !document.mozFullScreenElement
    ) {
        /* 念のためフルスクリーン判定 */
        if (!target.webkitRequestFullScreen) {
            target.get(0).webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
            target.addClass("isFullScreen");
        } else if (!target.mozRequestFullScreen) {
            target.get(0).mozRequestFullScreen();
            target.addClass("isFullScreen");
        } else if (!target.requestFullscreen) {
            target.get(0).requestFullscreen();
            target.addClass("isFullScreen");
        }
    } else {
        /* 念のためフルスクリーン判定 */
        console.log(modalVideoH);
        if (document.webkitFullscreenElement) {
            document.webkitExitFullscreen();
            target.removeClass("isFullScreen");
            if (modalVideoH != "") {
                $(".manualAttachment.isActive").css("height", modalVideoH);
            }
        } else if (document.mozFullScreenElement) {
            document.mozCancelFullScreen();
            target.removeClass("isFullScreen");
            if (modalVideoH != "") {
                $(".manualAttachment.isActive").css("height", modalVideoH);
            }
        } else if (document.exitFullscreen) {
            document.exitFullscreen();
            target.removeClass("isFullScreen");
            if (modalVideoH != "") {
                $(".manualAttachment.isActive").css("height", modalVideoH);
            }
        }
    }
});
/* PiP */
$(document).on("click", ".manualAttachment__ui__btnPiP", function () {
    let targetMovie = $(".manualAttachment.isActive").find("video");
    if (
        targetMovie.webkitSupportsPresentationMode &&
        typeof targetMovie.webkitSetPresentationMode === "function"
    ) {
        targetMovie.get(0).webkitSetPresentationMode("picture-in-picture");
    } else {
        targetMovie.get(0).requestPictureInPicture();
    }
});
/* シークバークリック時 */
$(document).on("click", ".manualAttachment__ui__seekbar", function (e) {
    /* クリック位置とシークバーの表示位置取得 */
    let clickPos = e.pageX;
    let clientRect = this.getBoundingClientRect();
    /* 相対位置 */
    let posX = clientRect.left + window.pageXOffset;
    posX = clickPos - posX;

    /* 割合取得 */
    let seekBarW = $(this).innerWidth();
    let clickPosPer = Math.floor((posX / seekBarW) * 100);

    moveCurrentTime(clickPosPer);
});
/* 10秒進む・戻るボタン */
$(document).on(
    "click",
    ".manualAttachment__ui__btnForward , .manualAttachment__ui__btnReplay",
    function () {
        let targetMovie = $(".manualAttachment.isActive").find("video");
        let movieCurrentTime = targetMovie.get(0).currentTime;

        let targetTime;
        if ($(this).hasClass("manualAttachment__ui__btnForward")) {
            targetTime = movieCurrentTime + 10;
        } else if ($(this).hasClass("manualAttachment__ui__btnReplay")) {
            targetTime = movieCurrentTime - 10;
        }

        targetMovie.get(0).currentTime = targetTime;
    }
);

/* 要素全体を押したときに停止/再生する */
$(document).on(
    "click",
    ".manualAttachment.isActive .manualAttachment__videoCover, .manualAttachment__btnPlay",
    function () {
        let chkTarget;
        chkTarget = $(this).siblings("video");

        if (!chkTarget.fullscreenEnabled && !chkTarget.get(0).paused) {
            chkTarget.get(0).pause();
        } else {
            chkTarget.get(0).play();
        }
    }
);

// PDFを前ページ表示されるようにする処理
document.addEventListener('DOMContentLoaded', function() {
    // .pdf-containerクラスを持つ全ての要素を取得
    const pdfContainers = document.querySelectorAll('.pdf-container');

    // 各PDFコンテナに対して処理を実行
    pdfContainers.forEach(container => {
        const url = container.dataset.url; // データ属性からURLを取得
        let pdfDoc = null;                 // PDFドキュメントオブジェクト
        let currentPage = 1;               // 現在のページ番号
        let isAllPages = false;            // 全ページ表示フラグ

        // URLが存在する場合、PDFを取得
        if (url) {
            pdfjsLib.getDocument(url).promise.then(function(pdf) {
                pdfDoc = pdf; // 取得したPDFドキュメントを保存

                // ページ数が2以上の場合にボタンを追加
                if (pdf.numPages > 1) {
                    const modalBtnInner = document.createElement('div');
                    modalBtnInner.classList.add('toggle-view');

                    // 1ページ表示ボタンを作成
                    const toggleSinglePageButton = document.createElement('button');
                    toggleSinglePageButton.type = 'button';
                    toggleSinglePageButton.classList.add('toggle-view-btn');
                    toggleSinglePageButton.textContent = '1ページ表示';
                    modalBtnInner.appendChild(toggleSinglePageButton);

                    // 全ページ表示ボタンを作成
                    const toggleAllPagesButton = document.createElement('button');
                    toggleAllPagesButton.type = 'button';
                    toggleAllPagesButton.classList.add('toggle-view-btn');
                    toggleAllPagesButton.textContent = '全ページ表示';
                    modalBtnInner.appendChild(toggleAllPagesButton);

                    // ボタンをコンテナに追加
                    container.appendChild(modalBtnInner);

                    // 1ページ表示ボタンのクリックイベント
                    toggleSinglePageButton.addEventListener('click', () => {
                        isAllPages = false;                      // 全ページ表示フラグをfalseに設定
                        toggleSinglePageButton.disabled = true;  // 1ページ表示ボタンを無効化
                        toggleAllPagesButton.disabled = false;   // 全ページ表示ボタンを有効化
                        currentPage = 1;                         // 現在のページを1に設定
                        container.innerHTML = '';                // コンテナをクリア
                        container.appendChild(modalBtnInner);    // ボタンを再追加
                        renderPage(currentPage);                 // 1ページ目をレンダリング
                    });

                    // 全ページ表示ボタンのクリックイベント
                    toggleAllPagesButton.addEventListener('click', () => {
                        isAllPages = true;                       // 全ページ表示フラグをtrueに設定
                        toggleAllPagesButton.disabled = true;    // 全ページ表示ボタンを無効化
                        toggleSinglePageButton.disabled = false; // 1ページ表示ボタンを有効化
                        currentPage = 1;                         // 現在のページを1に設定
                        container.innerHTML = '';                // コンテナをクリア
                        container.appendChild(modalBtnInner);    // ボタンを再追加
                        renderPage(currentPage);                 // 全ページをレンダリング
                    });

                    // 初期状態で1ページ表示ボタンを無効化
                    toggleSinglePageButton.disabled = true;
                }

                // 初期表示のページをレンダリング
                renderPage(currentPage);
            });
        }

        // 指定されたページをレンダリングする関数
        function renderPage(pageNum) {
            pdfDoc.getPage(pageNum).then(function(page) {
                const viewport = page.getViewport({ scale: 1.5 }); // ページのビューポートを設定
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const pageContainer = document.createElement('div');
                pageContainer.style.position = 'relative';
                pageContainer.style.marginBottom = '20px';

                // ページ番号を表示する要素を作成
                const pageNumber = document.createElement('div');
                pageNumber.classList.add('page-number');
                pageNumber.textContent = `${pageNum} / ${pdfDoc.numPages}`;
                pageContainer.appendChild(pageNumber);

                // キャンバスをページコンテナに追加
                pageContainer.appendChild(canvas);
                container.appendChild(pageContainer);

                // ページをキャンバスにレンダリング
                page.render({
                    canvasContext: context,
                    viewport: viewport
                }).promise.then(function() {
                    // 全ページ表示モードの場合、次のページをレンダリング
                    if (isAllPages && pageNum < pdfDoc.numPages) {
                        renderPage(pageNum + 1);
                    }
                });
            });
        }
    });
});
