@extends('layouts.parent')

@push('css')
    <link href="{{ asset('/css/detail.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
@endpush

@section('title', 'マニュアル')
    @section('previous_page')
        <a href="{{{ session('current_url', route('manual.index')) }}}">マニュアル</a>
    @endsection

    @section('content')
        <header class="header header--detail">
            <section class="header__inner flex">
                <div class="header__titleBox flex">
                    <section class="header__title">
                        <h1 class="txtBold txtBlue">{{ $manual->title }}</h1>
                        <time datetime="{{ $manual->formatted_start_datetime }}" class="mr8 txtBold">{{ $manual->formatted_start_datetime }}</time>
                    </section>
                </div>
                <ul class="header__menu flex">
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

                    @if( in_array($manual->content_type, ['mp4', 'mov', 'MP4'], true ))
                        {{-- 動画 --}}
                        <div class="main__supplement__detail flex">
                            <div class="main__thumb">
                                <p class="text-content">{{ $manual->description }}</p>
                                <img src="{{ ($manual->thumbnails_url) ? asset($manual->thumbnails_url) : asset('img/img_manual_dummy.jpg')}}" alt="">
                                <!-- 再生ボタンにしたい場合playクラスをつける -->
                                <button type="button" class="main__thumb__icon play"></button>
                            </div>
                        </div>

                        <!-- 添付ファイル -->
                        {{-- クエリパラメータにautoplayがあれば自動再生 --}}
                        <div class="manualAttachmentBg {{(request()->input('autoplay')) ? 'isActive' : ''}}"></div>
                        <div class="manualAttachment {{(request()->input('autoplay')) ? 'isActive' : ''}}">
                            <div class="manualAttachment__inner">
                                <!-- ロード画面（動画再生の場合のみ） -->
                                <div class="manualAttachment__preload">
                                    <div>
                                        <p class="manualAttachment__preload__txt txtBold">読み込み中です</p>
                                    </div>
                                </div>
                                <!-- 動画の場合、スマートフォンで再生前に動画を表示できるように#t=0.1を指定 -->
                                <div class="manualAttachment__videoCover"></div>
                                <video playsinline preload class="isPaused">
                                    <source src="{{ asset($manual->content_url) }}#t=0.1" type="video/mp4">
                                </video>
                                <button type="button" class="manualAttachment__btnPlay"><img src="{{asset('/img/btn_play.svg')}}" alt=""></button>
                                <button type="button" class="manualAttachment__close"></button>
                                <!-- 操作UI（動画再生の場合のみ） -->
                                <!-- material-symbols-outlinedはgoogle fontsでアイコン読み込み -->
                                <div class="manualAttachment__ui">
                                    <div class="manualAttachment__ui__inner">
                                        <div class="manualAttachment__ui__main">
                                        <button class="manualAttachment__ui__btnPlay">
                                            <span class="material-symbols-outlined txtPlay">play_circle</span>
                                            <span class="material-symbols-outlined txtPause">stop_circle</span>
                                        </button><!-- /btnPlay -->
                                        <button class="manualAttachment__ui__btnReplay">
                                            <span class="material-symbols-outlined">replay_10</span>
                                        </button>
                                        <button class="manualAttachment__ui__btnForward">
                                            <span class="material-symbols-outlined">forward_10</span>
                                        </button>
                                        </div>

                                        <div class="manualAttachment__ui__other">
                                            <button class="manualAttachment__ui__btnFull" title="フルスクリーンモードで表示する">
                                                <span class="material-symbols-outlined txtFullScreen">fullscreen</span>
                                                <span class="material-symbols-outlined txtExitFullScreen">fullscreen_exit</span>
                                            </button>
                                            <button class="manualAttachment__ui__btnPiP" title="ピクチャインピクチャで表示する">
                                                <span class="material-symbols-outlined">picture_in_picture_alt</span>
                                            </button>
                                            <div class="manualAttachment__ui__btnPlaySpeed" title="再生速度を変更する">
                                                <span class="material-symbols-outlined">settings_slow_motion</span>
                                                <ul class="listPlaySpeed">
                                                <li data-play-speed="0.5">0.5x</li>
                                                <li data-play-speed="1.0" class="is-selected">1x</li>
                                                <li data-play-speed="1.25">1.25x</li>
                                                <li data-play-speed="1.5">1.5x</li>
                                                <li data-play-speed="2">2x</li>
                                                </ul>
                                            </div>
                                        </div><!-- /other -->

                                    </div>
                                    <div class="manualAttachment__ui__seekbarInner">
                                        <div class="manualAttachment__ui__seekbar">
                                            <div class="manualAttachment__ui__progress">
                                                <a onclick="return false;" href="#" class="manualAttachment__ui__progressDot draggable ui-widget-content" draggable="true"></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @elseif( in_array($manual->content_type, ['pdf'], true ))
                        {{-- PDF（メイン） --}}
                        <p class="text-content">{{ $manual->description }}</p>
                        <div class="pdf-swiper-container" id="pdf-container-main">
                            <div class="swiper pdf-swiper" id="pdf-swiper-main">
                                <div class="swiper-wrapper" id="pdfViewer-main"></div>
                                {{-- ナビゲーション --}}
                                <div class="swiper-button-prev pdf-prev-manual"></div>
                                <div class="swiper-button-next pdf-next-manual"></div>
                            </div>
                        </div>

                        {{-- 元コード --}}
                        {{-- <div class="main__supplement__detail">
                            <p class="text-content">{{ $manual->description }}</p>
                            <div class="pdf-container" data-url="{{ asset($manual->content_url) }}"></div>
                        </div> --}}

                    @else
                        {{-- 画像 --}}
                        <div class="main__supplement__detail">
                            <p class="text-content">{{ $manual->description }}</p>
                            <img src="{{ asset($manual->content_url)}}" alt="" style="width: 100%;">
                        </div>
                    @endif

                </div>
                @foreach( $contents as $content )
                    <section class="main__box thumb_parents">
                        <h2 class="mb10" style="font-size: xx-large;">手順{{$loop->iteration}}：{{$content->title}}</h2>

                        @if( in_array($content->content_type, ['mp4', 'mov', 'MP4'], true ))
                            {{-- 動画 --}}
                            <p class="text-content">{{ $content->description }}</p>
                            <div class=" flex">
                                <div class="main__thumb">
                                    <img src="{{ ($content->thumbnails_url) ? asset($content->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="">
                                    <!-- 再生ボタンにしたい場合playクラスをつける -->
                                    <button type="button" class="main__thumb__icon play"></button>
                                </div>
                            </div>

                            <!-- 添付ファイル -->
                            <div class="manualAttachmentBg"></div>
                            <div class="manualAttachment">
                                <div class="manualAttachment__inner">
                                    <!-- ロード画面（動画再生の場合のみ） -->
                                    <div class="manualAttachment__preload">
                                        <div>
                                            <p class="manualAttachment__preload__txt txtBold">読み込み中です</p>
                                        </div>
                                    </div>
                                    <!-- 動画の場合、スマートフォンで再生前に動画を表示できるように#t=0.1を指定 -->
                                    <div class="manualAttachment__videoCover"></div>
                                    <video playsinline preload class="isPaused">
                                        <source src="{{ asset($content->content_url) }}#t=0.1" type="video/mp4">
                                    </video>
                                    <button type="button" class="manualAttachment__btnPlay"><img src="{{asset('/img/btn_play.svg')}}" alt=""></button>
                                    <button type="button" class="manualAttachment__close"></button>
                                    <!-- 操作UI（動画再生の場合のみ） -->
                                    <!-- material-symbols-outlinedはgoogle fontsでアイコン読み込み -->
                                    <div class="manualAttachment__ui">
                                        <div class="manualAttachment__ui__inner">
                                            <div class="manualAttachment__ui__main">
                                            <button class="manualAttachment__ui__btnPlay">
                                                <span class="material-symbols-outlined txtPlay">play_circle</span>
                                                <span class="material-symbols-outlined txtPause">stop_circle</span>
                                            </button><!-- /btnPlay -->
                                            <button class="manualAttachment__ui__btnReplay">
                                                <span class="material-symbols-outlined">replay_10</span>
                                            </button>
                                            <button class="manualAttachment__ui__btnForward">
                                                <span class="material-symbols-outlined">forward_10</span>
                                            </button>
                                            </div>

                                            <div class="manualAttachment__ui__other flex">
                                                <button class="manualAttachment__ui__btnFull" title="フルスクリーンモードで表示する">
                                                    <span class="material-symbols-outlined txtFullScreen">fullscreen</span>
                                                    <span class="material-symbols-outlined txtExitFullScreen">fullscreen_exit</span>
                                                </button>
                                                <button class="manualAttachment__ui__btnPiP" title="ピクチャインピクチャで表示する">
                                                    <span class="material-symbols-outlined">picture_in_picture_alt</span>
                                                </button>
                                                <div class="manualAttachment__ui__btnPlaySpeed" title="再生速度を変更する">
                                                    <span class="material-symbols-outlined">settings_slow_motion</span>
                                                    <ul class="listPlaySpeed">
                                                    <li data-play-speed="0.5">0.5x</li>
                                                    <li data-play-speed="1.0" class="is-selected">1x</li>
                                                    <li data-play-speed="1.25">1.25x</li>
                                                    <li data-play-speed="1.5">1.5x</li>
                                                    <li data-play-speed="2">2x</li>
                                                    </ul>
                                                </div>
                                            </div><!-- /other -->
                                        </div>
                                        <div class="manualAttachment__ui__seekbarInner">
                                            <div class="manualAttachment__ui__seekbar">
                                                <div class="manualAttachment__ui__progress">
                                                    <div class="manualAttachment__ui__progressDot draggable ui-widget-content"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @elseif( in_array($content->content_type, ['pdf'], true ))
                            {{-- PDF（手順） --}}
                            <div class="pdf-swiper-container" id="pdf-container-{{ $loop->index }}">
                                <div class="swiper pdf-swiper" id="pdf-swiper-{{ $loop->index }}">
                                    <div class="swiper-wrapper" id="pdfViewer-{{ $loop->index }}"></div>
                                    {{-- ナビゲーション --}}
                                    <div class="swiper-button-prev pdf-prev-manual"></div>
                                    <div class="swiper-button-next pdf-next-manual"></div>
                                </div>
                            </div>

                            {{-- 元コード --}}
                            {{-- <p class="text-content">{{ $content->description }}</p>
                            <div class="flex">
                                <div class="pdf-container" data-url="{{ asset($content->content_url) }}"></div>
                            </div> --}}

                        @else
                            {{-- 画像 --}}
                            <p class="text-content">{{ $content->description }}</p>
                            <div class="flex">
                                <img src="{{ asset($content->content_url)}}" alt="" style="width: 100%;">
                            </div>
                            <!-- 添付ファイル -->
                            <div class="manualAttachmentBg"></div>
                            <div class="manualAttachment">
                                <div class="manualAttachment__inner">
                                    <img src="{{ asset($content->content_url)}}" alt="">
                                </div>
                            </div>
                        @endif

                    </section>
                @endforeach
            </div>
        </main>

        @include('common.footer')
        <!-- pdfjs -->
        <script src="{{ asset('/js/oldjslibrary/pdfjs-2.10.377-dist/build/pdf.js') }}"></script>
        <script src="{{ asset('/js/detail.js') }}?date={{ date('Ymd') }}" defer></script>

        <!-- Swiper JS -->
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script src="{{ asset('/js/businessContactSwiper.js')}}?date={{ date('Ymd') }}"></script>

        <!-- PDF.js 表示制御 -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", async function() {
                // === PDF.js設定 ===
                pdfjsLib.GlobalWorkerOptions.workerSrc =
                    "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js";

                // ==== (1) メインPDF ====
                @if($manual->content_type === 'pdf')
                    await renderPdf(
                        "{{ asset($manual->content_url) }}",
                        "main"
                    );
                @endif

                // ==== (2) 手順PDF群 ====
                @foreach($contents as $index => $content)
                    @if($content->content_type === 'pdf')
                        await renderPdf(
                            "{{ asset($content->content_url) }}",
                            "{{ $index }}"
                        );
                    @endif
                @endforeach

                // ==== 共通レンダリング関数 ====
                async function renderPdf(url, key) {
                    const containerId = `pdfViewer-${key}`;
                    const swiperId = `pdf-swiper-${key}`;
                    const paginationId = `page-count-${key}`;

                    const container = document.getElementById(containerId);
                    if (!container) return;

                    try {
                        const pdf = await pdfjsLib.getDocument(url).promise;
                        const numPages = pdf.numPages;

                        for (let i = 1; i <= numPages; i++) {
                            const page = await pdf.getPage(i);
                            const scale = 1.2;
                            const viewport = page.getViewport({ scale });
                            const canvas = document.createElement("canvas");
                            const context = canvas.getContext("2d");
                            canvas.width = viewport.width;
                            canvas.height = viewport.height;
                            await page.render({ canvasContext: context, viewport }).promise;

                            const slide = document.createElement("div");
                            slide.className = "swiper-slide flex flex-col items-center";

                            const pageNumEl = document.createElement("div");
                            pageNumEl.className = "pdf-page-number";
                            pageNumEl.textContent = `${i}/${numPages}`;

                            slide.appendChild(canvas);
                            slide.appendChild(pageNumEl);
                            container.appendChild(slide);
                        }

                        // SwiperをPDFごとに初期化
                        const swiper = new Swiper(`#${swiperId}`, {
                            slidesPerView: 2,
                            // slidesPerView: key === "main" ? 1 : 2,
                            slidesPerGroup: 1,
                            spaceBetween: 20,
                            loop: false,
                            navigation: {
                                nextEl: `#${swiperId} .pdf-next-manual`,
                                prevEl: `#${swiperId} .pdf-prev-manual`
                            },
                            on: {
                                init() { updatePagination(this); },
                                slideChange() { updatePagination(this); }
                            }
                        });

                        // ページ番号更新
                        function updatePagination(swiper) {
                            const totalSpreads = Math.ceil(numPages / 2);
                            const currentSpread = Math.floor(swiper.activeIndex / 1) + 1;
                            const paginationEl = document.getElementById(paginationId);
                            if (paginationEl) {
                                paginationEl.textContent = `${currentSpread}/${totalSpreads}`;
                            }
                        }

                    } catch (error) {
                        console.error("PDF読み込みエラー:", error);
                    }
                }
            });
        </script>
@endsection
