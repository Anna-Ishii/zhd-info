"use strict";

$(document).on('click', '.btnSidebar , .sidebar__close' , function(){
	$('.sidebarBg , .sidebar').toggleClass('isActive');
});

$(document).on('click', '.sidebarBg' , function(e){
	if(!e.target.closest('.sidebar')){
		$('.sidebarBg , .sidebar').toggleClass('isActive');
		$('.btnSidebarLabel').show();
		$('.sidebar__inputArea').hide();	
	}
});

$(document).on('click' , '.btnSidebarLabel' , function(){
	$(this).hide();
	$('.sidebar__inputArea').show();
	$('.sidebar__inputArea').find('input[type=text]').focus();
});

$(document).on('click' , '.btnAddLabel' , function(){
	let chkInputVal = $(this).siblings('input[type=text]').val();
	if(chkInputVal == ''){
		alert('ラベル名を入力してください。');
		return false;
	}
});

$(document).on('click' , '.btnSort', function(){
	let chkMenu = $('.sortMenu');
	if(!chkMenu.is(':visible')){
		event.preventDefault();
		$('.sortMenu').addClass('isActive');
		$('.btnSort').empty().text('ホーム');
	}else{
		return true;
	}
});

/* ソートカテゴリ選択 */
$(document).on('change' , '.selectAll' , function(){
	let chkStatus = $(this).prop('checked');
	let target = $(this).parents('.sortMenu__box').find('input[type=checkbox]');
	if(chkStatus){
		target.each(function(){
			if(!$(this).prop('checked')){
				$(this).prop('checked' , true);
			}
		});
		$(this).addClass('isSelectAll');
	}else{
		$(this).siblings('label').removeClass('isSelectAll');
		target.each(function(){
			if($(this).prop('checked')){
				$(this).prop('checked' , false);
			}
		});
	}
});


let targetInput;
let cnt;
let chkBulkTarget;
function changeBulk(e){
	if(cnt >= 1){
		chkBulkTarget.removeClass('isSelectAll');
	}
}
$(document).on('change' , '.sortMenu__list__item input[type=checkbox]' , function(){
	targetInput = $(this);
	chkBulk(changeBulk);
});
function chkBulk(callback){
	let chkTarget = targetInput.parents('.sortMenu__list').find('input[type=checkbox]');
	/* 一回一括のチェックを外す */
	chkBulkTarget = targetInput.parents('.sortMenu__box').find('.selectAll');
	chkBulkTarget.prop('checked' , false);
	/* 全部チェックされているか判別する */
	cnt = 0;
	chkTarget.each(function(){
		if($(this).prop('checked')){
			console.log(chkBulkTarget.length);
			chkBulkTarget.prop('checked' , true);
			chkBulkTarget.addClass('isSelectAll');
		}else{
			cnt = cnt + 1;
			console.log(cnt);
		}
	});
	callback();
}


$(document).on('click' , '.btnSearchReset', function(){
	let target = $('.sortMenu').find('input[type=checkbox]');
	target.each(function(){
		$(this).prop('checked' , false);
	});
});

/* 検索処理 */
$(document).on('click' , '.btnSearch', function(){
	/* フッターのリンク入れ替え */
	$('.btnSort').empty().text('カテゴリ選択');
});
