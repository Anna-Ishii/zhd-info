@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                <li>
                    <a href="#" class="nav-label">業務連絡</a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/message/publish/">配信</a></li>
                        <li style="display:none"><a href="/admin/message/manage/">管理</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">動画マニュアル</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/manual/publish/">配信</a></li>
                        <li style="display:none"><a href="/admin/manual/manage/">管理</a></li>
                    </ul>
                </li>
                <li class="nav-current-page">
                    <a href="#" class="nav-label">アカウント管理</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/account/">アカウント</a></li>
                    </ul>
                </li>
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
            <div>
                <button id="deleteBtn" class="btn btn-admin">削除</button>
                <a href="/admin/account/new" class="btn btn-admin" style="margin-left: 5px">新規登録</a>
            </div>
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