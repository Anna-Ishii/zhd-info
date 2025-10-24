@extends('layouts.admin.app')

@section('title', 'マニュアル編集')

@push('styles')
    <link href="{{ asset('/css/phase3/manual-video-create-edit.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('/js/admin/manual/publish/new/edit.js') }}?date={{ date('Ymd') }}" defer></script>
@endpush

@section('page_header')
    <div class="l-header__bottom">
        <div class="l-header__bottom__wrap">
            <div class="l-header__back"><a class="prev" href="{{ route('admin.manual.publish.index') }}"><img src="{{ asset('/img/back-icon.svg') }}" alt="">戻る</a></div>
            <p class="l-header__bottom__ttl">マニュアル 編集</p>
        </div>
    </div>
    <div class="l-header__link">
        <div class="l-header__link__wrap">
            <div class="l-header__link__text"><a class="page_link active" href="{{ route('admin.manual.publish.index') }}">マニュアル一覧</a></div>
            <div class="l-header__link__text"><a class="page_link" href="#">業態設定</a></div>
        </div>
    </div>
@endsection

@section('content')
    <div class="stream-wrap">
        @if ($manual_detail['unsubscribe'])
            <span class="stream-stop-btn streamStopModalBtn">配信停止中</span>
        @else
            <button id="modal-btn" class="stream-stop-btn streamStopModalBtn">配信停止</button>
        @endif
    </div>
    <main class="manual-video-create-edit">
        <form class="form" action="{{ route('admin.manual.publish.edit.update', ['manual_id' => $manual->id]) }}" method="post">
            @csrf
            @php
                $startPending = old('start_pending', $manual_detail['start_pending'] ? '1' : null);
                $endPending = old('end_pending', $manual_detail['end_pending'] ? '1' : null);
                $isAllShopSelected = $shop_selection['is_all'];
                $selectedShopCount = $shop_selection['selected_count'];
            @endphp
            <div class="content-item">
                <p><span class="required"></span><span class="required_txt">：必須項目</span></p>
                <div class="content__wrap">
                    <p class="content required">形式</p>
                    <p>{{ $manual_detail['type_name'] }}</p>
                </div>
                <div class="content__wrap">
                    <p class="content required">カテゴリ</p>
                    <p>{{ $manual_detail['category_name'] }}</p>
                </div>
                <div class="content__wrap">
                    <p class="content required">タイトル</p>
                    <p>{{ $manual_detail['title'] }}</p>
                </div>
                <div class="content__wrap">
                    <p class="content required">ファイル添付</p>
                    <div class="file-uploaded">
                        <p class="file__name"><a href="{{ asset($manual_detail['content_url']) }}" target="_blank">{{ $manual_detail['content_name'] }}</a></p>
                        <p class="file__size">{{ $manual_detail['content_size'] }}</p>
                    </div>
                </div>
                @if ($contents && count($contents) > 0)
                    <div class="procedure__wrap">
                        @foreach ($contents as $index => $item)
                            <div class="content__wrap">
                                <p class="content">手順{{ $index + 1 }}</p>
                                <div class="content-sub__wrap">
                                    <p class="content-sub">手順名</p>
                                    <p>{{ $item->title }}</p>
                                </div>
                                <div class="content-sub__wrap">
                                    <p class="content-sub">手順ファイル添付</p>
                                    <div class="file-uploaded">
                                        <p class="file__name"><a href="{{ asset($item->content_url) }}" target="_blank">{{ $item->content_name }}</a></p>
                                        <p class="file__size">{{ $item->content_file_size }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="content__wrap content-set content-checkbox">
                    <p class="content">掲載期間</p>
                    <p>開始日時</p>
                    <div class="input-group custom-date">
                        <input
                               class="date-input calendar-input"
                               id="start-date"
                               type="datetime-local"
                               name="start_datetime"
                               value="{{ old('start_datetime', $manual_detail['start_datetime_input']) }}"
                               @if ($startPending) disabled @endif>
                        <label class="custom-checkbox custom-checkbox-square">
                            <input type="checkbox" id="start-date-pending" name="start_pending" value="1" data-toggle-target="#start-date" {{ $startPending ? 'checked' : '' }}>
                            <span class="checkmark"></span>
                            未定
                        </label>
                    </div>
                    @error('start_datetime')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                    <p>終了日時</p>
                    <div class="input-group custom-date">
                        <input
                               class="date-input calendar-input"
                               id="end-date"
                               type="datetime-local"
                               name="end_datetime"
                               value="{{ old('end_datetime', $manual_detail['end_datetime_input']) }}"
                               @if ($endPending) disabled @endif>
                        <label class="custom-checkbox custom-checkbox-square">
                            <input type="checkbox" id="end-date-pending" name="end_pending" value="1" data-toggle-target="#end-date" {{ $endPending ? 'checked' : '' }}>
                            <span class="checkmark"></span>
                            未定
                        </label>
                    </div>
                    @error('end_datetime')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="content__wrap content-checkbox">
                    <p class="content required">対象業態</p>
                    <div class="input-group readonly-fields">
                        <label class="custom-checkbox custom-checkbox-square max-size">
                            <input type="checkbox" id="brandAll" value="全業態" {{ $all_brand_selected ? 'checked' : '' }} disabled>
                            <span class="checkmark"></span>
                            全業態
                        </label>
                        @foreach ($brand_list as $brand)
                            <label class="custom-checkbox custom-checkbox-square">
                                <input type="checkbox" value="{{ $brand->id }}" {{ in_array($brand->id, $target_brand, true) ? 'checked' : '' }} disabled>
                                <span class="checkmark"></span>
                                {{ $brand->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="content__wrap content-button">
                    <p class="content required">対象店舗</p>
                    <div class="input-group readonly-buttons">
                        <div class="button__wrap__item is-disabled {{ $isAllShopSelected ? 'is-active' : '' }}">
                            <p>全店</p>
                        </div>
                        <div class="button__wrap__item is-disabled {{ $isAllShopSelected ? '' : 'is-active' }}">
                            <p>店舗選択<span class="select-stores">{{ $isAllShopSelected ? '(全店)' : '(' . $selectedShopCount . '店舗)' }}</span></p>
                        </div>
                        <div class="button__wrap__item is-disabled">
                            <img src="{{ asset('/img/inport_icon.svg') }}" alt="インポート">
                            <p>インポート</p>
                        </div>
                        <div class="button__wrap__item is-disabled">
                            <img src="{{ asset('/img/export_icon.svg') }}" alt="エクスポート">
                            <p>エクスポート</p>
                        </div>
                    </div>
                </div>
                <div class="content__wrap content-checkbox">
                    <p class="content">説明文</p>
                    <p>{{ $manual_detail['description'] }}</p>
                </div>
            </div>

            <div class="footer">
                <p><a href="{{ route('admin.manual.publish.index') }}">一覧に戻る</a></p>
                <button
                        class="c-btn__white"
                        type="submit"
                        formaction="{{ route('admin.manual.publish.edit.duplicate', ['manual_id' => $manual->id]) }}"
                        formmethod="post">
                    複製
                </button>
                <button class="c-btn__blue" type="submit">更新</button>
            </div>
        </form>

        <!-- 配信停止モーダル -->
        <div class="modal-overlay" id="streamStopModal">
            <div class="modal2">
                <p>
                    配信を停止してもよろしいですか？
                </p>
                <div class="c-btn">
                    <button class="c-btn__white" id="stopBackBtn">戻る</button>
                    <form id="streamStopForm" action="{{ route('admin.manual.publish.streamStop', ['manual_id' => $manual->id]) }}" method="POST" style="display: inline;">
                        @csrf
                        <button class="c-btn__blue" type="submit">配信停止する</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection
