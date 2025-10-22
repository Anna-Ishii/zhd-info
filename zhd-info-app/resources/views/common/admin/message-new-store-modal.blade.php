<!-- インポートモーダル -->

<div class="modal-overlay" id="messageStoreImportModal">
    <div class="modal inport-modal">
        <div class="close-btn" id="inportCloselBtn">
            <img src="{{ asset('img/cancel_icon.svg') }}" alt="閉じる">
        </div>
        <div class="top-erea">
            <p class="ttl">店舗選択csvインポート</p>
            <p class="txt">CSVデータを店舗モーダルに表示します</p>
        </div>
        <div class="middle-erea">
            <p><span class="required"></span><span class="required_txt">：必須項目</span></p>
            <p class="ttl">CSV添付<span class="required"></span></p>
            <input type="hidden" name="organization1" value="{{ $organization1->id ?? '' }}">
            <div class="file-upload__container">
                <div class="file-upload__container__item">
                    <div class="file-upload__container__item__wrap">
                        <div class="file-input-item">
                            <div class="uploader">
                                <label for="csvFileUp" class="upload-before">
                                    <img class="uploadbefore" src="{{ asset('img/upload-cloud.svg') }}" alt="" />
                                    <div class="upload-txt uploadbefore">
                                        <p>ここにファイルをドロップ</p>
                                        <p>または</p>
                                        <p class="upload-txt-btn">ファイルを選択</p>
                                    </div>
                                </label>
                                <div class="store-file-uploaded" style="display: none;">
                                    <p class="file__name">ファイルを選択してください</p>
                                    <p class="file__size">0KB</p>
                                    <p class="file__upload_message">アップロード完了</p>
                                    <p class="file__delete_btn_store">
                                        <img src="{{ asset('img/delete_icon.svg') }}" alt="ファイル削除">
                                    </p>
                                </div>
                                <input type="file" name="csv" id="csvFileUp" accept=".csv" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="c-btn">
            <button class="c-btn__blue disabled-btn" id="csvImportBtn" data-file="bb_sk_inport_csv">インポート</button>
        </div>
    </div>
</div>


<!-- モーダル：店舗選択 -->
<div class="store-modal" id="storeModal">
    <div class="modal-content">
        <div class="store-modal__header">
            <p class="ttl">店舗を選択してください。</p>
        </div>
        <div class="store-modal__main">
            <div class="accordion">
                <h4><span class="selected-count">0</span>店舗選択中</h4>
                <div class="store-selected-list">
                    <p class="accordion-sub-subheader"><span class="accordion-check"></span>選択中の店舗を表示</p>
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
                                    class="checkStore mr8 org-checkbox"
                                    id="org5_{{ $organization['organization5_id'] }}"
                                    @if (old('organization.org5'))
                                        {{ in_array((string) $organization['organization5_id'], old('organization.org5', []), true) ? 'checked' : '' }}
                                    @elseif(!request()->old())
                                        {{ '' }}
                                    @endif
                                    style="display: none;"
                                >
                                <p class="accordion-sub-subheader">
                                    <span class="accordion-check" onclick="document.getElementById('org5_{{ $organization['organization5_id'] }}').click();"></span>
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
                                                    class="checkCommon mr8 shop-checkbox"
                                                    @if (old('organization_shops'))
                                                        {{ in_array((string) $shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                    @elseif(!request()->old())
                                                        {{ '' }}
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
                                    class="checkStore mr8 org-checkbox"
                                    id="org4_{{ $organization['organization4_id'] }}"
                                    @if (old('organization.org4'))
                                        {{ in_array((string) $organization['organization4_id'], old('organization.org4', []), true) ? 'checked' : '' }}
                                    @elseif(!request()->old())
                                        {{ '' }}
                                    @endif
                                    style="display: none;"
                                >
                                <p class="accordion-sub-subheader">
                                    <span class="accordion-check" onclick="document.getElementById('org4_{{ $organization['organization4_id'] }}').click();"></span>
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
                                                    class="checkCommon mr8 shop-checkbox"
                                                    @if (old('organization_shops'))
                                                        {{ in_array((string) $shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                    @elseif(!request()->old())
                                                        {{ '' }}
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
                                    class="checkStore mr8 org-checkbox"
                                    id="org3_{{ $organization['organization3_id'] }}"
                                    @if (old('organization.org3'))
                                        {{ in_array((string) $organization['organization3_id'], old('organization.org3', []), true) ? 'checked' : '' }}
                                    @elseif(!request()->old())
                                        {{ '' }}
                                    @endif
                                    style="display: none;"
                                >
                                <p class="accordion-sub-subheader">
                                    <span class="accordion-check" onclick="document.getElementById('org3_{{ $organization['organization3_id'] }}').click();"></span>
                                    {{ $organization['organization3_name'] }}
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
                                                    class="checkCommon mr8 shop-checkbox"
                                                    @if (old('organization_shops'))
                                                        {{ in_array((string) $shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                    @elseif(!request()->old())
                                                        {{ '' }}
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
                                    class="checkStore mr8 org-checkbox"
                                    id="org2_{{ $organization['organization2_id'] }}"
                                    @if (old('organization.org2'))
                                        {{ in_array((string) $organization['organization2_id'], old('organization.org2', []), true) ? 'checked' : '' }}
                                    @elseif(!request()->old())
                                        {{ '' }}
                                    @endif
                                    style="display: none;"
                                >
                                <p class="accordion-sub-subheader">
                                    <span class="accordion-check" onclick="document.getElementById('org2_{{ $organization['organization2_id'] }}').click();"></span>
                                    {{ $organization['organization2_name'] }}
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
                                                    class="checkCommon mr8 shop-checkbox"
                                                    @if (old('organization_shops'))
                                                        {{ in_array((string) $shop['id'], $organization_shops, true) ? 'checked' : '' }}
                                                    @elseif(!request()->old())
                                                        {{ '' }}
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
            <button class="c-btn__white" id="cancelSelectBtn">キャンセル</button>
            <button class="c-btn__blue" id="storeModalConfirmBtn">選択する</button>
        </div>
    </div>
</div>



