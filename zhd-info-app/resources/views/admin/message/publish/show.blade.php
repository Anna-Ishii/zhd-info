{{-- layouts.admin.app をレイアウトとして継承する --}}
@extends('layouts.admin.app')

{{-- 'title' セクションにページ固有のタイトルを設定する --}}
@section('title', '業務連絡 閲覧率詳細')

{{-- 'styles' スタックにページ固有のCSSを追加する --}}
@push('styles')
    <link href="{{ asset('/admin/css/show.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
@endpush

@section('page_header')
    <div class="l-header__bottom">
        <div class="l-header__bottom__wrap">
            <div class="l-header__back"><a class="prev"
                    href="/admin/message/publish?{{ session('message_publish_url') }}"><img
                        src="{{ asset('/img/back-icon.svg') }}"alt="">戻る</a></div>
            <p class="l-header__bottom__ttl">業務連絡</p>
        </div>
        <div class="l-header__bottom__link">
            <button
                onclick="location.href='{{ route('admin.message.publish.export', $message->id) }}?{{ http_build_query(request()->query()) }}'"><img
                    src="{{ asset('/img/export_icon.svg') }}" alt="">エクスポート</button>
        </div>
    </div>
    <x-admin.header-links />
@endsection

{{-- 'content' セクションにメインコンテンツを記述する --}}
@section('content')
    <main class="view-rate-detail">
        <div class="view-rate-detail__main">
            <table class="view-rate-detail__disp">
                <thead>
                    <tr class="head">
                        <th class="column1">対象業態</th>
                        <th class="column2">ラベル</th>
                        <th class="column3">カテゴリ</th>
                        <th class="column4">タイトル</th>
                        <th class="column5">添付</th>
                        <th class="column6">掲載期間</th>
                        <th class="column7">状態</th>
                        <th class="column8">閲覧率</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="column1">{{ $message->brands_string }}</td>
                        @if ($message->emergency_flg)
                            <td class="column2">
                                <span class="pill red"><span class="pill-val">重要</span></span>
                            </td>
                        @else
                            <td class="column2"></td>
                        @endif
                        <td class="column3">{{ $message->category?->name }}</td>
                        <td class="column4"><a href="#">{{ $message->title }}</a></td>
                        <td class="column5"></td>
                        <td class="column6">
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
                        <td class="column7">{{ $message->status->text() }}</td>
                        <td class="column8">
                            <div class="progress-inline">
                                <span class="pill"><span
                                        class="pill-val">{{ $message->total_users != 0 ? round(($message->read_users / $message->total_users) * 100, 1) : 0 }}%</span></span>
                                <span class="ratio">
                                    (<span class="ratio-now">{{ $message->read_users }}</span>/<span
                                        class="ratio-max">{{ $message->total_users }}</span>)
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- 絞り込み部分 -->
        <div class="view-rate-detail__search">
            <form method="get">
                <div class="filter-bar">
                    <div class="field">
                        <div class="label">業態</div>
                        <div class="control">
                            <select name="brand" class="form-control">
                                <option value="">指定なし</option>
                                @foreach ($brand_list as $brand)
                                    <option value="{{ base64_encode($brand->id) }}"
                                        {{ request()->input('brand') == base64_encode($brand->id) ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <div class="label">DS</div>
                        <div class="control">
                            <select name="org3" class="form-control">
                                <option value="">指定なし</option>
                                @foreach ($org3_list as $org3)
                                    <option value="{{ $org3->organization_id }}"
                                        {{ request()->input('org3') == $org3->organization_id ? 'selected' : '' }}>
                                        {{ $org3->organization_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <div class="label">BL</div>
                        <div class="control">
                            <select name="org5" class="form-control">
                                <option value="">指定なし</option>
                                @foreach ($org5_list as $org5)
                                    <option value="{{ $org5->organization_id }}"
                                        {{ request()->input('org5') == $org5->organization_id ? 'selected' : '' }}>
                                        {{ $org5->organization_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <div class="label">AR</div>
                        <div class="control">
                            <select name="org4" class="form-control">
                                <option value="">指定なし</option>
                                @foreach ($org4_list as $org4)
                                    <option value="{{ $org4->organization_id }}"
                                        {{ request()->input('org4') == $org4->organization_id ? 'selected' : '' }}>
                                        {{ $org4->organization_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <div class="label">店舗</div>
                        <div class="control searchbox">
                            <div class="report-search__item search">
                                <div class="input-icon">
                                    <input type="text" id="filter" name="shop_freeword" class="form-control"
                                        value="{{ request()->input('shop_freeword') }}" placeholder="店舗名を入力">
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

                    <div class="field">
                        <div class="label">既読状況</div>
                        <div class="control">
                            <select name="read_flg" class="form-control">
                                <option value="">指定なし</option>
                                <option value="true" {{ request()->input('read_flg') == 'true' ? 'selected' : '' }}>
                                    既読
                                </option>
                                <option value="false" {{ request()->input('read_flg') == 'false' ? 'selected' : '' }}>未読
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="field field--range">
                        <div class="label">閲覧日時</div>
                        <div class="control">
                            <div class="custom-date-picker custom-calendar-input start-date calendarOnly-input">
                                <input class="date-input calendar-input" id="start-date" type="text"
                                    name="readed_date[0]" placeholder="yyyy/MM/dd" readonly>
                                <span class="calendar-icon"></span>
                                <div class="custom-calendar hidden">
                                    <!-- カレンダー描画される部分 -->
                                    <div class="time-picker">
                                        <input type="time" class="time-input"
                                            value="{{ request()->input('readed_date.0') }}">
                                    </div>
                                </div>
                            </div>
                            <span class="tilde">〜</span>
                            <div class="custom-date-picker custom-calendar-input start-date calendarOnly-input">
                                <input class="date-input calendar-input" id="start-date" type="text"
                                    name="readed_date[1]" placeholder="yyyy/MM/dd" readonly>
                                <span class="calendar-icon"></span>
                                <div class="custom-calendar end-date hidden">
                                    <!-- カレンダー描画される部分 -->
                                    <div class="time-picker">
                                        <input type="time" class="time-input"
                                            value="{{ request()->input('readed_date.1') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="annotation">※「インポート」「エクスポート「新規登録」は検索時に設定した業態で行われます。</p>
                <button type="submit" style="display: none;"></button>
            </form>
        </div>
        <div class="view-rate-detail__main">
            <p class="total__dsp">全{{ $user_list->total() }}件</p>
            <table class="view-rate-detail__main__disp">
                <thead>
                    <tr class="head">
                        <th class="column1">対象業態</th>
                        <th class="column2">DS</th>
                        <th class="column3">BL</th>
                        <th class="column4">AR</th>
                        <th class="column5">店舗名</th>
                        <th class="column6">既読状況</th>
                        <th class="column7">最終閲覧閲覧日時</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($user_list as $user)
                        <tr>
                            <td class="column1">{{ $user->shop->brand->name }}</td>
                            <td class="column2">
                                {{ $user->shop->organization3 ? $user->shop->organization3->name : '-' }}</td>
                            <td class="column3">
                                {{ $user->shop->organization5 ? $user->shop->organization5->name : '-' }}</td>
                            <td class="column4">
                                {{ $user->shop->organization4 ? $user->shop->organization4->name : '-' }}</td>
                            <td class="column5">
                                <div class="store__name">
                                    <p class="store__name_id">{{ substr($user->shop->shop_code, -4) }}</p>
                                    <p class="store__name_nane">{{ $user->shop->name }}</p>
                                </div>
                            </td>
                            <td class="column6">{{ $user->pivot->read_flg ? '既読' : '未読' }}</td>
                            <td class="column7">{{ $user->pivot->formatted_readed_datetime }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('common.admin.pagenation', ['objects' => $user_list])
    </main>
@endsection
