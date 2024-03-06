"use strict";
const csrfToken = $('meta[name="csrf-token"]').attr('content');

window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        // バックボタンでページが再表示された場合にリロードする
        window.location.reload();
    }
});
if($('.sortMenu').hasClass('isActive')){
	$("body").css("position", "fixed");
}
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

$(document).on('click' , '.btnSort', function(event){
	let chkMenu = $('.sortMenu');
	if(!chkMenu.is(':visible')){
		event.preventDefault();
		$('.sortMenu').addClass('isActive');
		$("body").css("position", "fixed");
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
		getCrewsData = res.crews;

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

$(document).on('click', '.btnModal[data-modal-target="check"]', async function(e) {
	e.preventDefault();

	$('#editSort3').prop('checked', true);
	$('.modal[data-modal-target="check"]').find('.readEdit__list.sort_name').show();
	$('.modal[data-modal-target="check"]').find('.readEdit__list.sort_code').hide();
	$('.modal[data-modal-target="check"]').find('.readEdit__list.filter_word').hide();
	$('.modal input[type="text"]').val("");

	// 初期化
	$(`.modal[data-modal-target="check"] .readEdit__list__accordion li`).remove();
	$(`.modal[data-modal-target="check"] .readEdit__list.sort_code`).find(`.readEdit__list__head, .readEdit__list__accordion`).remove();
	$('.modal[data-modal-target="check"] form input[name="current_url"]').remove();

	$('.modal[data-modal-target="check"] form').append(`
		<input type="hidden" name="current_url" value="${window.location.href}">
	`);

	await crewsData.fetchCheckCrews()
	
	crewsData.crews.forEach((value, index) => {
		// 名前
		$(`.modal[data-modal-target="check"] .readEdit__list__accordion[data-sort-num="${value.name_sort}"] ul`).append(`
			<li>
				${value.part_code} ${value.name} 
				<input type="radio" name="read_edit_radio[]" id="user_${value.part_code}_radio" data-code="${value.part_code}" value="${value.id}">
				<label for="user_${value.part_code}_radio" class="readEdit__list__check">未選択</label>
			</li>
		`)
	})

	crewsData.sortCode();

	let sortCodeHeader = "";
	let _index = "";
	let count = 0;
	crewsData.crews.forEach((value, index, array) => {
		// 従業員番号
		if(count === 0) _index = value.part_code

			sortCodeHeader += 
				`<li>${value.part_code} ${value.name}
					<input type="radio" data-code="${value.part_code}" value="${value.c_id}">
					<label for="user_${value.part_code}_radio" class="readEdit__list__check">未選択</label>
				</li>
			`;

			if((count + 1) % 10 == 0 || index + 1 == crewsData.crews.length) {
				let head = `
					<div class="readEdit__list__head">${_index} ~ ${value.part_code}</div>
					<div class="readEdit__list__accordion">
						<ul>
							${sortCodeHeader}
						</ul>
					</div>
				`;
				$(`.modal[data-modal-target="check"] .sort_code`).append(head);
				sortCodeHeader = "";
				_index = crewsData.crews[index+1]?.part_code;
			}
		count++;
	})

	let target = "check";
	modalAnim(target);

})

var clickMessage;
var getCrewsData;
$(document).on('click', '.btnModal[data-modal-target="read"]', async function(e) {
	e.preventDefault();

	$('#editSort1').prop('checked', true);
	$('.modal[data-modal-target="edit"]').find('.readEdit__list.sort_name').show();
	$('.modal[data-modal-target="edit"]').find('.readEdit__list.sort_code').hide();
	$('.modal[data-modal-target="edit"]').find('.readEdit__list.filter_word').hide();
	$('.modal input[type="text"]').val("");

	let btnModel = $(this).closest('.btnModal');
	clickMessage = btnModel;
	var csrfToken = $('meta[name="csrf-token"]').attr('content');
	var message = btnModel.find('.list__item>.list__id').text();
	let editUserListTargetForm = $('.modal[data-modal-target="edit"] form');
	
	// 初期化
	$(`.modal[data-modal-target="edit"] .readEdit__list__accordion li`).remove();
	$(`.modal[data-modal-target="edit"] .readEdit__list.sort_code`).find(`.readEdit__list__head, .readEdit__list__accordion`).remove();
	$('.modal[data-modal-target="edit"] form input[name="message"]').remove();


	if (document.getElementById("reading_crews")) {
		let continueUserListTargetForm = $('.modal[data-modal-target="continue"] form');
		continueUserListTargetForm.append(`
			<input type="hidden" name="message" value="${message}">
		`);
		modalAnim('continue');
		return;
	}

	await crewsData.fetchReadCrews(message);
	
	editUserListTargetForm.append(`
		<input type="hidden" name="message" value="${message}">
	`);

	crewsData.crews.forEach((value, index, array) => {
		if (value.readed == 0) {
			$(`.modal[data-modal-target="edit"] .sort_name .readEdit__list__accordion[data-sort-num="${value.name_sort}"] ul`).append(`
				<li> ${value.part_code} ${value.name}
					<input type="checkbox" name="read_edit_radio[]" id="user_${value.part_code}_check" data-code="${value.part_code}" value="${value.c_id}">
					<label for="user_${value.part_code}_check" class="readEdit__list__check">未選択</label>
				</li>
			`)
		}
	})

	crewsData.sortCode();
	let sortCodeHeader = "";
	let _index = "";
	let count = 0;

	crewsData.crews.forEach((value, index, array) => {
		if (value.readed == 0) {
			if(count === 0) _index = value.part_code

			sortCodeHeader += `<li>${value.part_code} ${value.name}
					<input type="checkbox" data-code="${value.part_code}" value="${value.c_id}">
					<label for="user_${value.part_code}_check" class="readEdit__list__check">未選択</label></li>
			`;

			console.log(`${index}, ${crewsData.crews.length}`);
			if((count + 1) % 10 == 0 || index + 1 == crewsData.crews.length ) {
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
				_index = crewsData.crews[index+1]?.part_code;
			}

			count++;
		}
	})
	
	let target = btnModel.data('modal-target');
	target = "edit";
	modalAnim(target);

})


$(document).on('click' , '.readEdit__list__head' , function(){
	$(this).toggleClass('isOpen');
})

$(document).on('click' , '.readEdit__list__accordion input[type=checkbox]' , function(){
	let code = $(this).data("code");

	let check = $(this).parents('div[data-modal-target="edit"]').find(`input[data-code="${code}"]`);

	if($(this).prop('checked')){
		// $(this).siblings('label').text('選択');
		check.prop('checked', true)
	}else{
		// $(this).siblings('label').text('未選択');
		check.prop('checked', false)
	}
});

/* 各従業員の閲覧管理 */
$(document).on('change' , '.readEdit__list__accordion input[type=checkbox]' , function(){
	let id = $(this).attr('id');
	if($(this).prop('checked')){
		$(this).parents('.readEdit').find(`.readEdit__list__check[for="${id}"]`).text('選択');
	}else{
		$(this).parents('.readEdit').find(`.readEdit__list__check[for="${id}"]`).text('未選択');
	}
	let chkTarget = $(this).parents('.readEdit__list').find('input[type=checkbox]:checked');
	let txtReplaceTarget = $(this).parents('.modal__inner').find('button[type=submit]');
	txtReplaceTarget.text('表示する('+chkTarget.length+'人選択中)');
});

$(document).on('click' , '.readEdit__list__accordion input[type=radio]' , function(){
	let code = $(this).data("code");
	$(this).parents('div[data-modal-target="check"]').find(`.readEdit__list input`).prop('checked', false);
	let checkRadio = $(this).parents('div[data-modal-target="check"]').find(`input[data-code="${code}"]`);
	checkRadio.prop('checked',true);
});

/* 閲覧従業員選択 */
$(document).on('change' , '.readEdit__list__accordion input[type=radio]' , function(){
	let txtResetTarget = $(this).parents('.readEdit').find('.readEdit__list__check');
	txtResetTarget.text('未選択');
	let id = $(this).attr('id');
	if($(this).prop('checked')){
		$(this).parents('.readEdit').find(`.readEdit__list__check[for="${id}"]`).text('選択');
	}else{
		$(this).parents('.readEdit').find(`.readEdit__list__check[for="${id}"]`).text('未選択');
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

var currnt_sort_value = 1;
/* 名前・従業員番号切り替え仮置き（必要なら） */
$(document).on('change' , '.readEdit__menu__inner input[type=radio]' , function(){
	let sort_value = $(this).val();
	currnt_sort_value = sort_value;
	let searchText = $(this).parents('.readEdit__menu__inner').find('input[type="text"]').val();
	if(searchText.length != 0) return;

	if(sort_value == 1 || sort_value == 3){
		$('.readEdit__list.sort_name').show();
		$('.readEdit__list.sort_code').hide();
	}
	if(sort_value == 2 || sort_value == 4){
		$('.readEdit__list.sort_name').hide();
		$('.readEdit__list.sort_code').show();
	}
});

// ログアウトサブミット
$(document).on('click', '#crewLogout', function (){
	document.getElementById('logoutForm').submit();
})

$(document).on('input', '.modal input[type="text"]', function () {
	const PARTCODE_INDEX = 0;
	const NAME_INDEX = 1;
	const NAMEKANA_INDEX = 2;
	let searchText = $(this).val();
	let searchText_formatted = normalizeString(searchText);
	let readEdit_sortName = $(this).parents('.readEdit').find('.readEdit__list.sort_name');
	let readEdit_sortCode = $(this).parents('.readEdit').find('.readEdit__list.sort_code');
	let readEdit_filterWord = $(this).parents('.readEdit').find('.readEdit__list.filter_word');
	readEdit_filterWord.find('ul').empty()
	let li_crew_dom = ``;
	if(searchText.length == 0){
		// let sort_value = $(this).parents('.readEdit').find(".readEdit__menu__inner input[type=radio]").val()
		if(currnt_sort_value == 1 || currnt_sort_value == 3){
			readEdit_sortName.show()
			readEdit_sortCode.hide()
		}
		if(currnt_sort_value == 2 || currnt_sort_value == 4){
			readEdit_sortName.hide()
			readEdit_sortCode.show()
		}
		readEdit_filterWord.hide()
	}else{
		readEdit_sortName.hide();
		readEdit_sortCode.hide();
		readEdit_filterWord.show()

		let modal_target = $(this).parents('.modal').data('modal-target');
		if (modal_target == "check") {
			crewsData.crews.filter(item =>
				Object.values(item).some((value, index) => {
					// if(index == PARTCODE_INDEX || index == NAME_INDEX || index == NAMEKANA_INDEX){
						if(value?.toString().includes(kanaFullToHalf(searchText_formatted))){
							let isChecked = $(`#user_${item.part_code}_radio`).prop('checked');
							li_crew_dom += 
								`<li>${item.part_code} ${item.name}
									<input type="radio"  value="${item.c_id}" data-code="${item.part_code}" ${isChecked ? "checked" : ""}>
									<label for="user_${item.part_code}_radio" class="readEdit__list__check">${isChecked ? "選択" : "未選択"}</label>
								</li>`;
							
						}
					// }
				})
			);
		}else if(modal_target == "edit") {
			crewsData.crews.filter(item =>
				Object.values(item).some((value, index) => {
					if(index == PARTCODE_INDEX || index == NAME_INDEX){
						if(value?.toString().includes(searchText_formatted) && item.readed == 0){
							let isChecked = $(`#user_${item.part_code}_check`).prop('checked');
							li_crew_dom += 
								`<li>${item.part_code} ${item.name}
									<input type="checkbox" value="${item.c_id}" data-code="${item.part_code}" ${isChecked ? "checked" : ""}>
									<label for="user_${item.part_code}_check" class="readEdit__list__check">${isChecked ? "選択" : "未選択"}</label>
								</li>`;
							
						}
						
					}else if(index == NAMEKANA_INDEX){
						if(value.includes(kanaFullToHalf(searchText_formatted)) && item.readed == 0){
							let isChecked = $(`#user_${item.part_code}_check`).prop('checked');
							li_crew_dom += 
								`<li>${item.part_code} ${item.name}
									<input type="checkbox" value="${item.c_id}" data-code="${item.part_code}" ${isChecked ? "checked" : ""}>
									<label for="user_${item.part_code}_check" class="readEdit__list__check">${isChecked ? "選択" : "未選択"}</label>
								</li>`;
							
						}
					}
				})
			);
		}
		readEdit_filterWord.find('ul').append(li_crew_dom);
	}

})

function normalizeString(str) {
    // ひらがなを半角カタカナに変換する関数
    function hiraganaToKatakana(hiragana) {
        return hiragana.replace(/[\u3041-\u3096]/g, function(match) {
            var chr = match.charCodeAt(0) + 0x60;
            return String.fromCharCode(chr);
        });
    }

    // 文字列を正規化して返す
    return hiraganaToKatakana(str).toLowerCase();
}

const crewsData = {
	crews: null,
	
	// 既読チェックするときのクルーを取得
	fetchCheckCrews: async function () {
		const url = '/message/crews';

		await fetchData(url)
			.done(data => {
				this.crews = data.crews
			})
	},

	// 既読するときのクルーを取得
	fetchReadCrews: async function (message_id) {
		const url = '/message/crews-message';
		const data = { message : message_id}
		const options = {
			data: data
		}
		await fetchData(url, options)
			.done(data => {
				this.crews = data.crews
			})
	},
	// フリーワード検索
	searchCrews: function (text) {
		
	},
	// 従業員番号でソートする
	sortCode: function () {
		this.crews.sort((a, b) => {
			return a.part_code - b.part_code;
		})
	} 
}

function fetchData(url, options = {}) {
  // デフォルトの設定を指定
  const defaultOptions = {
    method: 'GET',
    dataType: 'json', // 応答のデータタイプ
	headers: {
		'X-CSRF-TOKEN': csrfToken,
	},
    // 他のオプションをここに追加できます
    ...options,
  };

  // jQueryの$.ajax()を使用してAjaxリクエストを実行
  return $.ajax({
    url,
    ...defaultOptions,
  }).fail((jqXHR, textStatus, errorThrown) => {
    console.error('Ajax error:', textStatus, errorThrown);
    throw errorThrown; // エラーを再スローして呼び出し元で処理できるようにする
  });
}

function kanaFullToHalf(str){
    let kanaMap = {
        "ガ": "ｶﾞ", "ギ": "ｷﾞ", "グ": "ｸﾞ", "ゲ": "ｹﾞ", "ゴ": "ｺﾞ",
        "ザ": "ｻﾞ", "ジ": "ｼﾞ", "ズ": "ｽﾞ", "ゼ": "ｾﾞ", "ゾ": "ｿﾞ",
        "ダ": "ﾀﾞ", "ヂ": "ﾁﾞ", "ヅ": "ﾂﾞ", "デ": "ﾃﾞ", "ド": "ﾄﾞ",
        "バ": "ﾊﾞ", "ビ": "ﾋﾞ", "ブ": "ﾌﾞ", "ベ": "ﾍﾞ", "ボ": "ﾎﾞ",
        "パ": "ﾊﾟ", "ピ": "ﾋﾟ", "プ": "ﾌﾟ", "ペ": "ﾍﾟ", "ポ": "ﾎﾟ",
        "ヴ": "ｳﾞ", "ヷ": "ﾜﾞ", "ヺ": "ｦﾞ",
        "ア": "ｱ", "イ": "ｲ", "ウ": "ｳ", "エ": "ｴ", "オ": "ｵ",
        "カ": "ｶ", "キ": "ｷ", "ク": "ｸ", "ケ": "ｹ", "コ": "ｺ",
        "サ": "ｻ", "シ": "ｼ", "ス": "ｽ", "セ": "ｾ", "ソ": "ｿ",
        "タ": "ﾀ", "チ": "ﾁ", "ツ": "ﾂ", "テ": "ﾃ", "ト": "ﾄ",
        "ナ": "ﾅ", "ニ": "ﾆ", "ヌ": "ﾇ", "ネ": "ﾈ", "ノ": "ﾉ",
        "ハ": "ﾊ", "ヒ": "ﾋ", "フ": "ﾌ", "ヘ": "ﾍ", "ホ": "ﾎ",
        "マ": "ﾏ", "ミ": "ﾐ", "ム": "ﾑ", "メ": "ﾒ", "モ": "ﾓ",
        "ヤ": "ﾔ", "ユ": "ﾕ", "ヨ": "ﾖ",
        "ラ": "ﾗ", "リ": "ﾘ", "ル": "ﾙ", "レ": "ﾚ", "ロ": "ﾛ",
        "ワ": "ﾜ", "ヲ": "ｦ", "ン": "ﾝ",
        "ァ": "ｧ", "ィ": "ｨ", "ゥ": "ｩ", "ェ": "ｪ", "ォ": "ｫ",
        "ッ": "ｯ", "ャ": "ｬ", "ュ": "ｭ", "ョ": "ｮ",
        "。": "｡", "、": "､", "ー": "ｰ", "「": "｢", "」": "｣", "・": "･",
        "　": " "
    };
    let reg = new RegExp('(' + Object.keys(kanaMap).join('|') + ')', 'g');
    return str.replace(reg, function(s){
        return kanaMap[s];
    }).replace(/゛/g, 'ﾞ').replace(/゜/g, 'ﾟ');
}