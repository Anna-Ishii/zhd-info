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
        @livewire('admin.account-search-form')

        </form>

        <!-- 検索結果 -->
        <form>
            <div class="pagenation-top">
                @include('common.admin.pagenation', ['objects' => $users])
            </div>

            <div class="tableInner" style="height: 70vh;">
                <table id="list" class="table-list table table-bordered table-hover table-condensed text-center">
                    <thead>
                        {{-- <tr>
                            <th nowrap class="text-center"></th>
                            <th nowrap class="text-center">ユーザーID</th>
                            <th nowrap class="text-center">氏名</th>
                            <th nowrap class="text-center">社員番号</th>
                            <th nowrap class="text-center">所属</th>
                            <th nowrap class="text-center">メールアドレス</th>
                        </tr> --}}
                        <tr>
                            <th class="text-center" rowspan="2" nowrap></th>
                            <th class="text-center" rowspan="2" nowrap>DS</th>
                            <th class="text-center" rowspan="2" nowrap>BL</th>
                            <th class="text-center" rowspan="2" nowrap>AR</th>
                            <!-- 店舗を2つの列に分ける -->
                            <th class="text-center" colspan="2" nowrap>店舗</th>
                            <th class="text-center" colspan="3" nowrap>WowTalk1</th>
                            <th class="text-center" colspan="3" nowrap>WowTalk2</th>
                            <th class="text-center" colspan="4" nowrap>DM</th>
                            <th class="text-center" colspan="4" nowrap>BM</th>
                            <th class="text-center" colspan="4" nowrap>AM</th>
                        </tr>
                        <tr>
                            <!-- 店舗のサブヘッダー -->
                            <th class="text-center" nowrap>ID</th>
                            <th class="text-center" nowrap>店舗名</th>
                            <!-- WowTalk1のサブヘッダー -->
                            <th class="text-center" nowrap>ID</th>
                            <th class="text-center" nowrap>閲覧状況</th>
                            <th class="text-center" nowrap>業連配信</th>
                            <!-- WowTalk2のサブヘッダー -->
                            <th class="text-center" nowrap>ID</th>
                            <th class="text-center" nowrap>閲覧状況</th>
                            <th class="text-center" nowrap>業連配信</th>
                            <!-- DMのサブヘッダー -->
                            <th class="text-center" nowrap>ID</th>
                            <th class="text-center" nowrap>氏名</th>
                            <th class="text-center" nowrap>メール</th>
                            <th class="text-center" nowrap>閲覧状況通知</th>
                            <!-- BMのサブヘッダー -->
                            <th class="text-center" nowrap>ID</th>
                            <th class="text-center" nowrap>氏名</th>
                            <th class="text-center" nowrap>メール</th>
                            <th class="text-center" nowrap>閲覧状況通知</th>
                            <!-- AMのサブヘッダー -->
                            <th class="text-center" nowrap>ID</th>
                            <th class="text-center" nowrap>氏名</th>
                            <th class="text-center" nowrap>メール</th>
                            <th class="text-center" nowrap>閲覧状況通知</th>
                        </tr>
                    </thead>

                    <tbody>
                        {{-- @foreach ($users as $u)
                            <tr class="">
                                <td>
                                    <input type="checkbox" class="form-check-input">
                                </td>
                                <td class="user_id" nowrap>{{ $u->id }}</td>
                                <td nowrap>{{ $u->name }}</td>
                                <td nowrap>{{ $u->employee_code }}</td>
                                <td nowrap>{{ $u->belong_label }}</td>
                                <td nowrap><a href="mailto:hogehoge@hoge.jp">{{ $u->email }}</a></td>
                            </tr>
                        @endforeach --}}
                        @foreach ($users as $u)
                            <tr class="">
                                <td>
                                    <input type="checkbox" class="form-check-input">
                                </td>
                                <td class="label-DS" nowrap></td>
                                <td class="label-BL" nowrap></td>
                                <td class="label-AR" nowrap></td>
                                <td class="label-shop_id" nowrap>{{ $u->id }}</td>
                                <td class="label-shop_name" nowrap>{{ $u->name }}</td>
                                <td class="label-WT1_id" nowrap></td>
                                <td class="label-WT1_status" nowrap></td>
                                <td class="label-WT1_send" nowrap></td>
                                <td class="label-WT2_id" nowrap></td>
                                <td class="label-WT2_status" nowrap></td>
                                <td class="label-WT2_send" nowrap></td>
                                <td class="label-DM_id" nowrap></td>
                                <td class="label-DM_name" nowrap>{{ $u->name }}</td>
                                <td class="label-DM_email" nowrap><a href="mailto:hogehoge@hoge.jp">{{ $u->email }}</a></td>
                                <td class="label-DM_view" nowrap></td>
                                <td class="label-BM_id" nowrap></td>
                                <td class="label-BM_name" nowrap>{{ $u->name }}</td>
                                <td class="label-BM_email" nowrap><a href="mailto:hogehoge@hoge.jp">{{ $u->email }}</a></td>
                                <td class="label-BM_view" nowrap></td>
                                <td class="label-AM_id" nowrap></td>
                                <td class="label-AM_name" nowrap>{{ $u->name }}</td>
                                <td class="label-AM_email" nowrap><a href="mailto:hogehoge@hoge.jp">{{ $u->email }}</a></td>
                                <td class="label-AM_view" nowrap></td>
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
