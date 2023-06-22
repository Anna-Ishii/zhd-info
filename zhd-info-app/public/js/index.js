"use strict";
$(document).on('click' , '.toggleTab .tab' , function(){
	if($(this).hasClass('isCurrent')){
		return false;
	}

	let chkTabNumber = $(this).data('sort-number');
	if(chkTabNumber){
		location.href = "/admin/manual/publish?category=" + chkTabNumber;
	}else{
		location.href = "/admin/manual/publish"
	}

	// let target = $('.toggleContent[data-tab-number="'+chkTabNumber+'"]');

	// $('.toggleContent').hide();
	// target.show();
	
	// let tabs = $(this).siblings('.tab');
	// tabs.removeClass('isCurrent');
	// $(this).addClass('isCurrent');
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