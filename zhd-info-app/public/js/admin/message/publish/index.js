$("#editBtn").on('click', function (e) {
    e.preventDefault();
    var checkedCheckboxes = $(".form-check-input:checked");
    if(checkedCheckboxes.length != 1) {
        alert("1つの業務連絡を選択してください") ;
        return;
    }
    var title = checkedCheckboxes.closest('tr');
    var link = title.find('.message-title a');
    console.log(link);
    window.location.href = link.attr('href');

});

$("#StopBtn").on('click', function (e) {
    e.preventDefault();
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var checkedCheckboxes = $(".form-check-input:checked");
    if(checkedCheckboxes.length != 1) {
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

