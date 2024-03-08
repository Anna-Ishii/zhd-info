<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOP | 業連・動画配信システム</title>
    <link rel="stylesheet" href="{{ asset('/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">

    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
</head>

<body>
    <header class="header header--detail">
        <section class="header__inner flex">
            <div class="header__titleBox flex">
                <a href="{{ url()->previous() }}" class="header__prev txtCenter">
                    <img src=" asset('/img/icon_prev.svg') " alt="" class="mr10 spmr4">
                    <p class="txtBold">戻る</p>
                </a>
                <section class="header__title">
                    <h1 class="txtBold txtBlue">{{ $message->title }}</h1>
                    <time datetime="{{ $message->start_datetime }}" class="mr8 txtBold">{{ $message->start_datetime }}</time>
                </section>
            </div>
            <ul class="header__menu flex">
                <li>
                    <a href="{{ asset($message->content_url)}}" download="test.pdf">
                        <img src="{{ asset('img/icon_folder_open.svg')}}" alt="">
                    </a>
                </li>
                <li>
                    <button type="button" class="btnPrint">
                        <img src="{{ asset('img/icon_print.svg')}}" alt="印刷する">
                    </button>
                </li>
            </ul>
        </section>
    </header>

    <main class="main message">
        <div class="main__supplement mb26">
            <p>{{ $message->detail }}</p>
            <p class="test"></p>
        </div>
        <div class="main__inner">
            <div class="main__fileView">
                <!-- ?file=の後を表示したいPDFのパスに変更してください -->
                <iframe src="/js/pdfjs/web/viewer.html?file={{ asset($message->content_url) }}"></iframe>
            </div>
        </div>
    </main>

    @include('common.footer')

