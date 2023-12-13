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
                <div class="form-group">
                    <label class="col-lg-2 control-label">CSV添付<span class="text-danger required">*<span></label>
                    <div class="col-lg-9">
                        <label class="inputFile form-control">
                            <span class="fileName">ファイルを選択またはドロップ</span>
                            <input type="file" name="csv" accept=".csv">
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="text-right">
                        <input type="button" class="btn btn-admin" value="インポート">
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>