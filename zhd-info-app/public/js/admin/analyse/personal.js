
function mergeRowCell(targetCells) {
    var counter = 0;
	var text ="";
	var target="";

	$(targetCells).each(function(index) {
	  if ($(this).text() == text) {
		counter++;
		if(target !="")
			target.remove();
	  } else  {
		if(target !="")
			target.attr('rowSpan', counter);
		counter=1;
	  }
	  text = $(this).text();
	  target = $(this);
      if (targetCells.length - 1 == index) target.attr('rowSpan', counter);
	});

}
$(function() {
    mergeRowCell($('td.orgDS','tr').get().reverse());
	mergeRowCell($('td.orgAR','tr').get().reverse());
    mergeRowCell($('td.orgBL','tr').get().reverse());
});


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