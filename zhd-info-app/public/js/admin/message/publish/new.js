$(document).ready(function(){
    $('#form').submit(function(event) {
        event.preventDefault();
        // ファイルは送信しない
        $('input[type="file"]').prop('disabled', true);
        $('#form').submit();
    });
});
$(document).on('change', 'input[type="file"]', function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    let formData = new FormData();
    formData.append("file", $(this)[0].files[0]);

    var labelForm = $(this).parent();
    var progress = labelForm.parent().find('.progress');
    var progressBar = progress.children(".progress-bar");

    progressBar.hide();
    progressBar.css('width', 0 + '%');
    progress.show();
    
    let fileName = $(this).siblings('input[name="file_name"]');
    let filePath = $(this).siblings('input[name="file_path"]');

    $.ajax({
        url: '/admin/message/publish/upload',
        type: 'post',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
        xhr: function(){
            XHR = $.ajaxSettings.xhr();
            if(XHR.upload){
                XHR.upload.addEventListener('progress',function(e){
                    var progVal = parseInt(e.loaded/e.total*10000)/100 ;
                    progressBar.show();
                    progressBar.css('width', progVal + '%');
                    console.log(progVal);

                    if (progVal == 100)
                    {
                        // アップロードが完了したら、サーバー側で保存処理が始まる
                        setTimeout(() => {
                            progress.hide();
                        }, 1000);
                    } 
                }, false);
            }
            return XHR;
        }
    }).done(function(response){
        labelForm.parent().find('.text-danger').remove();
        fileName.val(response.content_name);
        filePath.val(response.content_url);

    }).fail(function(jqXHR, textStatus, errorThrown){
        labelForm.parent().find('.text-danger').remove();
        jqXHR.responseJSON.errorMessages?.forEach((errorMessage)=>{
            labelForm.parent().append(`
                <div class="text-danger">${errorMessage}</div>
            `);
        })
        if(errorThrown) {
            labelForm.parent().append(`
                <div class="text-danger">アップロードできませんでした</div>
            `);
        }
        fileName.val("");
        filePath.val("");
    });
});

window.onbeforeunload = function(e) {
    if(inputCheck()) return;
    e.preventDefault();
    e.returnValue = "";
}

// 入力チェック
// 何か入力状態であれば、falseを返す
function inputCheck() {
    if($('input[name="title"]').val() != "") return false;
    if($('input[name="file"]').val() != "") return false;
    if($('input[name="category_id"]:checked').val() != null) return false;
    if($('input[name="emergency_flg"]:checked').val() != null) return false
    if($("input[class='dateDisabled']:checked").length > 0) return false;
    if($('input[name="start_datetime"]').val() != "") return false
    if($('input[name="end_datetime"]').val() != "") return false
    if($('input[name="target_roll[]"]:checked').val() != null) return false
    if($('input[name="brand[]"]:checked').val() != null) return false
    if($('input[name="organization[]"]:checked').val() != null) return false
    return true
}