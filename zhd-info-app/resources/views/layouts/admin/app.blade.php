<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet" />

    <!-- 全ページ共通のCSS -->
    <link href="{{ asset('/admin/css/common.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
    <!-- ページごとのCSSがここに入る -->
    @stack('styles')
    <!-- Primary Meta Tags -->
    <meta name="title" content="" />
    <meta name="description" content="" />

    <title>@yield('title', 'Z-Reporter')</title>
</head>

<body>
    {{-- ($admin, $message は子ビューから渡されます) --}}
    <x-admin.header :admin="$admin" />

    @yield('page_header')

    @yield('content')

    {{-- 共通のJSファイル --}}
    <script src="{{ asset('/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/admin/js/hamburger.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/admin/js/calendarWeekdays.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/admin/js/bbsk.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('js/admin/navigation/index.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/admin/message/publish/index.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/admin/js/bootstrap.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.1/js/extras/jquery.metadata.min.js"></script>
    <script src="{{ asset('/js/edit.js') }}?date={{ date('Ymd') }}" defer></script>
    @livewireScripts

    {{-- ページ固有のJSファイルがここに入る --}}
    @stack('scripts')
    <div id="overlay">
        <div class="cv-spinner">
            <span class="spinner"></span>
        </div>
    </div>
    </div>
</body>

</html>
