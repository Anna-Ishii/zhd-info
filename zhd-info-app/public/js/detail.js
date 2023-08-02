"use strict";

/* マニュアルモーダル */
$(document).on('click' , '.main__thumb' , function(){
	let thumbParents = $(this).parents('.main__box , .main__box--single');
	thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');

	/* 動画を自動再生する */
	let targetMovie = $('.manualAttachment.isActive').find('video');
	if(targetMovie.length){
		targetMovie.get(0).play();
		targetMovie.removeClass('isPaused');
	}
});
$(document).on('click', '.manualAttachmentBg , .manualAttachment__close' , function(e){
	let thumbParents = $(this).parents('.main__box , .main__box--single');
	if($(this).hasClass('manualAttachmentBg') && !e.target.closest('.manualAttachment')){
		/* 動画を止める */
		let targetMovie = $('.manualAttachment.isActive').find('video');
		if(targetMovie.length){
			targetMovie.get(0).pause();
		}
		$('.manualAttachment.isActive').find('.listPlaySpeed').hide();

		thumbParents.find('.manualAttachmentBg , .manualAttachment').removeClass('isActive');
	}else if($(this).hasClass('manualAttachment__close')){
		/* 動画を止める */
		let targetMovie = $('.manualAttachment.isActive').find('video');
		if(targetMovie.length){
			targetMovie.get(0).pause();
		}
		$('.manualAttachment.isActive').find('.listPlaySpeed').hide();

		thumbParents.find('.manualAttachmentBg , .manualAttachment').removeClass('isActive');
	}
});

/* 時間計測 */
function setCurrentTime(e){
	let movieLength = $(e).get(0).duration;
	let movieCurrentTime = $(e).get(0).currentTime;
	let currentTime = Math.floor(movieCurrentTime / movieLength * 100);

	let targetSeekBar = $('.manualAttachment.isActive').find('.manualAttachment__ui__progress');
	targetSeekBar.css('width' , currentTime+'%');
}
/* 秒数の手動移動 */
function moveCurrentTime(e){
	/* 動画の総再生時間を取っておく */
	let targetMovie = $('.manualAttachment.isActive').find('video');
	let movieLength = targetMovie.get(0).duration;
	
	/* 総再生時間から移動先の秒数を計算 */
	let targetTime = movieLength * (e / 100);
	targetMovie.get(0).currentTime = targetTime;

	/* 点を正しい位置に戻す */
	let targetSeekBarDot = $('.manualAttachment.isActive').find('.manualAttachment__ui__progressDot');
	let resetCss = {
		right: '0',
		left: 'auto',
	}
	targetSeekBarDot.css(resetCss);
}


$(window).on('load' , function(){
	let targetMovie = $('.manualAttachment').find('video');
	targetMovie.each(function(){
		/* 再生されたときに再生ボタンを消す */
		$(this).get(0).addEventListener('play', function(){
			$(this).removeClass('isPaused');
			$('.manualAttachment.isActive').find('.txtPlay').hide();
			$('.manualAttachment.isActive').find('.txtPause').show();
		}, true);
		/* 停止されたときに再生ボタンを表示する */
		$(this).get(0).addEventListener('pause', function(){
			$(this).addClass('isPaused');
			$('.manualAttachment.isActive').find('.txtPlay').show();
			$('.manualAttachment.isActive').find('.txtPause').hide();
		}, true);
		/* 経過時間を計測する */
		$(this).get(0).addEventListener('timeupdate' , function(){
			let target = $(this);
			setCurrentTime(target);
		});
	});
	/* jquery UI */
	let targetSeekBar = $('.manualAttachment').find('.manualAttachment__ui__progressDot');
	targetSeekBar.each(function(){
		$(this).draggable({
			axis: 'x',
			containment: '.manualAttachment__ui__seekbar',
			scroll: false,
			stop: function(e){
				let targetSeekBar = $('.manualAttachment.isActive').find('.manualAttachment__ui__seekbar');
				/* ドラッグ位置とシークバーの表示位置取得 */
				let dragPos = e.pageX;
				let clientRect = targetSeekBar.get(0).getBoundingClientRect();
				/* 相対位置 */
				let posX = clientRect.left + window.pageXOffset;
				posX = dragPos - posX;

				/* 割合取得 */
				let seekBarW = targetSeekBar.innerWidth();
				let clickPosPer = Math.floor(posX / seekBarW * 100);

				moveCurrentTime(clickPosPer);
			},
		});
	});
	/* プリロード画面の削除 */
	$('.manualAttachment').find('.manualAttachment__preload').fadeOut();
});

/* 動画UIの操作関係 */
$(document).on('click' , '.manualAttachment__ui__btnPlay' , function(){
	let targetMovie = $('.manualAttachment.isActive').find('video');
	if(!targetMovie.hasClass('isPaused')){
		targetMovie.get(0).pause();
	}else{
		targetMovie.get(0).play();
	}
});
$(document).on('click' , '.manualAttachment__ui__btnPlaySpeed' , function(){
	let target = $(this).find('.listPlaySpeed');
	if(!target.is(':visible')){
		target.show();
	}else{
		target.hide();
	}
});
$(document).on('click' , '.listPlaySpeed li', function(){
	let playSpeed = $(this).data('play-speed');
	let targetMovie = $('.manualAttachment.isActive').find('video');
	targetMovie.get(0).playbackRate = playSpeed;
	$(this).parents('.listPlaySpeed').hide();
});
$(document).on('click' , '.manualAttachment__ui__btnFull' , function(){
	let target = $('.manualAttachment.isActive');
	/* アイコンの表示状態で判別してフルスクリーンの開始・終了をする */
	if($(this).find('.txtFullScreen').is(':visible')){
		/* 念のためフルスクリーン判定 */
		if(!target.webkitRequestFullScreen){
			target.get(0).webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
		}else if(!target.mozRequestFullScreen){
			target.get(0).mozRequestFullScreen();
		}
	}else if($(this).find('.txtExitFullScreen').is(':visible')){
		/* 念のためフルスクリーン判定 */
		if(document.webkitFullscreenElement){
			document.webkitExitFullscreen();
		}else if(document.mozFullScreenElement){
			document.mozCancelFullScreen();
		}else{
			console.warn('フルスクリーン要素がありません');
		}
	}
});
/* PiP */
$(document).on('click' , '.manualAttachment__ui__btnPiP' , function(){
	let targetMovie = $('.manualAttachment.isActive').find('video');
	if(targetMovie.webkitSupportsPresentationMode && typeof targetMovie.webkitSetPresentationMode === 'function'){
		targetMovie.get(0).webkitSetPresentationMode('picture-in-picture');
	}else{
		targetMovie.get(0).requestPictureInPicture();
	}
});
$(document).on('click' , '.manualAttachment__ui__seekbar', function(e){
	/* クリック位置とシークバーの表示位置取得 */
	let clickPos = e.pageX;
	let clientRect = this.getBoundingClientRect();
	/* 相対位置 */
	let posX = clientRect.left + window.pageXOffset;
	posX = clickPos - posX;

	/* 割合取得 */
	let seekBarW = $(this).innerWidth();
	let clickPosPer = Math.floor(posX / seekBarW * 100);

	moveCurrentTime(clickPosPer);
});
$(document).on('click' , '.manualAttachment__ui__btnForward , .manualAttachment__ui__btnReplay' , function(){
	let targetMovie = $('.manualAttachment.isActive').find('video');
	let movieCurrentTime = targetMovie.get(0).currentTime;

	let targetTime;
	if($(this).hasClass('manualAttachment__ui__btnForward')){
		targetTime = movieCurrentTime + 10;
	}else if($(this).hasClass('manualAttachment__ui__btnReplay')){
		targetTime = movieCurrentTime - 10;
	}

	targetMovie.get(0).currentTime = targetTime;
});

/* 要素全体を押したときに停止/再生する */
$(document).on('click', '.manualAttachment.isActive .manualAttachment__videoCover, .manualAttachment__btnPlay' , function(){
	let chkTarget;
	chkTarget = $(this).siblings('video');

	if(!chkTarget.fullscreenEnabled && !chkTarget.get(0).paused){
		chkTarget.get(0).pause();
	}else{
		chkTarget.get(0).play();
	}
});

$(document).on('click' , '.btnPrint' , function(){
	window.print();
});

/* 移動先選択モーダル */
$(document).on('click' , '.btnMoveFolder' , function(){
	let chkTargetName = $(this).data('target-name');
	let modalTarget = $('.modal[data-target-name='+chkTargetName+']');
	console.log(modalTarget.length);
	if(!$('.modalBg').is(':visible')){
		$('.modalBg').show();
		modalTarget.show();
	}
});
$(document).on('click', '.modalBg' , function(e){
	if(!e.target.closest('.modal')){
		$('.modalBg , .modal').hide();	
	};
});
/* 移動先フォルダ選択時の背景色切り替え */
$(document).on('change' , '.moveFolder' , function(){
	$('.modal__list__item').find('label').removeClass('isSelected');
	if($(this).prop('checked' , true)){
		console.log('test');
		$(this).parents('label').addClass('isSelected');
	}
});