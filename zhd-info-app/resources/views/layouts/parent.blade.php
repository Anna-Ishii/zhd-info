<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>業務連絡一覧 | 業連・動画配信システム</title>
    <link rel="stylesheet" href="{{ asset('/css/reset.css') }}?date={{ date('Ymd') }}">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}?date={{ date('Ymd') }}">
    @stack('css')
    <!-- jQuery UI -->
    <link rel="stylesheet" href="{{ asset('/js/oldjslibrary/jquery-ui.css') }}">

    <script src="{{ asset('/js/oldjslibrary/jquery.min.js') }}"></script>
    <script src="{{ asset('/js/oldjslibrary/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('/js/oldjslibrary/jquery.ui.touch-punch.min.js') }}"></script>

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

    <script src="{{ asset('/js/timer.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/common.js') }}?date={{ date('Ymd') }}" defer></script>
    {{-- <script>
        const crew = @json($user->crew);
        console.log(crew);
    </script> --}}
    @livewireScripts
</body>

</html>
