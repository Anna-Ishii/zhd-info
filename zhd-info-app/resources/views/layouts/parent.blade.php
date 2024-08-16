<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>業務連絡一覧 | 業連・動画配信システム</title>
    <link rel="stylesheet" href="{{ asset('/css/reset.css') }}?data=20240809">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}?date=20240809">
    @stack('css')
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

    {{-- <div class="version-number">
        Ver. {{config('version.version')}}
    </div> --}}
    <div class="modalBg"></div>
    @include('common.modal-check')
    @include('common.modal-edit')
    @include('common.modal-continue')

    @include('common.footer')

    <script src="{{ asset('/js/timer.js') }}" defer></script>
    <script src="{{ asset('/js/common.js') }}?date=20240809" defer></script>
    {{-- <script>
        const crew = @json($user->crew);
        console.log(crew);
    </script> --}}
    @livewireScripts
</body>

</html>
