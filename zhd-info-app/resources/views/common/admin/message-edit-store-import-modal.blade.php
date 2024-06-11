<!-- モーダル：CSV取込 -->
<div class="modal fade" id="messageStoreImportModal" tabindex="-1">
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
                <form class="form-horizontal">
                    {{-- <input type="hidden" name="organization1" value="{{$organization1->id}}"> --}}
                    <input type="hidden" name="organization1" value="">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">csv添付<span class="text-danger required">*<span></label>
                        <div class="col-sm-9">
                            <label class="inputFile form-control">
                                <span class="fileName">ファイルを選択またはドロップ</span>
                                <input type="file" name="csv" accept=".csv">
                            </label>
                            <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-3 control-label">
                            <span class="text-danger required">*</span>：必須項目
                        </div>
                        <div class="col-sm-2 col-sm-offset-6 control-label">
                            {{-- <input type="button" class="btn btn-admin" data-toggle="modal" data-target="#messageStoreEditModal" value="インポート" disabled> --}}
                            <input type="button" id="importButton" class="btn btn-admin" data-toggle="modal" data-target="#messageStoreEditModal" value="インポート">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="messageStoreEditModal" style="top: -20%;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                <h4 class="modal-title">以下店舗で取り込みました。<br /><small class="text-muted">変更ある場合は、「再取込」もしくは下記で選択しなおしてください</small></h4>
            </div>

            <div class="modal-body" id="csvModal">
                <div id="csvStoreSelected" class="mb-1">0店舗選択中</div>
                <ul class="nav nav-tabs" id="myTab" role="tablist" style="margin-left: 30px; margin-right: 30px;">
                    <li class="nav-item active" role="presentation">
                        <a class="nav-link" id="byCsvOrganization-tab" data-toggle="tab" href="#byCsvOrganization" role="tab" aria-controls="byCsvOrganization" aria-selected="true">組織単位</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="byCsvCode-tab" data-toggle="tab" href="#byCsvCode" role="tab" aria-controls="byCsvCode" aria-selected="false">店舗コード順</a>
                    </li>
                </ul>
                <div class="tab-content modal-body-scroll" id="csvTabContent" style="max-height: 400px; overflow-y: auto;">
                    <div class="tab-pane fade in active" id="byCsvOrganization" role="tabpanel" aria-labelledby="byCsvOrganization-tab">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <div class="checkbox">
                                    <label class="mb-1">
                                        <input type="checkbox" name="organization[org5][]" data-organization-id="" value="" class="checkCommon mr8 org-checkbox">
                                        東京・川崎BL
                                    </label>
                                    <div id="id-collapse" data-toggle="collapse" aria-expanded="false" data-target="#csvCollapse" style=" float: right;"></div>
                                    <ul id="csvCollapse" class="list-group mt-2 collapse">
                                        <li class="list-group-item">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="organization_shops[]" data-organization-id="" data-store-id="" value="" class="checkCommon mr8 shop-checkbox" >
                                                    257保谷
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="byCsvCode" role="tabpanel" aria-labelledby="byCsvCode-tab">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox"name="shops_code[]" data-store-id="" value="" class="checkCommon mr8 shop-checkbox" checked >
                                        257保谷
                                    </label>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-admin" id="selectCsvBtn">選択</button>
            </div>
        </div>
    </div>
</div>
