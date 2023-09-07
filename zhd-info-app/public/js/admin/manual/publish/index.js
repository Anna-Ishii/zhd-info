$(".detailBtn").on('click', function (e) {
    e.preventDefault();
    var targetElement = $(this).parents("tr");
    var manual_id= targetElement.attr("data-manual_id");

    let uri = new URL(window.location.href);
    let targetUrl = uri.origin + "/admin/manual/publish/" + manual_id;

    window.location.href = targetUrl;
});

$(".editBtn").on('click', function (e) {
    e.preventDefault();
    var targetElement = $(this).parents("tr");
    var manual_id= targetElement.attr("data-manual_id");

    let uri = new URL(window.location.href);
    let targetUrl = uri.origin + "/admin/manual/publish/edit/" + manual_id;

    window.location.href = targetUrl;

});

$(".StopBtn").on('click', function (e) {
    e.preventDefault();
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var targetElement = $(this).parents("tr");
    var manual_id= targetElement.attr("data-manual_id");
    
    let manuals = [];
    manuals.push(manual_id);

    fetch("/admin/manual/publish/stop", {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({
            'manual_id': manuals
        })
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            return response.json().then(data => {
                throw new Error(data.message); // エラーメッセージをスロー
            });
        }})
        .then(data => {
            const message = data.message;
            // メッセージの表示や処理を行う
            alert(message);
            window.location.reload();
        })
        .catch(error => {
            const message = error.message;
            alert(message);
        })    
});

$(window).on('load' , function(){
	let d = new Date();
	/* datetimepicker */
	$.datetimepicker.setLocale('ja');

	$('#publishDateFrom').datetimepicker({
		format:'Y/m/d',
		timepicker:false,
		onShow:function( ct ){
			this.setOptions({
				maxDate:jQuery('#publishDateTo').val()?jQuery('#publishDateTo').val():false
			})
		 },
		 defaultDate: d,
	});	
	$('#publishDateTo').datetimepicker({
		format:'Y/m/d',
		timepicker:false,
		onShow:function( ct ){
			this.setOptions({
				minDate:jQuery('#publishDateFrom').val()?jQuery('#publishDateFrom').val():false
			})
		 },
		 defaultDate: d,
	});	
    $('#readedDateFrom').datetimepicker({
		format:'Y/m/d H:i',
		onShow:function( ct ){
			this.setOptions({
				maxDate:jQuery('#readedDateTo').val()?jQuery('#readedDateTo').val():false
			})
		 },
		 defaultDate: d,
	});	
	$('#readedDateTo').datetimepicker({
		format:'Y/m/d H:i',
		onShow:function( ct ){
			this.setOptions({
				minDate:jQuery('#readedDateFrom').val()?jQuery('#readedDateFrom').val():false
			})
		 },
		 defaultDate: d,
	});	
});