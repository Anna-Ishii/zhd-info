<div class="modal fade" id="manualImportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                <h4 class="modal-title">動画マニュアルcsvインポート</h4>
            </div>
            <div class="modal-body">
                <div>
                    csvデータを動画マニュアルに上書きします
                </div>
                <form class="form-horizontal">
                    <input type="hidden" name="organization1" value="<?php echo e($organization1->id); ?>">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">csv添付<span class="text-danger required">*<span></label>
                        <div class="col-sm-9">
                            <label class="inputFile form-control">
                                <span class="fileName">ファイルを選択またはドロップ</span>
                                <input type="file" name="csv" accept=".csv">
                            </label>
                            <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0"
                                aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-3 control-label">
                            <span class="text-danger required">*</span>：必須項目
                        </div>
                        <div class="col-sm-2 col-sm-offset-6 control-label">
                            <input type="button" class="btn btn-admin" value="インポート" disabled>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/zhd-info-app/resources/views/common/admin/manual-import-modal.blade.php ENDPATH**/ ?>