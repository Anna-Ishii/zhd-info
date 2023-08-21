"use strict";

/* ページ読み込み時にリサイズ */
$(window).on('load' , function(){
	if($('.manualAttachment.isActive').find('video').length){
		changeMovieUIsize();
	}
});
