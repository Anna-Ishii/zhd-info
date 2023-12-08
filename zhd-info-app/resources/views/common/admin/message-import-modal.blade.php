<div class="modal fade" id="messageImportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                <h4 class="modal-title">業務連絡csvインポート</h4>
            </div>
            <div class="modal-body">           
                <div>
                    csvデータを業務連絡に上書きします
                </div>
                <div>
                    <input type="file" name="csv" accept=".csv">
                </div>
                <div>
                    最終取り込み日時
                </div>
                <div class="text-right">
                    <input type="button" class="btn btn-admin" value="インポート">
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
<script>
    $('#messageImportModal input[type="button"]').click(function(e){
        e.preventDefault();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        let formData = new FormData();
        formData.append("file", $('#messageImportModal input[name="csv"]')[0].files[0]);
        
        var overlay = document.getElementById('overlay');
	    overlay.style.display = 'block';

        $('#messageImportModal .modal-body .alert-danger').remove();
        $.ajax({
        url: '/admin/message/publish/import',
        type: 'post',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
        }).done(function(response){
            overlay.style.display = 'none';
            $('#messageImportModal .modal-body').replaceWith(successTamplate);

        }).fail(function(jqXHR, textStatus, errorThrown){
            overlay.style.display = 'none';

            $('#messageImportModal .modal-body').prepend(`
                <div class="alert alert-danger">
                    <ul></ul>
                </div>
            `);
            // labelForm.parent().find('.text-danger').remove();
            
            jqXHR.responseJSON.error_message?.forEach((errorMessage)=>{

                console.log(errorMessage['row']);
                let error_li;
                
                errorMessage['errors'].forEach((error) => {
                    $('#messageImportModal .modal-body .alert ul').append(
                       `<li>${errorMessage['row']}行目：${error}</li>`
                    );
                })
            })
            if(errorThrown) {
                console.log("エラーが発生しました");
            }
            // fileName.val("");
            // filePath.val("");
        });
    })

    const successTamplate = `
        <div class="modal-body">
            <div class="text-center">
                <div>
                    csv取り込み完了しました
                </div>
                <div>
                    <a href="/admin/message/publish" class=" btn btn-admin">一覧に戻る</a>
                </div>
            </div>
        </div>
    `
    
</script>