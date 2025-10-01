{{-- layouts.admin.app をレイアウトとして継承する --}}
@extends('layouts.admin.app')

{{-- 'title' セクションにページ固有のタイトルを設定する --}}
@section('title', 'DM/BM/AMメール配信設定')

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

@push('css')
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet"
    />
@endpush

{{-- 'styles' スタックにページ固有のCSSを追加する --}}
@push('styles')
    <link href="{{ asset('/css/phase3/email-distribution-settings.css') }}?date={{ date('Ymd') }}" rel="stylesheet"/>
@endpush

@push('scripts')
    <script src="{{ asset('/js/admin/account/mailaccount/index.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/admin/account/mailaccount/email-distribution-settings.js') }}?date={{ date('Ymd') }}" defer></script>
@endpush

<meta name="title" content="" />
<meta name="description" content="" />

<title>DM/BM/AMメール配信設定</title>

@section('content')
    <header class="l-header">
        <div class="l-header__bottom">
            <div class="l-header__bottom__wrap">
                <div class="l-header__back"><a class="prev" href="#"><img src="{{ asset('img/back-icon.svg') }}" alt="">戻る</a></div>
                <p class="l-header__bottom__ttl">DM/BM/AMメール配信設定</p>
            </div>
            <div class="l-header__bottom__link">
                <a href="{{ route('admin.account.mail.export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-admin"><img src="{{ asset('img/export_icon.svg') }}" alt="">エクスポート</a>
            </div>
        </div>
        <div class="l-header__link">
            <div class="l-header__link__wrap">
                <div class="l-header__link__text"><a class="page_link" href="{{ isset($message_saved_url) && $message_saved_url->page_name == 'message-publish' ? $message_saved_url->url : '/admin/message/publish/' }}">業務連絡一覧</a></div>
                <div class="l-header__link__text"><a class="page_link" href="{{ isset($analyse_personal_saved_url) && $analyse_personal_saved_url->page_name == 'analyse-personal' ? $analyse_personal_saved_url->url : '/admin/analyse/personal/' }}">閲覧状況</a></div>
                <div class="l-header__link__text"><a class="page_link" href="/admin/account/">店舗アカウント</a></div>
                <div class="l-header__link__text"><a class="page_link active" href="/admin/account/mail">DM/BM/AMメール配信設定</a></div>
                <div class="l-header__link__text"><a class="page_link" href="/admin/account/adminmail">本部従業員への配信設定</a></div>
                <div class="l-header__link__text"><a class="page_link" href="/admin/manage/ims">IMS連携</a></div>
            </div>
        </div>
    </header>
    <!-- 絞り込み部分 -->
    <main class="email-distribution-settings">
        <div class="email-distribution-settings__search ">   
            <form method="get" class="mb24">
                <div class="form-group form-inline mb16 ">             
                    <div class="filter-bar">
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
                        <!-- DS,BL,ARでの絞り込み(存在しない場合は空欄になる) -->
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
                        <form method="get" class="mb24">
                            <div class="field">
                                <div class="label">店舗</div>
                                <div class="control searchbox">
                                    <div class="report-search__item search">
                                        <div class="input-icon">
                                            <input type="text" id="filter" name="shop_freeword" class="form-control" value="{{ request()->input('shop_freeword') }}" placeholder="店舗名を入力">
                                            <span class="icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M20.3 20.3C19.9134 20.6866 19.2866 20.6866 18.9 20.3L13.3 14.7C12.8 15.1 12.225 15.4167 11.575 15.65C10.925 15.8833 10.2333 16 9.5 16C7.68333 16 6.14583 15.3708 4.8875 14.1125C3.62917 12.8542 3 11.3167 3 9.5C3 7.68333 3.62917 6.14583 4.8875 4.8875C6.14583 3.62917 7.68333 3 9.5 3C11.3167 3 12.8542 3.62917 14.1125 4.8875C15.3708 6.14583 16 7.68333 16 9.5C16 10.2333 15.8833 10.925 15.65 11.575C15.4167 12.225 15.1 12.8 14.7 13.3L20.3 18.9C20.6866 19.2866 20.6866 19.9134 20.3 20.3V20.3ZM9.5 14C10.75 14 11.8125 13.5625 12.6875 12.6875C13.5625 11.8125 14 10.75 14 9.5C14 8.25 13.5625 7.1875 12.6875 6.3125C11.8125 5.4375 10.75 5 9.5 5C8.25 5 7.1875 5.4375 6.3125 6.3125C5.4375 7.1875 5 8.25 5 9.5C5 10.75 5.4375 11.8125 6.3125 12.6875C7.1875 13.5625 8.25 14 9.5 14Z" fill="#8E9199"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </form>
        </div>

    <!-- 検索結果 -->

        <div class="email-distribution-settings__main">
            <p class="total__dsp">全{{ $users->total() }}件</p>
            <div>
                @if ($admin->ability == App\Enums\AdminAbility::Edit)
                    <div class="account-edit-btn-group" style="display: flex; justify-content: flex-end;">
                        <p class="accountEditBtn btn btn-admin" onclick="this.style.pointerEvents = 'none';" style="text-align:center; width: 128px; line-height: 1.5; font-weight: bold; font-size: 1.6rem !important; border-radius: 5px; background: #0051A3; color: white; display: inline-block; margin-top: -55px; margin-bottom: 15px; margin-right: 20px; padding: 10px 16px; cursor: pointer;">編集</p>
                    </div>
                @endif
            </div>
            <div class="table-wrap">
                <div class="tableInner" style="height: 70vh;">
                    <table id="list" class="mail-account table-list table table-bordered table-hover table-condensed text-center">

                        <thead>
                            <!-- colspanの列幅指定用 -->
                            <tr class="def">
                                <th class="column1"></th>
                                <th class="column2"></th>
                                <th class="column3"></th>
                                <th class="column4"></th>
                                <th class="column5"></th>
                                <th class="column6"></th>
                                <th class="column7"></th>
                                <th class="column8"></th>
                                <th class="column9"></th>
                                <th class="column6"></th>
                                <th class="column7"></th>
                                <th class="column8"></th>
                                <th class="column9"></th>
                                <th class="column6"></th>
                                <th class="column7"></th>
                                <th class="column8"></th>
                                <th class="column9"></th>
                            </tr>
                            <tr class="head">
                                <th class="column1" rowspan="2" nowrap data-column="0">DS</th>
                                <th class="column2" rowspan="2" nowrap data-column="1">BL</th>
                                <th class="column3" rowspan="2" nowrap data-column="2">AR</th>
                                <th class="column4" colspan="2" style="text-align: center;" nowrap>店舗</th>
                                <th class="column6" colspan="4" style="text-align: center;" nowrap>DM</th>
                                <th class="column6" colspan="4" style="text-align: center;" nowrap>BM</th>
                                <th class="column6" colspan="4" style="text-align: center;" nowrap>AM</th>
                            </tr>
                            <tr class="head_bottom">
                                <th class="column4">コード</th>
                                <th class="column5">店舗名</th>
                                <th class="column6">従業員番号</th>
                                <th class="column7">氏名</th>
                                <th class="column8">メールアドレス</th>
                                <th class="column9">業連閲覧状況<br>メール配信<br class="DMStatusBreak" style="display: none;">
                                        <button type="button" class="btn btn-outline-primary btn-sm DMStatusAllSelectBtn"
                                            data-toggle="button" aria-pressed="false"
                                            style="display: none; font-size: 1.0rem; line-height: 1.2; color: #8E9199; border: 1px solid #8E9199; border-radius: 2px; padding: 8px; background-color: #fff;">
                                            すべて選択/解除
                                    </button>
                                </th>
                                <th class="column6">従業員番号</th>
                                <th class="column7">氏名</th>
                                <th class="column8">メールアドレス</th>
                                <th class="column9">業連閲覧状況<br>メール配信<br class="BMStatusBreak" style="display: none;">
                                    <button type="button" class="btn btn-outline-primary btn-sm BMStatusAllSelectBtn"
                                        data-toggle="button" aria-pressed="false"
                                        style="display: none; font-size: 1.0rem; line-height: 1.2; color: #8E9199; border: 1px solid #8E9199; border-radius: 2px; padding: 8px; background-color: #fff;">
                                        すべて選択/解除
                                    </button>
                                </th>
                                <th class="column6">従業員番号</th>
                                <th class="column7">氏名</th>
                                <th class="column8">メールアドレス</th>
                                <th class="column9">業連閲覧状況<br>メール配信<br class="AMStatusBreak" style="display: none;">
                                    <button type="button" class="btn btn-outline-primary btn-sm AMStatusAllSelectBtn"
                                        data-toggle="button" aria-pressed="false"
                                        style="display: none; font-size: 1.0rem; line-height: 1.2; color: #8E9199; border: 1px solid #8E9199; border-radius: 2px; padding: 8px; background-color: #fff;">
                                        すべて選択/解除
                                    </button>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($users as $u)
                        <tr data-user_id="{{ $u->id }}"
                            data-shop_id="{{ $u->shop_id }}"
                            data-DM_id="{{ $u->DM_id }}"
                            data-BM_id="{{ $u->BM_id }}"
                            data-AM_id="{{ $u->AM_id }}"
                            class="">
                            <!-- DS -->
                            <td class="label-DS" nowrap>
                                @if (isset($organizations[$u->shop_id]['DS']))
                                    @foreach ($organizations[$u->shop_id]['DS'] as $ds)
                                        {{ $ds['org3_name'] }}
                                    @endforeach
                                @endif
                            </td>
                            <!-- BL -->
                            <td class="label-BL" nowrap>
                                @if (isset($organizations[$u->shop_id]['BL']))
                                    @foreach ($organizations[$u->shop_id]['BL'] as $bl)
                                        {{ $bl['org5_name'] }}
                                    @endforeach
                                @endif
                            </td>
                            <!-- AR -->
                            <td class="label-AR" style="border-right: 1px solid #DADADB;" nowrap>
                                @if (isset($organizations[$u->shop_id]['AR']))
                                    @foreach ($organizations[$u->shop_id]['AR'] as $ar)
                                        {{ $ar['org4_name'] }}
                                    @endforeach
                                @endif
                            </td>
                            <!-- 店舗 -->
                            <td class="label-shop_id" style="border-right: 1px solid #DADADB; text-align: center" nowrap>{{ $u->shop_code }}</td>
                            <td class="label-shop_name" style="border-right: 1px solid #DADADB;" nowrap>{{ $u->shop_name }}</td>
                            <!-- DM -->
                            <td class="label-DM_id" style="text-align: center" nowrap>{{ $u->DM_id }}</td>
                            <td class="label-DM_name" nowrap>{{ $u->DM_name }}</td>
                            <td class="label-DM_email" nowrap>{{ $u->DM_email }}</td>
                            <td class="label-DM_status" style="border-right: 1px solid #DADADB; text-align: center" nowrap>
                                <span class="DM_status-select"
                                    value="{{ $u->DM_view_notification == '〇' ? 'selected' : '未設定' }}">{{ $u->DM_view_notification == '〇' ? 'selected' : '未設定' }}</span>
                            </td>
                            <!-- BM -->
                            <td class="label-BM_id" style="text-align: center" nowrap>{{ $u->BM_id }}</td>
                            <td class="label-BM_name" nowrap>{{ $u->BM_name }}</td>
                            <td class="label-BM_email" nowrap>{{ $u->BM_email }}</td>
                            <td class="label-BM_status" style="border-right: 1px solid #DADADB; text-align: center" nowrap>
                                <span class="BM_status-select" data-BM_id="{{ $u->BM_id }}"
                                    value="{{ $u->BM_view_notification == '〇' ? 'selected' : '' }}">{{ $u->BM_view_notification == '〇' ? 'selected' : '未設定' }}</span>
                            </td>
                            <!-- AM -->
                            <td class="label-AM_id" style="text-align: center" nowrap>{{ $u->AM_id }}</td>
                            <td class="label-AM_name" nowrap>{{ $u->AM_name }}</td>
                            <td class="label-AM_email" nowrap>{{ $u->AM_email }}</td>
                            <td class="label-AM_status" style="border-right: 1px solid #DADADB; text-align: center" nowrap>
                                <span class="AM_status-select" data-AM_id="{{ $u->AM_id }}"
                                    value="{{ $u->AM_view_notification == '〇' ? 'selected' : '' }}">{{ $u->AM_view_notification == '〇' ? 'selected' : '未設定' }}</span>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @include('common.admin.pagenation', ['objects' => $users])
    </main>
@endsection
