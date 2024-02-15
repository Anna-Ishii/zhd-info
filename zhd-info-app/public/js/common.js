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

/* 汎用モーダル処理 */
function modalAnim(e){
	let modalTarget = $('.modal[data-modal-target='+e+']')
	if(modalTarget.length){
		$('.modalBg').show();
		modalTarget.show();	
	}
}

/* 汎用モーダル表示 */
$(document).on('click' , '.btnModal', function(){
	event.preventDefault();
	let target = $(this).data('modal-target');
	modalAnim(target);

	if(target == 'read'){
		let target = $('.readUser__sort').find('.isSelected');
		userSort(target);
	}
});
$(document).on('click' , '.modal__close, .modalBg', function(e){
	if($(this).hasClass('modalBg') && $(e).closest('.modal')){
		$('.modalBg').hide();
		$('.modal').hide();
	}else{
		$('.modalBg').hide();
		$('.modal').hide();
	}
});

/* モーダル内の未読・既読表示変更 */
$(document).on('click' , '.readUser__switch__item', function(){
	let chkTab = $(this).data('readuser-target');
	$('.readUser__switch__item').removeClass('isSelected');
	$('.readUser__list').hide();
	$(this).addClass('isSelected');
	$('.readUser__list[data-readuser-target='+chkTab+']').show();

	/* 所属未所属をチェック */
	let chkTabBelongs = $('.readUser__sort').find('.isSelected').data('readuser-belong');
	let users = $('.readUser__list:visible').find('.readUser__list__item');
	users.each(function(){
		if($(this).data('readuser-belong') == chkTabBelongs){
			$(this).show();
		}else{
			$(this).hide();	
		}
	});
});

/* 所属・未所属表示変更 */
function userSort(e){
	let chkTabBelongs = $(e).data('readuser-belong');
	$('.readUser__sort').find('button').removeClass('isSelected');
	$(e).addClass('isSelected');
	
	let users = $('.readUser__list:visible').find('.readUser__list__item');
	users.each(function(){
		if($(this).data('readuser-belong') == chkTabBelongs){
			$(this).show();
		}else{
			$(this).hide();	
		}
	});
}
$(document).on('click' , '.readUser__sort button', function(){
	let targegt = $(this);
	userSort(targegt);
});

/* 検索処理 */
$(document).on('click' , '.btnSearch', function(){
	/* フッターのリンク入れ替え */
	$('.btnSort').empty().text('カテゴリ選択');
});

$(document).on('click', '.keyword_button', function(e) {
	let type = $("input[name='type']:checked").val()
	let search_period = $('select[name="search_period"]').val()
	if(type == '1'){
		location.href=`message?keyword=${e.target.innerText}&search_period=${search_period}`
	}else if(type == '2'){
		location.href=`manual?keyword=${e.target.innerText}&search_period=${search_period}`
	}else{
	}
})

$(document).on('click', '.crew>input', function(e) {
	let crew_id = this.getAttribute('data-crew-id');
	var csrfToken = $('meta[name="csrf-token"]').attr('content');

	console.log(csrfToken);
	$.ajax({
		type: 'POST',
		url: '/message/crews',
		data: {
			crew: crew_id
		},
		dataType: 'json',
		headers: {
		'X-CSRF-TOKEN': csrfToken,
		},
	})
	.done(function(res) {
		console.log(res.messages);
	})
})

$(document).on('click' , '.btnChangeStatus' , function(){
	if($('.list__status__limit').is(':visible')){
		$('.list__status__limit , .list__status__read').hide();
		$('.list__status__read').show();
		$(this).text('掲載期間の表示');
	}else if($('.list__status__read').is(':visible')){
		$('.list__status__limit , .list__status__read').hide();
		$('.list__status__limit').show();
		$(this).text('閲覧状況の表示');
	}
});
