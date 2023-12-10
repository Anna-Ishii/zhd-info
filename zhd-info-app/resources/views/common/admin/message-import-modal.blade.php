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
                    最終取り込み日時 {{isset($message_csv_log) ? $message_csv_log : ""}}
                </div>
                <div class="text-right">
                    <input type="button" class="btn btn-admin" value="インポート">
                </div>
            </div>
        </div>
        </div>
    </div>
</div>