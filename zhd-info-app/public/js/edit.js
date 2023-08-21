'use strict';

/* ファイル検知 */
function changeFileName(e){
	let fileNameTarget = e.siblings('.fileName');
	if(e.val() == ''){
		fileNameTarget.empty().text('ファイルを選択またはドロップ');
	}else{
		let chkFileName = e.prop('files')[0].name;
		fileNameTarget.empty().text(chkFileName);
	}
}
$(document).on('change' , '.inputFile input[type=file]' , function(){
	let changeTarget = $(this);
	changeFileName(changeTarget);
});

$(window).on('load' , function(){
	var d = new Date();
	d.setDate(d.getDate() + 1);
	/* datetimepicker */
	$.datetimepicker.setLocale('ja');
	$('#dateFrom').datetimepicker({
		format:'Y/m/d H:00',
		onShow:function( ct ){
			this.setOptions({
				maxDate:jQuery('#dateTo').val()?jQuery('#dateTo').val():false
			})
		 },
		 defaultDate: d,
		 defaultTime: '00:00',
	});	
	$('#dateTo').datetimepicker({
		format:'Y/m/d H:i',
		onShow:function( ct ){
			this.setOptions({
				minDate:jQuery('#dateFrom').val()?jQuery('#dateFrom').val():false
			})
		},
		allowTimes:[
			'00:00','01:00','02:00','03:00','04:00','05:00','06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00',
		],
		defaultDate: d,
		defaultTime: '00:00',
	});	
});

/* パスワード入力検知 */
$(document).on('focusout' , '.inputPassword , .inputPassword2' , function(){
	let linkTarget;
	if($(this).hasClass('inputPassword')){
		linkTarget = $('.inputPassword2');
	}else if($(this).hasClass('inputPassword2')){
		linkTarget = $('.inputPassword');
	}

	let chkLinkVal = linkTarget.val();
	console.log(chkLinkVal);
	if(chkLinkVal != '' && $(this).val() != chkLinkVal){
		$('input[name=check_password]').val('1');
	}else{
		$('input[name=check_password]').val('0');
	}
});

$(document).on('click' , '#submitbutton' , function(){
	if($('input[name=check_password]').length){
		if($('input[name=check_password]').val() != 0){
			alert('パスワードが一致しません。\nパスワード欄、確認欄を入力し直してください。');
			return false;
		}
	}
});

/* 日程の未定選択時 */
function toggleInputDate(e){
	let chkTargetData = e.data('target');
	let toggleTarget = $('#'+chkTargetData);
	if(!e.prop('checked')){
		toggleTarget.prop('disabled' , false);
	}else{
		toggleTarget.val('').prop('disabled' , true);		
	}
}
$(document).on('change' , '.dateDisabled' , function(){
	let changeTarget = $(this);
	toggleInputDate(changeTarget);
});

/* 全業態、対象ブロック全て選択時 */
function chkAll(e){
	let targets = e.parents('.checkArea').find('input[type=checkbox]').not('#checkAll');
	if(!e.prop('checked')){
		targets.each(function(){
			if($(this).prop('checked')){
				$(this).prop('checked' , false);
			}
		});
	}else{
		targets.each(function(){
			if(!$(this).prop('checked')){
				$(this).prop('checked' , true);
			}
		});
	}
}
$(document).on('click' , '#checkAll' , function(){
	let clickTarget = $(this);
	chkAll(clickTarget);
});

/* チェックを入れた時の全業態、対象ブロック全て部分の切り替え */
function toggleBulkCheckbox(e){
	let chkTarget = e.parents('.checkArea').find('.checkCommon');
	let toggleTarget = e.parents('.checkArea').find('#checkAll');
	chkTarget.each(function(){
		if(!$(this).prop('checked')){
			toggleTarget.prop('checked' , false);
			return false;
		}else{
			toggleTarget.prop('checked' , true);
		}
	});
}
$(document).on('click' , '.checkCommon' , function(){
	let clickTarget = $(this);
	toggleBulkCheckbox(clickTarget);
});

/* name振り直し */
function countVariableBox(){
	let fileTarget = $('.manualVariableArea').find('.manualVariableBox').not('#cloneTarget');
	let fileTargetNum = 0;
	fileTarget.each(function(){
		$(this).find('input[data-variable-name=manual_flow_title]').attr({'name':'manual_flow['+fileTargetNum+'][title]'});
		$(this).find('input[data-variable-name=manual_file]').attr({'name':'manual_flow['+fileTargetNum+'][file]'});
		$(this).find('textarea[data-variable-name=manual_flow_detail]').attr('name' , 'manual_flow['+fileTargetNum+'][detail]');
		fileTargetNum = fileTargetNum + 1;
	});
}
/* 手順タイトル、添付ファイル、手順内容の追加 */
function addVariableBox(callback){
	let cloneTarget = $('#cloneTarget');
	cloneTarget.clone().appendTo('.manualVariableArea');
	if($('.manualVariableBox').length != 1){
		let removeIdTarget = $('.manualVariableArea').find('.manualVariableBox:last-child');
		removeIdTarget.removeAttr('id');
	}

	callback();
}
$(document).on('click' , '.btnAddBox' , function(){
	addVariableBox(countVariableBox);
});

/* 手順タイトル、添付ファイル、手順内容の削除 */
function removeVariableBox(e , callback){
	let removeTargetTitle = e.parents('.manualVariableBox').find('input[data-variable-name=manual_flow_title]').val();
	if(removeTargetTitle != ''){
		if(confirm('手順「'+removeTargetTitle+'」を削除します。\nよろしいですか？')){
			e.parents('.manualVariableBox').remove();
			callback();	
		}
	}else{
		e.parents('.manualVariableBox').remove();
		callback();	
	}
}
$(document).on('click' , '.btnRemoveBox' , function(){
	let removeTarget = $(this);
	removeVariableBox(removeTarget , countVariableBox);
});

$(document).on('submit' , '#form' , function(event){
	// イベントを停止する
	event.preventDefault();
	// ローディングアニメーション
	var overlay = document.getElementById('overlay');
	overlay.style.display = 'block';

	// form.submit()ではサブミットのボタンをpostしないので、パラメータを追加する
	var submitterButtonName = event.originalEvent.submitter.attributes['name'].value;
	if(submitterButtonName == 'save'){
		let fm = $('#form');
		fm.append($('<input />', {
            type: 'hidden',
            name: 'save',
            value: 1,
        }));
	}
	// 改めてsubmitする
	form.submit();
});

$(document).on('invalid' , '#form' , function(event){
	overlay.style.display = 'none';
}, true);
