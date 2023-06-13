<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
    <META HTTP-EQUIV="Expires" CONTENT="-1">

    <title>一覧 | 業務連絡配信</title>

    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="{{ asset('/css/metisMenu.min.css') }}" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="{{ asset('/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Custom CSS -->
    <link href="{{ asset('/css/sb-admin-2.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/style.css') }}" rel="stylesheet">

    <script src="{{ asset('/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('/js/sb-admin-2.js') }}"></script>

    <script src="{{ asset('/js/sb-admin-form.js') }}"></script>
</head>

<body>

    <div id="wrapper">
        @include('common.navigation')
        @yield('content')
    </div>
    <div id="footer" class="text-center" style="margin: 20px;">
        Powered by NSSX
    </div>
</body>

</html>