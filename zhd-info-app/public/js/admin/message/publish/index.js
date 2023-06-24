$("#editBtn").on('click', function (e) {
    e.preventDefault();
    var checkedCheckboxes = $(".form-check-input:checked");
    if(checkedCheckboxes.length != 1) {
        alert("1つの業務連絡を選択してください") ;
        return;
    }
    var title = checkedCheckboxes.closest('tr');
    var link = title.find('.manual-title a');
    console.log(link);
    window.location.href = link.attr('href');

});

$("#StopBtn").on('click', function (e) {
    e.preventDefault();
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var checkedCheckboxes = $(".form-check-input:checked");
    if(checkedCheckboxes.length < 1) {
        alert("1つの業務連絡を選択してください") ;
        return;
    }

    let checkedValues = [];
    checkedCheckboxes.each(function() {
      checkedValues.push($(this).val());
    });

    fetch("/admin/message/publish/stop", {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({
            'message_id': checkedValues
        })
    })
    .then(response => {
        alert("停止しました");
        window.location.reload();
    })
    .catch(error => {
        alert("エラーです");
    });
});