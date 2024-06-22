<div class="modal-dialog" style="max-width: 450px;">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
            <h4 class="modal-title">以下店舗で取り込みました。<br /><small class="text-muted">変更ある場合は、「再取込」もしくは下記で選択しなおしてください</small></h4>
        </div>
        <div class="modal-body" id="storeModal">
            <div id="storeSelected" class="mb-1">0店舗選択中</div>
            <ul class="nav nav-tabs" id="myTab" role="tablist" style="margin-left: 30px; margin-right: 30px;">
                <li class="nav-item active" role="presentation">
                    <a class="nav-link" id="byOrganization-tab" data-toggle="tab" href="#byOrganization" role="tab" aria-controls="byOrganization" aria-selected="true">組織単位</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="byStoreCode-tab" data-toggle="tab" href="#byStoreCode" role="tab" aria-controls="byStoreCode" aria-selected="false">店舗コード順</a>
                </li>
            </ul>
            <div class="tab-content modal-body-scroll" id="storeTabContent" style="max-height: 400px; overflow-y: auto;">
                <div class="tab-pane fade in active" id="byOrganization" role="tabpanel" aria-labelledby="byOrganization-tab">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="selectAllOrganization"> 全て選択/選択解除
                                </label>
                            </div>
                        </li>
                        @php
                            $organization_shops = explode(',', old('organization_shops', ''));
                        @endphp
                        @foreach ($organization_list as $index => $organization)
                            @if (isset($organization['organization5_name']))
                                <li class="list-group-item">
                                    <div class="checkbox">
                                        <div style="padding-bottom: 5px;">
                                            <label>
                                                <input type="checkbox" name="organization[org5][]" data-organization-id="{{$organization['organization5_id']}}" value="{{$organization['organization5_id']}}" class="checkCommon mr8 org-checkbox"
                                                @if(request()->old())
                                                    {{ in_array((string)$organization['organization5_id'], old("organization.org5", []), true) ? 'checked' : '' }}
                                                @else
                                                    {{-- {{ in_array($organization['organization5_id'], $target_org['org5'], true) ? 'checked' : '' }} --}}
                                                @endif
                                                >
                                                {{$organization['organization5_name']}}
                                            </label>
                                            <div id="id-collapse" data-toggle="collapse" aria-expanded="false" data-target="#storeCollapse{{$index}}" style=" float: right;"></div>
                                        </div>
                                        <ul id="storeCollapse{{$index}}" class="list-group mt-2 collapse">
                                            @foreach ($organization['organization5_shop_list'] as $index => $shop)
                                                @if (isset($shop['display_name']))
                                                    <li class="list-group-item">
                                                        <div>
                                                            <label>
                                                                <input type="checkbox" name="organization_shops[]" data-organization-id="{{$organization['organization5_id']}}" data-store-id="{{$shop['id']}}" value="{{$shop['id']}}" class="checkCommon mr8 shop-checkbox"
                                                                @if(request()->old())
                                                                    {{ in_array((string)$shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                                @else
                                                                    {{ in_array($shop['id'], $csvStoreIds, true) ? 'checked' : '' }}
                                                                @endif
                                                                >
                                                                {{ $shop['shop_code'] }} {{ $shop['display_name'] }}
                                                            </label>
                                                        </div>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @elseif (isset($organization['organization4_name']))
                                <li class="list-group-item">
                                    <div class="checkbox">
                                        <div style="padding-bottom: 5px;">
                                            <label>
                                                <input type="checkbox" name="organization[org4][]" data-organization-id="{{$organization['organization4_id']}}" value="{{$organization['organization4_id']}}" class="checkCommon mr8 org-checkbox"
                                                @if(request()->old())
                                                    {{ in_array((string)$organization['organization4_id'], old("organization.org4", []), true) ? 'checked' : '' }}
                                                @else
                                                    {{-- {{ in_array($organization['organization4_id'], $target_org['org4'], true) ? 'checked' : '' }} --}}
                                                @endif
                                                >
                                                {{$organization['organization4_name']}}
                                            </label>
                                            <div id="id-collapse" data-toggle="collapse" aria-expanded="false" data-target="#storeCollapse{{$index}}" style=" float: right;"></div>
                                        </div>
                                        <ul id="storeCollapse{{$index}}" class="list-group mt-2 collapse">
                                            @foreach ($organization['organization4_shop_list'] as $index => $shop)
                                                @if (isset($shop['display_name']))
                                                    <li class="list-group-item">
                                                        <div>
                                                            <label>
                                                                <input type="checkbox" name="organization_shops[]" data-organization-id="{{$organization['organization4_id']}}" data-store-id="{{$shop['id']}}" value="{{$shop['id']}}" class="checkCommon mr8 shop-checkbox"
                                                                @if(request()->old())
                                                                    {{ in_array((string)$shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                                @else
                                                                    {{ in_array($shop['id'], $csvStoreIds, true) ? 'checked' : '' }}
                                                                @endif
                                                                >
                                                                {{ $shop['shop_code'] }} {{ $shop['display_name'] }}
                                                            </label>
                                                        </div>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @elseif (isset($organization['organization3_name']))
                                <li class="list-group-item">
                                    <div class="checkbox">
                                        <div style="padding-bottom: 5px;">
                                            <label>
                                                <input type="checkbox" name="organization[org3][]" data-organization-id="{{$organization['organization3_id']}}" value="{{$organization['organization3_id']}}" class="checkCommon mr8 org-checkbox"
                                                @if(request()->old())
                                                    {{ in_array((string)$organization['organization3_id'], old("organization.org3", []), true) ? 'checked' : '' }}
                                                @else
                                                    {{-- {{ in_array($organization['organization3_id'], $target_org['org3'], true) ? 'checked' : '' }} --}}
                                                @endif
                                                >
                                                {{$organization['organization3_name']}}直轄
                                            </label>
                                            <div id="id-collapse" data-toggle="collapse" aria-expanded="false" data-target="#storeCollapse{{$index}}" style=" float: right;"></div>
                                        </div>
                                        <ul id="storeCollapse{{$index}}" class="list-group mt-2 collapse">
                                            @foreach ($organization['organization3_shop_list'] as $index => $shop)
                                                @if (isset($shop['display_name']))
                                                    <li class="list-group-item">
                                                        <div>
                                                            <label>
                                                                <input type="checkbox" name="organization_shops[]" data-organization-id="{{$organization['organization3_id']}}" data-store-id="{{$shop['id']}}" value="{{$shop['id']}}" class="checkCommon mr8 shop-checkbox"
                                                                @if(request()->old())
                                                                    {{ in_array((string)$shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                                @else
                                                                    {{ in_array($shop['id'], $csvStoreIds, true) ? 'checked' : '' }}
                                                                @endif
                                                                >
                                                                {{ $shop['shop_code'] }} {{ $shop['display_name'] }}
                                                            </label>
                                                        </div>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @elseif (isset($organization['organization2_name']))
                                <li class="list-group-item">
                                    <div class="checkbox">
                                        <div style="padding-bottom: 5px;">
                                            <label>
                                                <input type="checkbox" name="organization[org2][]" data-organization-id="{{$organization['organization2_id']}}" value="{{$organization['organization2_id']}}" class="checkCommon mr8 org-checkbox"
                                                @if(request()->old())
                                                    {{ in_array((string)$shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                @else
                                                    {{-- {{ in_array($organization['organization2_id'], $target_org['org2'], true) ? 'checked' : '' }} --}}
                                                @endif
                                                >
                                                {{$organization['organization2_name']}}直轄
                                            </label>
                                            <div id="id-collapse" data-toggle="collapse" aria-expanded="false" data-target="#storeCollapse{{$index}}" style=" float: right;"></div>
                                        </div>
                                        <ul id="storeCollapse{{$index}}" class="list-group mt-2 collapse">
                                            @foreach ($organization['organization2_shop_list'] as $index => $shop)
                                                @if (isset($shop['display_name']))
                                                    <li class="list-group-item">
                                                        <div>
                                                            <label>
                                                                <input type="checkbox" name="organization_shops[]" data-organization-id="{{$organization['organization2_id']}}" data-store-id="{{$shop['id']}}" value="{{$shop['id']}}" class="checkCommon mr8 shop-checkbox"
                                                                @if(request()->old())
                                                                    {{ in_array((string)$shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                                @else
                                                                    {{ in_array($shop['id'], $csvStoreIds, true) ? 'checked' : '' }}
                                                                @endif
                                                                >
                                                                {{ $shop['shop_code'] }} {{ $shop['display_name'] }}
                                                            </label>
                                                        </div>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                <div class="tab-pane fade" id="byStoreCode" role="tabpanel" aria-labelledby="byStoreCode-tab">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="selectAllStoreCode"> 全て選択/選択解除
                                </label>
                            </div>
                        </li>
                        @foreach ($all_shop_list as $index => $shop_list)
                            @if (isset($shop_list['shop_code']))
                                <li class="list-group-item">
                                    <div>
                                        <label>
                                            <input type="checkbox"name="shops_code[]" data-store-id="{{$shop_list['shop_id']}}" value="{{$shop_list['shop_id']}}" class="checkCommon mr8 shop-checkbox"
                                            @if(request()->old())
                                                {{ in_array((string)$shop_list['shop_id'], $organization_shops, true) ? 'checked' : '' }}
                                            @else
                                                {{ in_array($shop_list['shop_id'], $csvStoreIds, true) ? 'checked' : '' }}
                                            @endif
                                            >
                                            {{ $shop_list['shop_code'] }} {{ $shop_list['display_name'] }}
                                        </label>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-admin" id="selectCsvBtn">選択</button>
            <input type="button" class="btn btn-admin" id="csvImportBtn" data-toggle="modal" data-target="#manualStoreImportModal" value="再インポート">
        </div>
    </div>
</div>
