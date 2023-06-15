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

