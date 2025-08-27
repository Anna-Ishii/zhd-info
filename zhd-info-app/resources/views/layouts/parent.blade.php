<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>業務連絡一覧 | 業連・動画配信システム</title>
    <link rel="stylesheet" href="{{ asset('/css/reset.css') }}?date={{ date('Ymd') }}">

    <!-- style.css -->
    <script>
        // IEの判定
        var isIE = /*@cc_on!@*/ false || !!document.documentMode;

        if (!isIE) {
            // IEでない場合
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = "{{ asset('/css/style.css') }}?date={{ date('Ymd') }}";
            document.head.appendChild(link);
        } else {
            // IEの場合
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = "{{ asset('/css/iecsslibrary/style.css') }}?date={{ date('Ymd') }}";
            document.head.appendChild(link);
        }
    </script>

    <!-- Google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" />

    <!-- phase3 style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="{{ asset('/css/phase3/common.min.css') }}">

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

    <div class="modalBg"></div>
    @include('common.modal-check')
    @include('common.modal-edit')
    @include('common.modal-continue')
    @include('common.modal-logout')

    {{--@include('common.footer')--}}

    <script src="{{ asset('/js/timer.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/common.js') }}?date={{ date('Ymd') }}" defer></script>

    <!-- Livewire -->
    <script>
        // IEの判定
        var isIE = /*@cc_on!@*/ false || !!document.documentMode;

        if (!isIE) {
            // IEでない場合
            var script = document.createElement('script');
            script.src = "{{ asset('livewire/livewire.js') }}";
            document.body.appendChild(script);
        }
    </script>
    @stack('js')
</body>

</html>
