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
    $('#messageImportModal input[type="button"]').click(function(){
        $('#messageImportModal .modal-body').replaceWith(successTamplate);
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