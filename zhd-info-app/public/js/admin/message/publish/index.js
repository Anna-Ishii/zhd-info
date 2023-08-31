$(".detailBtn").on('click', function (e) {
    e.preventDefault();
    var targetElement = $(this).parents("tr");
    var message_id= targetElement.attr("data-message_id");

    let uri = new URL(window.location.href);
    let targetUrl = uri.origin + "/admin/message/publish/" +  message_id;

    window.location.href = targetUrl;
});

$(".editBtn").on('click', function (e) {
    e.preventDefault();
    var targetElement = $(this).parents("tr");
    var message_id= targetElement.attr("data-message_id");

    let uri = new URL(window.location.href);
    let targetUrl = uri.origin + "/admin/message/publish/edit/" +  message_id;

    window.location.href = targetUrl;
});

$(".StopBtn").on('click', function (e) {
    e.preventDefault();
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var targetElement = $(this).parents("tr");
    var message_id= targetElement.attr("data-message_id");
    
    let messages = [];
    messages.push(message_id);

    fetch("/admin/message/publish/stop", {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({
            'message_id': messages
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

