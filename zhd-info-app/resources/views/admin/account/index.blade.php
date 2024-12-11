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
                @if (in_array('account-shop', $arrow_pages, true) || in_array('account-admin', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">3.管理</span></a>
                        <ul class="nav nav-second-level">
                            @if (in_array('account-shop', $arrow_pages, true))
                                <li class="active"><a href="/admin/account/">3-1.店舗アカウント</a></li>
                            @endif
                            @if (in_array('account-admin', $arrow_pages, true))
                                <li><a href="/admin/account/admin">3-2.本部アカウント</a></li>
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
        {{-- @livewire('admin.account-search-form')
        </form> --}}

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
                            <select name="org[{{ $organization }}]" class="form-control">
                                <option value="">全て</option>
                                @foreach ($organization_list[$organization] as $org)
                                    <option value="{{ $org->id }}"
                                        {{ request()->input('org.' . $organization) == $org->id ? 'selected' : '' }}>
                                        {{ $org->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <select name="org[{{ $organization }}]" class="form-control" disabled></select>
                        @endif
                    </div>
                @endforeach

                <div class="input-group spMb16">
                    <label class="input-group-addon">店舗</label>
                    <input type="text" name="shop_freeword" class="form-control"
                        value="{{ request()->input('shop_freeword') }}">
                </div>
                <div class="input-group col-lg-1 spMb16">
                    <input name="message_freeword" value="{{ request()->input('message_freeword') }}" class="form-control"
                        placeholder="キーワードを入力してください" />
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
                            <th class="head1" colspan="4" nowrap>DM</th>
                            <th class="head1" colspan="4" nowrap>BM</th>
                            <th class="head1" colspan="4" nowrap>AM</th>
                        </tr>
                        <tr>
                            <!-- 店舗のサブヘッダー -->
                            <th class="head1" nowrap data-column="3">ID</th>
                            <th class="head1" nowrap data-column="4">店舗名</th>
                            <!-- WowTalk1のサブヘッダー -->
                            <th class="head1" nowrap>ID</th>
                            <th class="head1" nowrap>閲覧状況通知</th>
                            <th class="head1" nowrap>業連配信通知</th>
                            <!-- WowTalk2のサブヘッダー -->
                            <th class="head2" nowrap>ID</th>
                            <th class="head2" nowrap>閲覧状況通知</th>
                            <th class="head2" nowrap>業連配信通知</th>
                            <!-- DMのサブヘッダー -->
                            <th class="head1" nowrap>ID</th>
                            <th class="head1" nowrap>氏名</th>
                            <th class="head1" nowrap>メール</th>
                            <th class="head1" nowrap>閲覧状況通知</th>
                            <!-- BMのサブヘッダー -->
                            <th class="head1" nowrap>ID</th>
                            <th class="head1" nowrap>氏名</th>
                            <th class="head1" nowrap>メール</th>
                            <th class="head1" nowrap>閲覧状況通知</th>
                            <!-- AMのサブヘッダー -->
                            <th class="head1" nowrap>ID</th>
                            <th class="head1" nowrap>氏名</th>
                            <th class="head1" nowrap>メール</th>
                            <th class="head1" nowrap>閲覧状況通知</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($users as $u)
                            <tr class="">
                                <!-- DS -->
                                <td class="label-DS" nowrap>
                                    @if(isset($organizations[$u->shop_id]['DS']))
                                        @foreach($organizations[$u->shop_id]['DS'] as $ds)
                                            {{ $ds->org3_name }}
                                        @endforeach
                                    @endif
                                </td>
                                <!-- BL -->
                                <td class="label-BL" nowrap>
                                    @if(isset($organizations[$u->shop_id]['BL']))
                                        @foreach($organizations[$u->shop_id]['BL'] as $bl)
                                            {{ $bl->org5_name }}
                                        @endforeach
                                    @endif
                                </td>
                                <!-- AR -->
                                <td class="label-AR" nowrap>
                                    @if(isset($organizations[$u->shop_id]['AR']))
                                        @foreach($organizations[$u->shop_id]['AR'] as $ar)
                                            {{ $ar->org4_name }}
                                        @endforeach
                                    @endif
                                </td>
                                <!-- 店舗 -->
                                <td class="label-shop_id" nowrap>{{ $u->shop_id }}</td>
                                <td class="label-shop_name" nowrap>{{ $u->shop_name }}</td>
                                <!-- WowTalk1 -->
                                <td class="label-WT1_id" nowrap>{{ $u->wowtalk1_id }}</td>
                                <td class="label-WT1_status" nowrap>{{ $u->notification_target1 }}</td>
                                <td class="label-WT1_send" nowrap>{{ $u->business_notification1 }}</td>
                                <!-- WowTalk2 -->
                                <td class="label-WT2_id" nowrap>{{ $u->wowtalk2_id }}</td>
                                <td class="label-WT2_status" nowrap>{{ $u->notification_target2 }}</td>
                                <td class="label-WT2_send" nowrap>{{ $u->business_notification2 }}</td>
                                <!-- DM -->
                                <td class="label-DM_id" nowrap>{{ $u->DM_id }}</td>
                                <td class="label-DM_name" nowrap>{{ $u->DM_name }}</td>
                                <td class="label-DM_email" nowrap><a href="mailto:hogehoge@hoge.jp">{{ $u->DM_email }}</a></td>
                                <td class="label-DM_view" nowrap>{{ $u->DM_view_notification }}</td>
                                <!-- BM -->
                                <td class="label-BM_id" nowrap>{{ $u->BM_id }}</td>
                                <td class="label-BM_name" nowrap>{{ $u->BM_name }}</td>
                                <td class="label-BM_email" nowrap><a href="mailto:hogehoge@hoge.jp">{{ $u->BM_email }}</a></td>
                                <td class="label-BM_view" nowrap>{{ $u->BM_view_notification }}</td>
                                <!-- AM -->
                                <td class="label-AM_id" nowrap>{{ $u->AM_id }}</td>
                                <td class="label-AM_name" nowrap>{{ $u->AM_name }}</td>
                                <td class="label-AM_email" nowrap><a href="mailto:hogehoge@hoge.jp">{{ $u->AM_email }}</a></td>
                                <td class="label-AM_view" nowrap>{{ $u->AM_view_notification }}</td>
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
