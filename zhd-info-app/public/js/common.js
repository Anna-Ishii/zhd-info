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

"use strict";

$(document).on('click' , '.btnSort', function(){
	$('.sortMenu').addClass('isActive');
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
	}else{
		target.each(function(){
			if($(this).prop('checked')){
				$(this).prop('checked' , false);
			}
		});
	}
});

$(document).on('change' , '.sortMenu__list__item input[type=checkbox]' , function(){
	let chkTarget = $(this).parents('.sortMenu__list').find('input[type=checkbox]');
	/* 一回チェックを外す */
	$(this).parents('.sortMenu__box').find('.selectAll').prop('checked' , false);
	chkTarget.each(function(){
		if($(this).prop('checked')){
			$(this).parents('.sortMenu__box').find('.selectAll').prop('checked' , true);
			return false;
		}
	});
});

$(document).on('click' , '.btnSearchReset', function(){
	let target = $('.sortMenu').find('input[type=checkbox]');
	target.each(function(){
		$(this).prop('checked' , false);
	});
});

/* 検索処理（仮置き） */
$(document).on('click' , '.btnSearch', function(){
	$('.sortMenu').removeClass('isActive');
});

