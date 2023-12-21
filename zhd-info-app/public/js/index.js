"use strict";
$(document).on('click' , '.toggleTab .tab' , function(){
	if($(this).hasClass('isCurrent')){
		return false;
	}
	var queryParams = [];

	let chkTabNumber = $(this).data('sort-number');
	let statusValue = $('select[name="status"]').val();
	let qValue = $('input[name="q"]').val();

	let url = "/admin/manual/publish"
	if(chkTabNumber) queryParams.push("category=" + chkTabNumber);
	if(statusValue) queryParams.push("status=" + statusValue);
	if(qValue) queryParams.push("q=" + qValue);

	if (queryParams.length > 0) {
      url += '?' + queryParams.join('&');
    }
	
	location.href = url

});

let scrollVal;
function chkScrollLoad(){
	/* 幅を取得 */
	let scrollW = $('.toggleTab__inner').get(0).scrollWidth;
	let parentW = $('.toggleTab__inner').parent('.toggleTab').innerWidth();
	
	if(scrollW == parentW){
		$('.scrollHintL , .scrollHintR').fadeOut("fast");
	}
}
function chkScroll(){
	/* 幅を取得 */
	let scrollW = $('.toggleTab__inner').get(0).scrollWidth;
	let windowW = $(window).innerWidth();

	/* スクロール量を取得 */
	scrollVal = $('.toggleTab__inner').scrollLeft();

	let max = scrollW - windowW;

	if(scrollVal >= 40){
		$('.scrollHintL').fadeIn("fast");
	}else if(scrollVal <= windowW){
		$('.scrollHintL').fadeOut("fast");
	}

	if(scrollVal >= max){
		$('.scrollHintR').fadeOut("fast");
	}else if(scrollVal <= max){
		$('.scrollHintR').fadeIn("fast");
	}

	console.log(scrollVal);
}
function CurrentScroll(){
	let target = $('.isCurrent');
	// isCurrentまでのポジションを計算
	// isCurrentまでのx値 - toggleTab全体のwirdh + isCurrentのwidth値
	let target_pos = target.position().left - $('.toggleTab__inner').width() + target.outerWidth(true);
	$('.toggleTab__inner').scrollLeft(target_pos);
}
$(window).on('load' , function(){
	chkScrollLoad();
	CurrentScroll(); // .isCurrentまでスクロールする
	$('.toggleTab__inner').on('scroll', function(){
		chkScroll();
	});
});