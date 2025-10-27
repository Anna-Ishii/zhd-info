<!-- モーダル：店舗選択CSVモード -->
<div class="modal-content">
    <div class="store-modal__header">
        <p class="ttl">以下店舗で取り込みました。</p>
        <p class="txt">変更がある場合は、「再インポート」もしくは下記で選択しなおしてください</p>
    </div>
    <div class="store-modal__main">
        <div class="accordion">
            <h4><span class="selected-count">0</span>店舗選択中</h4>
            <div class="store-selected-list">
                <p class="accordion-sub-subheader select-display-accordion"><span class="accordion-check"></span>選択中の店舗を表示</p>
                <ul class="accordion-sub-subbody" id="selectedStores"></ul>
            </div>

            {{-- 業態データをJavaScriptに渡す --}}
            <script>
                window.brandList = @json($brand_list);
            </script>

            {{-- 選択された業態ごとの「○○すべて」チェックボックスを動的に生成 --}}
            <div id="brandSelectAllContainer">
                {{-- JavaScriptで動的に生成される --}}
            </div>

            {{-- 組織階層の店舗リスト（動的生成） --}}
            @php
                $organization_shops = explode(',', old('organization_shops', ''));
                // デバッグ: データの内容を確認
                // dd($organization_shops);
            @endphp

            {{-- 組織階層を動的に生成 --}}
            @foreach($organization_list as $organization)
                {{-- デバッグ: ループ変数の内容を確認 --}}
                {{-- {{ dd($organization) }} --}}
                @if (isset($organization['organization5_name']))
                    {{-- 組織5階層の店舗グループ --}}
                    <div class="accordion-item">
                        <div id="storeList">
                            {{-- 組織5のチェックボックス --}}
                            <input type="checkbox" name="organization[org5][]"
                                data-organization-id="{{ $organization['organization5_id'] }}"
                                value="{{ $organization['organization5_id'] }}"
                                class="checkStore org-checkbox"
                                id="org5_{{ $organization['organization5_id'] }}"
                                @if (request()->old())
                                    {{ in_array((string) $organization['organization5_id'], old('organization.org5', []), true) ? 'checked' : '' }}
                                @else
                                    {{-- {{ in_array($organization['organization5_id'], $target_org['org5'], true) ? 'checked' : '' }} --}}
                                @endif
                                style="display: none;"
                            >
                            <p class="accordion-sub-subheader">
                                <span class="accordion-check"></span>
                                {{ $organization['organization5_name'] }}
                            </p>
                            {{-- 組織5配下の店舗リスト（折りたたみ可能） --}}
                            <div class="accordion-sub-subbody">
                                @foreach ($organization['organization5_shop_list'] as $index => $shop)
                                    @if (isset($shop['display_name']))
                                        {{-- 個別店舗のチェックボックス --}}
                                        <label class="custom-checkbox-square">
                                            <input type="checkbox" name="organization_shops[]"
                                                data-organization-id="{{ $organization['organization5_id'] }}"
                                                data-store-id="{{ $shop['id'] }}"
                                                data-brand-id="{{ $shop['brand_id'] }}"
                                                value="{{ $shop['id'] }}"
                                                class="checkCommon shop-checkbox"
                                                @if (request()->old())
                                                    {{ in_array((string) $shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                @else
                                                    {{ in_array($shop['id'], $csvStoreIds, true) ? 'checked' : '' }}
                                                @endif
                                            >
                                            <span class="checkmark"></span>
                                            <span class="code">{{ $shop['shop_code'] }}</span>{{ $shop['display_name'] }}
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif (isset($organization['organization4_name']))
                    {{-- 組織4階層の店舗グループ --}}
                    <div class="accordion-item">
                        <div id="storeList">
                            {{-- 組織4のチェックボックス --}}
                            <input type="checkbox" name="organization[org4][]"
                                data-organization-id="{{ $organization['organization4_id'] }}"
                                value="{{ $organization['organization4_id'] }}"
                                class="checkStore org-checkbox"
                                id="org4_{{ $organization['organization4_id'] }}"
                                @if (request()->old())
                                    {{ in_array((string) $organization['organization4_id'], old('organization.org4', []), true) ? 'checked' : '' }}
                                @else
                                    {{-- {{ in_array($organization['organization4_id'], $target_org['org4'], true) ? 'checked' : '' }} --}}
                                @endif
                                style="display: none;"
                            >
                            <p class="accordion-sub-subheader">
                                <span class="accordion-check"></span>
                                {{ $organization['organization4_name'] }}
                            </p>
                            {{-- 組織4配下の店舗リスト（折りたたみ可能） --}}
                            <div class="accordion-sub-subbody">
                                @foreach ($organization['organization4_shop_list'] as $index => $shop)
                                    @if (isset($shop['display_name']))
                                        {{-- 個別店舗のチェックボックス --}}
                                        <label class="custom-checkbox-square">
                                            <input type="checkbox" name="organization_shops[]"
                                                data-organization-id="{{ $organization['organization4_id'] }}"
                                                data-store-id="{{ $shop['id'] }}"
                                                data-brand-id="{{ $shop['brand_id'] }}"
                                                value="{{ $shop['id'] }}"
                                                class="checkCommon shop-checkbox"
                                                @if (request()->old())
                                                    {{ in_array((string) $shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                @else
                                                    {{ in_array($shop['id'], $csvStoreIds, true) ? 'checked' : '' }}
                                                @endif
                                            >
                                            <span class="checkmark"></span>
                                            <span class="code">{{ $shop['shop_code'] }}</span>{{ $shop['display_name'] }}
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif (isset($organization['organization3_name']))
                    {{-- 組織3階層の店舗グループ --}}
                    <div class="accordion-item">
                        <div id="storeList">
                            {{-- 組織3のチェックボックス --}}
                            <input type="checkbox" name="organization[org3][]"
                                data-organization-id="{{ $organization['organization3_id'] }}"
                                value="{{ $organization['organization3_id'] }}"
                                class="checkStore org-checkbox"
                                id="org3_{{ $organization['organization3_id'] }}"
                                @if (request()->old())
                                    {{ in_array((string) $organization['organization3_id'], old('organization.org3', []), true) ? 'checked' : '' }}
                                @else
                                    {{-- {{ in_array($organization['organization3_id'], $target_org['org3'], true) ? 'checked' : '' }} --}}
                                @endif
                                style="display: none;"
                            >
                            <p class="accordion-sub-subheader">
                                <span class="accordion-check"></span>
                                {{ $organization['organization3_name'] }}直轄
                            </p>
                            {{-- 組織3配下の店舗リスト（折りたたみ可能） --}}
                            <div class="accordion-sub-subbody">
                                @foreach ($organization['organization3_shop_list'] as $index => $shop)
                                    @if (isset($shop['display_name']))
                                        {{-- 個別店舗のチェックボックス --}}
                                        <label class="custom-checkbox-square">
                                            <input type="checkbox" name="organization_shops[]"
                                                data-organization-id="{{ $organization['organization3_id'] }}"
                                                data-store-id="{{ $shop['id'] }}"
                                                value="{{ $shop['id'] }}"
                                                class="checkCommon shop-checkbox"
                                                @if (request()->old())
                                                    {{ in_array((string) $shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                @else
                                                    {{ in_array($shop['id'], $csvStoreIds, true) ? 'checked' : '' }}
                                                @endif
                                            >
                                            <span class="checkmark"></span>
                                            <span class="code">{{ $shop['shop_code'] }}</span>{{ $shop['display_name'] }}
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif (isset($organization['organization2_name']))
                    {{-- 組織2階層の店舗グループ --}}
                    <div class="accordion-item">
                        <div id="storeList">
                            {{-- 組織2のチェックボックス --}}
                            <input type="checkbox" name="organization[org2][]"
                                data-organization-id="{{ $organization['organization2_id'] }}"
                                value="{{ $organization['organization2_id'] }}"
                                class="checkStore org-checkbox"
                                id="org2_{{ $organization['organization2_id'] }}"
                                @if (request()->old())
                                    {{ in_array((string) $shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                @else
                                    {{-- {{ in_array($organization['organization2_id'], $target_org['org2'], true) ? 'checked' : '' }} --}}
                                @endif
                                style="display: none;"
                            >
                            <p class="accordion-sub-subheader">
                                <span class="accordion-check"></span>
                                {{ $organization['organization2_name'] }}直轄
                            </p>
                            {{-- 組織2配下の店舗リスト（折りたたみ可能） --}}
                            <div class="accordion-sub-subbody">
                                @foreach ($organization['organization2_shop_list'] as $index => $shop)
                                    @if (isset($shop['display_name']))
                                        {{-- 個別店舗のチェックボックス --}}
                                        <label class="custom-checkbox-square">
                                            <input type="checkbox" name="organization_shops[]"
                                                data-organization-id="{{ $organization['organization2_id'] }}"
                                                data-store-id="{{ $shop['id'] }}"
                                                data-brand-id="{{ $shop['brand_id'] }}"
                                                value="{{ $shop['id'] }}"
                                                class="checkCommon shop-checkbox"
                                                @if (request()->old())
                                                    {{ in_array((string) $shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                @else
                                                    {{ in_array($shop['id'], $csvStoreIds, true) ? 'checked' : '' }}
                                                @endif
                                            >
                                            <span class="checkmark"></span>
                                            <span class="code">{{ $shop['shop_code'] }}</span>{{ $shop['display_name'] }}
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    <div class="c-btn">
        <button class="c-btn__white" id="csvReImportBtn" data-action="reImport">再インポート</button>
        <button class="c-btn__white" id="cancelBtn" data-dismiss="modal" style="display: none;">キャンセル</button>
        <button class="c-btn__blue" id="selectCsvBtn">選択</button>
    </div>
</div>




