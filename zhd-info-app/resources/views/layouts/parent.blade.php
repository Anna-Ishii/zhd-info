<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>業務連絡一覧 | 業連・動画配信システム</title>
    <link rel="stylesheet" href="{{ asset('/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
    <!-- google fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <!-- jQuery UI -->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>  

    @livewireStyles
</head>

<body>
    @include('common.header')

    @yield('content')

    <div class="version-number">
        Ver. {{config('version.version')}}
    </div>

    <script src="{{ asset('/js/timer.js') }}" defer></script>
    <script>
        // ブラウザバックの時、リロードする
        // 既読処理のレイアウトを再描画させるため
        window.onpageshow = function(event) {
            if (event.persisted) {
                    window.location.reload();
            }
        };
    </script>
    @livewireScripts
</body>

</html>