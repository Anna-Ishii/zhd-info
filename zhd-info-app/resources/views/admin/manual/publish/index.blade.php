{{-- layouts.admin.app をレイアウトとして継承する --}}
@extends('layouts.admin.app')

{{-- 'title' セクションにページ固有のタイトルを設定する --}}
@section('title', 'マニュアル一覧')

{{-- 'styles' スタックにページ固有のCSSを追加する --}}
@push('styles')
<link href="{{ asset('/admin/css/show.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
{{-- ページごとのCSSがここに入る --}}
<link href="{{ asset('/admin/css/manual-list.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
@endpush

@section('page_header')
<div class="l-header__bottom">
    <div class="l-header__bottom__wrap">
        <div class="l-header__back"><a class="prev"
                href="/admin/message/publish?{{ session('message_publish_url') }}"><img
                    src="{{ asset('/img/back-icon.svg') }}" alt="">戻る</a></div>
        <p class="l-header__bottom__ttl">マニュアル一覧</p>

    </div>
    <div class="l-header__bottom__link">
        @if ($admin->ability == App\Enums\AdminAbility::Edit)
        <button class="inport-modal-btn" data-toggle="modal" data-target="#manualImportModal"><img src="/img/inport_icon.svg" alt="">インポート</button>
        @endif
        <button class="export-modal-btn" data-toggle="modal" data-target="#manualExportModal"><img src="/img/export_icon.svg" alt="">エクスポート</button>
        @if ($admin->ability == App\Enums\AdminAbility::Edit)
        <a href="{{ route('admin.manual.publish.new', ['organization1' => $organization1]) }}"><img src="/img/register_icon.svg" alt="">新規登録</a>
        @endif
    </div>
</div>


</div>
<x-admin.manual-nav :admin="$admin" :organization1="$organization1"/>
@endsection

@section('content')
    <main class="manual-list">
        <!-- 絞り込み部分 -->
        <div class="manual-list__search ">
        <form method="get">
            <div class="filter-bar">
                <div class="field">
                    <div class="label">業態</div>
                    <div class="control">
                        <select name="brand" class="form-control">
                            @foreach ($organization1_list as $org1)
                                <option
                                    value="{{ base64_encode($org1->id) }}"
                                    {{ request()->input('brand') == base64_encode($org1->id) ? 'selected' : '' }}>
                                    {{ $org1->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field">
                    <div class="label">形式</div>
                    <div class="control">
                        <select name="manual_type" class="form-control">
                            <option value="" class="custom-dropdown-text">指定なし</option>
                            @foreach ($manual_types as $type)
                                <option value="{{ $type->id }}" {{ request()->input('manual_type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field">
                    <div class="label">カテゴリ</div>
                    <div class="control">
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span id="selectedCategories" class="custom-dropdown-text">指定なし</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                    <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" onclick="event.stopPropagation();">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllCategories">
                                    <label class="form-check-label custom-label" for="selectAllCategories" onclick="event.stopPropagation();">全て選択/選択解除</label>
                                </div>
                                @foreach ($new_category_list as $category)
                                    <div class="form-check">
                                        <input class="form-check-input categoryCheck" type="checkbox" name="new_category[]" value="{{ $category->id }}"
                                               id="new_category{{ $category->id }}"
                                               {{ in_array($category->id, request()->input('new_category', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label custom-label" for="new_category{{ $category->id }}" onclick="event.stopPropagation();">
                                            {{ $category->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <div class="label">状態</div>
                    <div class="control">
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownStatusButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span id="selectedStatus" class="custom-dropdown-text">指定なし</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                    <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownStatusButton" onclick="event.stopPropagation();">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllStatuses" onclick="toggleAllStatuses()">
                                    <label class="form-check-label custom-label" for="selectAllStatuses" onclick="event.stopPropagation();">全て選択/選択解除</label>
                                </div>
                                @foreach ($publish_status as $status)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="status[]" value="{{ $status->value }}"
                                               id="status{{ $status->value }}"
                                               {{ in_array($status->value, request()->input('status', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label custom-label" for="status{{ $status->value }}" onclick="event.stopPropagation();">
                                            {{ $status->text() }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field field--range">
                    <div class="label">掲載期間</div>
                    <div class="control">
                        <div class="custom-date-picker custom-calendar-input start-date calendarOnly-input">
                            <input class="date-input calendar-input" name="publish-date[0]" type="text"
                               placeholder="yyyy/MM/dd" readonly
                               value="{{ request()->input('publish-date.0', '') }}">
                            <span class="calendar-icon"></span>
                            <div class="custom-calendar hidden">
                                <!-- カレンダー描画される部分 -->
                                <div class="time-picker">
                                    <input type="time" class="time-input" value="00:00">
                                </div>
                            </div>
                        </div>
                        <span class="tilde">〜</span>
                        <div class="custom-date-picker custom-calendar-input start-date calendarOnly-input">
                            <input class="date-input calendar-input" name="publish-date[1]" type="text"
                               placeholder="yyyy/MM/dd" readonly
                               value="{{ request()->input('publish-date.1', '') }}">
                            <span class="calendar-icon"></span>
                            <div class="custom-calendar hidden">
                                <!-- カレンダー描画される部分 -->
                                <div class="time-picker">
                                    <input type="time" class="time-input" value="00:00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- キーワード検索 --}}
                <div class="field">
                    <div class="label">キーワード検索</div>
                    <div class="control searchbox">
                        <div class="report-search__item search">
                            <div class="input-icon">
                                <input type="text" id="filter" name="q" placeholder="キーワード" value="{{ request()->input('q', '') }}">
                                <span class="icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M20.3 20.3C19.9134 20.6866 19.2866 20.6866 18.9 20.3L13.3 14.7C12.8 15.1 12.225 15.4167 11.575 15.65C10.925 15.8833 10.2333 16 9.5 16C7.68333 16 6.14583 15.3708 4.8875 14.1125C3.62917 12.8542 3 11.3167 3 9.5C3 7.68333 3.62917 6.14583 4.8875 4.8875C6.14583 3.62917 7.68333 3 9.5 3C11.3167 3 12.8542 3.62917 14.1125 4.8875C15.3708 6.14583 16 7.68333 16 9.5C16 10.2333 15.8833 10.925 15.65 11.575C15.4167 12.225 15.1 12.8 14.7 13.3L20.3 18.9C20.6866 19.2866 20.6866 19.9134 20.3 20.3V20.3ZM9.5 14C10.75 14 11.8125 13.5625 12.6875 12.6875C13.5625 11.8125 14 10.75 14 9.5C14 8.25 13.5625 7.1875 12.6875 6.3125C11.8125 5.4375 10.75 5 9.5 5C8.25 5 7.1875 5.4375 6.3125 6.3125C5.4375 7.1875 5 8.25 5 9.5C5 10.75 5.4375 11.8125 6.3125 12.6875C7.1875 13.5625 8.25 14 9.5 14Z" fill="#8E9199"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <button type="button" class="save saveSearchBtn">検索条件を保存</button>
                </div>
                <p class="annotation">※「インポート」「エクスポート「新規登録」は検索時に設定した業態で行われます。</p>
                <button type="submit" style="display: none;"></button>
            </form>
        </div>

        <div class="manual-list__main">
            <form method="post" action="#">
            <p class="total__dsp">全{{ $manual_list->total() }}件</p>
                <table id="list">
                    <thead>
                        <tr class="head">
                            <th class="column1">No</th>
                            <th class="column2">形式</th>
                            <th class="column3">対象業態</th>
                            <th class="column4">カテゴリ</th>
                            <th class="column5">タイトル</th>
                            <th class="column6">掲載期間</th>
                            <th class="column7">状態</th>
                            <th class="column8">閲覧率</th>
                            <th class="column9">編集</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($manual_list as $manual)
                            <tr data-manual_id={{ $manual->id }}
                                class="@if ($manual->status == App\Enums\PublishStatus::Publishing) publishing
                                        @elseif($manual->status == App\Enums\PublishStatus::Published) published
                                        @elseif($manual->status == App\Enums\PublishStatus::Wait) wait
                                        @elseif($manual->status == App\Enums\PublishStatus::Editing) editing @endif">
                                <td class="column1">{{ $manual->number }}</td>
                                <td class="column2">{{ $manual->manual_types }}</td>
                                <td class="column3">{{ $manual->brand_name }}</td>
                                <td class="column4">
                                    @if ($manual->category_level1)
                                        {{ "{$manual->category_level1?->name} |" }}
                                    @endif
                                    {{ $manual->category_level2?->name }}
                                </td>
                                <td class="column5">
                                    {{ $manual->title }}
                                </td>
                                <td class="column6">
                                    <div class="date-range">
                                        <div class="dt">
                                            <div class="dt-date">{{ $manual->formatted_start_date }}</div>
                                            <div class="dt-time">{{ $manual->formatted_start_time }}</div>
                                        </div>
                                        <div class="dt-sep">〜</div>
                                        <div class="dt">
                                            <div class="dt-date">{{ $manual->formatted_end_date }}</div>
                                            <div class="dt-time">{{ $manual->formatted_end_time }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="column7">{{ $manual->status->text() }}</td>
                                @if ($manual->status == App\Enums\PublishStatus::Wait || $manual->status == App\Enums\PublishStatus::Editing)
                                    <td></td>
                                    <td></td>
                                    <td nowrap>詳細</td>
                                @else
                                    <!-- 閲覧率を表示 -->
                                    <td
                                        class="column8 view-rate {{ ($manual->total_users != 0 ? $manual->view_rate : 0) <= 30 ? 'under-quota' : '' }}">
                                        <div class="progress-inline">
                                            <div class="pill"><span class="pill-val">{{ $manual->total_users != 0 ? $manual->view_rate : '0.0' }}% </span></div>
                                                <div class="ratio">
                                                    <span>(</span>
                                                    <span class="ratio-num">{{ $manual->read_users }}/{{ $manual->total_users }}</span>
                                                    <span>)</span>
                                                </div>
                                                <a href="/admin/manual/publish/{{ $manual->id }}" class="more">詳細</a>
                                            </div>
                                    </td>
                                @endif

                                @if ($admin->ability == App\Enums\AdminAbility::Edit)
                                    <td class="column9 edit editIcon">
                                        <img src="{{ asset('/img/edit_icon_blue.svg') }}" alt="編集">
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
        @include('common.admin.pagenation', ['objects' => $manual_list])
    </main>
    @include('common.admin.manual-import-modal', ['organization1' => $organization1])
    @include('common.admin.manual-export-modal', ['organization1' => $organization1])
    @include('common.admin.confirm-modal')
    @include('common.admin.complete-modal')

    <script src="{{ asset('/js/admin/manual/publish/index.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/admin/manual/publish/calendarWeekdays.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/index.js') }}?date={{ date('Ymd') }}" defer></script>
@endsection
