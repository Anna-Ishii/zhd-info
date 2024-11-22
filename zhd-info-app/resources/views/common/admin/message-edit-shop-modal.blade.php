<!-- モーダル：店舗編集 -->
@foreach($message_list as $message)
    <div id="editShopModal-{{ $message->id }}" class="modal fade editShopModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <form id="editForm-{{ $message->id }}" class="form-horizontal">

                        @csrf
                        <input type="hidden" name="id" id="messageId-{{ $message->id }}">

                        <div class="form-group">
                            <div class="editShopInputs" data-message-id="{{ $message->id }}">
                                <label class="col-sm-2 control-label">対象店舗<span class="text-danger required">*<span></label>
                                <div class="col-sm-10 checkArea">
                                    <div class="check-store-list mb8 text-left">
                                        {{-- <input type="hidden" class="checkOrganization5" name="organization[org5][]" value="">
                                        <input type="hidden" class="checkOrganization4" name="organization[org4][]" value="">
                                        <input type="hidden" class="checkOrganization3" name="organization[org3][]" value="">
                                        <input type="hidden" class="checkOrganization2" name="organization[org2][]" value="">
                                        <input type="hidden" class="checkOrganizationShops" name="organization_shops" value=""> --}}

                                        <label class="mr16">
                                            @if ($message->target_org['select'] === 'store')
                                                <input type="button" class="btn btn-admin check-selected" id="checkStore-{{ $message->id }}" data-toggle="modal"
                                                    data-target="#editShopSelectModal-{{ $message->id }}" value="店舗選択">
                                                {{-- <input type="hidden" id="selectStore-{{ $message->id }}" name="select_organization[store]" value="selected"> --}}
                                            @else
                                                @if ($message->target_org['select'] === 'oldStore')
                                                    <input type="button" class="btn btn-admin check-selected" id="checkStore-{{ $message->id }}" data-toggle="modal"
                                                        data-target="#editShopSelectModal-{{ $message->id }}" value="店舗選択">
                                                    {{-- <input type="hidden" id="selectStore-{{ $message->id }}" name="select_organization[store]" value="selected"> --}}
                                                @else
                                                    <input type="button" class="btn btn-admin check-selected" id="checkStore-{{ $message->id }}" data-toggle="modal"
                                                        data-target="#editShopSelectModal-{{ $message->id }}" value="店舗選択">
                                                    {{-- <input type="hidden" id="selectStore-{{ $message->id }}" name="select_organization[store]" value=""> --}}
                                                @endif
                                            @endif
                                        </label>

                                        <label class="mr16">
                                            <input type="button" class="btn btn-admin" id="importCsv-{{ $message->id }}" data-toggle="modal"
                                                data-target="#editShopImportModal-{{ $message->id }}" value="インポート">
                                            {{-- <input type="hidden" id="selectCsv-{{ $message->id }}" name="select_organization[csv]" value=""> --}}
                                        </label>

                                        <label class="mr16">
                                            <input type="button" class="btn btn-admin" id="exportCsv-{{ $message->id }}" value="エクスポート">
                                            <input type="hidden" name="message_id" value="{{$message->id}}">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3 control-label">
                                <span class="text-danger required">*</span>：必須項目
                            </div>
                            <div class="col-sm-2 col-sm-offset-6 control-label">
                                <input type="button" id="fileImportBtn-{{ $message->id }}" class="btn btn-admin" data-toggle="modal"
                                    data-target="#messageStoreModal" value="設定">
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach



<script>

</script>
