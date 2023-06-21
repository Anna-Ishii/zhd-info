<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>業務連絡詳細 | 業連・動画配信システム</title>
    <link rel="stylesheet" href="{{ asset('/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
</head>

<body>
    <header class="header header--detail">
        <section class="header__inner flex">
            <div class="header__titleBox flex">
                <a href="{{ route('manual.index') }}" class="header__prev txtCenter">
                    <img src="{{ asset('img/icon_prev.svg') }}" alt="" class="mr10 spmr4">
                    <p class="txtBold">戻る</p>
                </a>
                <section class="header__title">
                    <h1 class="txtBold txtBlue">{{ $manual->title }}</h1>
                    <time datetime="{{ $manual->created_at }}" class="mr8 txtBold">{{ $manual->created_at }}</time>
                </section>
            </div>
            <ul class="header__menu flex">
                <li>
                    <a href="{{ asset('img/test.pdf') }}" download="test.pdf">
                        <img src="{{ asset('img/icon_folder_open.svg') }}" alt="">
                    </a>
                </li>
                <li>
                    <button type="button" class="btnPrint">
                        <img src="{{ asset('img/icon_print.svg') }}" alt="印刷する">
                    </button>
                </li>
            </ul>
        </section>
    </header>

    <main class="main manual">
        <input id="manual_id" value="{{$manual->id}}" hidden>
        <div class="main__inner">
            <div class="main__supplement main__box--single flex">
                <div class="main__supplement__detail flex">
                    <div class="main__thumb">
                        <img src="{{ asset('img/img_manual_dummy.jpg')}}" alt="">
                        <!-- 再生ボタンにしたい場合playクラスをつける -->
                        <button type="button" class="main__thumb__icon play"></button>
                    </div>
                    <p>{{ $manual->description}}</p>
                </div>
                <div class="main__supplement__btnInner">
                    <p class="txtCenter">見た！<br class="spBlock">ボタン</p>
                    <!-- フラグが1ならisActiveを付ける -->

                    <button class="btnWatched {{ $read_flg != true ? 'isActive' : '' }}"></button>
                    <p class="txtBlue txtBold txtCenter">{{ $read_flg_count }}</p>
                </div>

                <!-- 添付ファイル -->
                <div class="manualAttachmentBg"></div>
                <div class="manualAttachment">
                    <div class="manualAttachment__inner">
                        <!-- 動画の場合、スマートフォンで再生前に動画を表示できるように#t=0.1を指定 -->
                        <video controls playsinline preload>
                            <source src="{{ asset($manual->content_url) }}#t=0.1" type="video/mp4">
                        </video>
                        <button type="button" class="manualAttachment__close"></button>
                    </div>
                </div>

            </div>
            @foreach( $contents as $content )
            <section class="main__box">
                <h2 class="mb10">手順1：{{$content->title}}</h2>
                @if($content->content_type == 'mp4')
                <div class=" flex">
                    <div class="main__thumb">
                        <img src="{{ asset('img/img_manual_dummy.jpg') }}" alt="">
                        <!-- 再生ボタンにしたい場合playクラスをつける -->
                        <button type="button" class="main__thumb__icon play"></button>
                    </div>
                    <p>{{ $content->description }}</p>
                </div>
                <!-- 添付ファイル -->
                <div class="manualAttachmentBg"></div>
                <div class="manualAttachment">
                    <div class="manualAttachment__inner">
                        <!-- 動画の場合、スマートフォンで再生前に動画を表示できるように#t=0.1を指定 -->
                        <video controls playsinline preload>
                            <source src="{{ asset($content->content_url) }}#t=0.1" type="video/mp4">
                        </video>
                        <button type="button" class="manualAttachment__close"></button>
                    </div>
                </div>
                @else
                <div class="flex">
                    <div class="main__thumb">
                        <img src="{{ asset($content->content_url)}}" alt="">
                        <!-- 再生ボタンにしたい場合playクラスをつける -->
                        <button type="button" class="main__thumb__icon"></button>
                    </div>
                    <p>{{ $content->description }}</p>
                </div>
                <!-- 添付ファイル -->
                <div class="manualAttachmentBg"></div>
                <div class="manualAttachment">
                    <div class="manualAttachment__inner">
                        <img src="{{ asset($content->content_url)}}" alt="">
                        <button type="button" class="manualAttachment__close"></button>
                    </div>
                </div>
                @endif
            </section>
            @endforeach


        </div>
    </main>

    @include('common.footer')

    <script src="{{ asset('/js/common.js') }}" defer></script>
    <script src="{{ asset('/js/detail.js') }}" defer></script>