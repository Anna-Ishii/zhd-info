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
                                <li class="message-publish">
                                    <a href="{{ isset($message_saved_url) && $message_saved_url->page_name == 'message-publish' ? $message_saved_url->url : '/admin/message/publish/' }}">1-1 業務連絡</a>
                                </li>
                            @endif
                            @if (in_array('manual', $arrow_pages, true))
                                <li class="manual-publish">
                                    <a href="{{ isset($manual_saved_url) && $manual_saved_url->page_name == 'manual-publish' ? $manual_saved_url->url : '/admin/manual/publish/' }}">1-2 動画マニュアル</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (in_array('message-analyse', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">2.データ抽出</span></a>
                        <ul class="nav nav-second-level">
                            <li class="analyse-personal">
                                <a href="{{ isset($analyse_personal_saved_url) && $analyse_personal_saved_url->page_name == 'analyse-personal' ? $analyse_personal_saved_url->url : '/admin/analyse/personal/' }}">2-1.業務連絡の閲覧状況</a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if (in_array('account-shop', $arrow_pages, true) || in_array('account-admin', $arrow_pages, true) || in_array('account-mail', $arrow_pages, true) || in_array('account-admin-mail', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">3.管理</span></a>
                        <ul class="nav nav-second-level">
                            @if (in_array('account-shop', $arrow_pages, true))
                                <li><a href="/admin/account/">3-1.店舗アカウント</a></li>
                            @endif
                            @if (in_array('account-admin', $arrow_pages, true))
                                <li><a href="/admin/account/admin">3-2.本部アカウント</a></li>
                            @endif
                            @if (in_array('account-mail', $arrow_pages, true))
                                <li><a href="/admin/account/mail">3-3.DM/BM/AMメール配信設定</a></li>
                            @endif
                            @if (in_array('account-admin-mail', $arrow_pages, true))
                                <li class="active"><a href="/admin/account/adminmail">3-4.本部従業員への配信設定</a></li>
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
                            <option value="{{ base64_encode($org1->id) }}"
                                {{ request()->input('organization1') == base64_encode($org1->id) ? 'selected' : '' }}>
                                {{ $org1->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group">
                    <button class="btn btn-admin">検索</button>
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
                <table id="list" class="mail-admin-account table-list table table-bordered table-hover table-condensed text-center">

                    <thead>
                        <tr>
                            <th class="head1" nowrap>従業員番号</th>
                            <th class="head1" nowrap>氏名</th>
                            <th class="head1" nowrap>メールアドレス</th>
                            <th class="head1 head-status" nowrap>業連閲覧状況メール配信<br class="statusBreak" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm statusAllSelectBtn"
                                    data-toggle="button" aria-pressed="false"
                                    style="position: relative; z-index: 5; display: none;">
                                    すべて選択/解除
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($users as $u)
                            <tr data-id="{{ $u->id }}"
                                class="">
                                <td class="label-employee_number" nowrap>{{ $u->employee_number }}</td>
                                <td class="label-name" nowrap>{{ $u->name }}</td>
                                <td class="label-email" nowrap>{{ $u->email }}</td>
                                <td class="label-status" nowrap>
                                    <span class="status-select"
                                        value="{{ $u->status == '〇' ? 'selected' : '' }}">{{ $u->status }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            @include('common.admin.pagenation', ['objects' => $users])

        </form>

    </div>
    <script src="{{ asset('/js/admin/account/mailadminaccount/index.js') }}?date={{ date('Ymd') }}" defer></script>
@endsection
