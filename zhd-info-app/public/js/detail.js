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
		
		var csrfToken = $('meta[name="csrf-token"]').attr('content');

		let manual_id = $('#manual_id').val();
		fetch("/manual/watched", {
			method: 'PUT',
			headers: {
				"Content-Type": "application/json",
				"X-CSRF-TOKEN": csrfToken
			},
			body: JSON.stringify({
				'manual_id': manual_id
			})
		})
		.then(response => {
			alert("閲覧ました");
			$(this).addClass('isActive');
			window.location.reload();
		})
		.catch(error => {
			alert("エラーです");
		});

	}
});

$(document).on('click' , '.btnPrint' , function(){
	window.print();
});