$("#deleteBtn").on('click', function (e) {
    e.preventDefault();
    console.log("delete");

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var checkedCheckboxes = $(".form-check-input:checked");

    if(checkedCheckboxes.length < 1) {
        alert("ユーザーを選択してください") ;
        return;
    }

    let checkedValues = checkedCheckboxes.map(function() {
        var row = $(this).closest('tr');
        return row.find('.user_id').text();
    }).get();

    fetch("/admin/account/delete", {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({
            'user_id': checkedValues
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
    })

