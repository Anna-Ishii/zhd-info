@extends('layouts.parent')

@push('css')
<!-- detail.css -->
<script>
    // IEの判定
        var isIE = /*@cc_on!@*/false || !!document.documentMode;

        if (!isIE) {
            // IEでない場合
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = "{{ asset('/css/detail.css') }}?date={{ date('Ymd') }}";
            document.head.appendChild(link);
        } else {
            // IEの場合
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = "{{ asset('/css/iecsslibrary/detail.css') }}?date={{ date('Ymd') }}";
            document.head.appendChild(link);
        }
</script>

<link href="{{ asset('/css/phase3/business-contact.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
@endpush

@section('backUrl', session('current_url', route('message.index')))
@section('title', '業務連絡')
@section('previous_page')
<a href="{{{ session('current_url', route('message.index')) }}}">業務連絡</a>
@endsection

@section('content')

<main>
    <div class="business-contact business-contact__detail">
        <input id="manual_id" value="{{$message->id}}" hidden>
        <div class="business-contact__detail__head">
            <h2 class="business-contact__detail__head__ttl">{{ $message->title }}</h2>
            <button class="business-contact__detail__head__btn btn-print-message">
                <img src="{{ asset('img/icon_print.svg') }}" alt="印刷する">
                印刷
            </button>
        </div>

        {{-- PDF表示部分 --}}
        <div class="pdf-swiper-container">
            <div class="swiper pdf-swiper">
                <div class="swiper-wrapper" id="pdfViewer"></div>
                {{-- ナビゲーション --}}
                <div class="swiper-button-prev pdf-prev"></div>
                <div class="swiper-button-next pdf-next"></div>
            </div>
        </div>

        <div class="business-contact__recent">
            <h2 class="business-contact__recent__ttl">新着業務連絡</h2>
            <div class="swiper business-contact__recent__swiper">
                <div class="swiper-wrapper business-contact__recent__list">
                    @foreach($latest_messages as $latest_message)
                    <div class="swiper-slide">
                        <div href="#" class="business-contact__recent__item business-contact__item">
                            <div class="item__info">
                                <p class="item__ttl">{{ $latest_message->title }}</p>
                                <a href="{{ route('message.detail', $latest_message->id) }}" class="item__link">内容を確認する<img src="{{ asset('img/arrow_right.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Add navigation arrows -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
    </div>
</main>

<!-- pdfjs -->
<script>
    // IEの判定
        var isIE = /*@cc_on!@*/false || !!document.documentMode;

        if (!isIE) {
            // IEでない場合
            var script = document.createElement('script');
            script.src = "{{ asset('/js/oldjslibrary/pdfjs-2.10.377-dist/build/pdf.js') }}";
            document.body.appendChild(script);
        } else {
            // IEの場合
            var script = document.createElement('script');
            script.src = "{{ asset('/js/iejslibrary/pdfjs-2.3.200-dist/build/pdf.js') }}";
            script.onload = function() {
                pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('/js/iejslibrary/pdfjs-2.3.200-dist/build/pdf.worker.js') }}";
            };
            document.body.appendChild(script);
        }
</script>
<script src="{{ asset('/js/detail.js') }}?date={{ date('Ymd') }}" defer></script>
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="{{ asset('/js/businessContactSwiper.js')}}?date={{ date('Ymd') }}"></script>

<!-- PDF.js 表示制御 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", async function() {
        const url = "{{ asset($message->content_url) }}";
        const container = document.getElementById("pdfViewer");
        const paginationEl = document.getElementById("page-count");

        // worker設定
        // pdfjsLib.GlobalWorkerOptions.workerSrc =
        //     "{{ asset('/js/pdfjs/build/pdf.worker.js') }}";
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js";

        try {
            // PDFロード
            const pdf = await pdfjsLib.getDocument(url).promise;
            const numPages = pdf.numPages;

            // PDFロード後
            for (let i = 1; i <= numPages; i++) {
                const page = await pdf.getPage(i);
                const scale = 1.2;
                const viewport = page.getViewport({ scale });
                const canvas = document.createElement("canvas");
                const context = canvas.getContext("2d");
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                await page.render({ canvasContext: context, viewport }).promise;

                // 1ページを1スライドとして追加
                const pageNumEl = document.createElement("div");
                pageNumEl.className = "pdf-page-number";
                pageNumEl.textContent = `${i}/${numPages}`;

                // スライドにcanvasとページ番号を追加
                const slide = document.createElement("div");
                slide.className = "swiper-slide flex flex-col items-center";
                slide.appendChild(canvas);
                slide.appendChild(pageNumEl);

                container.appendChild(slide);
            }

            // Swiper 初期化
            let pdfSwiper = new Swiper(".pdf-swiper", {
                slidesPerView: getSlidesPerView(), // 向きに応じて設定
                slidesPerGroup: 1,
                spaceBetween: 20,
                loop: false,
                centeredSlides: false,
                navigation: {
                    nextEl: ".pdf-next",
                    prevEl: ".pdf-prev"
                },
                keyboard: true,
                on: {
                    init() {
                        updatePagination(this);
                    },
                    slideChange() {
                        updatePagination(this);
                    }
                }
            });

            // 向きまたは画面サイズで切り替え
            function getSlidesPerView() {
                if (window.matchMedia("(orientation: landscape)").matches) {
                    return 2; // 横向き → 2ページ
                } else {
                    return 1; // 縦向き → 1ページ
                }
            }

            // 向き変更時に再設定
            window.addEventListener("resize", () => {
                setTimeout(() => {
                    pdfSwiper.params.slidesPerView = getSlidesPerView();
                    pdfSwiper.update();
                }, 300);
            });

            // ページ番号更新処理（見開き単位）
            function updatePagination(swiper) {
                // 総見開き数（ページずつカウント）
                const totalSpreads = Math.ceil(numPages / 1);

                // 現在の見開き番号を計算
                // activeIndexは左ページのインデックス（0始まり）
                const currentSpread = Math.floor(swiper.activeIndex / 1) + 1;

                paginationEl.textContent = `${currentSpread}/${totalSpreads}`;
            }
        } catch (error) {
            console.error("PDF読み込みエラー:", error);
        }
    });
</script>
@endsection
