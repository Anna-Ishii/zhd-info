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