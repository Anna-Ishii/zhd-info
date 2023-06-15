"use strict";

/* マニュアルモーダル */
$(document).on('click' , '.main__thumb' , function(){
	let thumbParents = $(this).parents('.main__box');
	thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');
});
$(document).on('click', '.manualAttachmentBg , .manualAttachment__close' , function(e){
	let thumbParents = $(this).parents('.main__box');
	if($(this).hasClass('manualAttachmentBg') && !e.target.closest('.manualAttachment')){
		/* 動画を止める */
		let chkActiveMovie = $('.manualAttachment.isActive').find('video');
		if(chkActiveMovie.length){
			chkActiveMovie.get(0).pause();
		}

		thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');
	}else if($(this).hasClass('manualAttachment__close')){
		/* 動画を止める */
		let chkActiveMovie = $('.manualAttachment.isActive').find('video');
		if(chkActiveMovie.length){
			chkActiveMovie.get(0).pause();
		}

		thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');
	}
});

/* 「見た！」ボタンの色切り替え */
$(document).on('click' , '.btnWatched' , function(){
	if(!$(this).hasClass('isActive')){
		$(this).addClass('isActive');
	}
});

$(document).on('click' , '.btnPrint' , function(){
	window.print();
});