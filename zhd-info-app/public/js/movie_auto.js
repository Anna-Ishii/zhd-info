"use strict";

/* ページ読み込み時に自動再生 */
let targetAutoPlay = $('.manualAttachment:visible video');
$(window).on('DOMContentLoaded' , function(){
	targetAutoPlay.removeClass('isPaused');
	$('.manualAttachment:visible').find('.txtPlay').hide();
	$('.manualAttachment:visible').find('.txtPause').show();
	targetAutoPlay.get(0).play();
});
