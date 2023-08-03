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
        @include('common.admin.navigation')
        @yield('content')
    </div>

    <div class="version-number">
        Ver. {{config('version.admin_version')}}
    </div>

    <script src="{{ asset('/js/edit.js') }}" defer></script>
    @livewireScripts
</body>

</html>