"use strict";
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        // バックボタンでページが再表示された場合にリロードする
        window.location.reload();
    }
});
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
// $(document).on('click' , '.btnModal', function(event){
// 	event.preventDefault();
// 	let target = $(this).data('modal-target');
// 	modalAnim(target);

// 	if(target == 'read'){
// 		let target = $('.readUser__sort').find('.isSelected');
// 		userSort(target);
// 	}
// });
$(document).on('click', '.modal[data-modal-target="continue"] .modal__close', function(e) {
	let btnModel = clickMessage;
	var csrfToken = $('meta[name="csrf-token"]').attr('content');
	var message = btnModel.find('.list__item>.list__id').text();
	let editUserListTargetForm = $('.modal[data-modal-target="edit"] form');
	
	// 初期化
	$(`.modal[data-modal-target="edit"] .readEdit__list__accordion li`).remove();
	$('.modal[data-modal-target="edit"] form input[name="message"]').remove();

	$.ajax({
		type: 'GET',
		url: '/message/crews-message',
		data: {
			message: message
		},
		dataType: 'json',
		headers: {
		'X-CSRF-TOKEN': csrfToken,
		},
	})
	.done(function(res) {
		let crews = res.crews;

		editUserListTargetForm.append(`
			<input type="hidden" name="message" value="${message}">
		`);

		let sortCodeHeader = "";
		let from_part_code = "";
		let count = 0;
		crews.forEach((value, index, array) => {
			if (value.readed == 0) {
				$(`.modal[data-modal-target="edit"] .sort_name .readEdit__list__accordion[data-sort-num="${value.name_sort}"] ul`).append(`
					<li>
						${value.part_code} ${value.name}
						<input type="checkbox" name="read_edit_radio[]" id="user_${value.part_code}" value="${value.c_id}">
						<label for="user_${value.part_code}" class="readEdit__list__check">未選択</label>
					</li>
				`)

				if(count === 0) from_part_code = value.part_code

				sortCodeHeader += `<li>${value.part_code} ${value.name}
						<input type="checkbox" name="read_edit_radio[]" id="user_${value.part_code}" value="${value.c_id}">
						<label for="user_${value.part_code}" class="readEdit__list__check">未選択</label></li>
				`;

				if((count + 1) % 10 == 0 || count == crews.length + 1) {
					let head = `
						<div class="readEdit__list__head">${from_part_code} ~ ${value.part_code}</div>
						<div class="readEdit__list__accordion">
							<ul>
								${sortCodeHeader}
							</ul>
						</div>
					`;
					$(`.modal[data-modal-target="edit"] .sort_code`).append(head);
					sortCodeHeader = "";
					from_part_code = crews[index+1]?.part_code;
				}

				count++;
			}
		})
		let target = btnModel.data('modal-target');
		target = "edit";
		modalAnim(target);
	})
})

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

	if(chkTab == 1) {
		$('button[data-readuser-belong="1"]').text(`所属(${modalNotReadCrewBelong})`)
		$('button[data-readuser-belong="2"]').text(`未所属(${modalNotReadCrewNotBelong})`)
	}else {
		$('button[data-readuser-belong="1"]').text(`所属(${modalReadCrewBelong})`)
		$('button[data-readuser-belong="2"]').text(`未所属(${modalReadCrewNotBelong})`)
	}

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


var modalReadCrew = 0; // 既読数
var modalNotReadCrew = 0; // 未読数
var modalReadCrewBelong = 0; // 所属・既読数
var modalReadCrewNotBelong = 0; // 未所属・既読数
var modalNotReadCrewBelong = 0; // 所属・未読数
var modalNotReadCrewNotBelong = 0; // 未所属・未読数

$(document).on('click', '.list__status__read', function(e) {
	e.preventDefault();
	e.stopPropagation();
	let btnModel = $(this).closest('.btnModal');
	var csrfToken = $('meta[name="csrf-token"]').attr('content');
	var message = btnModel.find('.list__item>.list__id').text();

	$.ajax({
		type: 'GET',
		url: '/message/crews-message',
		data: {
			message: message
		},
		dataType: 'json',
		headers: {
		'X-CSRF-TOKEN': csrfToken
		},
	})
	.done(function(res) {
		let crews = res.crews;

		let readUserListTarget1Element = $('.readUser__list[data-readuser-target="1"]');
		let readUserListTarget2Element = $('.readUser__list[data-readuser-target="2"]');

		readUserListTarget1Element.empty()
		readUserListTarget2Element.empty()
		modalReadCrew = 0;
		modalNotReadCrew = 0;
		modalReadCrewBelong = 0;
		modalReadCrewNotBelong = 0;
		modalNotReadCrewBelong = 0;
		modalNotReadCrewNotBelong = 0;


		crews.forEach((value, index, array) => {
			let belong = value.new_face == 0 ? 1 : 2;
			if (value.readed == 0) {
				modalNotReadCrew++
				if(value.new_face == 0) {
					modalNotReadCrewBelong++
					readUserListTarget1Element.append(`
						<li class="readUser__list__item" data-readuser-belong="1">${value.part_code} ${value.name}</li>
					`)
				}else {
					modalNotReadCrewNotBelong++
					readUserListTarget1Element.append(`
						<li class="readUser__list__item" data-readuser-belong="2">${value.part_code} ${value.name}</li>
					`)
				}
			}else {
				modalReadCrew++
				if(value.new_face == 0) {
					modalReadCrewBelong++
					readUserListTarget2Element.append(`
						<li class="readUser__list__item" data-readuser-belong="1">
							<div>
								<div>${value.part_code} ${value.name}</div>
								<div>${value.readed_at}</div>
							</div>
						</li>
					`)
				}else {
					modalReadCrewNotBelong++
					readUserListTarget2Element.append(`
						<li class="readUser__list__item" data-readuser-belong="2">
							<div>
								<div>${value.part_code} ${value.name}</div>
								<div>${value.readed_at}</div>
							</div>	
						</li>
					`)
				}
			}
		})

		$(".readUser__switch__item[data-readuser-target='1']").text(`未読(${modalNotReadCrew})`);
		$(".readUser__switch__item[data-readuser-target='2']").text(`既読(${modalReadCrew})`);


		let chkTab = $(".readUser__switch__item.isSelected").data('readuser-target');
		if(chkTab == 1) {
			$('button[data-readuser-belong="1"]').text(`所属(${modalNotReadCrewBelong})`)
			$('button[data-readuser-belong="2"]').text(`未所属(${modalNotReadCrewNotBelong})`)
		}else {
			$('button[data-readuser-belong="1"]').text(`所属(${modalReadCrewBelong})`)
			$('button[data-readuser-belong="2"]').text(`未所属(${modalReadCrewNotBelong})`)
		}

		let target = btnModel.data('modal-target');
		target = 'read';
		modalAnim(target);

		if(target == 'read'){
			let target = $('.readUser__sort').find('.isSelected');
			userSort(target);
		}

	}).fail(function(error){
		console.log(error);
	})
})

$(document).on('click', '.btnModal[data-modal-target="check"]', function(e) {
	e.preventDefault();
	console.log(window.location.href);
	var csrfToken = $('meta[name="csrf-token"]').attr('content');
	$('.modal[data-modal-target="check"] form').append(`
		<input type="hidden" name="current_url" value="${window.location.href}">
	`);
	$.ajax({
		type: 'GET',
		url: '/message/crews',
		data: {
		},
		dataType: 'json',
		headers: {
		'X-CSRF-TOKEN': csrfToken,
		},
	})
	.done(function(res) {
		let crews = res.crews;

		crews.forEach((value, index, array) => {

			$(`.modal[data-modal-target="check"] .readEdit__list__accordion[data-sort-num="${value.name_sort}"] ul`).append(`
				<li>
					${value.part_code} ${value.name} 
					<input type="radio" name="read_edit_radio[]" id="user_${value.part_code}_radio" value="${value.id}">
					<label for="user_${value.part_code}_radio" class="readEdit__list__check">未選択</label>
				</li>
			`)

		});

		let target = "check";
		modalAnim(target);
	}).fail(function(error){
		console.log(error);
	})

})

var clickMessage;
$(document).on('click', '.btnModal[data-modal-target="read"]', function(e) {
	e.preventDefault();

	let btnModel = $(this).closest('.btnModal');
	clickMessage = btnModel;
	var csrfToken = $('meta[name="csrf-token"]').attr('content');
	var message = btnModel.find('.list__item>.list__id').text();
	let editUserListTargetForm = $('.modal[data-modal-target="edit"] form');
	
	// 初期化
	$(`.modal[data-modal-target="edit"] .readEdit__list__accordion li`).remove();
	$('.modal[data-modal-target="edit"] form input[name="message"]').remove();


	if (document.getElementById("reading_crews")) {
		let continueUserListTargetForm = $('.modal[data-modal-target="continue"] form');
		continueUserListTargetForm.append(`
			<input type="hidden" name="message" value="${message}">
		`);
		modalAnim('continue');
		return;
	}

	$.ajax({
		type: 'GET',
		url: '/message/crews-message',
		data: {
			message: message
		},
		dataType: 'json',
		headers: {
		'X-CSRF-TOKEN': csrfToken,
		},
	})
	.done(function(res) {
		let crews = res.crews;

		editUserListTargetForm.append(`
			<input type="hidden" name="message" value="${message}">
		`);

		let sortCodeHeader = "";
		let _index = "";
		let count = 0;
		crews.forEach((value, index, array) => {
			if (value.readed == 0) {
				$(`.modal[data-modal-target="edit"] .sort_name .readEdit__list__accordion[data-sort-num="${value.name_sort}"] ul`).append(`
					<li> ${value.part_code} ${value.name}
						<input type="checkbox" name="read_edit_radio[]" id="user_${value.part_code}" value="${value.c_id}">
						<label for="user_${value.part_code}" class="readEdit__list__check">未選択</label>
					</li>
				`)

				if(count === 0) _index = value.part_code

				sortCodeHeader += `<li>${value.part_code} ${value.name}
						<input type="checkbox" name="read_edit_radio[]" id="user_${value.part_code}" value="${value.c_id}">
						<label for="user_${value.part_code}" class="readEdit__list__check">未選択</label></li>
				`;

				if((count + 1) % 10 == 0 || count == crews.length + 1) {
					let head = `
						<div class="readEdit__list__head">${_index} ~ ${value.part_code}</div>
						<div class="readEdit__list__accordion">
							<ul>
								${sortCodeHeader}
							</ul>
						</div>
					`;
					$(`.modal[data-modal-target="edit"] .sort_code`).append(head);
					sortCodeHeader = "";
					_index = crews[index+1]?.part_code;
				}

				count++;
			}
		})


		let target = btnModel.data('modal-target');
		target = "edit";
		modalAnim(target);

	}).fail(function(error){
		console.log(error);
	})
})


$(document).on('click' , '.readEdit__list__head' , function(){
	$(this).toggleClass('isOpen');
})

/* 各従業員の閲覧管理 */
$(document).on('change' , '.readEdit__list__accordion input[type=checkbox]' , function(){
	if($(this).prop('checked')){
		$(this).siblings('label').text('選択');
	}else{
		$(this).siblings('label').text('未選択');
	}
	let chkTarget = $(this).parents('.readEdit__list').find('input[type=checkbox]:checked');
	let txtReplaceTarget = $(this).parents('.modal__inner').find('button[type=submit]');
	txtReplaceTarget.text('表示する('+chkTarget.length+'人選択中)');
});
/* 閲覧従業員選択 */
$(document).on('change' , '.readEdit__list__accordion input[type=radio]' , function(){
	let txtResetTarget = $(this).parents('.readEdit__list').find('.readEdit__list__check');
	txtResetTarget.text('未選択');
	if($(this).prop('checked')){
		$(this).siblings('label').text('選択');
	}else{
		$(this).siblings('label').text('未選択');
	}

	let chkRadioEnable = $(this).parents('.readEdit__list').find('input[type=radio]:checked');
	let buttonTarget = $(this).parents('.modal__inner').find('button[type=submit]');
	if(chkRadioEnable.length){
		buttonTarget.removeClass('isDisabled').prop('disabled' , false);
	}else{
		buttonTarget.addClass('isDisabled').prop('disabled' , true);		
	}
});

/* 選択中のみ表示 */
$(document).on('change' , '#read_users_sort' , function(){
	if($(this).is(':checked')){
		let targetCheck = $(this).siblings('.readEdit__list').find('input[type=checkbox]');
		targetCheck.each(function(){
			/* 各従業員のチェックを確認 */
			if(!$(this).is(':checked')){
				let parents = $(this).parents('li').hide();
			}
		});
	}else{
		let targetList = $(this).siblings('.readEdit__list').find('li');
		targetList.show();
	}
});

/* 名前・従業員番号切り替え仮置き（必要なら） */
$(document).on('change' , '.readEdit__menu__inner input[type=radio]' , function(){
	let sort_value = $(this).val();
	if(sort_value == 1 || sort_value == 3){
		$('.readEdit__list.sort_name').show();
		$('.readEdit__list.sort_code').hide();
	}
	if(sort_value == 2 || sort_value == 4){
		$('.readEdit__list.sort_name').hide();
		$('.readEdit__list.sort_code').show();
	}
});