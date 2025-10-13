{{-- layouts.admin.app をレイアウトとして継承する --}}
@extends('layouts.admin.app')

{{-- 'title' セクションにページ固有のタイトルを設定する --}}
@section('title', '業務連絡一覧')

{{-- 'styles' スタックにページ固有のCSSを追加する --}}
@push('styles')
    <link href="{{ asset('/admin/css/bbsk.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('/js/admin/message/publish/edit_list.js') }}?date={{ date('Ymd') }}" defer></script>
@endpush

@section('page_header')
    <div class="l-header__bottom">
        <div class="l-header__bottom__wrap">
            {{-- @TODO どこに遷移？ --}}
            <div class="l-header__back"><a class="prev"
                    href="/admin/message/publish?{{ session('message_publish_url') }}"><img
                        src="{{ asset('/img/back-icon.svg') }}"alt="">戻る</a></div>
            <p class="l-header__bottom__ttl">業務連絡一覧</p>
        </div>
        <div class="l-header__bottom__link">
            @if ($admin->ability == App\Enums\AdminAbility::Edit)
                <button data-toggle="modal" data-target="#messageImportModal">
                    <img src="{{ asset('/img/inport_icon.svg') }}" alt="">インポート
                </button>
            @endif

            @if ($admin->ability == App\Enums\AdminAbility::Edit)
                {{-- BBの場合、SKの場合 --}}
                @if ($organization1->id == 2 || $organization1->id == 8)
                    <button data-toggle="modal" data-target="#messageExportModal">
                        <img src="{{ asset('/img/export_icon.svg') }}" alt="">エクスポート
                    </button>
                @else
                    <a href="{{ route('admin.message.publish.export-list') }}?{{ http_build_query(request()->query()) }}"
                        class="exportBtn"
                        data-filename="{{ '業務連絡_' . $organization1->name . now()->format('_Y_m_d') . '.csv' }}">
                        <img src="{{ asset('/img/export_icon.svg') }}" alt="">エクスポート
                    </a>
                @endif
            @endif

            @if ($admin->ability == App\Enums\AdminAbility::Edit)
                <a href="{{ route('admin.message.publish.new', ['organization1' => $organization1]) }}">
                    <img src="{{ asset('/img/register_icon.svg') }}" alt="">新規登録
                </a>
            @endif
        </div>
    </div>
    <x-admin.header-links />
@endsection

{{-- 'content' セクションにメインコンテンツを記述する --}}
@section('content')
    <main class="bbsk">
        <div class="bbsk__search ">
            <form method="get">
                <div class="filter-bar">
                    {{-- 業態 --}}
                    <x-admin.select-box name="brand" label="業態" :options="$organization1_list" :base64-value="true" />
                    {{-- ラベル --}}
                    <x-admin.select-box name="label" label="ラベル" :options="[['value' => '1', 'name' => '重要']]" value-key="value" name-key="name" />

                    @php
                        // 業態SKの時は「消防設備点検実施のお知らせ」「その他店舗へのお知らせ」を表示
                        $filtered_category_list = $category_list->filter(function ($category) use ($organization1) {
                            return $organization1->id === 8 || ($category->id !== 7 && $category->id !== 8);
                        });
                    @endphp
                    {{-- カテゴリ --}}
                    <x-admin.select-checkbox label="カテゴリ" name="category" :options="$filtered_category_list" />
                    {{-- 状態 --}}
                    <x-admin.select-checkbox label="状態" name="status" :options="$publish_status" value-key="value"
                        name-key="text" />
                    <div class="field field--range">
                        <div class="label">掲載期間</div>
                        <div class="control">
                            <div class="custom-date-picker custom-calendar-input start-date calendarOnly-input">
                                <input id="publishDateFrom" name="publish-date[0]"
                                    value="{{ request()->input('publish-date.0') }}" class="date-input calendar-input"
                                    type="text" placeholder="yyyy/MM/dd" autocomplete="off" readonly>
                                <span class="calendar-icon"></span>
                                <div class="custom-calendar hidden"></div>
                            </div>
                            <span class="tilde">〜</span>
                            <div class="custom-date-picker custom-calendar-input end-date calendarOnly-input">
                                <input id="publishDateTo" name="publish-date[1]"
                                    value="{{ request()->input('publish-date.1') }}" class="date-input calendar-input"
                                    type="text" placeholder="yyyy/MM/dd" autocomplete="off" readonly>
                                <span class="calendar-icon"></span>
                                <div class="custom-calendar hidden"></div>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <div class="label">キーワード検索</div>
                        <div class="control searchbox">
                            <div class="report-search__item search">
                                <div class="input-icon">
                                    <input type="text" id="filter"name="q" value="{{ request()->input('q') }}"
                                        placeholder="キーワード">
                                    <span class="icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M20.3 20.3C19.9134 20.6866 19.2866 20.6866 18.9 20.3L13.3 14.7C12.8 15.1 12.225 15.4167 11.575 15.65C10.925 15.8833 10.2333 16 9.5 16C7.68333 16 6.14583 15.3708 4.8875 14.1125C3.62917 12.8542 3 11.3167 3 9.5C3 7.68333 3.62917 6.14583 4.8875 4.8875C6.14583 3.62917 7.68333 3 9.5 3C11.3167 3 12.8542 3.62917 14.1125 4.8875C15.3708 6.14583 16 7.68333 16 9.5C16 10.2333 15.8833 10.925 15.65 11.575C15.4167 12.225 15.1 12.8 14.7 13.3L20.3 18.9C20.6866 19.2866 20.6866 19.9134 20.3 20.3V20.3ZM9.5 14C10.75 14 11.8125 13.5625 12.6875 12.6875C13.5625 11.8125 14 10.75 14 9.5C14 8.25 13.5625 7.1875 12.6875 6.3125C11.8125 5.4375 10.75 5 9.5 5C8.25 5 7.1875 5.4375 6.3125 6.3125C5.4375 7.1875 5 8.25 5 9.5C5 10.75 5.4375 11.8125 6.3125 12.6875C7.1875 13.5625 8.25 14 9.5 14Z"
                                                fill="#8E9199" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="save saveSearchBtn">検索条件を保存</button>
                </div>
                <p class="annotation">※「インポート」「エクスポート「新規登録」は検索時に設定した業態で行われます。</p>
                <button type="submit" style="display: none;"></button>
            </form>
        </div>
        <div class="bbsk__main">
            <form method="post" action="#">
                <p class="total__dsp">全{{ $message_list->total() }}件</p>
                <table>
                    <thead>
                        <tr class="head">
                            <th class="column1">No</th>
                            <th class="column2">対象業態</th>
                            <th class="column3">ラベル</th>
                            <th class="column4">カテゴリ</th>
                            <th class="column5">タイトル</th>
                            <th class="column6">添付</th>
                            <th class="column7">掲載期間</th>
                            <th class="column8">状態</th>
                            <th class="column9">閲覧率</th>
                            <th class="column10">編集</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($message_list as $message)
                            <tr data-message_id="{{ $message->id }}" data-organization1_id="{{ $organization1->id }}">
                                {{-- No --}}
                                <td class="column1" data-message-number="{{ $message->number }}">{{ $message->number }}
                                </td>
                                {{-- 対象業態 --}}
                                <td class="column2">{{ $message->brand_name }}</td>
                                {{-- ラベル --}}
                                <td class="column3">
                                    @if ($message->emergency_flg)
                                        <span class="pill red"><span class="pill-val">重要</span></span>
                                    @endif
                                </td>
                                {{-- カテゴリ --}}
                                <td class="column4">
                                    {{ $message->category?->name }}
                                </td>
                                {{-- タイトル --}}
                                <td class="column5">
                                    @if (isset($message->content_url))
                                        @if (isset($message->main_file))
                                            @if ($message->main_file_count < 2)
                                                <a href="{{ asset($message->main_file['file_url']) }}" target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="title-text">{{ $message->title }}</a>
                                            @else
                                                <a href="{{ asset($message->main_file['file_url']) }}" target="_blank"
                                                    rel="noopener noreferrer" class="title-text">{{ $message->title }}
                                                    ({{ $message->main_file_count }}ページ)
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ asset($message->content_url) }}" target="_blank"
                                                rel="noopener noreferrer" class="title-text">{{ $message->title }}</a>
                                        @endif
                                    @else
                                        {{ $message->title }}
                                    @endif
                                </td>
                                {{-- 添付 --}}
                                <td class="column6">
                                    @if ($message->file_count > 0)
                                        <a href="#" data-toggle="modal"
                                            data-target="#singleFileModal{{ $message->id }}">
                                            有 ({{ $message->file_count }})
                                        </a>
                                    @endif
                                </td>
                                {{-- 掲載期間 --}}
                                <td class="column7">
                                    <div class="date-range">
                                        <div class="dt">
                                            <div class="dt-date">{{ $message->formatted_start_date }}</div>
                                            <div class="dt-time">{{ $message->formatted_start_time }}</div>
                                        </div>
                                        <div class="dt-sep">〜</div>
                                        <div class="dt">
                                            <div class="dt-date">{{ $message->formatted_end_date }}</div>
                                            <div class="dt-time">{{ $message->formatted_end_time }}</div>
                                        </div>
                                    </div>
                                </td>
                                {{-- 状態 --}}
                                <td class="column8">
                                    {{ $message->status->text() }}
                                </td>
                                {{-- 詳細 --}}
                                <td class="column9">
                                    <div class="progress-inline">
                                        @if ($message->status == App\Enums\PublishStatus::Wait || $message->status == App\Enums\PublishStatus::Editing)
                                            詳細
                                        @else
                                            <!-- 閲覧率を表示 -->
                                            <span class="pill"><span
                                                    class="pill-val">{{ $message->total_users != 0 ? $message->view_rate : '0.0' }}%</span></span>
                                            <!-- ユーザー数を表示 -->
                                            <span class="ratio">
                                                (<span class="ratio-now">{{ $message->read_users }}</span>/<span
                                                    class="ratio-max">{{ $message->total_users }}</span>)
                                            </span>
                                            <a href="{{ route('admin.message.publish.show', $message->id) }}"
                                                class="more">詳細</a>
                                        @endif
                                    </div>
                                </td>
                                {{-- 編集 --}}
                                <td class="column10 edit">
                                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                                        <a href="{{ route('admin.message.publish.edit', $message->id) }}"><img
                                                src="{{ asset('/img/edit_icon_blue.svg') }}" alt=""></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
        @include('common.admin.pagenation', ['objects' => $message_list])
    </main>
    @include('common.admin.message-import-modal', ['organization1' => $organization1])
    @include('common.admin.message-export-modal', ['organization1' => $organization1])
    @include('common.admin.confirm-modal')
    @include('common.admin.complete-modal')
@endsection
