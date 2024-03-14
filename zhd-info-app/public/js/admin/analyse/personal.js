$(window).on('load' , function(){
	let d = new Date();
	/* datetimepicker */
	$.datetimepicker.setLocale('ja');

    $('#publishDateFrom').datetimepicker({
		format:'Y/m/d(D)',
		timepicker:false,
		onShow:function( ct ){
			this.setOptions({
				maxDate:jQuery('#publishDateTo').val()?jQuery('#publishDateTo').val():false
			})
		 },
		 defaultDate: d,
	});	
	$('#publishDateTo').datetimepicker({
		format:'Y/m/d(D)',
		timepicker:false,
		onShow:function( ct ){
			this.setOptions({
				minDate:jQuery('#publishDateFrom').val()?jQuery('#publishDateFrom').val():false
			})
		 },
		 defaultDate: d,
	});	
});
$(document).ready(function () {
	// テーブルソートの初期化
	$("#table").tablesorter({
		headers: {
			'0': { 
				sorter: false 
			},
			'1': { 
				sorter: false 
			},
			'2': { 
				sorter: false 
			},
			'3': { 
				sorter: false 
			},
			'4': { 
				sorter: false 
			},
			// '.head2': {
			// 	sorter: "float",
			// },
		},
	});
});

//
// 店舗のポプアップのjs
//

/* 汎用モーダル処理 */
function modalAnim(e){
	let modalTarget = $('.modal2[data-modal-target='+e+']')
	if(modalTarget.length){
		$('.modalBg').show();
		modalTarget.show();	
	}
}

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

$(document).on('click' , '.modal__close, .modalBg', function(e){
	if($(this).hasClass('modalBg') && $(e).closest('.modal2')){
		$('.modalBg').hide();
		$('.modal2').hide();
	}else{
		$('.modalBg').hide();
		$('.modal2').hide();
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

$(document).on('click' , '.readEdit__list__head' , function(){
	$(this).toggleClass('isOpen');
})

var modalReadCrew = 0; // 既読数
var modalNotReadCrew = 0; // 未読数
var modalReadCrewBelong = 0; // 所属・既読数
var modalReadCrewNotBelong = 0; // 未所属・既読数
var modalNotReadCrewBelong = 0; // 所属・未読数
var modalNotReadCrewNotBelong = 0; // 未所属・未読数

$(document).on('click', '.view_rate[data-view-type="shops"]', function(e) {
	e.preventDefault();
	e.stopPropagation();
	var csrfToken = $('meta[name="csrf-token"]').attr('content');
	var message = $(this).parent('td').attr("data-message");
	var shop = $(this).parent('td').attr("data-shop");

	$.ajax({
		type: 'GET',
		url: '/admin/analyse/personal/shop-message',
		data: {
			shop: shop,
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

		modalAnim('read');

		if(target == 'read'){
			let target = $('.readUser__sort').find('.isSelected');
			userSort(target);
		}

	}).fail(function(error){
		console.log(error);
	})
})

$(document).on('click', '.view_rate[data-view-type="orgs"]', function(e) {
	e.preventDefault();
	e.stopPropagation();
	var csrfToken = $('meta[name="csrf-token"]').attr('content');
	var message = $(this).parent('td').attr("data-message");
	var org_type = $(this).parent('td').attr("data-org-type");
	var org_id = $(this).parent('td').attr("data-org-id");

	$.ajax({
		type: 'GET',
		url: '/admin/analyse/personal/org-message',
		data: {
			message: message,
			org_type: org_type,
			org_id: org_id
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

		modalAnim('read');

		if(target == 'read'){
			let target = $('.readUser__sort').find('.isSelected');
			userSort(target);
		}

	}).fail(function(error){
		console.log(error);
	})
})