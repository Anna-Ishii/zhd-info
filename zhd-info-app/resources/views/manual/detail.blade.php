@extends('layouts.parent')

@section('content')
    <header class="header header--detail">
        <section class="header__inner flex">
            <div class="header__titleBox flex">
                <a href="{{ url()->previous() }}" class="header__prev txtCenter">
                    <img src="{{ asset('img/icon_prev.svg') }}" alt="" class="mr10 spmr4">
                    <p class="txtBold">戻る</p>
                </a>
                <section class="header__title">
                    <h1 class="txtBold txtBlue">{{ $manual->title }}</h1>
                    <time datetime="{{ $manual->start_datetime }}" class="mr8 txtBold">{{ $manual->start_datetime }}</time>
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
            <div class="main__supplement main__box--single thumb_parents flex">
                @if( in_array($manual->content_type, ['mp4', 'mov'], true ))
                <div class="main__supplement__detail flex">
                    <div class="main__thumb">
                        <img src="{{ ($manual->thumbnails_url) ? asset($manual->thumbnails_url) : asset('img/img_manual_dummy.jpg')}}" alt="">
                        <!-- 再生ボタンにしたい場合playクラスをつける -->
                        <button type="button" class="main__thumb__icon play"></button>
                    </div>
                    <p>{{ $manual->description}}</p>
                </div>
                @else
                <div class="main__supplement__detail flex">
                    <div class="main__thumb">
                        <img src="{{ asset($manual->content_url)}}" alt="">
                        <!-- 再生ボタンにしたい場合playクラスをつける -->
                        <button type="button" class="main__thumb__icon"></button>
                    </div>
                    <p>{{ $manual->description }}</p>
                </div>
                @endif

                @livewire('manual.reading-button', ['manual' => $manual])

                @if( in_array($manual->content_type, ['mp4', 'mov'], true ))
                    @if($manual->content->isEmpty())
                    <!-- 添付ファイル -->
                    <div class="manualAttachmentBg isActive"></div>
                    <div class="manualAttachment isActive">
                        <div class="manualAttachment__inner">
                            <!-- 動画の場合、スマートフォンで再生前に動画を表示できるように#t=0.1を指定 -->
                            <video controls playsinline preload autoplay class="is-paused" id="aaa">
                                <source src="{{ asset($manual->content_url) }}#t=0.1" type="video/mp4">
                            </video>
                            <button type="button" class="manualAttachment__btnPlay"><img src="{{asset('/img/btn_play.svg')}}" alt=""></button>
                            <button type="button" class="manualAttachment__close"></button>
                        </div>
                    </div>
                    @else
                    <!-- 添付ファイル -->
                    <div class="manualAttachmentBg"></div>
                    <div class="manualAttachment">
                        <div class="manualAttachment__inner">
                            <!-- 動画の場合、スマートフォンで再生前に動画を表示できるように#t=0.1を指定 -->
                            <video controls playsinline preload autoplay>
                                <source src="{{ asset($manual->content_url) }}#t=0.1" type="video/mp4">
                            </video>
                            <button type="button" class="manualAttachment__close"></button>
                        </div>
                    </div>
                    @endif
                @else
                <!-- 添付ファイル -->
                <div class="manualAttachmentBg"></div>
                <div class="manualAttachment">
                    <div class="manualAttachment__inner">
                        <img src="{{ asset($manual->content_url)}}" alt="">
                        <button type="button" class="manualAttachment__close"></button>
                    </div>
                </div>
                @endif

            </div>
            @foreach( $contents as $content )
            <section class="main__box thumb_parents">
                <h2 class="mb10">手順{{$loop->iteration}}：{{$content->title}}</h2>
                @if( in_array($content->content_type, ['mp4', 'mov'], true ))
                <div class=" flex">
                    <div class="main__thumb">
                        <img src="{{ ($content->thumbnails_url) ? asset($content->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="">
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
                        <video controls playsinline preload class="is-paused" id="aaa">
                            <source src="{{ asset($content->content_url) }}#t=0.1" type="video/mp4">
                        </video>
                        <button type="button" class="manualAttachment__btnPlay"><img src="{{asset('/img/btn_play.svg')}}" alt=""></button>
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
    @endsection