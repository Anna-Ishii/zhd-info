@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                <li>
                    <a href="#" class="nav-label">1.配信</a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/message/publish/">1-1 業務連絡</a></li>
                        <li><a href="/admin/manual/publish/">1-2 動画マニュアル</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">2.データ抽出</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/analyse/personal">2-1.業務連絡の閲覧状況</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">3.管理</span></a>
                    <ul class="nav nav-second-level">
                        <li class="active"><a href="/admin/account/">3-1.アカウント</a></li>
                        <li><a href="/admin/account/admin">3-2.本部アカウント</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">4.その他</span></a>
                    <ul class="nav nav-second-level">
                        <li class="{{$is_error_ims ? 'warning' : ''}}"><a href="/admin/manage/ims">4-1.IMS連携</a></li>
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
@section('content')
<div id="page-wrapper">
    @include('common.admin.page-head',['title' => 'パスワード変更'])
    <form method="post" action="" class="form-horizontal">
        @csrf
        <div class="form-group">
            <label class="col-lg-2 control-label">現在のパスワード</label>
            <div class="col-lg-10">
                <input type="password" class="form-control" name="oldpasswd" value="" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">新しいパスワード</label>
            <div class="col-lg-10">
                <input type="password" class="form-control" name="newpasswd" value="" required="required">
            </div>
        </div>

        <div class="text-center">
            <input id="submitbutton" class="btn btn-danger" type="submit" value="登　録" />
            <a href="{{route('admin.account.index')}}" class="btn btn-default">一覧に戻る</a>
        </div>

    </form>
</div>
@endsection