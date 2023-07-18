"use strict";

/* マニュアルモーダル */
$(document).on('click' , '.main__thumb' , function(){
	let thumbParents = $(this).parents('.main__box , .main__box--single');
	thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');

	/* 動画を自動再生する */
	let targetMovie = $('.manualAttachment.isActive').find('video');
	if(targetMovie.length){
		setTimeout(function(){
			targetMovie.get(0).play();
		},1000);
		
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
$(document).on('click', '.manualAttachment.isActive video' , function(){
	let chkTarget = $(this);

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