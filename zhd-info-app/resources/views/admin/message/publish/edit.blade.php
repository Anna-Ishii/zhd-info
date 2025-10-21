{{-- layouts.admin.app をレイアウトとして継承する --}}
@extends('layouts.admin.app')

{{-- 'title' セクションにページ固有のタイトルを設定する --}}
@section('title', '01-4-1_管理画面_業務連絡_編集')

@push('styles')
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/phase3/business-notice-create-instruction.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/phase3/business-notice-create.css') }}?v={{ time() }}">
    <!-- モーダルカスタムCSSを読み込み -->
    <link rel="stylesheet" href="{{ asset('css/phase3/message-import-modal.css') }}?v={{ time() }}">
@endpush

@section('page_header')
    <div class="l-header__bottom">
        <div class="l-header__bottom__wrap">
            <div class="l-header__back"><a class="prev"
                    href="/admin/message/publish?{{ session('message_publish_url') }}"><img
                        src="{{ asset('/img/back-icon.svg') }}"alt="">戻る</a></div>
            <p class="l-header__bottom__ttl">業務連絡 編集</p>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
    <!-- /.navbar-static-side -->
    <x-admin.header-links />
@endsection

@section('content')
    <main class="business-notice-create business-notice-create-instruction mb-0">
        <div id="page-wrapper">

            <form class="form" id="form" method="post" enctype="multipart/form-data" class="form-horizontal">
                @csrf
                <div class="content-item">
                    <div class="content__wrap">
                        <p class="content required">カテゴリ</p>
                        <div class="control">
                            @foreach ($category_list as $category)
                                {{-- 業態SKの時は「消防設備点検実施のお知らせ」「その他店舗へのお知らせ」を表示 --}}
                                @if ($message->organization1_id === 8 || $category->id !== 7 && $category->id !== 8)
                                    @if (request()->old('category_id') == $category->id || $message->category_id == $category->id)
                                        <p>{{ $category->name }}</p>
                                    @endif
                                @endif
                            @endforeach
                            <input type="hidden" name="category_id" value="{{ $message->category_id }}">
                        </div>
                    </div>
                    <div class="content__wrap content-checkbox">
                        <p class="content">ラベル</p>
                        <div class="input-group">
                            <label class="custom-checkbox custom-checkbox-square">
                                <input type="checkbox" name="emergency_flg_display" class="mr8" disabled
                                    {{ $message->emergency_flg ? 'checked' : '' }}>
                                <span class="checkmark"></span>
                                重要
                            </label>
                            <input type="hidden" name="emergency_flg" value="{{ $message->emergency_flg ? 'on' : '' }}">
                        </div>
                    </div>

                    <div class="content__wrap">
                        <p class="content required">タイトル</p>
                        <div class="input-group custom-textbox">
                            <p>{{ $message->title }}</p>
                            <input type="hidden" name="title" value="{{ $message->title }}">
                        </div>
                    </div>
                    {{-- <div class="counter">入力数 {{mb_strlen(old('title', $message->title))}}/20文字</div> --}}

                    <div class="content__wrap content-file">
                        <p class="content required">業連ファイル</p>
                        @if ($message_contents->isNotEmpty())
                            @foreach ($message_contents as $index => $message_content)
                                <div class="file-uploaded" style="padding: 0; border: none; height: auto;">
                                    <p class="file__name"><a href="#">{{ $message_content->content_name ?? 'ファイル' }}</a></p>
                                    <p class="file__upload_message"></p>
                                    <!-- 削除ボタン非表示 -->
                                    <input type="hidden" name="content_id[]" value="{{ $message_content->id }}">
                                    <input type="hidden" name="file_name[]" value="{{ $message_content->content_name }}">
                                    <input type="hidden" name="file_path[]" value="{{ $message_content->content_url }}">
                                    <input type="hidden" name="join_flg[]" value="{{ $message_content->join_flg }}">
                                </div>
                            @endforeach
                        @else
                            {{-- 単一ファイルの場合 --}}
                            @if ($message->content_name)
                                <div class="file-uploaded" style="padding: 0; border: none; height: auto;">
                                    <p class="file__name"><a href="#">{{ $message->content_name }}</a></p>
                                    <p class="file__upload_message"></p>
                                    <!-- 削除ボタン非表示 -->
                                    <input type="hidden" name="content_id[]" value="{{ $message->id }}">
                                    <input type="hidden" name="file_name[]" value="{{ $message->content_name }}">
                                    <input type="hidden" name="file_path[]" value="{{ $message->content_url }}">
                                    <input type="hidden" name="join_flg[]" value="">
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="content__wrap content-set content-checkbox">
                        <p class="content">掲載期間</p>
                        <p>開始日時</p>
                        <div class="input-group custom-date">
                            <div class="custom-date-picker custom-calendar-input report-search__item start-date calendarOnly-input">
                                <input class="date-input calendar-input" id="dateFrom" name="start_datetime" type="text" value="{{ request()->old() ? old('start_datetime') : $message->start_datetime }}" placeholder="yyyy/MM/dd (曜日) hh:mm" autocomplete="off" style="border: 1px solid #1B2131;">
                                <span class="calendar-icon"></span>
                                <div class="custom-calendar hidden">
                                    <!-- カレンダー描画される部分 -->
                                    <div class="time-picker">
                                        <input type="time" class="time-input" value="00:00">
                                    </div>
                                </div>
                            </div>
                            <label class="custom-checkbox custom-checkbox-square">
                                <input type="checkbox" class="dateDisabled" data-target="dateFrom"
                                    @if (request()->old())
                                        {{ empty(old('start_datetime')) ? 'checked' : '' }}
                                    @else
                                        {{ empty($message->start_datetime) ? 'checked' : '' }}
                                    @endif
                                    >
                                <span class="checkmark"></span>
                                未定
                            </label>
                        </div>
                        <p>終了日時</p>
                        <div class="input-group custom-date">
                            <div class="custom-date-picker custom-calendar-input report-search__item end-date calendarOnly-input">
                                <input class="date-input calendar-input" id="dateTo" name="end_datetime" type="text" value="{{ request()->old() ? old('end_datetime') : $message->end_datetime }}" placeholder="yyyy/MM/dd (曜日) hh:mm" autocomplete="off" style="border: 1px solid #1B2131;">
                                <span class="calendar-icon"></span>
                                <div class="custom-calendar hidden">
                                    <!-- カレンダー描画される部分 -->
                                    <div class="time-picker">
                                        <input type="time" class="time-input" value="00:00">
                                    </div>
                                </div>
                            </div>
                            <label class="custom-checkbox custom-checkbox-square">
                                <input type="checkbox" class="dateDisabled" data-target="dateTo"
                                    @if (request()->old())
                                        {{ empty(old('end_datetime')) ? 'checked' : '' }}
                                    @else
                                        {{ empty($message->end_datetime) ? 'checked' : '' }}
                                    @endif
                                    >
                                <span class="checkmark"></span>
                                未定
                            </label>
                        </div>
                    </div>
                    <div class="content__wrap content-checkbox">
                        <p class="content required">対象業態</p>
                        <div class="input-group">
                            <label class="custom-checkbox custom-checkbox-square max-size">
                                <input type="checkbox" id="checkAll" name="brandAll_display" disabled
                                    {{ count($target_brand) === count($brand_list) ? 'checked' : '' }}>
                                <span class="checkmark"></span>
                                全業態
                            </label>
                            @foreach ($brand_list as $brand)
                                <label class="custom-checkbox custom-checkbox-square">
                                    <input type="checkbox" name="brand_display[]" value="{{ $brand->id }}" disabled
                                        {{ in_array($brand->id, $target_brand, true) ? 'checked' : '' }}>
                                    <span class="checkmark"></span>
                                    {{ $brand->name }}
                                </label>
                            @endforeach
                            <!-- 実際のデータ送信用 -->
                            @foreach ($target_brand as $brand_id)
                                <input type="hidden" name="brand[]" value="{{ $brand_id }}">
                            @endforeach
                        </div>
                    </div>

                    <div class="content__wrap content-button">
                        <p class="content required">対象店舗</p>
                        <div class="input-group check-store-list">
                            <div class="button__wrap__item {{ $target_org['select'] === 'all' ? 'active' : '' }}" style="pointer-events: none; opacity: 0.6;">
                                <p data-action="all">全店</p>
                            </div>
                            <div class="button__wrap__item {{ ($target_org['select'] === 'store' || $target_org['select'] === 'oldStore') ? 'active' : '' }}" style="pointer-events: none; opacity: 0.6;">
                                <p data-action="store">店舗選択</p>
                            </div>
                            <div class="button__wrap__item" style="pointer-events: none; opacity: 0.6;">
                                <img src="{{ asset('img/inport_icon.svg') }}" alt="インポート">
                                <p data-action="import">インポート</p>
                            </div>
                            <div class="button__wrap__item" style="pointer-events: none; opacity: 0.6;">
                                <img src="{{ asset('img/export_icon.svg') }}" alt="エクスポート">
                                <p id="exportCsv" data-action="export">エクスポート</p>
                            </div>
                            <input type="hidden" name="organization1_id" value="{{$message->organization1_id}}">
                            <input type="hidden" name="message_id" value="{{$message->id}}">
                        </div>
                        <!-- 実際のデータ送信用 hidden inputs -->
                        <input type="hidden" id="checkOrganization5" name="organization[org5][]" value="">
                        <input type="hidden" id="checkOrganization4" name="organization[org4][]" value="">
                        <input type="hidden" id="checkOrganization3" name="organization[org3][]" value="">
                        <input type="hidden" id="checkOrganization2" name="organization[org2][]" value="">
                        <input type="hidden" id="checkOrganizationShops" name="organization_shops" value="{{ implode(',', $target_org['shops']) }}">
                        @if ($target_org['select'] === 'all')
                            <input type="hidden" id="selectOrganizationAll" name="select_organization[all]" value="selected">
                        @else
                            <input type="hidden" id="selectOrganizationAll" name="select_organization[all]" value="">
                        @endif
                        @if ($target_org['select'] === 'store' || $target_org['select'] === 'oldStore')
                            <input type="hidden" id="selectStore" name="select_organization[store]" value="selected">
                        @else
                            <input type="hidden" id="selectStore" name="select_organization[store]" value="">
                        @endif
                        <input type="hidden" id="selectCsv" name="select_organization[csv]" value="">
                    </div>

                    <div class="content__wrap content-radio">
                        <p class="content required">指示作成</p>
                        <div class="input-group custom-radio-wrap">
                            <label class="custom-radio">
                                <input type="radio" name="instruction_display" value="なし" id="instruction_none_display" checked disabled>
                                <span class="radio-mark"></span>
                                なし
                            </label>
                            <label class="custom-radio">
                                <input type="radio" name="instruction_display" value="あり" id="instruction_yes_display" disabled>
                                <span class="radio-mark"></span>
                                あり
                            </label>
                            <input type="hidden" name="instruction_flg" value="0">
                        </div>
                    </div>

                    <div class="content__wrap content-checkbox">
                        <p class="content">WowTalk通知</p>
                        <div class="input-group">
                            @if ($message->is_broadcast_notification == 1)
                                <p>あり</p>
                            @else
                                <p>なし</p>
                            @endif
                            <input type="hidden" name="wowtalk_notification" value="{{ $message->is_broadcast_notification == 1 ? 'on' : '' }}">
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </main>

    <div class="footer">
        <p><a href="/admin/message/publish?{{ session('message_publish_url') }}">一覧に戻る</a></p>
        @if ($message->editing_flg)
            <button class="c-btn__white" type="submit" name="save" form="form" onclick="window.onbeforeunload=null">保存</button>
        @endif
        <button class="c-btn__blue" type="submit" name="register" form="form" id="registerBtn" onclick="window.onbeforeunload=null">登録</button>
    </div>

    @include('common.admin.message-edit-store-modal', ['organization_list' => $organization_list, 'all_shop_list' => $all_shop_list, 'target_org' => $target_org, 'organization1_id' => $message->organization1_id])
    @include('common.admin.message-new-join-file-modal', [])
@endsection

{{-- ページ固有のJSファイルがここに入る --}}
@push('scripts')
    <script src="{{ asset('/js/admin/message/publish/edit.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/admin/message/publish/edit_store.js') }}?date={{ date('Ymd') }}" defer></script>
    <!-- JavaScript -->
    <script src="{{ asset('js/phase3/business-notice-create.js') }}"></script>
    <!-- モーダルJavaScriptを読み込み -->
    <script src="{{ asset('js/phase3/importModal.js') }}?v={{ time() }}"></script>
@endpush
