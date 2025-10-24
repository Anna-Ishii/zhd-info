@extends('layouts.admin.app')

{{-- 'title' セクションにページ固有のタイトルを設定する --}}
@section('title', '2-1_業務連絡の閲覧状況')

{{-- 'styles' スタックにページ固有のCSSを追加する --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('/admin/css/read-status.css') }}?date={{ date('Ymd') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('/js/admin/analyse/personal.js') }}?date={{ date('Ymd') }}" defer></script>
@endpush

@section('page_header')
<div class="l-header__bottom">
    <div class="l-header__bottom__wrap">
        <div class="l-header__back"><a class="prev" href="{{ request()->header('referer') ? url()->previous() : route('admin.message.publish.index') }}"><img src="{{ asset('/img/back-icon.svg') }}" alt="">戻る</a></div>
        <p class="l-header__bottom__ttl">状況連絡 閲覧状況</p>
    </div>
    <div class="l-header__bottom__link">
        <img src="{{ asset('/img/export_icon.svg') }}" alt=""><a href="{{ route('admin.analyse.export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-admin exportBtn" data-filename="{{ '業務連絡閲覧状況_' . $organization1->name . now()->format('_Y_m_d') . '.xlsx' }}">エクスポート</a>
    </div>
</div>
<x-admin.header-links />
@endsection

{{-- 'content' セクションにメインコンテンツを記述する --}}
@section('content')

<main class="read-status">

    <div class="read-status__search">
        <form method="get" id="searchForm">
            <div class="filter-bar">

                {{-- 業態 --}}
                <!-- <x-admin.select-box name="brand" label="業態" :options="$organization1_list" :base64-value="true" /> -->
                <div class="field">
                    <div class="label">業態</div>
                    <div class="control">
                        <select name="organization1" class="form-control">
                            @foreach ($organization1_list as $org1)
                                <option value="{{ base64_encode($org1->id) }}"
                                    {{ request()->input('organization1') == base64_encode($org1->id) ? 'selected' : '' }}>
                                    {{ $org1->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @foreach (['DS', 'BL', 'AR'] as $organization)
                    <div class="field">
                        <div class="label">{{ $organization }}</div>
                        <div class="control">
                            @if (isset($organization_list[$organization]))
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownOrg{{ $organization }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span id="selectedOrgs{{ $organization }}" class="custom-dropdown-text">全て</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                    </button>
                                    <div id="selectOrg{{ $organization }}" class="dropdown-menu" aria-labelledby="dropdownOrg{{ $organization }}" onclick="event.stopPropagation();">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllOrgs{{ $organization }}" onclick="toggleAllOrgs('{{ $organization }}')">
                                            <label class="form-check-label" for="selectAllOrgs{{ $organization }}" class="custom-label" onclick="event.stopPropagation();">全て選択/選択解除</label>
                                        </div>
                                        @foreach ($organization_list[$organization] as $org)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="org[{{ $organization }}][]" value="{{ $org->id }}"
                                                    {{ in_array($org->id, request()->input('org.' . $organization, [])) ? 'checked' : '' }} id="org{{ $organization }}{{ $org->id }}" onchange="updateSelectedOrgs('{{ $organization }}')">
                                                <label class="form-check-label" for="org{{ $organization }}{{ $org->id }}" class="custom-label" onclick="event.stopPropagation();">
                                                    {{ $org->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownOrg{{ $organization }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disabled>
                                        <span id="selectedOrgs{{ $organization }}" class="custom-dropdown-text">　</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                    </button>
                                    <div id="selectOrg{{ $organization }}" class="dropdown-menu" aria-labelledby="dropdownOrg{{ $organization }}" onclick="event.stopPropagation();">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- 店舗 --}}
                <div class="field">
                    <label class="label">店舗</label>
                    <div class="report-search__item search">
                        <div class="input-icon">
                            <input type="text" name="shop_freeword" class="form-control" value="{{ request()->input('shop_freeword') }}" placeholder="店舗名を入力">
                            <span class="icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.3 20.3C19.9134 20.6866 19.2866 20.6866 18.9 20.3L13.3 14.7C12.8 15.1 12.225 15.4167 11.575 15.65C10.925 15.8833 10.2333 16 9.5 16C7.68333 16 6.14583 15.3708 4.8875 14.1125C3.62917 12.8542 3 11.3167 3 9.5C3 7.68333 3.62917 6.14583 4.8875 4.8875C6.14583 3.62917 7.68333 3 9.5 3C11.3167 3 12.8542 3.62917 14.1125 4.8875C15.3708 6.14583 16 7.68333 16 9.5C16 10.2333 15.8833 10.925 15.65 11.575C15.4167 12.225 15.1 12.8 14.7 13.3L20.3 18.9C20.6866 19.2866 20.6866 19.9134 20.3 20.3V20.3ZM9.5 14C10.75 14 11.8125 13.5625 12.6875 12.6875C13.5625 11.8125 14 10.75 14 9.5C14 8.25 13.5625 7.1875 12.6875 6.3125C11.8125 5.4375 10.75 5 9.5 5C8.25 5 7.1875 5.4375 6.3125 6.3125C5.4375 7.1875 5 8.25 5 9.5C5 10.75 5.4375 11.8125 6.3125 12.6875C7.1875 13.5625 8.25 14 9.5 14Z" fill="#8E9199"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 期間 --}}
                <div class="field field--range">
                    <div class="label">期間</div>
                    <div class="control">
                        <div class="custom-date-picker custom-calendar-input start-date calendarOnly-input">
                            <input id="publishDateFrom" class="date-input calendar-input" name="publish-from-date" value="{{ request()->input('publish-from-date') }}" autocomplete="off" placeholder="yyyy/MM/dd" readonly>
                            <span class="calendar-icon"></span>
                            <div class="custom-calendar hidden">
                                <div class="time-picker">
                                    <input type="time" class="time-input" value="00:00">
                                </div>
                            </div>
                        </div>
                        <span class="tilde">〜</span>
                        <div class="custom-date-picker custom-calendar-input start-date calendarOnly-input">
                            <input id="publishDateTo" class="form-control" name="publish-to-date" value="{{ request()->input('publish-to-date') }}" autocomplete="off" placeholder="yyyy/MM/dd" readonly>
                            <span class="calendar-icon"></span>
                            <div class="custom-calendar hidden">
                                <div class="time-picker">
                                    <input type="time" class="time-input" value="00:00">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 掲載開始日・掲載終了日 --}}
                    <div class="input-group spMb16">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="publish-from-check" {{ request()->input('publish-from-check') == 'on' ? 'checked' : '' }}>
                                掲載開始日
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="publish-to-check" {{ request()->input('publish-to-check') == 'on' ? 'checked' : '' }}>
                                掲載終了日
                            </label>
                        </div>
                    </div>
                </div>

                {{-- キーワード --}}
                <div class="field">
                    <div class="label">キーワード検索</div>
                    <div class="control searchbox">
                        <div class="report-search__item search">
                            <div class="input-icon">
                                <input type="text" name="message_freeword" value="{{ request()->input('message_freeword') }}" class="form-control" placeholder="キーワード" />
                                <span class="icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20.3 20.3C19.9134 20.6866 19.2866 20.6866 18.9 20.3L13.3 14.7C12.8 15.1 12.225 15.4167 11.575 15.65C10.925 15.8833 10.2333 16 9.5 16C7.68333 16 6.14583 15.3708 4.8875 14.1125C3.62917 12.8542 3 11.3167 3 9.5C3 7.68333 3.62917 6.14583 4.8875 4.8875C6.14583 3.62917 7.68333 3 9.5 3C11.3167 3 12.8542 3.62917 14.1125 4.8875C15.3708 6.14583 16 7.68333 16 9.5C16 10.2333 15.8833 10.925 15.65 11.575C15.4167 12.225 15.1 12.8 14.7 13.3L20.3 18.9C20.6866 19.2866 20.6866 19.9134 20.3 20.3V20.3ZM9.5 14C10.75 14 11.8125 13.5625 12.6875 12.6875C13.5625 11.8125 14 10.75 14 9.5C14 8.25 13.5625 7.1875 12.6875 6.3125C11.8125 5.4375 10.75 5 9.5 5C8.25 5 7.1875 5.4375 6.3125 6.3125C5.4375 7.1875 5 8.25 5 9.5C5 10.75 5.4375 11.8125 6.3125 12.6875C7.1875 13.5625 8.25 14 9.5 14Z" fill="#8E9199"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="button" class="save" value="検索条件を保存">
            </div>
        </form>
    </div>

    <div class="read-status__main">
        <p class="total__dsp">※直近の業連を最大10件表示しています。それ以外を確認したい場合は、条件を指定してください。</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr class="head">
                        <th class="column1">DS</th>
                        <th class="column2">BL</th>
                        <th class="column3">AR</th>
                        <th class="column4">店舗</th>
                        <th class="column5">期間計</th>
                        @foreach($messages as $key => $m)
                            @php
                                $columnNum = 6 + ($key % 6);
                            @endphp
                            <th class="column{{ $columnNum }}">
                                <div class="sort-wrap">
                                    <div class="sort-wrap__item">
                                        {{ $m->start_datetime?->isoFormat('YYYY/MM/DD') }}<br>
                                        @isset($m->content_url)
                                            <a href="{{ asset($m->content_url) }}" target="_blank" rel="noopener noreferrer">
                                                {{ $m->title }}
                                            </a>
                                        @else
                                            {{ $m->title }}
                                        @endisset
                                    </div>
                                    <div class="sort-wrap__img">
                                        <img src="{{ asset('/img/sort_icon.svg') }}" alt="">
                                    </div>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                {{-- 業態 (計) --}}
                <tbody>
                    @if(!request('shop_freeword'))
                        <tr>
                            <td class="column1" colspan="4">{{ $organization1->name }}計</td>
                            <td class="column5" nowrap>
                                <div class="progress-inline">
                                    <span class="ratio">{{ $viewrates['org1_readed_sum'] ?? 0 }} / {{ $viewrates['org1_sum'] ?? 0 }}</span>
                                    <span class="pill">
                                        @if(isset($viewrates['org1_readed_sum']) && ($viewrates['org1_sum'] ?? 0) > 0)
                                            @php
                                                $viewrate = 0;
                                                $viewrate = number_format(
                                                ($viewrates['org1_readed_sum'] / $viewrates['org1_sum']) * 100,
                                                1,
                                                );
                                            @endphp
                                            {{ $viewrate }}%
                                        @else
                                            0.0%
                                        @endif
                                    </span>
                                </div>
                            </td>

                            @isset($viewrates['org1'])
                                @foreach($viewrates['org1'] as $key => $v_org1)
                                    @php
                                        $columnNum = 6 + ($key % 6);
                                    @endphp
                                    @isset($v_org1[0]->count)
                                        <td class="{{ $columnNum }}" data-message="{{ $messages[$key]->id }}" data-org-type="Org1" data-org-id="{{ $organization1->id }}" nowrap>
                                            <div class="view_rate progress-inline" data-view-type="orgs">
                                                <span class="ratio">{{ $v_org1[0]->readed_count }} / {{ $v_org1[0]->count }}</span>
                                                <span class="pill">{{ $v_org1[0]->view_rate }}%</div>
                                            </div>
                                        </td>
                                    @else
                                        <td class="{{ $columnNum }}" nowrap>
                                            <div class="progress-inline">
                                                <span class="ratio">0 / 0</span>
                                                <span class="pill">0.0%</span>
                                            </div>
                                        </td>
                                    @endisset
                                @endforeach
                            @endisset
                        </tr>

                        {{-- 組織ごと (計) --}}
                        @foreach($organizations as $organization)
                            @isset($viewrates[$organization][0])
                                @foreach($viewrates[$organization][0] as $v_org_key => $v_o)
                                    <tr>
                                        <td class="column1" colspan="4" nowrap>{{ $v_o->name }}</td>
                                        <td class="column5" nowrap>
                                            <div class="progress-inline">
                                                <span class="ratio">
                                                    {{ $viewrates[$organization . '_readed_sum'][$v_o->id] }} / {{ $viewrates[$organization . '_sum'][$v_o->id] }}
                                                </span>
                                                <span class="pill">
                                                    @if(isset($viewrates[$organization . '_readed_sum'][$v_o->id]) &&
                                                        ($viewrates[$organization . '_sum'][$v_o->id] ?? 0) > 0)
                                                        @php
                                                            $viewrate = 0;
                                                            $viewrate = number_format(
                                                            ($viewrates[$organization . '_readed_sum'][$v_o->id] /
                                                            $viewrates[$organization . '_sum'][$v_o->id]) *
                                                            100,
                                                            1,
                                                            );
                                                        @endphp
                                                        <div>{{ $viewrate }}%</div>
                                                    @else
                                                        <div>0.0%</div>
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                        @foreach($messages as $key => $ms)
                                            @php
                                                $columnNum = 6 + ($key % 6);
                                            @endphp
                                            @isset($viewrates[$organization][$key][$v_org_key]->count)
                                                <td class="column{{ $columnNum }}" data-message={{ $messages[$key]->id }} data-org-id={{ $v_o->id }} data-org-type={{ $organization }} nowrap>
                                                    <div class="view_rate progress-inline" data-view-type="orgs">
                                                        <span class="ratio">{{ $viewrates[$organization][$key][$v_org_key]->readed_count }} / {{ $viewrates[$organization][$key][$v_org_key]->count }}</span>
                                                        <span data-message={{ $ms->id }} class="pill message-viewlate {{ $viewrates[$organization][$key][$v_org_key]->view_rate < 10 ? 'under-quota' : '' }} ">
                                                            {{ $viewrates[$organization][$key][$v_org_key]->view_rate }}%
                                                        </span>
                                                    </div>
                                                </td>
                                            @else
                                                <td class="column{{ $columnNum }}" data-message={{ $messages[$key]->id }} data-org-id={{ $v_o->id }} data-org-type={{ $organization }} nowrap>
                                                    <div class="progress-inline">
                                                        <span class="ratio">0 / 0</span>
                                                        <span class="pill">
                                                            0.0%
                                                        </span>
                                                    </div>
                                                </td>
                                            @endisset
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endisset
                        @endforeach
                    @endif

                    {{-- 店舗ごと --}}
                    @isset($viewrates['shop'][0])
                        @foreach($viewrates['shop'][0] as $v_key => $m_c)
                            <tr>
                                @isset($m_c->o3_name)
                                    <td class="column1" style="border-right: none" nowrap>{{ $m_c->o3_name }}</td>
                                @else
                                    <td class="column1" style="border-right: none"></td>
                                @endisset
                                @isset($m_c->o5_name)
                                    <td class="column2" nowrap>{{ $m_c->o5_name }}</td>
                                @else
                                    <td class="column2"></td>
                                @endisset
                                @isset($m_c->o4_name)
                                    <td class="column3" nowrap>{{ $m_c->o4_name }}</td>
                                @else
                                    <td class="column3"></td>
                                @endisset
                                <td class="column4" style="border-right: 1px solid #8E9199;" nowrap>{{ $m_c->shop_code }}　{{ $m_c->shop_name }}</td>
                                <td class="column5" nowrap>
                                    <div class="progress-inline">
                                        <span class="ratio">
                                            {{ $viewrates['shop_readed_sum'][$m_c->shop_code] }} / {{ $viewrates['shop_sum'][$m_c->shop_code] }}
                                        </span>
                                        <span class="pill">
                                            @if(isset($viewrates['shop_readed_sum'][$m_c->shop_code]) && ($viewrates['shop_sum'][$m_c->shop_code] ?? 0) > 0)
                                                @php
                                                    $viewrate = 0;
                                                    $viewrate = number_format(
                                                    ($viewrates['shop_readed_sum'][$m_c->shop_code] /
                                                    $viewrates['shop_sum'][$m_c->shop_code]) *
                                                    100,
                                                    1,
                                                    );
                                                @endphp
                                                {{ $viewrate }}%
                                            @else
                                                0.0%
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                @foreach($messages as $key => $ms)
                                    @php
                                        $columnNum = 6 + ($key % 6);
                                    @endphp
                                    @if(($viewrates['shop'][$key][$v_key]->count ?? 0) > 0)
                                        <td class="column{{ $columnNum }}" data-message={{ $ms->id }} data-shop={{ $viewrates['shop'][$key][$v_key]->_shop_id }} class="{sortValue: {{ $viewrates['shop'][$key][$v_key]->view_rate }}}" nowrap>
                                            <div class="view_rate progress-inline" data-view-type="shops">
                                                <span class="ratio">{{ $viewrates['shop'][$key][$v_key]->readed_count }} / {{ $viewrates['shop'][$key][$v_key]->count }}</span>
                                                <span class="pill">{{ $viewrates['shop'][$key][$v_key]->view_rate ?? 0.0 }}%</span>
                                            </div>
                                        </td>
                                    @else
                                        <td class="{sortValue: 0.0}" nowrap>
                                            <div class="progress-inline view_rate_container">
                                                <span class="ratio">0 / 0</span>
                                                <span class="pill">0.0% </span>
                                            </div>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    @endisset
                </tbody>
            </table>
        </div>
    </div>
</main>
<script src="{{ asset('/js/admin/analyse/personal.js') }}?date={{ date('Ymd') }}" defer></script>
<script src="{{ asset('/js/admin/analyse/read-status.js') }}?date={{ date('Ymd') }}"></script>
@endsection
