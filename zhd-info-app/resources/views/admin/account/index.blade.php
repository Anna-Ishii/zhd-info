@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                @if (in_array('message', $arrow_pages, true) || in_array('manual', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">1.配信</a>
                        <ul class="nav nav-second-level">
                            @if (in_array('message', $arrow_pages, true))
                                <li><a href="/admin/message/publish/">1-1 業務連絡</a></li>
                            @endif
                            @if (in_array('manual', $arrow_pages, true))
                                <li><a href="/admin/manual/publish/">1-2 動画マニュアル</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (in_array('message-analyse', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">2.データ抽出</span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/admin/analyse/personal">2-1.業務連絡の閲覧状況</a></li>
                        </ul>
                    </li>
                @endif
                @if (in_array('account-shop', $arrow_pages, true) || in_array('account-admin', $arrow_pages, true) || in_array('account-mail', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">3.管理</span></a>
                        <ul class="nav nav-second-level">
                            @if (in_array('account-shop', $arrow_pages, true))
                                <li class="active"><a href="/admin/account/">3-1.店舗アカウント</a></li>
                            @endif
                            @if (in_array('account-admin', $arrow_pages, true))
                                <li><a href="/admin/account/admin">3-2.本部アカウント</a></li>
                            @endif
                            @if (in_array('account-mail', $arrow_pages, true))
                                <li><a href="/admin/account/mail">3-3.DM/BM/AMメール配信設定</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (in_array('ims', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">4.その他</span></a>
                        <ul class="nav nav-second-level">
                            <li class="{{ $is_error_ims ? 'warning' : '' }}"><a href="/admin/manage/ims">4-1.IMS連携</a></li>
                        </ul>
                    </li>
                @endif
                <li>
                    <a href="#" class="nav-label">Ver. {{ config('version.admin_version') }}</span></a>
                </li>
            </ul>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
    <!-- /.navbar-static-side -->
@endsection

@section('content')
    <div id="page-wrapper">

        <!-- 絞り込み部分 -->
        <form method="get" class="mb24">
            <div class="form-group form-inline mb16 ">
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">業態</label>
                    <select name="organization1" class="form-control">
                        @foreach ($organization1_list as $org1)
                            <option value="{{ $org1->id }}"
                                {{ request()->input('organization1') == $org1->id ? 'selected' : '' }}>{{ $org1->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @foreach (['DS', 'BL', 'AR'] as $organization)
                    <div class="input-group col-lg-1 spMb16">
                        <label class="input-group-addon">{{ $organization }}</label>
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
                            <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownOrg{{ $organization }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disabled>
                                <span id="selectedOrgs{{ $organization }}" class="custom-dropdown-text">　</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                    <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endforeach
                <div class="input-group spMb16">
                    <label class="input-group-addon">店舗</label>
                    <input type="text" name="shop_freeword" class="form-control"
                        value="{{ request()->input('shop_freeword') }}">
                </div>
                <div class="input-group">
                    <button class="btn btn-admin">検索</button>
                </div>
                <div class="input-group">
                    <a href="{{ route('admin.account.export') }}?{{ http_build_query(request()->query()) }}"
                        class="btn btn-admin">エクスポート</a>
                </div>
            </div>
        </form>

        <!-- 検索結果 -->
        <form>
            <div class="pagenation-top">
                @include('common.admin.pagenation', ['objects' => $users])
                <div>
                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <div class="account-edit-btn-group">
                            <p class="accountEditBtn btn btn-admin" onclick="this.style.pointerEvents = 'none';">編集</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="tableInner" style="height: 70vh;">
                <table id="list" class="account table-list table table-bordered table-hover table-condensed text-center">

                    <thead>
                        <tr>
                            <th class="head1" rowspan="2" nowrap data-column="0">DS</th>
                            <th class="head1" rowspan="2" nowrap data-column="1">BL</th>
                            <th class="head1" rowspan="2" nowrap data-column="2">AR</th>
                            <!-- 店舗を2つの列に分ける -->
                            <th class="head1" colspan="2" nowrap>店舗</th>
                            <th class="head1" colspan="3" nowrap>WowTalk1</th>
                            <th class="head2" colspan="3" nowrap>WowTalk2</th>
                        </tr>
                        <tr>
                            <!-- 店舗のサブヘッダー -->
                            <th class="head1" nowrap data-column="3">コード</th>
                            <th class="head1" nowrap data-column="4">店舗名</th>
                            <!-- WowTalk1のサブヘッダー -->
                            <th class="head1" nowrap>ID</th>
                            <th class="head1 head-WT1_status" nowrap>業連閲覧状況の通知<br class="WT1StatusBreak" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm WT1StatusAllSelectBtn"
                                    data-toggle="button" aria-pressed="false"
                                    style="position: relative; z-index: 10; display: none;">
                                    すべて選択/解除
                                </button>
                            </th>
                            <th class="head1 head-WT1_send" nowrap>業連・マニュアル配信の通知<br class="WT1SendBreak" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm WT1SendAllSelectBtn"
                                    data-toggle="button" aria-pressed="false"
                                    style="position: relative; z-index: 10; display: none;">
                                    すべて選択/解除
                                </button>
                            </th>
                            <!-- WowTalk2のサブヘッダー -->
                            <th class="head2" nowrap>ID</th>
                            <th class="head2 head-WT2_status" nowrap>業連閲覧状況の通知<br class="WT2StatusBreak" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm WT2StatusAllSelectBtn"
                                    data-toggle="button" aria-pressed="false"
                                    style="position: relative; z-index: 10; display: none;">
                                    すべて選択/解除
                                </button>
                            </th>
                            <th class="head2 head-WT2_send" nowrap>業連・マニュアル配信の通知<br class="WT2SendBreak" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm WT2SendAllSelectBtn"
                                    data-toggle="button" aria-pressed="false"
                                    style="position: relative; z-index: 10; display: none;">
                                    すべて選択/解除
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($users as $u)
                            <tr data-shop_id="{{ $u->shop_id }}" class="">
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
                                <td class="label-AR" nowrap>
                                    @if (isset($organizations[$u->shop_id]['AR']))
                                        @foreach ($organizations[$u->shop_id]['AR'] as $ar)
                                            {{ $ar['org4_name'] }}
                                        @endforeach
                                    @endif
                                </td>
                                <!-- 店舗 -->
                                <td class="label-shop_id" nowrap>{{ $u->shop_code }}</td>
                                <td class="label-shop_name" nowrap>{{ $u->shop_name }}</td>
                                <!-- WowTalk1 -->
                                <td class="label-WT1_id" nowrap>{{ $u->wowtalk1_id }}</td>
                                <td class="label-WT1_status" nowrap>
                                    <span class="WT1_status-select"
                                        value="{{ $u->notification_target1 == '〇' ? 'selected' : '' }}">{{ $u->notification_target1 }}</span>
                                </td>
                                <td class="label-WT1_send" nowrap>
                                    <span class="WT1_send-select"
                                        value="{{ $u->business_notification1 == '〇' ? 'selected' : '' }}">{{ $u->business_notification1 }}</span>
                                </td>
                                <!-- WowTalk2 -->
                                <td class="label-WT2_id" nowrap>{{ $u->wowtalk2_id }}</td>
                                <td class="label-WT2_status" nowrap>
                                    <span class="WT2_status-select"
                                        value="{{ $u->notification_target2 == '〇' ? 'selected' : '' }}">{{ $u->notification_target2 }}</span>
                                </td>
                                <td class="label-WT2_send" nowrap>
                                    <span class="WT2_send-select"
                                        value="{{ $u->business_notification2 == '〇' ? 'selected' : '' }}">{{ $u->business_notification2 }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            @include('common.admin.pagenation', ['objects' => $users])

        </form>

    </div>
    <script src="{{ asset('/js/admin/account/index.js') }}?date={{ date('Ymd') }}" defer></script>
@endsection
