<div class="modal fade" id="messageExportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                <h4 class="modal-title">業務連絡csvエクスポート</h4>
            </div>
            <div class="modal-body">
                <div>
                    csvデータをエクスポートします
                    <br>
                    出力対象を選択してください
                </div>
                <form class="form-horizontal">
                    <input type="hidden" name="organization1" value="<?php echo e($organization1->id); ?>">
                    <div class="form-group">
                        <div class="col-sm-3 control-label">
                            <a href="<?php echo e(route('admin.message.publish.export-list', ['all' => true])); ?>&<?php echo e(http_build_query(request()->query())); ?>"
                                class="btn btn-admin exportBtn" data-filename="<?php echo e('業務連絡_' . $organization1->name . now()->format('_Y_m_d') . '.csv'); ?>">全ページ</a>
                        </div>
                        <div class="col-sm-2 col-sm-offset-6 control-label">
                            <a href="<?php echo e(route('admin.message.publish.export-list', ['all' => false])); ?>&<?php echo e(http_build_query(request()->query())); ?>"
                                class="btn btn-admin exportBtn" data-filename="<?php echo e('業務連絡_' . $organization1->name . now()->format('_Y_m_d') . '.csv'); ?>">表示中ページ</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/zhd-info-app/resources/views/common/admin/message-export-modal.blade.php ENDPATH**/ ?>