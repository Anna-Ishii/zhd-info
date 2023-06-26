$("#logout_btn").on('click', function() {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    fetch("/admin/logout", {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken
        },
    })
    .then(response => {
        alert("ログアウトしました");
        window.location.reload();
    })
    .catch(error => {
        alert("エラーです");
    });
})