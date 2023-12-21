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

window.onbeforeunload = function(e) {
    if(inputCheck()) return;
    e.preventDefault();
    e.returnValue = "";
    overlay.style.display = 'none';
}

// 入力チェック
// 何か入力状態であれば、falseを返す
function inputCheck() {
    if($('input[name="title"]').val() != "") return false;
    if($('textarea[name="description"]').val() != "") return false;
    if($('input[name="file"]').val() != "") return false;
    if($('input[name="category_id"]:checked').val() != null) return false;
    if($("input[class='dateDisabled']:checked").length > 0) return false;
    if(!(_manual_flow_titleCheck($('input[name^="manual_flow"][name$="[title]"]')))) return false
    if(!(_manual_flow_fileCheck($('input[name^="manual_flow"][name$="[file]"]')))) return false
    if(!(_manual_flow_detailCheck($('textarea[name^="manual_flow"][name$="[detail]"]')))) return false
    if($('input[name="start_datetime"]').val() != "") return false
    if($('input[name="end_datetime"]').val() != "") return false
    if($('input[name="brand[]"]:checked').val() != null) return false
    return true
}

// 値があったらfalseを変えす
function _manual_flow_titleCheck(objects) {
    if(objects == null) return true

    let rtn = true;
    objects.each((i,v) => {
        if(v.value != "") {
            rtn = false;
            return false; //ループを抜ける
        }
    });
    return rtn;
}

// fileがあったらfalseを変えす
function _manual_flow_fileCheck(objects) {
    if(objects == null) return true

    let rtn = true;
    objects.each((i,v) => {
        if(v.files.length > 0) {
            rtn = false;
            return false; //ループを抜ける
        }

    });
    return rtn;
}

function _manual_flow_detailCheck(objects) {
    if(objects == null) return true

    let rtn = true;
    objects.each((i,v) => {
        if(v.value != "") {
            rtn = false;
            return false; //ループを抜ける
        }
    });
    console.log(rtn);
    return rtn;
}
