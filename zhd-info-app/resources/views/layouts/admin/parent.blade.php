<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
    <META HTTP-EQUIV="Expires" CONTENT="-1">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>一覧 | 業務連絡配信</title>

    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('/admin/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="{{ asset('/admin/css/metisMenu.min.css') }}" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="{{ asset('/admin/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Custom CSS -->
    <link href="{{ asset('/admin/css/sb-admin-2.css') }}" rel="stylesheet">
    <link href="{{ asset('/admin/css/style.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.css" integrity="sha512-bYPO5jmStZ9WI2602V2zaivdAnbAhtfzmxnEGh9RwtlI00I9s8ulGe4oBa5XxiC6tCITJH/QG70jswBhbLkxPw==" crossorigin="anonymous" />

    <script src="{{ asset('/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/admin/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/admin/js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('/admin/js/sb-admin-2.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js" integrity="sha512-AIOTidJAcHBH2G/oZv9viEGXRqDNmfdPVPYOYKGy3fti0xIplnlgMHUGfuNRzC6FkzIo0iIxgFnr9RikFxK+sw==" crossorigin="anonymous" defer></script>

    <script src="{{ asset('/admin/js/sb-admin-form.js') }}"></script>
    @livewireStyles
</head>

<body>

    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{ route('admin.message.publish.index') }}">業連・動画配信システム</a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user"><span class="mr4">{{ $admin->name }}</span></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="{{ route('admin.setting.change_password.index') }}"><i class="fa fa-user"><span class="mr4">{{ $admin->name }}</span></i> パスワード変更</a></li>
                        <form id="logout-form" action="/admin/logout" method="post">@csrf</form>
                        <li class="logout-btn"><a><i class="fa fa-sign-out fa-fw"></i> ログアウト</a></li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->
            @yield('sideber')
        </nav>
        <script src="{{asset('js/admin/navigation/index.js')}}"></script>
        @yield('content')
    </div>

    <div id="overlay">
        <div class="cv-spinner">
            <span class="spinner"></span>
        </div>
    </div>

        <!-- モーダル・ダイアログ -->
    <div class="modal fade" id="messageImportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                    <h4 class="modal-title">業務連絡csvインポート</h4>
                </div>
                <div class="modal-body">
                    <div>
                        csvデータを業務連絡に上書きします
                    </div>
                    <div>
                        <input type="file" name="csv" accept=".csv">
                    </div>
                    <div>
                        最終取り込み日時
                    </div>
                    <div>
                        <input type="button" class="btn btn-admin" value="インポート">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('/js/edit.js') }}" defer></script>
    @livewireScripts
</body>

</html>