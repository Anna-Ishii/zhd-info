"use strict";

/* 動画再生・停止時に再生ボタンの表示の切り替えをする */
$(window).on('load' , function(){
	let targetMovie = $('.manualAttachment').find('video');
	console.log(targetMovie.length);
	targetMovie.each(function(){
		$(this).get(0).addEventListener('play', function(){
			$(this).removeClass('is-paused');
		}, true);
		$(this).get(0).addEventListener('pause', function(){
			$(this).addClass('is-paused');
		}, true);
		$(this).get(0).addEventListener('seeked , timeupdate', function(){
			return false;
		}, true);
	});
});
$(document).on('play', 'video', function(){
	console.log('ugoitayo');
	$(this).removeClass('is-paused');
});
$(document).on('pause', 'video', function(){
	console.log('tomattayo');
	$(this).addClass('is-paused');
});

/* マニュアルモーダル */
$(document).on('click' , '.main__thumb' , function(){
	let thumbParents = $(this).parents('.main__box , .main__box--single');
	thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');

	/* 動画を自動再生する */
	let targetMovie = $('.manualAttachment.isActive').find('video');
	if(targetMovie.length){
		targetMovie.get(0).play();
	}
});
$(document).on('click', '.manualAttachmentBg , .manualAttachment__close' , function(e){
	let thumbParents = $(this).parents('.main__box , .main__box--single');
	if($(this).hasClass('manualAttachmentBg') && !e.target.closest('.manualAttachment')){
		/* 動画を止める */
		let targetActiveMovie = $('.manualAttachment.isActive').find('video');
		if(targetActiveMovie.length){
			targetActiveMovie.get(0).pause();
		}

		thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');
	}else if($(this).hasClass('manualAttachment__close')){
		/* 動画を止める */
		let targetActiveMovie = $('.manualAttachment.isActive').find('video');
		if(targetActiveMovie.length){
			targetActiveMovie.get(0).pause();
		}

		thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');
	}
});
/* 要素全体を押したときに停止/再生する */
$(document).on('click', '.manualAttachment.isActive video, .manualAttachment__btnPlay' , function(){
	let chkTarget;
	if($(this).hasClass('manualAttachment__btnPlay')){
		chkTarget = $(this).siblings('video');
	}else{
		chkTarget = $(this);	
	}	

	if(!chkTarget.get(0).paused){
		chkTarget.get(0).pause();
		return false;
	}else{
		chkTarget.get(0).play();
		return false;
	}
});

$(document).on('click' , '.btnPrint' , function(){
	window.print();
});

/* モーダル */
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

/* 移動先フォルダ選択時 */
$(document).on('change' , '.moveFolder' , function(){
	$('.modal__list__item').find('label').removeClass('isSelected');
	if($(this).prop('checked' , true)){
		console.log('test');
		$(this).parents('label').addClass('isSelected');
	}
});