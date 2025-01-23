$(".editBtn").on('click', function (e) {
    e.preventDefault();
    var targetElement = $(this).parents("tr");
    var admin_id= targetElement.attr("data-admin_id");

    let uri = new URL(window.location.href);
    let targetUrl = uri.origin + "/admin/account/admin/edit/" +  admin_id;

    window.location.href = targetUrl;
});
