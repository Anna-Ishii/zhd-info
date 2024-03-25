@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                @if(in_array('message', $arrow_pages, true) || in_array('manual', $arrow_pages, true))
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
                        <li class="{{$is_error_ims ? 'warning' : ''}}"><a href="/admin/manage/ims">4-1.IMS連携</a></li>
                    </ul>
                </li>
                @endif
                <li>
                    <a href="#" class="nav-label">Ver. {{config('version.admin_version')}}</span></a>
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
                    <tr>
                        <th nowrap class="text-center"></th>
                        <th nowrap class="text-center">ユーザーID</th>
                        <th nowrap class="text-center">氏名</th>
                        <th nowrap class="text-center">社員番号</th>
                        <th nowrap class="text-center">所属</th>
                        <th nowrap class="text-center">メールアドレス</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $u)
                    <tr class="">
                        <td>
                            <input type="checkbox" class="form-check-input">
                        </td>
                        <td class="user_id" nowrap>{{$u->id}}</td>
                        <td nowrap>{{$u->name}}</td>
                        <td nowrap>{{$u->employee_code}}</td>
                        <td nowrap>{{$u->belong_label}}</td>
                        <td nowrap><a href="mailto:hogehoge@hoge.jp">{{$u->email}}</a></td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        @include('common.admin.pagenation', ['objects' => $users])

    </form>

</div>
<script src="{{ asset('/js/admin/account/index.js') }}" defer></script>
@endsection