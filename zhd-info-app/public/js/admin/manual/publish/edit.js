$(document).ready(function(){
    $('#form').submit(function(event) {
        event.preventDefault();
        // ファイルは送信しない
        $('input[type="file"]').prop('disabled', true);
        $('#form').off('submit').submit();
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


// タグを削除
$(document).on('click', '.tag-form-delete', function() {
    $(this).parent().remove();
})

// カーソルをフォーカスする
$('.tag-form .form-control').click(function() {
    $('.tag-form-input').focus();
})

// 入力を監視する
$(document).on('keydown', '.tag-form-input', function(e) {

    let tagLabelText = $(this)[0].innerText;
    // エンターの入力
    if(e.keyCode == 13) {
        if(tagLabelText == "") return false;

        ajaxCreateTag($(this), tagLabelText);
        $(this)[0].innerText = "";
        return false;
    }
    
    // 「,」の入力 
    if(e.keyCode == 188) {
        ajaxCreateTag($(this), tagLabelText);
        $(this)[0].innerText = "";
        return false;
    }

    // Backspace
    if(e.keyCode == 8) {
        if($(this)[0].innerText == "") {
            $(this).prev().remove();
        }
    }
})

function createTag(tagId, tagLabelText) {
    return (
        `
        <span class="focus:outline-none tag-form-label">
            ${tagLabelText}<span class="tag-form-delete">×</span>
            <input type="hidden" name="tag_name[]" value="${tagLabelText}">
            <input type="hidden" name="tag_id[]" value="${tagId}">
        </span>
        `
    )
}

// タグのIDを返す
// タグがなければ作成する
function ajaxCreateTag(form, tagLabelText) {
    let _form = form;
    let _tagLabelText = tagLabelText;
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    let formData = new FormData();
    formData.append("tag_label_text", _tagLabelText);

    $.ajax({
    url: '/admin/manual/publish/tag',
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
                // progressBar.show();
                // progressBar.css('width', progVal + '%');
                console.log(progVal);

                if (progVal == 100)
                {
                    // アップロードが完了したら、サーバー側で保存処理が始まる
                    setTimeout(() => {
                        // progress.hide();
                    }, 1000);
                } 
            }, false);
        }
        return XHR;
    }
    }).done(function(response){
        // labelForm.parent().find('.text-danger').remove();
        _form.before(createTag(response.message_tag_id, _tagLabelText));

    }).fail(function(jqXHR, textStatus, errorThrown){
        // labelForm.parent().find('.text-danger').remove();
        // jqXHR.responseJSON.errorMessages?.forEach((errorMessage)=>{
        //     labelForm.parent().append(`
        //         <div class="text-danger">${errorMessage}</div>
        //     `);
        // })
        // if(errorThrown) {
        //     labelForm.parent().append(`
        //         <div class="text-danger">アップロードできませんでした</div>
        //     `);
        // }
        // fileName.val("");
        // filePath.val("");
    });
}