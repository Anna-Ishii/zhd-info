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
                </div>
                <form class="form-horizontal">
                    <input type="hidden" name="organization1" value="{{$organization1->id}}">
                    <div class="form-group">
                        <div class="col-sm-3 control-label">
                            <a href="{{ route('admin.message.publish.export-list', ['all' => true]) }}&{{ http_build_query(request()->query()) }}"
                                class="btn btn-admin exportBtn" data-filename="{{ '業務連絡_' . $organization1->name . now()->format('_Y_m_d') . '.csv' }}">全ページ</a>
                        </div>
                        <div class="col-sm-2 col-sm-offset-6 control-label">
                            <a href="{{ route('admin.message.publish.export-list', ['all' => false]) }}&{{ http_build_query(request()->query()) }}"
                                class="btn btn-admin exportBtn" data-filename="{{ '業務連絡_' . $organization1->name . now()->format('_Y_m_d') . '.csv' }}">一部ページ</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
