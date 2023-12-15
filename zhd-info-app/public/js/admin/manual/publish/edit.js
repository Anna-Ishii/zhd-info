$(document).ready(function(){
    $('#form').submit(function(event) {
        event.preventDefault();
        // ファイルは送信しない
        $('input[type="file"]').prop('disabled', true);

        if(!emptyTagInputForm()) {
            appendFormTagInput()
        }

        $('#form').off('submit').submit();
    });
});

function emptyTagLabelForm() {
    return $('.tag-form-label').length == 0;
}

function emptyTagInputForm() {
    return $('.tag-form-input')[0].innerText == '';
}

function appendFormTagInput() {
    $('<input>').attr({
        type: 'hidden',
        name: 'tag_name[]',
        value: $('.tag-form-input')[0].innerText
    }).appendTo($('#form'));
}

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
    
    let fileName = $(this).siblings('input[data-variable-name="manual_file_name"]');
    let filePath = $(this).siblings('input[data-variable-name="manual_file_path"]');

    $.ajax({
        url: '/admin/manual/publish/upload',
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
