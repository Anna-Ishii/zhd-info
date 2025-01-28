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
                @if (in_array('account-shop', $arrow_pages, true) || in_array('account-admin', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">3.管理</span></a>
                        <ul class="nav nav-second-level">
                            @if (in_array('account-shop', $arrow_pages, true))
                                <li><a href="/admin/account/">3-1.店舗アカウント</a></li>
                            @endif
                            @if (in_array('account-admin', $arrow_pages, true))
                                <li class="active"><a href="/admin/account/admin">3-2.本部アカウント</a></li>
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
@section('content')
    <div id="page-wrapper">
        @include('common.admin.page-head', ['title' => 'パスワード変更'])
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
                <a href="{{ route('admin.account.index') }}" class="btn btn-default">一覧に戻る</a>
            </div>

        </form>
    </div>
@endsection
